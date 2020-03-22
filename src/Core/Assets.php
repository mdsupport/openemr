<?php
/**
 * Core Assets provider.
 *
 * Copyright (C) 2019 MD Support <mdsupport@users.sourceforge.net>
*
* @package   OpenEMR
* @author    MD Support <mdsupport@users.sourceforge.net>
* @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Mpdf\Tag\Table;

class Assets
{
    private $assets = [];
    private $autoIncl = [];
    private $reviewed = false;
    // Inject localizations
    private $zSrc = '';
    private $zFn = '';
    // Dev Mode
    private $devAssist = true;
    private $devMsg = '';

    function __construct($vCfg = '')
    {
        if (empty($vCfg)) {
            $vCfg = "{$GLOBALS['fileroot']}/config/config.yaml";
        }
        if (is_array($vCfg)) {
            foreach ($vCfg as $strCfg) {
                $this->addConfig($strCfg);
            }
        } else {
            $this->addConfig($vCfg);
        }
        // mdsupport - inject Localization for individual scripts
        $trace = debug_backtrace();
        $calledBy = pathinfo(end($trace)['file']);
        $calledBy = sprintf(
            '%s_%s',
            substr($calledBy['dirname'], strlen($GLOBALS['webserver_root'])+1),
            str_replace('.', '_', $calledBy['filename'])
        );
        $this->zSrc = '/custom/zhdr.'.preg_replace('/[\\/\\\\]/', '.', $calledBy);
        $this->zFn = 'auto_'.preg_replace('/[\\/\\\\]/', '_', $calledBy);
    }

    public function addConfig($strCfg)
    {
        $this->assets = array_merge($this->assets, $this->getConfig($strCfg));
        $this->reviewed = false;
    }

    private function getConfig($strCfg)
    {
        try {
            // Find unique globals
            $config = file_get_contents($strCfg);
            $pattern = '/%(.*)%/';
            $matches = [];
            preg_match_all($pattern, $config, $matches);
            $matches = array_unique($matches[1]);

            // Replace by actual settings
            foreach ($matches as $match) {
                if (array_key_exists($match, $GLOBALS)) {
                    $config = str_replace("%$match%", $GLOBALS[$match], $config);
                }
            }
            $config = Yaml::parse($config);

            // Validate, Transform each asset
            foreach ($config['assets'] as $pkg => $assetConfigEntry) {
                $config['assets'][$pkg] = $this->mapConfigEntry($assetConfigEntry);
            }
            return $config['assets'];
        } catch (ParseException $e) {
            error_log($e->getMessage());
        }
    }

    private function mapConfigEntry($assetConfigEntry)
    {
        // Allow rtl sessions to override or add settings
        if ((!empty($_SESSION['language_direction'])) && ($_SESSION['language_direction'] == 'rtl') && (!empty($assetConfigEntry['rtl']))) {
            $rtl = $assetConfigEntry['rtl'];
            unset($assetConfigEntry['rtl']);
            $assetConfigEntry = array_merge($assetConfigEntry, $rtl);
        }

        $cache_sfx = '?v='.$GLOBALS['v_js_includes'];
        foreach (['script', 'link'] as $tag) {
            if (!empty($assetConfigEntry[$tag])) {
                if (is_string($assetConfigEntry[$tag])) {
                    $assetConfigEntry[$tag] = [$assetConfigEntry[$tag]];
                }
                foreach ($assetConfigEntry[$tag] as $ix => $basename) {
                    if ((empty($assetConfigEntry['alreadyBuilt'])) || (!$assetConfigEntry['alreadyBuilt'])) {
                        $assetConfigEntry[$tag][$ix] = $assetConfigEntry['basePath'].$basename;
                        $assetConfigEntry['cache_sfx'] = $cache_sfx;
                    } else {
                        $assetConfigEntry['cache_sfx'] = '';
                    }
                }
            }
        }
        return $assetConfigEntry;
    }

    // Since scripts are permitted to add entries, must call this method before any output
    // For now, limited to creating list of autoload packages
    private function reviewAssetEntries()
    {
        // mdsupport - Append zSrc entries
        foreach (['link' => '.css', 'script' => '.js'] as $tag => $ext) {
            if (file_exists($GLOBALS['webserver_root'].$this->zSrc.$ext)) {
                $this->assets['zsrc']['autoload'] = true;
                $this->assets['zsrc'][$tag] = [$GLOBALS['webroot'].$this->zSrc.$ext];
            }
        }
        $assets = $this->assets;
        foreach ($assets as $pkg => $assetConfigEntry) {
            if (!empty($assetConfigEntry['autoload']) && $assetConfigEntry['autoload']) {
                $this->autoIncl[$pkg] = true;
            }
        }
        $this->reviewed = true;
    }

    // TBD : Implement asset dependencies here
    private function selectAssets($reqAssets, $exclAssets, $inclAuto)
    {
        if (!$this->reviewed) $this->reviewAssetEntries();
        $assets = $this->assets;
        if (!is_array($reqAssets)) {
            // Remove spaces and accept ',' delimited assets
            $reqAssets = str_replace(' ', '', $reqAssets);
            $reqAssets = explode(',', $reqAssets);
        }
        if ($inclAuto) {
            $reqAssets = array_keys(array_merge($this->autoIncl, array_flip($reqAssets)));
        }
        $assets = array_intersect_key($assets, array_flip($reqAssets));
        $assets = array_diff_key($assets, array_flip($exclAssets));
        return $assets;
    }

    public function getLinkTags($reqAssets = [], $exclAssets = [], $inclAuto = true)
    {
        $assets = $this->selectAssets($reqAssets, $exclAssets, $inclAuto);
        $strHtm = '';
        if ($this->devAssist) {
            $strHtm .= sprintf('<!-- %s : %s.css -->%s', xlt('Local'), $this->zSrc, PHP_EOL);
        }
        foreach ($assets as $asset) {
            if (!empty($asset['link'])) {
                foreach ($asset['link'] as $cssfile) {
                    $strHtm .= sprintf(
                        '<link rel="stylesheet" href="%s%s">%s',
                        $cssfile, $asset['cache_sfx'], PHP_EOL
                    );
                }
            }
        }
        return $strHtm;
    }

    public function getScriptTags($reqAssets = [], $exclAssets = [], $inclAuto = true)
    {
        $assets = $this->selectAssets($reqAssets, $exclAssets, $inclAuto);
        $strHtm = '';
        if ($this->devAssist) {
            $strHtm .= sprintf('<!-- %s : %s.js / %s / %s -->%s', xlt('Local'), $this->zSrc, $this->zFn, $this->devMsg, PHP_EOL);
        }
        foreach ($assets as $assetid => $asset) {
            if (!empty($asset['script'])) {
                foreach ($asset['script'] as $jsfile) {
                    if ((!empty($asset['defer'])) && ($asset['defer'])) {
                        $defer = 'defer';
                    } else {
                        $defer = '';
                    }
                    $strHtm .= sprintf(
                        '<script src="%s?v=%s" class="%s" %s></script>%s',
                        $jsfile, $asset['cache_sfx'], $assetid, $defer, PHP_EOL
                    );
                }
            }
        }

        // mdsupport - Inject local coode after the standard
        $autoload = array_intersect_key($this->assets, $this->autoIncl);
        $autofn = $this->zFn;
        $strHtm .= sprintf(
'<script>
    var included_assets = %s;
    var autoload_assets = %s;
    $(document).ready(function() {
        if (typeof top.%s === "function") {
            top.%s($);
        } else if (typeof window.parent.%s === "function") {
            window.parent.%s($);
        }
    });
</script>',
            json_encode($assets), json_encode($autoload), $autofn, $autofn, $autofn, $autofn
        );

        return $strHtm;
    }

    /**
     * Helper function to provide standardized begining of dom document.
     * If standard header class exposes its method at later time, use that.
     * 
     * @param array $attr - Currently accepts following<br>
     *              title - Title tag<br>
     *         assets_inc - Array of asset keys for inclusion<br>
     *         assets_exc - Array of asset keys for exclusion<br>
     *        assets_auto - set to false if autoload assets setting should be ignored (not loaded). 
     * @return string strHtm - html to be printed by caller
     */
    public function domdocBegin($attr = [])
    {
        // Attribute defaults
        $attr = array_merge(['assets_inc' => [], 'assets_exc' => [], 'assets_auto' => true], $attr);

        // Use language devobject if available
        $iso639_2 = 'eng';
        if (isset($_SESSION['language_choice'])) {
            // No language object or methods
            $lang = sqlQuery(
                'SELECT lo.notes iso639_2 FROM list_options lo
                WHERE lo.list_id=? and option_id=?',
                ['language', $_SESSION['language_choice']]);
            if ($lang) {
                $iso639_2 = (strlen(trim($lang['iso639_2'])) > 0 ? $lang['iso639_2'] : $_SESSION['language_choice']);
            }
        }
        $strHtm = sprintf(
            '<!doctype html>
            <html lang="%s">
              <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">',
            attr($iso639_2)
        );

        if (isset($attr['title'])) {
            $strHtm .= sprintf('<title>%s</title>', xlt($attr['title']));
        }

        if (file_exists($GLOBALS['fileroot'].'/custom/assets/custom.yaml')) {
            $this->addConfig($GLOBALS['fileroot'].'/custom/assets/custom.yaml');
        }

        // Maintaining old style output
        $strHtm .= $this->getLinkTags($attr['assets_inc'], $attr['assets_exc'], $attr['assets_auto'])."\n";
        $strHtm .= $this->getScriptTags($attr['assets_inc'], $attr['assets_exc'], $attr['assets_auto'])."\n";

        return $strHtm;
    }
}

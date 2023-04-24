<?php

/**
 * OpenEMR <https://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

/**
 * Class OeScripts.
 *
 * Helper class to generate multiple types of `<script>` elements.
 * Output from this class instances in Command-line scripts is non-functional.
 *
 * @package OpenEMR
 * @subpackage OeUI
 * @author MD Support<mdsupport@users.sf.net>
 * @copyright Copyright (c) 2017-2023 MD Support
 */
class OeScripts
{
    private $scripts = [];
    
    public function __construct($aScripts=[], $chksrcjs=true) {
        // Convert string to single element array
        if (!is_array($aScripts)) {
            $aScripts = [$aScripts];
        }
        foreach ($aScripts as $aScript) {
            $this->add($aScript);
        }
        if ($chksrcjs) {
            $osjs = sprintf('%s.js', explode(".", $_SERVER['SCRIPT_FILENAME'], -1)[0]);
            $this->add($osjs);
        }
    }
    
    public function add($tagAttrs) {
        if (!is_array($tagAttrs)) {
            $tagAttrs = ['os_src' => $tagAttrs];
        }
        $objScr = new \SplFileInfo($tagAttrs['os_src']);
        if (!$objScr->isFile()) {
            return false;
        }
        $os_src_hash = hash('crc32', $tagAttrs['os_src']);
        // Manage link
        $src_symlink = sprintf(
            '/cache/%s_%s.js',
            $os_src_hash, $objScr->getMTime()
        );
        
        if (!file_exists($GLOBALS['OE_SITE_DIR'].$src_symlink)) {
            // Unlink existing symlinks
            $xsymlinks = sprintf(
                '%s/cache/%s_*.js',
                $GLOBALS['OE_SITE_DIR'], $os_src_hash
            );
            $symlinks = glob($xsymlinks);
            array_map('unlink', $symlinks);
            // Create new symlink
            symlink($tagAttrs['os_src'], $GLOBALS['OE_SITE_DIR'].$src_symlink);
        }

        $tagDefaults = [
            'async' => null,
            'defer' => null,
            'type' => null,
            'src' => ($GLOBALS['OE_SITE_WEBROOT'].$src_symlink),
        ];
        $tagAttrs = array_merge($tagDefaults, $tagAttrs);
        $this->scripts[$tagAttrs['src']] = $tagAttrs;
    }

    public function html() {
        $strHtml = '';
        foreach ($this->scripts as $src => $attr) {
            $strHtml .= sprintf(
                '<script %s src="%s" %s %s></script>%s',
                (empty($attr['type']) ? '' : 'type="'.$attr['type'].'"'),
                $src,
                (empty($attr['async']) ? '' : 'async'),
                (empty($attr['defer']) ? '' : 'defer'),
                PHP_EOL,
            );
        }
        return $strHtml;
    }

    public function injectBefore($htm, $tgt) {
        $htmScripts = $this->html();
        $lastTgtStart = strrpos($htm, $tgt);
        if ($lastTag !== false) {
            $htm = substr_replace($htm, ($htmScripts.$tgt), $lastTgtStart, strlen($tgt));
        }
        return $htm;
    }
}

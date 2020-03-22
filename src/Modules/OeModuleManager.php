<?php

/**
 * OeModuleManager class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    MD Support <mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2020 MD Support <mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules;

class OeModuleManager extends OeModule
{
    function __construct() {
        $this->setProp([
            'Name' => 'Modules Manager',
            'Desc' => 'Install and maintain EMR Modules',
            'boolActive' => true,
            'Version' => ['major' => 1, 'minor' => 0]
        ]);
        $this->setPropSub('Tags', ['manage', 'module', 'install', 'configure', 'enable', 'disable']);
        // Module should come pre-installed.
        if (($this->getModuleRegProps())) {
            $this->getModuleCfg();
        } else {
            $this->actionInstall();
        }
    }

    // ModuleManager will self install and enble so actionInstall is not available to objects.
    protected function actionInstall() {
        if ($this->getModuleRegProps()) return true;
        $modPropInit = [
            'mod_active' => 1,
            'mod_description' => $this->getProp('Desc'),
            'sql_run' => 1,
        ];
        $this->setModuleRegProps($modPropInit);
        // Sample single editable field setup
        $cbZend = [
            'om_cfg_key' => 'show_zend',
            'om_cfg_value' => true,
            'disp_lbl' => 'Show zhModules',
            'disp_type' =>'cb',
        ];
        $this->setModuleCfg($cbZend, true);
    }

    public function actionConfig() {
        // Module currently has no configurable settings.
        return true;
    }

    public function actionEnable() {
        $modPropInit = [
            'mod_active' => 1,
        ];
        return $this->setModuleRegProps($modPropInit);
    }

    public function actionUpgrade() {
        // Currently ModuleManager has no upgrade actions.
        return true;
    }

    // ModuleManager is needed to manage modules so it is not available to objects.
    protected function actionDisable() {
        $modPropInit = [
            'mod_active' => 0,
        ];
        return $this->setModuleRegProps($modPropInit);
    }

    public function getProp($strProp) {
        // ModuleManager exposes all properties to public.
        return $this->modProp[$strProp];
    }

    public function getConfig() {
        // ModuleManager exposes all properties to public.
        return $this->modProp;
    }

    public function getAllModules() {
        // Build list of modules and their properties
        $aaMods = [];

        // Composer based modules
        $strMatch = "OpenEMR\\Modules\\";
        $szMatch = strlen($strMatch);
        // Do not use require_once
        $classmap = require($GLOBALS['vendor_dir']."/composer/autoload_classmap.php");
        foreach ($classmap as $compCls => $compClsPath) {
            if (substr($compCls, 0, $szMatch) !== $strMatch) continue;
            $objChk = new \ReflectionClass($compCls);
            if ($objChk->isAbstract()) continue;
            $objMod = new $compCls;
            if ((is_object($objMod)) && (method_exists($objMod, 'actionInstall'))) {
                $aaVer = $objMod->getProp('Version');
                $aaMods[$compCls] = [
                    'Name' => $objMod->getProp('Name'),
                    'Desc' => $objMod->getProp('Desc'),
                    'Version' => $aaVer['major'].".".$aaVer['minor'],
                    'type' => 'OeModule',
                ];
            }
        }

        // Provide courtsey links for zend and custom modules
        // @TBD - Check show_zend setting if ui support is provided
        $strModDir = $GLOBALS['fileroot']."/interface/modules";
        foreach (['zhModule' => 'zend_modules/module', 'zhcModule' => 'custom_modules'] as $modType => $modDirPfx) {
            $modDir = sprintf('%s/%s', $strModDir, $modDirPfx);
            $modDir = new \DirectoryIterator($modDir);
            foreach ($modDir as $dirInfo) {
                if ($dirInfo->isDir() && !$dirInfo->isDot()) {
                    $aaMods[$dirInfo->getFilename()] = [
                        'Name' => $dirInfo->getFilename(),
                        'Desc' => $dirInfo->getFilename(),
                        'Version' => '',
                        'type' => $modType,
                    ];
                }
            }
        }

        ksort($aaMods);
        $this->modProp['modules'] = $aaMods;
        return $this->modProp['modules'];
    }
}

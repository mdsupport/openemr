<?php

/**
 * OeModule class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    MD Support <mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2020 MD Support <mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules;

abstract class OeModule
{
    /*
     * Implement following map to legacy modules table
     * om_class (mod_name) : Class name (e.g. OeModuleManager)
     * om_class_use (mod_directory) : Autoload class key (e.g. OpenEMR\Modules\OeModuleManager)
     * om_name (mod_ui_name) : Display short name published by Module (e.g. 'Module Manager')
     *
     * Remove mapping after adding columns used for OeModule
     */
    static $DBREC_MOD = [
        'om_class' => 'mod_name',           // php class
        'om_class_use' => 'mod_directory',      // php class including namespace
        'om_name' => 'mod_ui_name',         // Display name as published by module
    ];

    /*
     * Implement following map to legacy modules_settings table
     * Expected to provide details on how om_key values should be handled.
     * om_key (obj_name) : Settings key typically matches om_key (e.g. connection_type)
     * om_value (path) : Json encoded value of the setting
     *
     * Remove mapping after adding columns used for OeModule
     */
    static $DBREC_MODSET = [
        'om_key' => 'obj_name',         // Configuration key
        'om_value' => 'path',           // Json encoded maintenance settings
    ];

    /*
     * Implement following map to legacy module_configuration table
     * om_key (field_name) : Settings key (e.g. connection_type)
     * om_value (field_value) : Value of the setting
     *
     * Remove mapping after adding columns used for OeModule
     */
    static $DBREC_MODCFG = [
        'om_key' => 'field_name',         // Configuration key
        'om_value' => 'field_value',      // Configured value
    ];

    // Every module should at minimum specify the properties included in $modProp.
    // Upcased keys represent printable strings.  Others are internal keys or db columns.
    protected $modProp = [
        // Friendly name for listing in installer
        'Name' => '',
        // Short Description for listing in installer
        'Desc' => '',
        // Active status
        'boolActive' => false,
        // Version
        'Version' => [
            'major' => 0,
            'minor' => 0
        ],
        // Tags - used for appropriate search results about module
        'Tags' => []
    ];

    // Force Extending class to define following methods

    /**
     * Get public properties of object
     * @param string or array $vPropKeys
     * @Return string or associative array of properties for requested keys.
     */
    abstract protected function getProp($vPropKeys);

    // Every module must be able to install, config, enable, upgrade and disable itself.
    // Uninstall actions are outside the scope of this class.

    /**
     * Execute actions related to installationen of this module.
     */
    abstract protected function actionInstall();

    /**
     * Execute actions related to configuration of this module..
     */
    abstract protected function actionConfig();

    /**
     * Execute actions related to enabling of this module.
     */
    abstract protected function actionEnable();

    /**
     * Execute actions related to version upgrade of this module.
     */
    abstract protected function actionUpgrade();

    /**
     * Execute actions related to disabling of this module.
     */
    abstract protected function actionDisable();

    /**
     * Public configuration details of this module.
     */
    abstract protected function getConfig();

    // Common methods

    /**
     * Set protected properties of object
     * @param string/array $vPropKey string or associative array containing (key => value)
     * @param string $vPropValue  (Optional, ignored if $vPropKey is associative array)
     * @Return $vPropValue or true
     */
    protected function setProp($vPropKey, $vPropValue=true) {
        if (is_array($vPropKey)) {
            foreach ($vPropKey as $pkey => $pValue) {
                $this->setProp($pkey, $pValue);
            }
        } else {
            $this->modProp[$vPropKey] = $vPropValue;
        }
        return $vPropValue;
    }

    /**
     * Set protected sub-properties of object (convenience function for managing Tags)
     * @param string $strPropKey string key
     * @param array $propValues Values to be inserted
     * @Return int Resulting number of entries.
     */
    protected function setPropSub($strPropKey, $propValues) {
        if (empty($propValues)) return false;
        if (!array_key_exists($strPropKey, $this->modProp)) $this->modProp[$strPropKey] = [];
        if (is_array($propValues)) {
            foreach ($propValues as $propValue) {
                $this->modProp[$strPropKey][] = $propValue;
            }
        } else {
            $this->modProp[$strPropKey][]= $propValues;
        }
        return count($this->modProp[$strPropKey]);
    }

    /**
     * Get properties if module is registered.
     * Note - records for zhModules should not be processed by this method.
     * @Return array - modules table record associated with module.
     */
    protected function getModuleRegProps($cache_ok=true) {
        $db_modules = $this->getProp('db_modules');
        if ((!empty($db_modules)) && $cache_ok) return $db_modules;

        // Map until db changes are done
        $mappedCols = '';
        foreach (OeModule::$DBREC_MOD as $ocol => $mcol) {
            $mappedCols .= ", $mcol as $ocol";
        }

        $db_modules = sqlQuery(
            "SELECT * $mappedCols from modules
            WHERE mod_type=? and mod_directory=?",
            ['OeModule', get_class($this)]
        );
        if ($db_modules) {
            $this->setProp('db_modules', $db_modules);
        }
        return $db_modules;
    }

    protected function setModuleRegProps($aaProps=[]) {
        $this_class = get_class($this);
        $recDefaults = [
            'mod_type' => 'OeModule',
            'directory' => '',
            'type' => 2,
            'date' => date("Y-m-d"),
            'om_class_use' => $this_class,
            'om_class' => end(explode("\\", $this_class)),
            'om_name' => $this->getProp('Name'),
        ];
        $aaProps = (empty($aaProps) ? $recDefaults : array_merge($recDefaults, $aaProps));
        // Map until db changes are done
        foreach (OeModule::$DBREC_MOD as $ocol => $mcol) {
            $aaProps[$mcol] = $aaProps[$ocol];
            unset($aaProps[$ocol]);
        }

        $db_modules = $this->getProp('db_modules');
        $sql_up = (empty($db_modules) ? 'INSERT INTO' : 'UPDATE'). ' modules SET ';
        $sql_bind = [];
        foreach ($aaProps as $dbCol => $dbVal) {
            $sql_up .= (count($sql_bind)==0 ? '':',').$dbCol.'=?';
            array_push($sql_bind, $dbVal);
        }

        if (!empty($db_modules)) {
            $sql_up .= ' WHERE mod_id=?';
            array_push($sql_bind, $db_modules['mod_id']);
        }
        $db_modules = sqlStatement($sql_up, $sql_bind);
        if ($db_modules) {
            $db_modules = $this->getModuleRegProps(false);
        }
        return $db_modules;
    }

    /**
     * Get settings for the module.
     * @Return array - modules_settings table records associated with module.
     */
    protected function getModuleSettings($cache_ok=true) {
        $db_settings = $this->getProp('db_settings');
        if ((!empty($db_settings)) && $cache_ok) return $db_settings;

        // Map until db changes are done
        $mappedCols = '';
        foreach (OeModule::$DBREC_MODSET as $ocol => $mcol) {
            $mappedCols .= ", $mcol as $ocol";
        }

        $db_settings = [];
        $modProps = $this->getModuleRegProps();
        $rsSettings = sqlStatement(
            "SELECT mod_id $mappedCols from modules_settings WHERE mod_id=?",
            [$modProps['mod_id']]
            );
        if (!$db_settings) {
            while ($recSettings = sqlFetchArray($rsSettings)) {
                $aaValues = json_decode($recSettings['om_value'], true);
                $db_settings[$recSettings['om_key']] =
                (json_last_error()==JSON_ERROR_NONE ? $aaValues : $recSettings['om_value']);
            }
            $this->setProp('db_settings', $db_settings);
        }
        return $db_settings;
    }

    /**
     * Save settings in modules_settings table
     * @param array $aSettings - array of records using keys for DBREC_MODSET
     * @param boolean $allSettings - If true, module configuration is deleted first.
     * @return array - array of all configured settings.
     */
    protected function setModuleSettings($aSettings, $allSettings=false) {
        $db_modules = $this->getProp('db_modules');
        // Must have valid registered module for any settings.
        if (!$db_modules) return false;

        // Provide ability to clean the slate
        $this_mod_id = $db_modules['mod_id'];
        if ($allSettings) {
            sqlStatement('DELETE from modules_settings WHERE mod_id=?', [$this_mod_id]);
        }

        // Module is expected to provide array of arrays.
        if (!empty($aSettings['om_key'])) {
            // Handle situation when a single record is provided.
            $aSettings = [$aSettings];
        }

        // Process all settings records
        $db_settings = $this->getProp('db_settings');
        foreach ($aSettings as $aaSetting) {
            // Map until db changes are done
            $cols = [];
            foreach (OeModule::$DBREC_MODSET as $ocol => $mcol) {
                $cols[$mcol] = $aaSetting[$ocol];
                unset($aaSetting[$ocol]);
            }
            // Remaining elements are combined into om_value map
            if (count($aaSetting) > 0) {
                $json = json_encode($aaSetting);
                $cols[OeModule::$DBREC_MODSET['om_value']] =
                (json_last_error()==JSON_ERROR_NONE ? $json : print_r($aaSetting, true));
            }

            $sql_up = '';
            $sql_bind = [];
            foreach ($cols as $dbCol => $dbVal) {
                $sql_up .= (count($sql_bind)==0 ? '':',').$dbCol.'=?';
                array_push($sql_bind, $dbVal);
            }
            $db_setting = $db_settings[$cols[OeModule::$DBREC_MODSET['om_key']]];
            if (empty($db_setting)) {
                array_push($sql_bind, $this_mod_id);
                $sql_up = sprintf('INSERT INTO modules_settings SET %s, mod_id=?', $sql_up);
            } else {
                array_push($sql_bind, $this_mod_id, $cols[OeModule::$DBREC_MODSET['om_key']]);
                $sql_up = sprintf('UPDATE modules_settings SET %s WHERE mod_id=? and obj_name=?', $sql_up);
            }
            sqlStatement($sql_up, $sql_bind);
        }
        
        $db_settings = $this->getModuleRegProps(false);
        return $db_settings;
    }

    /**
     * Get configured values for module.
     * @Return array - module_configuration table records associated with module.
     */
    protected function getModuleCfg($cache_ok=true) {
        $db_cfg = $this->getProp('db_cfg');
        if ((!empty($db_cfg)) && $cache_ok) return $db_cfg;

        // Map until db changes are done
        $mappedCols = '';
        foreach (OeModule::$DBREC_MODCFG as $ocol => $mcol) {
            $mappedCols .= ", $mcol as $ocol";
        }

        $db_cfg = [];
        $modProps = $this->getModuleRegProps();
        $rsCfg = sqlStatement(
            "SELECT module_id $mappedCols from module_configuration WHERE module_id=?",
            [$modProps['mod_id']]
            );
        if ($rsCfg) {
            while ($recCfg = sqlFetchArray($rsCfg)) {
                $db_cfg[$recCfg['om_key']] = $recCfg['om_value'];
            }
            $this->setProp('db_cfg', $db_cfg);
        }
        return $db_cfg;
    }

    /**
     * Set configuration for a module - saved in module_configuration table
     * @param array $aCfgs - array of records using keys for DBREC_MODCFG
     * @param boolean $allSettings - If true, module configuration is deleted first.
     * @return array - array of all configured settings.
     */
    protected function setModuleCfg($aCfgs, $allSettings=false) {
        $db_modules = $this->getProp('db_modules');
        // Must have valid registered module for any settings.
        if (!$db_modules) return false;

        // Provide ability to clean the slate
        $this_mod_id = $db_modules['mod_id'];
        if ($allSettings) {
            sqlStatement('DELETE from module_configuration WHERE module_id=?', [$this_mod_id]);
        }

        // Module is expected to provide array of arrays.
        if (!empty($aCfgs['om_key'])) {
            // Handle situation when a single record is provided.
            $aCfgs = [$aCfgs];
        }

        // Process all setting records
        $db_cfgs = $this->getProp('db_cfg');
        foreach ($aCfgs as $aaCfg) {
            // Map until db changes are done
            $cols = [];
            foreach (OeModule::$DBREC_MODCFG as $ocol => $mcol) {
                $cols[$mcol] = $aaCfg[$ocol];
                unset($aaCfg[$ocol]);
            }

            $sql_up = '';
            $sql_bind = [];
            foreach ($cols as $dbCol => $dbVal) {
                $sql_up .= (count($sql_bind)==0 ? '':',').$dbCol.'=?';
                array_push($sql_bind, $dbVal);
            }
            $db_cfg = $db_cfgs[$cols['field_name']];
            if (empty($db_cfg)) {
                array_push($sql_bind, $this_mod_id);
                $sql_up = sprintf('INSERT INTO module_configuration SET %s, module_id=?', $sql_up);
            } else {
                array_push($sql_bind, $this_mod_id, $cols['field_name']);
                $sql_up = sprintf('UPDATE module_configuration SET %s WHERE module_id=? and field_name=?', $sql_up);
            }
            sqlStatement($sql_up, $sql_bind);
        }

        $db_cfgs = $this->getModuleCfg(false);
        return $db_cfgs;
    }

    /**
     * Check active status of a module.
     *
     * @return boolean Status indicator
     */
    public function isActive() {
        return $this->getProp('boolActive');
    }
}

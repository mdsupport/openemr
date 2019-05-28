<?php
/**
 * Hyperauth config
 */
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Hybridauth\Hybridauth;

function localAuthSvcs($resetConnections = false)
{
    $strCfg = "{$GLOBALS['fileroot']}/config/hybridauth_providers.yaml";
    $hauth_config = file_get_contents($strCfg);
    // if needed, replace strings here
    $hauth_config = Yaml::parse($hauth_config);
    foreach ($hauth_config['providers'] as $key => $entry) {
        if ((!(isset($entry['enabled']))) || (!($entry['enabled']))) {
            unset($hauth_config['providers'][$key]);
        }
    }
    $objAuthSvcs = new Hybridauth($hauth_config);
    if ($resetConnections) {
        $objAuthSvcs->disconnectAllAdapters();
    }
    return $objAuthSvcs;
}

function mapConnectedUser()
{
    
}
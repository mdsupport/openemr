<?php

use \OpenEMR\Events\Globals\GlobalsInitializedEvent;

use \OpenEMR\Menu\MenuEvent;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

function OmManager_add_menu_item(MenuEvent $event)
{
    $menu = $event->getMenu();

    $recMod = sqlQuery('SELECT directory FROM modules WHERE mod_directory=?', ['OpenEMR\Modules\OeModuleManager']);
    if (empty($recMod['directory'])) return $event;

    $menuItem = new stdClass();
    $menuItem->requirement = 0;
    $menuItem->target = 'mod';
    $menuItem->menu_id = 'mod0';
    $menuItem->label = xlt("OeModule Manager");
    $menuItem->url = $recMod['directory'].'/OmManager.php';
    $menuItem->children = [];
    $menuItem->acl_req = ["admin", "super"];
    $menuItem->global_req = [];

    foreach ($menu as $topmenu) {
        if ($topmenu->menu_id !== 'admimg') continue;
        foreach ($topmenu->children as $lblItem) {
            if ($lblItem->label !== 'Other') continue;
            $lblItem->label = xlt("Optional Features");
            $lblItem->children[] = $menuItem;
            break;
        }
    }

    $event->setMenu($menu);

    return $event;
}

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var array                    $module
 * @global                       $eventDispatcher @see ModulesApplication::loadCustomModule
 * @global                       $module          @see ModulesApplication::loadCustomModule
 */
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'OmManager_add_menu_item');

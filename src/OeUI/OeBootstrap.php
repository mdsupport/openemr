<?php

/**
 * Phase 2: Bootstrap Framework Components
 * 
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2025 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

/**
 * Bootstrap Framework Initialization
 * Sets default attributes for Bootstrap components
 */
class OeBootstrap {
    
    public static function initialize($version = '5.3.0') {
        // Button defaults
        OeTag::setOeTagDefault('button', [
            'type' => 'button',
            'class' => 'btn'
        ]);
        
        // Form defaults
        OeTag::setOeTagDefault('input', [
            'class' => 'form-control'
        ]);
        
        OeTag::setOeTagDefault('textarea', [
            'class' => 'form-control'
        ]);
        
        OeTag::setOeTagDefault('select', [
            'class' => 'form-select'
        ]);
        
        OeTag::setOeTagDefault('label', [
            'class' => 'form-label'
        ]);
        
        // Table defaults
        OeTag::setOeTagDefault('table', [
            'class' => 'table'
        ]);
        
        // Alert defaults
        OeTag::setOeTagDefault('div.alert', [
            'class' => 'alert',
            'role' => 'alert'
        ]);
        
        // Card defaults
        OeTag::setOeTagDefault('div.card', [
            'class' => 'card'
        ]);
        
        OeTag::setOeTagDefault('div.card-header', [
            'class' => 'card-header'
        ]);
        
        OeTag::setOeTagDefault('div.card-body', [
            'class' => 'card-body'
        ]);
        
        OeTag::setOeTagDefault('div.card-footer', [
            'class' => 'card-footer'
        ]);
        
        // Modal defaults
        OeTag::setOeTagDefault('div.modal', [
            'class' => 'modal fade',
            'tabindex' => '-1'
        ]);
        
        OeTag::setOeTagDefault('div.modal-dialog', [
            'class' => 'modal-dialog'
        ]);
        
        OeTag::setOeTagDefault('div.modal-content', [
            'class' => 'modal-content'
        ]);
        
        // Navigation defaults
        OeTag::setOeTagDefault('nav', [
            'class' => 'navbar'
        ]);
        
        OeTag::setOeTagDefault('ul.nav', [
            'class' => 'nav'
        ]);
        
        OeTag::setOeTagDefault('li.nav-item', [
            'class' => 'nav-item'
        ]);
        
        OeTag::setOeTagDefault('a.nav-link', [
            'class' => 'nav-link'
        ]);
        
        // Container defaults
        OeTag::setOeTagDefault('div.container', [
            'class' => 'container'
        ]);
        
        OeTag::setOeTagDefault('div.container-fluid', [
            'class' => 'container-fluid'
        ]);
        
        OeTag::setOeTagDefault('div.row', [
            'class' => 'row'
        ]);
        
        // Store version for CDN links
        self::$version = $version;
    }
    
    private static $version = '5.3.0';
    
    /**
     * Get Bootstrap CSS CDN link
     */
    public static function getCSSLink() {
        return "https://cdn.jsdelivr.net/npm/bootstrap@" . self::$version . "/dist/css/bootstrap.min.css";
    }
    
    /**
     * Get Bootstrap JS CDN link
     */
    public static function getJSLink() {
        return "https://cdn.jsdelivr.net/npm/bootstrap@" . self::$version . "/dist/js/bootstrap.bundle.min.js";
    }
    
    /**
     * Add Bootstrap to page automatically
     */
    public static function addToPage(OePage $page) {
        $page->addCSS(self::getCSSLink(), [
            'integrity' => 'sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN',
            'crossorigin' => 'anonymous'
        ]);
        
        $page->addJS(self::getJSLink(), [
            'integrity' => 'sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL',
            'crossorigin' => 'anonymous'
        ]);
        
        return $page;
    }
}

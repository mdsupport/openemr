<?php

/**
 * Phase 3: Data Integration - Tables and Data-Aware Components
 * 
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2025 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

/**
 * Bootstrap List Group - Data-aware Bootstrap list component
 */
class OeBootstrapListGroup extends OeDataList {
    
    public function __construct($attrs = []) {
        $defaultAttrs = ['class' => 'list-group'];
        $attrs = array_merge($defaultAttrs, $attrs);
        
        parent::__construct('div', $attrs);
        
        // Bootstrap list group empty template
        $this->setEmptyTemplate(function() {
            return new OeTag('div', '<em class="text-muted">No items to display</em>', [
                'class' => 'list-group-item text-center'
            ]);
        });
    }
    
    /**
     * Set default item template for simple list items
     */
    public function setSimpleItemTemplate($textField = 'name') {
        $this->setItemTemplate(function($item) use ($textField) {
            $text = is_array($item) ? ($item[$textField] ?? '') : (is_object($item) ? ($item->$textField ?? '') : $item);
            return new OeTag('div', htmlspecialchars($text), ['class' => 'list-group-item']);
        });
        return $this;
    }
}

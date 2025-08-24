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
 * Bootstrap Data Table with Bootstrap styling
 */
class OeBootstrapTable extends OeDataTable {
    
    public function __construct($attrs = []) {
        // Set Bootstrap table defaults
        $defaultAttrs = ['class' => 'table'];
        $attrs = array_merge($defaultAttrs, $attrs);
        
        parent::__construct($attrs);
        
        // Bootstrap-specific empty template
        $this->setEmptyTemplate(function() {
            return new OeTag('tr', new OeTag('td', 
                '<div class="text-center text-muted py-4"><em>No data available</em></div>', 
                [
                    'colspan' => count($this->columns) ?: 1,
                    'class' => 'text-center'
                ]
            ));
        });
    }
    
    /**
     * Set Bootstrap table variant (striped, bordered, hover, etc.)
     */
    public function setVariant($variant) {
        $currentClass = $this->tableAttrs['class'] ?? 'table';
        $this->tableAttrs['class'] = $currentClass . ' table-' . $variant;
        return $this;
    }
    
    /**
     * Set Bootstrap table size
     */
    public function setSize($size) {
        if ($size === 'small') {
            $currentClass = $this->tableAttrs['class'] ?? 'table';
            $this->tableAttrs['class'] = $currentClass . ' table-sm';
        }
        return $this;
    }
    
    /**
     * Make table responsive
     */
    public function setResponsive($responsive = true) {
        // This will need to be handled in render() by wrapping in responsive div
        $this->tableAttrs['data-responsive'] = $responsive;
        return $this;
    }
    
    /**
     * Override render to handle responsive wrapper
     */
    public function render() {
        $tableHtml = parent::render();
        
        if (isset($this->tableAttrs['data-responsive']) && $this->tableAttrs['data-responsive']) {
            return '<div class="table-responsive">' . $tableHtml . '</div>';
        }
        
        return $tableHtml;
    }
}

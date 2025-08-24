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
 * Base Data Table Component - Framework Agnostic
 */
class OeDataTable extends OeTagCollection implements OeDataAware {
    
    protected $dataSource;
    protected $columns = [];
    protected $headerTemplate;
    protected $rowTemplate;
    protected $footerTemplate;
    protected $emptyTemplate;
    protected $tableAttrs = [];
    protected $headerAttrs = [];
    protected $bodyAttrs = [];
    protected $footerAttrs = [];
    
    public function __construct($attrs = []) {
        parent::__construct();
        $this->tableAttrs = $attrs;
        
        // Default templates
        $this->setEmptyTemplate(function() {
            return new OeTag('tr', new OeTag('td', 'No data available', [
                'colspan' => count($this->columns) ?: 1,
                'class' => 'text-center'
            ]));
        });
    }
    
    /**
     * Set the data source (array, PDO result, iterator, etc.)
     */
    public function setDataSource(iterable $data) {
        $this->dataSource = $data;
        return $this;
    }
    
    /**
     * Define columns with labels and optional data accessors
     */
    public function setColumns(array $columns) {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * Set header template (callable that returns renderable content)
     */
    public function setHeaderTemplate(callable $template) {
        $this->headerTemplate = $template;
        return $this;
    }
    
    /**
     * Set row template (callable that receives row data and returns renderable content)
     */
    public function setRowTemplate(callable $template) {
        $this->rowTemplate = $template;
        return $this;
    }
    
    /**
     * Set footer template (callable that returns renderable content)
     */
    public function setFooterTemplate(callable $template) {
        $this->footerTemplate = $template;
        return $this;
    }
    
    /**
     * Set template for empty data state
     */
    public function setEmptyTemplate(callable $template) {
        $this->emptyTemplate = $template;
        return $this;
    }
    
    /**
     * Set table element attributes
     */
    public function setTableAttrs(array $attrs) {
        $this->tableAttrs = array_merge($this->tableAttrs, $attrs);
        return $this;
    }
    
    /**
     * Generate default header from columns
     */
    protected function generateDefaultHeader() {
        if (empty($this->columns)) {
            return null;
        }
        
        $headerCells = new OeTagCollection();
        foreach ($this->columns as $key => $column) {
            $label = is_array($column) ? ($column['label'] ?? $key) : $column;
            $headerCells->add(new OeTag('th', $label));
        }
        
        return new OeTag('tr', $headerCells);
    }
    
    /**
     * Generate default row from data
     */
    protected function generateDefaultRow($rowData) {
        if (empty($this->columns)) {
            // No columns defined, use all data
            $cells = new OeTagCollection();
            foreach ($rowData as $value) {
                $cells->add(new OeTag('td', htmlspecialchars($value ?? '')));
            }
            return new OeTag('tr', $cells);
        }
        
        // Use defined columns
        $cells = new OeTagCollection();
        foreach ($this->columns as $key => $column) {
            $value = '';
            
            if (is_array($column)) {
                // Column has configuration
                $accessor = $column['accessor'] ?? $key;
                if (is_callable($accessor)) {
                    $value = $accessor($rowData);
                } else {
                    $value = $this->getNestedValue($rowData, $accessor);
                }
            } else {
                // Simple column
                $value = $this->getNestedValue($rowData, $key);
            }
            
            $cells->add(new OeTag('td', htmlspecialchars($value ?? '')));
        }
        
        return new OeTag('tr', $cells);
    }
    
    /**
     * Get nested value from array/object using dot notation
     */
    protected function getNestedValue($data, $key) {
        if (is_object($data)) {
            return $data->$key ?? null;
        }
        
        if (is_array($data)) {
            // Support dot notation for nested arrays
            if (strpos($key, '.') !== false) {
                $keys = explode('.', $key);
                $value = $data;
                foreach ($keys as $k) {
                    if (is_array($value) && isset($value[$k])) {
                        $value = $value[$k];
                    } else {
                        return null;
                    }
                }
                return $value;
            }
            return $data[$key] ?? null;
        }
        
        return null;
    }
    
    /**
     * Render the complete table
     */
    public function render() {
        $table = new OeTag('table', '', $this->tableAttrs);
        $tableContent = new OeTagCollection();
        
        // Header
        if ($this->headerTemplate) {
            $headerContent = call_user_func($this->headerTemplate);
        } else {
            $headerContent = $this->generateDefaultHeader();
        }
        
        if ($headerContent) {
            $thead = new OeTag('thead', $headerContent, $this->headerAttrs);
            $tableContent->add($thead);
        }
        
        // Body
        $tbody = new OeTag('tbody', '', $this->bodyAttrs);
        $bodyContent = new OeTagCollection();
        
        // Check if we have data
        $hasData = false;
        if ($this->dataSource) {
            foreach ($this->dataSource as $rowData) {
                $hasData = true;
                
                if ($this->rowTemplate) {
                    $row = call_user_func($this->rowTemplate, $rowData);
                } else {
                    $row = $this->generateDefaultRow($rowData);
                }
                
                $bodyContent->add($row);
            }
        }
        
        // Handle empty data
        if (!$hasData && $this->emptyTemplate) {
            $emptyRow = call_user_func($this->emptyTemplate);
            $bodyContent->add($emptyRow);
        }
        
        $tbody->set('content', $bodyContent->render());
        $tableContent->add($tbody);
        
        // Footer
        if ($this->footerTemplate) {
            $footerContent = call_user_func($this->footerTemplate);
            if ($footerContent) {
                $tfoot = new OeTag('tfoot', $footerContent, $this->footerAttrs);
                $tableContent->add($tfoot);
            }
        }
        
        $table->set('content', $tableContent->render());
        return $table->render();
    }
}

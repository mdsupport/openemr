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
 * Data List Component - For non-tabular data display
 */
class OeDataList extends OeTagCollection implements OeDataAware {
    
    protected $dataSource;
    protected $itemTemplate;
    protected $wrapperTag = 'ul';
    protected $wrapperAttrs = [];
    protected $emptyTemplate;
    
    public function __construct($wrapperTag = 'ul', $attrs = []) {
        parent::__construct();
        $this->wrapperTag = $wrapperTag;
        $this->wrapperAttrs = $attrs;
        
        // Default empty template
        $this->setEmptyTemplate(function() {
            return new OeTag('li', '<em>No items to display</em>');
        });
    }
    
    /**
     * Set the data source
     */
    public function setDataSource(iterable $data) {
        $this->dataSource = $data;
        return $this;
    }
    
    /**
     * Set item template (callable that receives item data)
     */
    public function setItemTemplate(callable $template) {
        $this->itemTemplate = $template;
        return $this;
    }
    
    /**
     * Set empty state template
     */
    public function setEmptyTemplate(callable $template) {
        $this->emptyTemplate = $template;
        return $this;
    }
    
    /**
     * Render the list
     */
    public function render() {
        $wrapper = new OeTag($this->wrapperTag, '', $this->wrapperAttrs);
        $content = new OeTagCollection();
        
        $hasData = false;
        if ($this->dataSource && $this->itemTemplate) {
            foreach ($this->dataSource as $item) {
                $hasData = true;
                $renderedItem = call_user_func($this->itemTemplate, $item);
                $content->add($renderedItem);
            }
        }
        
        // Handle empty data
        if (!$hasData && $this->emptyTemplate) {
            $emptyItem = call_user_func($this->emptyTemplate);
            $content->add($emptyItem);
        }
        
        $wrapper->set('content', $content->render());
        return $wrapper->render();
    }
}

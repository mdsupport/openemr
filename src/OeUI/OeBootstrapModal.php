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
 * Bootstrap Modal Component
 */
class OeBootstrapModal extends OeTagCollection {
    
    protected $modalId;
    protected $title;
    protected $body;
    protected $footer;
    
    public function __construct($id, $title = '') {
        parent::__construct();
        $this->modalId = $id;
        $this->title = $title;
        $this->body = new OeTagCollection();
        $this->footer = new OeTagCollection();
    }
    
    /**
     * Set modal title
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Add content to modal body
     */
    public function addBodyContent($content) {
        $this->body->add($content);
        return $this;
    }
    
    /**
     * Add button to modal footer
     */
    public function addFooterButton($button) {
        $this->footer->add($button);
        return $this;
    }
    
    /**
     * Add close button to footer
     */
    public function addCloseButton($text = 'Close', $variant = 'secondary') {
        $closeBtn = new OeBootstrapButton($text, $variant, ['data-bs-dismiss' => 'modal']);
        $this->footer->add($closeBtn);
        return $this;
    }
    
    /**
     * Add primary action button to footer
     */
    public function addActionButton($text, $variant = 'primary', $attrs = []) {
        $actionBtn = new OeBootstrapButton($text, $variant, $attrs);
        $this->footer->add($actionBtn);
        return $this;
    }
    
    public function render() {
        // Modal structure
        $modal = new OeTag('div.modal', null, [
            'class' => 'modal fade',
            'id' => $this->modalId,
            'tabindex' => '-1',
            'aria-labelledby' => $this->modalId . 'Label',
            'aria-hidden' => 'true'
        ]);
        
        $modalDialog = new OeTag('div.modal-dialog', null, ['class' => 'modal-dialog']);
        $modalContent = new OeTag('div.modal-content', null, ['class' => 'modal-content']);
        
        // Header
        $headerContent = '';
        if ($this->title) {
            $headerContent = '<h1 class="modal-title fs-5" id="' . $this->modalId . 'Label">' . htmlspecialchars($this->title) . '</h1>';
        }
        $headerContent .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $modalHeader = new OeTag('div', $headerContent, ['class' => 'modal-header']);
        
        // Body
        $modalBody = new OeTag('div', $this->body->render(), ['class' => 'modal-body']);
        
        // Footer
        $modalFooter = null;
        if (count($this->footer) > 0) {
            $modalFooter = new OeTag('div', $this->footer->render(), ['class' => 'modal-footer']);
        }
        
        // Assemble
        $contentBody = $modalHeader->render() . $modalBody->render();
        if ($modalFooter) {
            $contentBody .= $modalFooter->render();
        }
        
        $modalContent->set('content', $contentBody);
        $modalDialog->set('content', $modalContent->render());
        $modal->set('content', $modalDialog->render());
        
        return $modal->render();
    }
}

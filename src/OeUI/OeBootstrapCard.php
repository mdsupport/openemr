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
 * Bootstrap Card Component
 */
class OeBootstrapCard extends OeTagCollection {
    
    protected $header;
    protected $body;
    protected $footer;
    protected $cardTag;
    
    public function __construct($attrs = []) {
        parent::__construct();
        
        $this->header = new OeTagCollection();
        $this->body = new OeTagCollection();
        $this->footer = new OeTagCollection();
        
        // Create main card div
        $attrs['class'] = 'card' . (isset($attrs['class']) ? ' ' . $attrs['class'] : '');
        $this->cardTag = new OeTag('div.card', null, $attrs);
    }
    
    /**
     * Set card header content
     */
    public function setHeader($content, $attrs = []) {
        $this->header->clear();
        if (is_string($content)) {
            $this->header->add(new OeTag('div.card-header', $content, $attrs));
        } else {
            $headerDiv = new OeTag('div.card-header', null, $attrs);
            $headerDiv->set('content', $content instanceof OeTagCollection ? $content->render() : $content);
            $this->header->add($headerDiv);
        }
        return $this;
    }
    
    /**
     * Add content to card body
     */
    public function addBody($content, $attrs = []) {
        if (count($this->body) === 0) {
            // Create body div if it doesn't exist
            $this->body->add(new OeTag('div.card-body', '', $attrs));
        }
        
        // Add content to existing body
        $bodyTag = $this->body[0];
        $currentContent = $bodyTag->get('content') ?? '';
        if ($content instanceof OeTagCollection || $content instanceof OeTag) {
            $newContent = $currentContent . $content->render();
        } else {
            $newContent = $currentContent . $content;
        }
        $bodyTag->set('content', $newContent);
        return $this;
    }
    
    /**
     * Set card footer content
     */
    public function setFooter($content, $attrs = []) {
        $this->footer->clear();
        if (is_string($content)) {
            $this->footer->add(new OeTag('div.card-footer', $content, $attrs));
        } else {
            $footerDiv = new OeTag('div.card-footer', null, $attrs);
            $footerDiv->set('content', $content instanceof OeTagCollection ? $content->render() : $content);
            $this->footer->add($footerDiv);
        }
        return $this;
    }
    
    /**
     * Set card title (adds to body with card-title class)
     */
    public function setTitle($title, $tag = 'h5') {
        $titleTag = new OeTag($tag, $title, ['class' => 'card-title']);
        $this->addBody($titleTag);
        return $this;
    }
    
    /**
     * Set card text (adds to body with card-text class)
     */
    public function setText($text) {
        $textTag = new OeTag('p', $text, ['class' => 'card-text']);
        $this->addBody($textTag);
        return $this;
    }
    
    public function render() {
        // Build complete card structure
        $cardContent = '';
        
        if (count($this->header) > 0) {
            $cardContent .= $this->header->render();
        }
        
        if (count($this->body) > 0) {
            $cardContent .= $this->body->render();
        }
        
        if (count($this->footer) > 0) {
            $cardContent .= $this->footer->render();
        }
        
        $this->cardTag->set('content', $cardContent);
        return $this->cardTag->render();
    }
}

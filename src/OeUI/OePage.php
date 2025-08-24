<?php

/**
 * Phase 1: Core Foundation Classes
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2025 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

/**
 * OePage - Document structure manager for complete HTML pages
 * Framework-agnostic page layout management
 */
class OePage extends OeTagCollection {
    
    protected $title = '';
    protected $headTags;
    protected $bodyTags;
    protected $doctype = '<!DOCTYPE html>';
    protected $htmlAttrs = ['lang' => 'en'];
    protected $headAttrs = [];
    protected $bodyAttrs = [];
    
    public function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
        $this->headTags = new OeTagCollection();
        $this->bodyTags = new OeTagCollection();
    }
    
    // ========== Page Structure Methods ==========
    
    /**
     * Set page title
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Get page title
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Set doctype
     */
    public function setDoctype($doctype) {
        $this->doctype = $doctype;
        return $this;
    }
    
    /**
     * Set HTML tag attributes
     */
    public function setHtmlAttrs($attrs) {
        $this->htmlAttrs = $attrs;
        return $this;
    }
    
    /**
     * Set head tag attributes
     */
    public function setHeadAttrs($attrs) {
        $this->headAttrs = $attrs;
        return $this;
    }
    
    /**
     * Set body tag attributes
     */
    public function setBodyAttrs($attrs) {
        $this->bodyAttrs = $attrs;
        return $this;
    }
    
    // ========== Head Management ==========
    
    /**
     * Add content to head section
     */
    public function addToHead($tag) {
        $this->headTags->add($tag);
        return $this;
    }
    
    /**
     * Add meta tag
     */
    public function addMeta($attrs) {
        $this->headTags->add(new OeTag('meta', null, $attrs));
        return $this;
    }
    
    /**
     * Add CSS link
     */
    public function addCSS($href, $attrs = []) {
        $defaultAttrs = ['rel' => 'stylesheet', 'href' => $href];
        $this->headTags->add(new OeTag('link', null, array_merge($defaultAttrs, $attrs)));
        return $this;
    }
    
    /**
     * Add JavaScript
     */
    public function addJS($src, $attrs = []) {
        $defaultAttrs = ['src' => $src];
        $this->headTags->add(new OeTag('script', '', array_merge($defaultAttrs, $attrs)));
        return $this;
    }
    
    /**
     * Add inline CSS
     */
    public function addInlineCSS($css) {
        $this->headTags->add(new OeTag('style', $css));
        return $this;
    }
    
    /**
     * Add inline JavaScript
     */
    public function addInlineJS($js) {
        $this->headTags->add(new OeTag('script', $js));
        return $this;
    }
    
    // ========== Body Management ==========
    
    /**
     * Add component to body
     */
    public function addComponent($component) {
        $this->bodyTags->add($component);
        return $this;
    }
    
    /**
     * Prepend component to body
     */
    public function prependComponent($component) {
        $this->bodyTags->prepend($component);
        return $this;
    }
    
    /**
     * Insert component at specific position in body
     */
    public function insertComponent($index, $component) {
        $this->bodyTags->insert($index, $component);
        return $this;
    }
    
    /**
     * Get body collection (for direct manipulation)
     */
    public function getBody() {
        return $this->bodyTags;
    }
    
    /**
     * Get head collection (for direct manipulation)
     */
    public function getHead() {
        return $this->headTags;
    }
    
    // ========== Rendering ==========
    
    /**
     * Render complete HTML page
     */
    public function render() {
        $output = $this->doctype . "\n";
        
        // HTML tag
        $htmlTag = new OeTag('html', null, $this->htmlAttrs);
        $output .= '<html';
        foreach ($this->htmlAttrs as $attr => $value) {
            $output .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $output .= ">\n";
        
        // Head section
        $output .= "<head";
        foreach ($this->headAttrs as $attr => $value) {
            $output .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $output .= ">\n";
        
        // Title
        if ($this->title) {
            $output .= "<title>" . htmlspecialchars($this->title, ENT_QUOTES) . "</title>\n";
        }
        
        // Head tags
        $output .= $this->headTags->render();
        $output .= "</head>\n";
        
        // Body section
        $output .= "<body";
        foreach ($this->bodyAttrs as $attr => $value) {
            $output .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $output .= ">\n";
        
        // Body content
        $output .= $this->bodyTags->render();
        $output .= "</body>\n</html>";
        
        return $output;
    }
}

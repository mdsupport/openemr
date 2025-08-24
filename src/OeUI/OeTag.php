<?php

/**
 * OemrUI class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2025 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

class OeTag {
    private $objTag;
    
    // Static object for default tag attributes
    public static $tagDefaults;
    
    public function __construct($tagIn, $tagContent = null, $tagAttrs = []) {
        // Initialize void elements in static tagDefaults if not already done
        if (!isset(self::$tagDefaults)) {
            $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
            foreach ($voidTags as $tagName) {
                self::setOeTagDefault($tagName, 'isVoid');
            }
        }
        
        // Initialize objTag as stdClass object
        // Handle object or string input
        if (is_object($tagIn)) {
            $this->objTag = $tagIn;
            $tagIn = $this->objTag->tag ?? '';
        } else {
            $this->objTag = new \stdClass();
        }
        
        // Extract ID if present
        if (preg_match('/#(.+?)(\.|$)/', $tagIn, $matched)) {
            $this->set('id', $matched[1]);
        }
        
        // Extract all classes
        if (preg_match_all('/\.([^#.]+)/', $tagIn, $matched)) {
            $this->set('class', implode(' ', $matched[1]));
        }
        
        // Set tagIn to first token (everything before . or #)
        $tagIn = preg_replace('/[.#].*/', '', $tagIn);
        
        // Apply default attributes for this tag
        $tagName = strtolower($tagIn);
        
        // Convert numeric indices to boolean properties
        foreach ($tagAttrs as $key => $value) {
            if (is_numeric($key)) {
                $tagAttrs[$value] = true;
                unset($tagAttrs[$key]);
            }
        }
        
        // Apply defaults (merge with existing objTag properties first)
        $existingAttrs = (array) $this->objTag;
        $defaults = (array) self::getOeTagDefault($tagName);
        // Special cases
        if (isset($defaults['class']) && isset($existingAttrs['class'])) {
            $existingAttrs['class'] = $defaults['class'].' '.$existingAttrs['class'];
        }
        
        $mergedAttrs = array_merge($defaults, $existingAttrs, $tagAttrs);
        
        // Set tag attributes
        foreach ($mergedAttrs as $key => $value) {
            if ($key !== 'tag' && $key !== 'content') {
                $this->set($key, $value);
            }
        }
        
        // Set tag name and content only if not already set
        if (!$this->get('tag')) {
            $this->set('tag', $tagIn);
        }
        if ($tagContent) {
            $this->set('content', $this->renderContent($tagContent));
        } else {
            $this->set('content', '');
        }
    }
    
    /**
     * Recursively render content of any depth
     * @param mixed $content Content to render (string, object, array, or nested arrays)
     * @return string Rendered HTML content
     */
    private function renderContent($content) {
        // Handle null or empty content
        if ($content === null || $content === '') {
            return '';
        }
        
        // Handle arrays (including nested arrays)
        if (is_array($content)) {
            $renderedContent = '';
            foreach ($content as $subContent) {
                $renderedContent .= $this->renderContent($subContent); // Recursive call
            }
            return $renderedContent;
        }
        
        // Handle objects with render method
        if (is_object($content) && method_exists($content, 'render')) {
            return $content->render();
        }
        
        // Handle primitive types (string, number, boolean)
        return (string) $content;
    }
    
    // Setter method to store properties in objTag
    public function set($key, $value = true) {
        if ($key === 'data' && is_array($value)) {
            foreach ($value as $propKey => $propValue) {
                $this->set("data-$propKey", $propValue);
            }
        } else {
            $this->objTag->$key = $value;
        }
        return $this; // Allow method chaining
    }
    
    // Setter method to store properties in objTag
    public function get($attr = null) {
        if ($attr === null) return $this->objTag;
        return $this->objTag->$attr ?? null;
    }
    
    private function getAttrs() {
        $attrs = '';
        foreach (get_object_vars($this->objTag) as $attrKey => $attrValue) {
            // Skip internal attributes like isVoid, content, and tag
            if (in_array($attrKey, ['isVoid', 'content', 'tag'])) continue;
            
            // Handle boolean attributes (like disabled, checked, etc.)
            if ($attrValue === true) {
                $attrs .= $attrKey . ' ';
                continue;
            }
            
            // Skip data attributes array processing (handled in set method now)
            if ($attrKey === 'data' && is_array($attrValue)) {
                continue; // Data attributes are now set as individual data-* properties
            }
            
            // Named attribute
            if (!is_numeric($attrValue)) {
                $attrValue = '"' . htmlspecialchars($attrValue, ENT_QUOTES) . '"';
            }
            $attrs .= $attrKey . '=' . $attrValue . ' ';
        }
        return trim($attrs);
    }
    
    public function render() {
        // Generate the final HTML
        if (isset($this->objTag->isVoid) && $this->objTag->isVoid) {
            // Void elements don't have closing tags or content
            $attrs = $this->getAttrs();
            return sprintf(
                '<%s%s>',
                $this->objTag->tag,
                $attrs ? ' ' . $attrs : ''
                );
        } else {
            // Regular elements with closing tags
            $attrs = $this->getAttrs();
            return sprintf(
                '<%s%s>%s</%s>',
                $this->objTag->tag,
                $attrs ? ' ' . $attrs : '',
                $this->objTag->content ?? '',
                $this->objTag->tag
                );
        }
    }
    
    // Static method to set default attributes for a tag
    public static function setOeTagDefault($tagName, $defaults) {
        if (!isset(self::$tagDefaults)) {
            self::$tagDefaults = new \stdClass();
        }
        $tagName = strtolower($tagName);
        
        // Convert string to array
        if (is_string($defaults)) {
            $defaults = [$defaults => true];
        }
        
        // Get existing defaults and merge
        $existing = (array) self::getOeTagDefault($tagName);
        $merged = array_merge($existing, $defaults);
        
        self::$tagDefaults->$tagName = (object) $merged;
    }
    
    // Static method to get default attributes for a tag
    public static function getOeTagDefault($tagName) {
        if (!isset(self::$tagDefaults)) {
            return new \stdClass();
        }
        $tagName = strtolower($tagName);
        return self::$tagDefaults->$tagName ?? new \stdClass();
    }
}

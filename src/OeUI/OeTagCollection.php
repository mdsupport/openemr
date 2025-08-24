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
 * OeTagCollection - Base collection class for managing groups of tags
 * Implements PHP collection interfaces for native array-like behavior
 */
class OeTagCollection implements \Iterator, \ArrayAccess, \Countable {
    
    protected $tags = [];
    protected $position = 0;
    
    public function __construct($tags = []) {
        if (is_array($tags)) {
            $this->tags = $tags;
        } elseif ($tags instanceof OeTag || $tags instanceof self) {
            $this->tags = [$tags];
        }
    }
    
    // ========== Collection Management Methods ==========
    
    /**
     * Add a tag or collection to the end
     */
    public function add($tag) {
        $this->tags[] = $tag;
        return $this;
    }
    
    /**
     * Add a tag or collection to the beginning
     */
    public function prepend($tag) {
        array_unshift($this->tags, $tag);
        return $this;
    }
    
    /**
     * Insert a tag at specific position
     */
    public function insert($index, $tag) {
        array_splice($this->tags, $index, 0, [$tag]);
        return $this;
    }
    
    /**
     * Remove tag at specific index
     */
    public function remove($index) {
        if (isset($this->tags[$index])) {
            array_splice($this->tags, $index, 1);
        }
        return $this;
    }
    
    /**
     * Replace tag at specific index
     */
    public function replace($index, $tag) {
        if (isset($this->tags[$index])) {
            $this->tags[$index] = $tag;
        }
        return $this;
    }
    
    /**
     * Clear all tags
     */
    public function clear() {
        $this->tags = [];
        return $this;
    }
    
    /**
     * Get all tags as array
     */
    public function toArray() {
        return $this->tags;
    }
    
    // ========== ArrayAccess Implementation ==========
    
    public function offsetExists($offset): bool {
        return isset($this->tags[$offset]);
    }
    
    public function offsetGet($offset): mixed {
        return $this->tags[$offset] ?? null;
    }
    
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->tags[] = $value;
        } else {
            $this->tags[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset): void {
        unset($this->tags[$offset]);
    }
    
    // ========== Iterator Implementation ==========
    
    public function current(): mixed {
        return $this->tags[$this->position];
    }
    
    public function key(): mixed {
        return $this->position;
    }
    
    public function next(): void {
        ++$this->position;
    }
    
    public function rewind(): void {
        $this->position = 0;
    }
    
    public function valid(): bool {
        return isset($this->tags[$this->position]);
    }
    
    // ========== Countable Implementation ==========
    
    public function count(): int {
        return count($this->tags);
    }
    
    // ========== Rendering ==========
    
    /**
     * Render all tags in the collection
     */
    public function render() {
        $output = '';
        foreach ($this->tags as $tag) {
            if ($tag instanceof OeTag || $tag instanceof self) {
                $output .= $tag->render();
            } elseif (is_string($tag) || is_numeric($tag)) {
                $output .= (string) $tag;
            }
        }
        return $output;
    }
    
    /**
     * Magic method to render when object is used as string
     */
    public function __toString() {
        return $this->render();
    }
}

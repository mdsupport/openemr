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
 * Bootstrap Button Component
 */
class OeBootstrapButton extends OeTag {
    
    public function __construct($text = '', $variant = 'primary', $attrs = []) {
        $attrs['class'] = 'btn btn-' . $variant . ($attrs['class'] ?? '');
        parent::__construct('button', $text, $attrs);
    }
    
    /**
     * Set button variant (primary, secondary, success, etc.)
     */
    public function setVariant($variant) {
        $currentClass = $this->get('class') ?? '';
        $newClass = preg_replace('/btn-\w+/', 'btn-' . $variant, $currentClass);
        $this->set('class', $newClass);
        return $this;
    }
    
    /**
     * Set button size
     */
    public function setSize($size) {
        $currentClass = $this->get('class') ?? '';
        // Remove existing size classes
        $currentClass = preg_replace('/\bbtn-(sm|lg)\b/', '', $currentClass);
        if ($size !== 'default') {
            $currentClass .= ' btn-' . $size;
        }
        $this->set('class', trim($currentClass));
        return $this;
    }
    
    /**
     * Make button outline style
     */
    public function setOutline($outline = true) {
        if ($outline) {
            $currentClass = $this->get('class') ?? '';
            $newClass = str_replace('btn-', 'btn-outline-', $currentClass);
            $this->set('class', $newClass);
        }
        return $this;
    }
}

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
 * Bootstrap Alert Component
 */
class OeBootstrapAlert extends OeTag {
    
    public function __construct($message = '', $variant = 'info', $dismissible = false, $attrs = []) {
        $classes = 'alert alert-' . $variant;
        if ($dismissible) {
            $classes .= ' alert-dismissible fade show';
        }
        
        $attrs['class'] = $classes . (isset($attrs['class']) ? ' ' . $attrs['class'] : '');
        $attrs['role'] = 'alert';
        
        $content = $message;
        if ($dismissible) {
            $content .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }
        
        parent::__construct('div.alert', $content, $attrs);
    }
    
    /**
     * Set alert variant
     */
    public function setVariant($variant) {
        $currentClass = $this->get('class') ?? '';
        $newClass = preg_replace('/alert-\w+/', 'alert-' . $variant, $currentClass);
        $this->set('class', $newClass);
        return $this;
    }
    
    /**
     * Make alert dismissible
     */
    public function setDismissible($dismissible = true) {
        $currentClass = $this->get('class') ?? '';
        $currentContent = $this->get('content') ?? '';
        
        if ($dismissible) {
            if (strpos($currentClass, 'alert-dismissible') === false) {
                $currentClass .= ' alert-dismissible fade show';
                $currentContent .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            }
        } else {
            $currentClass = str_replace('alert-dismissible fade show', '', $currentClass);
            $currentContent = str_replace('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', '', $currentContent);
        }
        
        $this->set('class', trim($currentClass));
        $this->set('content', $currentContent);
        return $this;
    }
}

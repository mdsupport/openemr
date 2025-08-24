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
 * Interface for components that can work with data sources
 */
interface OeDataAware {
    public function setDataSource(iterable $data);
    public function render();
}

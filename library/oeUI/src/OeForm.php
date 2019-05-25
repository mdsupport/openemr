<?php
/**
 * OemrUI Form class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author MD Support <mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2019 MD Support <mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\OeUI;

class OeForm extends \Form
{
    function __construct($attrs_form = []) {
        $form_id = (is_array($attrs_form) && isset($attrs_form['id'])) ? $attrs_form['id'] : 'oeForm';
        parent::__construct($form_id);
    }

    public function Button($label = "", $id = null, $attributes = [])
    {
        $attributes['class'] = (isset($attributes['class']) ? $attributes['class'].' ' : '').'btn-sm';

        parent::Button($label, $id, $attributes);
    }
}

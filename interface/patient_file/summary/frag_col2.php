<?php
/**
 *
 * Patient summary screen fragment - Column 2 of previous widgets(small).
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_col2($pid, $info)
{
    $frag_html = "";
    ob_start();
?>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
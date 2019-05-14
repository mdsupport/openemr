<?php
/**
 *
 * Patient summary screen fragment - Issues.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_iss($pid, $info)
{
    $frag_html = "";
    ob_start();
?>
    <div id='stats_div'>
        <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div>
        <script type="text/javascript">
        ajaxLoad("#stats_div", "stats.php");
        </script>
    </div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
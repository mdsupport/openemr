<?php
/**
 *
 * Patient summary screen fragment - Last vitals.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_vitals($pid, $info)
{
    $frag_html = "";
    ob_start();

    // vitals expand collapse widget
    $widgetTitle = xl("Vitals");
    $widgetLabel = "vitals";
    $widgetButtonLabel = xl("Trend");
    $widgetButtonLink = "../encounter/trend_form.php?formname=vitals";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    $widgetAuth = $existVitals;
    $fixedWidth = true;
    expand_collapse_widget(
        $widgetTitle,
        $widgetLabel,
        $widgetButtonLabel,
        $widgetButtonLink,
        $widgetButtonClass,
        $linkMethod,
        $bodyClass,
        $widgetAuth,
        $fixedWidth
    );
?>
    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div>
    <script type="text/javascript">
        ajaxLoad("#vitals_ps_expand", "vitals_fragment.php");
    </script>
</div> <!-- This is required by expand_collapse_widget(). -->
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
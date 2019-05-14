<?php
/**
 *
 * Patient summary screen fragment - Labs.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_labs($pid, $info)
{
    $frag_html = "";
    ob_start();

    // check to see if any labdata exist
    $spruch = "SELECT procedure_report.date_collected AS date " .
        "FROM procedure_report " .
        "JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " .
        "WHERE procedure_order.patient_id = ? " .
        "ORDER BY procedure_report.date_collected DESC ";
    $existLabdata = sqlQuery($spruch, array($pid));

    // labdata expand collapse widget
    $widgetTitle = xl("Labs");
    $widgetLabel = "labdata";
    $widgetButtonLabel = xl("Trend");
    $widgetButtonLink = "../summary/labdata.php";#"../encounter/trend_form.php?formname=labdata";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    $widgetAuth = ($existLabdata ? true : false);
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
  </div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
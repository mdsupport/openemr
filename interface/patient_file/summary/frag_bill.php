<?php
/**
 *
 * Patient summary screen fragment - Column 1 of previous widgets(large).
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_bill($pid, $info)
{
    $frag_html = "";
    ob_start();
?>
<table cellspacing=0 cellpadding=0>
<tr>
    <td>
        <?php
        // Billing expand collapse widget
        $widgetTitle = xl("Billing");
        $widgetLabel = "billing";
        $widgetButtonLabel = xl("Edit");
        $widgetButtonLink = "return newEvt();";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "notab";
        $widgetAuth = false;
        $fixedWidth = true;
        if ($GLOBALS['force_billing_widget_open']) {
            $forceExpandAlways = true;
        } else {
            $forceExpandAlways = false;
        }

        expand_collapse_widget(
            $widgetTitle,
            $widgetLabel,
            $widgetButtonLabel,
            $widgetButtonLink,
            $widgetButtonClass,
            $linkMethod,
            $bodyClass,
            $widgetAuth,
            $fixedWidth,
            $forceExpandAlways
        );
        ?>
<br>
<?php
        //PATIENT BALANCE,INS BALANCE naina@capminds.com
        $patientbalance = get_patient_balance($pid, false);
        //Debit the patient balance from insurance balance
        $insurancebalance = get_patient_balance($pid, true) - $patientbalance;
       $totalbalance=$patientbalance + $insurancebalance;

 // Show current balance and billing note, if any.
  echo "<table border='0'><tr><td>" .
  "<table ><tr><td><span class='bold'><font color='red'>" .
   xlt('Patient Balance Due') .
   " : " . text(oeFormatMoney($patientbalance)) .
   "</font></span></td></tr>".
     "<tr><td><span class='bold'><font color='red'>" .
   xlt('Insurance Balance Due') .
   " : " . text(oeFormatMoney($insurancebalance)) .
   "</font></span></td></tr>".
   "<tr><td><span class='bold'><font color='red'>" .
   xlt('Total Balance Due').
   " : " . text(oeFormatMoney($totalbalance)) .
   "</font></span></td></td></tr>";
if (!empty($result['billing_note'])) {
    echo "<tr><td><span class='bold'><font color='red'>" .
    xlt('Billing Note') . ":" .
    text($result['billing_note']) .
    "</font></span></td></tr>";
}

$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
$insco_name = "";
if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
    $insco_name = getInsuranceProvider($result3['provider']);
}
if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
    echo "<tr><td><span class='bold'>" .
    xlt('Primary Insurance') . ': ' . text($insco_name) .
    "</span>&nbsp;&nbsp;&nbsp;";
    if ($result3['copay'] > 0) {
        echo "<span class='bold'>" .
        xlt('Copay') . ': ' .  text($result3['copay']) .
        "</span>&nbsp;&nbsp;&nbsp;";
    }

    echo "<span class='bold'>" .
    xlt('Effective Date') . ': ' .  text(oeFormatShortDate($result3['effdate'])) .
    "</span></td></tr>";
}

  echo "</table></td></tr></td></tr></table><br>";

?>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>
</table>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
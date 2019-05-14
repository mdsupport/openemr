<?php
/**
 *
 * Patient summary screen fragment - Clinical Reminders.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_crem($pid, $info)
{
    $frag_html = "";
    ob_start();

    // clinical summary expand collapse widget
    $widgetTitle = xl("Clinical Reminders");
    $widgetLabel = "clinical_reminders";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=".$pid;

    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "summary_item small";
    $widgetAuth = acl_check('patients', 'alert', '', 'write');
    $fixedWidth = false;
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
    <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div>
</div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
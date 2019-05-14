<?php
/**
 *
 * Patient summary screen fragment - Main Demographics.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_demo($pid, $info)
{
    $frag_html = "";
    ob_start();

    // Demographics expand collapse widget
    $widgetTitle = xl("Demographics");
    $widgetLabel = "demographics";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "demographics_full.php";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "";
    $widgetAuth = acl_check('patients', 'demo', '', 'write');
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
    // Get patient/employer/insurance information.
    //
    $result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    $result2 = getEmployerData($pid);
?>
    <div id="DEM" >
        <ul class="tabNav">
        <?php display_layout_tabs('DEM', $result, $result2); ?>
        </ul>
        <div class="tabContainer">
        <?php display_layout_tabs_data('DEM', $result, $result2); ?>
        </div>
    </div>
</div> <!-- required for expand_collapse_widget -->
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
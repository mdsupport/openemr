<?php
/**
 *
 * Patient summary screen fragment - Trends.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_trends($pid, $info)
{
    $frag_html = "";
    ob_start();
    // This generates a section similar to Vitals for each LBF form that
    // supports charting.  The form ID is used as the "widget label".
    //
    $gfres = sqlStatement(
        "SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec
        FROM layout_group_properties
        WHERE grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_repeats > 0 AND grp_activity = 1
        ORDER BY grp_seq, grp_title"
    );
    while ($gfrow = sqlFetchArray($gfres)) {
        // $jobj = json_decode($gfrow['notes'], true);
        $LBF_ACO = empty($gfrow['grp_aco_spec']) ? false : explode('|', $gfrow['grp_aco_spec']);
        if ($LBF_ACO && !acl_check($LBF_ACO[0], $LBF_ACO[1])) {
            continue;
        }

        $fm = js_escape($gfrow['grp_form_id']);
        $ajaxCall = sprintf(
            '<script>
                ajaxLoad("#%s_ps_expand", "lbf_fragment.php?formname=%s");
            </script>',
            $fm, $fm
        );

        // Widget like vitals
        $vitals_form_id = $gfrow['option_id'];
        $widgetTitle = $gfrow['title'];
        $widgetLabel = $vitals_form_id;
        $widgetButtonLabel = xl("Trend");
        $widgetButtonLink = "../encounter/trend_form.php?formname=" . attr_url($vitals_form_id);
        $widgetButtonClass = "";
        $linkMethod = "html";
        $bodyClass = "notab";
        $widgetAuth = false;
        if (!$LBF_ACO || acl_check($LBF_ACO[0], $LBF_ACO[1], '', 'write')) {
            // check to see if any instances exist for this patient
            $existVitals = sqlQuery(
                "SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
                array($pid, $vitals_form_id)
            );
            $widgetAuth = $existVitals;
        }

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
       <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div>
       <?php echo $ajaxCall; ?>
  </div> <!-- This is required by expand_collapse_widget(). -->
<?php
    } // end while

    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
<?php
/**
 *
 * Patient summary screen fragment - Notes.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_pnote($pid, $info)
{
    $frag_html = "";
    ob_start();

    // Notes expand collapse widget
    $widgetTitle = xl("Notes");
    $widgetLabel = "pnotes";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "pnotes_full.php?form_active=1";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    $widgetAuth = acl_check('patients', 'notes', '', 'write');
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
    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
</div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
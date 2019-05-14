<?php
/**
 *
 * Patient summary screen fragment - Patient Header (Line 2).
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\PatientMenuRole;

function html_frag_phdr2($pid, $info)
{
    // Collect the patient menu then build it
    $menuPatient = new PatientMenuRole();
    $menu_restrictions = $menuPatient->getMenu();

    $frag_html = "";
    ob_start();
?>
<table cellspacing='0' cellpadding='0' border='0' class="subnav">
    <tr>
        <td class="small" colspan='4'>

            <?php
            $first = true;
            foreach ($menu_restrictions as $key => $value) {
                if (!empty($value->children)) {
                    // flatten to only show children items
                    foreach ($value->children as $children_key => $children_value) {
                        if (!$first) {
                            echo "|";
                        }
                        $first = false;
                        $link = ($children_value->pid != "true") ? $children_value->url : $children_value->url . attr($pid);
                        echo '<a href="' . $link . '" onclick="' . $children_value->on_click .'"> ' . text($children_value->label) . ' </a>';
                    }
                } else {
                    if (!$first) {
                        echo "|";
                    }
                    $first = false;
                    $link = ($value->pid != "true") ? $value->url : $value->url . attr($pid);
                    echo '<a href="' . $link . '" onclick="' . $value->on_click .'"> ' . text($value->label) . ' </a>';
                }
            }
            // Adding portal stuff
            $aResp = legacyDemPort($pid);
            foreach ($aResp as $portal_id => $has_login) {
                if ($portal_id == '*') {
                    printf(' | <a class="portal-no" title="%s" href="#"><s>%s</s></a>',
                        xla('Patient has not authorized the Patient Portal.'), xla("Portal")
                        );
                } else {
                    // js to trigger - create_portallogin.php?portalsite=[data-portal]&patient=[data-pid]
                    $temp = ($has_login ? 'Create':'Reset').' '.$portal_id.'site Credentials';
/*
                    printf(' | <a class="portal-login" data-portal="%s" data-pid="%s" title="%s" href="#">%s %s</a>',
                        $portal_id, attr($pid), xla($temp), xla("Portal"), xla($portal_id.'site')
                        );
*/
                    printf(' | <a class="small_modal" data-portal="%s" data-pid="%s" title="%s"
                         href="create_portallogin.php?portalsite=%s&patient=%s">%s %s</a>',
                         $portal_id, attr($pid), xla($temp), $portal_id, attr($pid), xla("Portal"), xla($portal_id.'site')
                    );
                }
                
            }
            ?>
        </td>
    </tr>
</table> <!-- end header -->
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
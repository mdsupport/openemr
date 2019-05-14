<?php
/**
 *
 * Patient summary screen fragment - Patient Header (Line 1).
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
function html_frag_phdr($pid, $info)
{
    $frag_html = "";
    ob_start();
?>
<table class="table_header">
    <tr>
        <td>
            <span class='title'>
                <?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES); ?>
            </span>
            <strong>
                <a id="pt-alerts" href="#" class="h5 text-secondary ml-3" data-popover-content="#pt-alerts-content"><?php echo xlt("Alerts") ?>
                    <span class="badge badge-pill badge-dark">0</span>
                </a>
            </strong>
        </td>
        <?php if (acl_check('admin', 'super') && $GLOBALS['allow_pat_delete']) : ?>
        <td style='padding-left:1em;' class="delete">
            <a class='css_button deleter'
               href='../deleter.php?patient=<?php echo htmlspecialchars($pid, ENT_QUOTES);?>'
               onclick='return top.restoreSession()'>
                <span><?php echo htmlspecialchars(xl('Delete'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <?php endif; // Allow PT delete
if ($GLOBALS['erx_enable']) : ?>
        <td style="padding-left:1em;" class="erx">
            <a class="css_button" href="../../eRx.php?page=medentry" onclick="top.restoreSession()">
                <span><?php echo htmlspecialchars(xl('NewCrop MedEntry'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <td style="padding-left:1em;">
            <a class="css_button iframe1"
               href="../../soap_functions/soap_accountStatusDetails.php"
               onclick="top.restoreSession()">
                <span><?php echo htmlspecialchars(xl('NewCrop Account Status'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <td id='accountstatus'></td>
<?php endif; // eRX Enabled
        // Display alert if patient is deceased
        $deceased = getPatientData($pid, 'deceased_date date, deceased_reason reason');
        if ($deceased && (!empty($deceased['date']))) {
            $objNow = new DateTime();
            $objDt = new DateTime($deceased['date']);
            $objDt = $objDt->diff($objNow);
            $ago = ($objDt->days == 0 ? xlt('Today') : '');
            if  ($ago == '') {
                $ago .= ($objDt->y == 0 ? '': $objDt->y.' '.xlt($objDt->y == 1 ? 'year':'years').' ');
                $ago .= ($objDt->m == 0 ? '': $objDt->m.' '.xlt($objDt->m == 1 ? 'month':'months').' ');
                $ago .= ($objDt->d == 0 ? '': $objDt->d.' '.xlt($objDt->d == 1 ? 'day':'days'));
            }
            printf(
                '<td><span class="h5 bg-light text-danger pl-3" title="%s">%s</span>
                    <span class="badge badge-info">%s</span>
                </td>
                <script>$("body").css("opacity", 0.75);</script>
                ',
                attr($deceased['reason']), xlt('DECEASED'), $ago
            );
        }
?>
    </tr>
</table>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
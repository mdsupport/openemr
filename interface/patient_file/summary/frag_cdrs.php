<?php
/**
 *
 * Patient summary screen fragment - Logic replicated from ../active_reminder_popup.php
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Container to pass popup alerts to client
// Ajax requests should deal directly with the javascript object
// Expected to be associative array -
$localAlerts = array();
function addPtAlertLocal($aCat, $aMsg) {
    global $localAlerts;
    if (empty($localAlerts[$aCat])) {
        $localAlerts[$aCat] = array();
    }
    $localAlerts[$aCat][] = $aMsg;
}

function html_frag_cdrs($pid, $info)
{
    global $localAlerts;
    $_SESSION['alert_notify_pid'] = $pid;
    $active_reminders = false;
    $all_allergy_alerts = false;

    if ($GLOBALS['enable_allergy_check'] && $GLOBALS['enable_alert_log']) {
        //Check for new allergies conflicts and throw popup if any exist(note need alert logging to support this)
        $new_allergy_alerts = allergy_conflict($pid, 'new', $_SESSION['authUser']);
        if (!empty($new_allergy_alerts)) {
            foreach ($new_allergy_alerts as $new_allergy_alert) {
                addPtAlertLocal(xls('ACTIVE MEDICATIONS ALLERGIES'), addslashes($new_allergy_alert));
            }
        }
    }

    if ((!isset($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && $GLOBALS['enable_cdr_crp']) {
        // showing a new patient, so check for active reminders and allergy conflicts, which use in active reminder popup
        $active_reminders = active_alert_summary($pid, "reminders-due", '', 'default', $_SESSION['authUser'], true);
        if (!empty($active_reminders)) {
            // As with many other routines, only html output is available 
            addPtAlertLocal(xls('Active Reminder(s)'), addslashes($active_reminders));
        }
    }

    if ($localAlerts) {
        // Insert alert objects into objAlerts array used by ajax framework
        $jqfn = '';
        foreach ($localAlerts as $lcat => $lcatalerts) {
            foreach ($lcatalerts as $lcatalert) {
                $jqfn .= sprintf('setPtAlert("%s", "%s");%s', attr_js($lcat), attr_js($lcatalert), PHP_EOL);
            }
        }
        // No idea if setPtAlert is before or after this.  So use jQ
?>
<script>
$(function() {
    <?php echo $jqfn; ?>
    showPtAlerts();
});
</script>';
<?php
    }
}

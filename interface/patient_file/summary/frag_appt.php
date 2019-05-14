<?php
/**
 *
 * Patient summary screen fragment - Appointments.
 * WARNING - This fragment is called inline
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['srcdir']."/appointments.inc.php");

function frag_appt_event_show()
{
    
}

function frag_appt_events($pid)
{
}

function html_frag_appt($pid, $info)
{
    $frag_html = "";
    ob_start();

    // Show current and upcoming appointments.
    //
    // Recurring appointment support and Appointment Display Sets
    // added to Appointments by Ian Jardine ( epsdky ).
    //
    $current_date2 = date('Y-m-d');
    $events = array();
    $apptNum = (int)$GLOBALS['number_of_appts_to_show'];
    if ($apptNum != 0) {
        $apptNum2 = abs($apptNum);
    } else {
        $apptNum2 = 10;
    }
    
    //
    $mode1 = !$GLOBALS['appt_display_sets_option'];
    $colorSet1 = $GLOBALS['appt_display_sets_color_1'];
    $colorSet2 = $GLOBALS['appt_display_sets_color_2'];
    $colorSet3 = $GLOBALS['appt_display_sets_color_3'];
    $colorSet4 = $GLOBALS['appt_display_sets_color_4'];
    //
    if ($mode1) {
        $extraAppts = 1;
    } else {
        $extraAppts = 6;
    }
    
    $events = fetchNextXAppts($current_date2, $pid, $apptNum2 + $extraAppts, true);
    //////
    if ($events) {
        $selectNum = 0;
        $apptNumber = count($events);
        //
        if ($apptNumber <= $apptNum2) {
            $extraApptDate = '';
            //
        } elseif ($mode1 && $apptNumber == $apptNum2 + 1) {
            $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
            array_pop($events);
            --$apptNumber;
            $selectNum = 1;
            //
        } elseif ($apptNumber == $apptNum2 + 6) {
            $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
            array_pop($events);
            --$apptNumber;
            $selectNum = 2;
            //
        } else { // mode 2 - $apptNum2 < $apptNumber < $apptNum2 + 6
            $extraApptDate = '';
            $selectNum = 2;
            //
        }
        
        //
        $limitApptIndx = $apptNum2 - 1;
        $limitApptDate = $events[$limitApptIndx]['pc_eventDate'];
        //
        switch ($selectNum) {
            //
            case 2:
                $lastApptIndx = $apptNumber - 1;
                $thisNumber = $lastApptIndx - $limitApptIndx;
                for ($i = 1; $i <= $thisNumber; ++$i) {
                    if ($events[$limitApptIndx + $i]['pc_eventDate'] != $limitApptDate) {
                        $extraApptDate = $events[$limitApptIndx + $i]['pc_eventDate'];
                        $events = array_slice($events, 0, $limitApptIndx + $i);
                        break;
                    }
                }
                
                //
            case 1:
                $firstApptIndx = 0;
                for ($i = 1; $i <= $limitApptIndx; ++$i) {
                    if ($events[$limitApptIndx - $i]['pc_eventDate'] != $limitApptDate) {
                        $firstApptIndx = $apptNum2 - $i;
                        break;
                    }
                }
                
                //
        }
        
        //
        if ($extraApptDate) {
            if ($extraApptDate != $limitApptDate) {
                $apptStyle2 = " style='background-color:" . attr($colorSet3) . ";'";
            } else {
                $apptStyle2 = " style='background-color:" . attr($colorSet4) . ";'";
            }
        }
    }
    
    //////
    
    // appointments expand collapse widget
    $widgetTitle = xl("Appointments");
    $widgetLabel = "appointments";
    $widgetButtonLabel = xl("Add");
    $widgetButtonLink = "return newEvt();";
    $widgetButtonClass = "";
    $linkMethod = "javascript";
    $bodyClass = "summary_item small";
    $widgetAuth = $resNotNull // $resNotNull reflects state of query in fetchAppointments
    && (acl_check('patients', 'appt', '', 'write') || acl_check('patients', 'appt', '', 'addonly'));
    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
    $count = 0;
    //
    $toggleSet = true;
    $priorDate = "";
    $therapyGroupCategories = array();
    $query = sqlStatement("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_cattype = 3 AND pc_active = 1");
    while ($result = sqlFetchArray($query)) {
        $therapyGroupCategories[] = $result['pc_catid'];
    }
    
    //
    foreach ($events as $row) { //////
        $count++;
        $dayname = date("l", strtotime($row['pc_eventDate'])); //////
        $dispampm = "am";
        $disphour = substr($row['pc_startTime'], 0, 2) + 0;
        $dispmin  = substr($row['pc_startTime'], 3, 2);
        if ($disphour >= 12) {
            $dispampm = "pm";
            if ($disphour > 12) {
                $disphour -= 12;
            }
        }
        
        $etitle = xl('(Click to edit)');
        if ($row['pc_hometext'] != "") {
            $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
        }
        
        //////
        if ($extraApptDate && $count > $firstApptIndx) {
            $apptStyle = $apptStyle2;
        } else {
            if ($row['pc_eventDate'] != $priorDate) {
                $priorDate = $row['pc_eventDate'];
                $toggleSet = !$toggleSet;
            }
            
            if ($toggleSet) {
                $apptStyle = " style='background-color:" . attr($colorSet2) . ";'";
            } else {
                $apptStyle = " style='background-color:" . attr($colorSet1) . ";'";
            }
        }
        
        //////
        echo "<div " . $apptStyle . ">";
        if (!in_array($row['pc_catid'], $therapyGroupCategories)) {
            echo "<a href='javascript:oldEvt(" . attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']) . ")' title='" . attr($etitle) . "'>";
        } else {
            echo "<span title='" . attr($etitle) . "'>";
        }
        
        echo "<b>" . text(oeFormatShortDate($row['pc_eventDate'])) . ", ";
        echo text(sprintf("%02d", $disphour) .":$dispmin " . xl($dispampm) . " (" . xl($dayname))  . ")</b> ";
        if ($row['pc_recurrtype']) {
            echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . xla("Repeating event") . "' alt='" . xla("Repeating event") . "'>";
        }
        
        echo "<span title='" . generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . "'>";
        echo "<br>" . xlt('Status') . "( " . text($row['pc_apptstatus']) . " ) </span>";
        echo text(xl_appt_category($row['pc_catname'])) . "\n";
        if (in_array($row['pc_catid'], $therapyGroupCategories)) {
            echo "<br><span>" . xlt('Group name') .": " . text(getGroup($row['pc_gid'])['group_name']) . "</span>\n";
        }
        
        if ($row['pc_hometext']) {
            echo " <span style='color:green'> Com</span>";
        }
        
        echo "<br>" . text($row['ufname'] . " " . $row['ulname']);
        echo !in_array($row['pc_catid'], $therapyGroupCategories) ? '</a>' : '<span>';
        echo "</div>\n";
        //////
    }
    
    if ($resNotNull) { //////
        if ($count < 1) {
            echo "&nbsp;&nbsp;" . xlt('No Appointments');
        } else { //////
            if ($extraApptDate) {
                echo "<div style='color:#0000cc;'><b>" . text($extraApptDate) . " ( + ) </b></div>";
            }
        }
        // Show Recall if one exists
        $query = sqlStatement("SELECT * FROM medex_recalls WHERE r_pid = ?", array($pid));
        
        while ($result2 = sqlFetchArray($query)) {
            //tabYourIt('recall', 'main/messages/messages.php?go=' + choice);
            //parent.left_nav.loadFrame('1', tabNAME, url);
            echo "&nbsp;&nbsp<b>Recall: <a onclick=\"top.left_nav.loadFrame('1', 'rcb', '../interface/main/messages/messages.php?go=addRecall');\">" . text(oeFormatShortDate($result2['r_eventDate'])). " (". text($result2['r_reason']).") </a></b>";
            $count2++;
        }
        //if there is no appt and no recall
        if (($count < 1) && ($count2 < 1)) {
            echo "<br /><br />&nbsp;&nbsp;<a onclick=\"top.left_nav.loadFrame('1', 'rcb', '../interface/main/messages/messages.php?go=addRecall');\">" . xlt('No Recall') . "</a>";
        }
        $count =0;
        echo "</div>";
    }
    echo '</div> <!-- This is required by expand_collapse_widget(). -->';

    /* Widget that shows recurrences for appointments. */
    if ($GLOBALS['appt_recurrences_widget']) {
        $widgetTitle = xl("Recurrent Appointments");
        $widgetLabel = "recurrent_appointments";
        $widgetButtonLabel = xl("Add");
        $widgetButtonLink = "return newEvt();";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = false;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $count = 0;
        $toggleSet = true;
        $priorDate = "";
        
        //Fetch patient's recurrences. Function returns array with recurrence appointments' category, recurrence pattern (interpreted), and end date.
        $recurrences = fetchRecurrences($pid);
        if (empty($recurrences)) { //if there are no recurrent appointments:
            echo "<div>";
            echo "<span>" . "&nbsp;&nbsp;" . xlt('None') . "</span>";
            echo "</div></div>";
        } else {
            foreach ($recurrences as $row) {
                //checks if there are recurrences and if they are current (git didn't end yet)
                if (!recurrence_is_current($row['pc_endDate'])) {
                    continue;
                }
                
                echo "<div>";
                echo "<span>" . xlt('Appointment Category') . ": <b>" . xlt($row['pc_catname']) . "</b></span>";
                echo "<br>";
                echo "<span>" . xlt('Recurrence') . ': ' . text($row['pc_recurrspec']) . "</span>";
                echo "<br>";
                $red_text = ""; //if ends in a week, make font red
                if (ends_in_a_week($row['pc_endDate'])) {
                    $red_text = " style=\"color:red;\" ";
                }
                
                echo "<span" . $red_text . ">" . xlt('End Date') . ': ' . text(oeFormatShortDate($row['pc_endDate'])) . "</span>";
                echo "</div>";
            }
            
            echo "</div>";
        }
    }
    echo '</div> <!-- This is required by expand_collapse_widget(). -->';
    /* End of recurrence widget */

    // Show PAST appointments.
    // added by Terry Hill to allow reverse sorting of the appointments
    $direction = "ASC";
    if ($GLOBALS['num_past_appointments_to_show'] < 0) {
        $direction = "DESC";
        ($showpast = -1 * $GLOBALS['num_past_appointments_to_show']);
    } else {
        $showpast = $GLOBALS['num_past_appointments_to_show'];
    }
    
    if ($showpast > 0) {
        $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
            "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
            "c.pc_catname, e.pc_apptstatus " .
            "FROM openemr_postcalendar_events AS e, users AS u, " .
            "openemr_postcalendar_categories AS c WHERE " .
            "e.pc_pid = ? AND e.pc_eventDate < CURRENT_DATE AND " .
            "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
            "ORDER BY e.pc_eventDate " . escape_sort_order($direction) . " , e.pc_startTime DESC " .
            "LIMIT " . escape_limit($showpast);
        
        $pres = sqlStatement($query, array($pid));
        
        // appointments expand collapse widget
        $widgetTitle = xl("Past Appointments");
        $widgetLabel = "past_appointments";
        $widgetButtonLabel = '';
        $widgetButtonLink = '';
        $widgetButtonClass = '';
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = false; //no button
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $count = 0;
        while ($row = sqlFetchArray($pres)) {
            $count++;
            $dayname = date("l", strtotime($row['pc_eventDate']));
            $dispampm = "am";
            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
            $dispmin  = substr($row['pc_startTime'], 3, 2);
            if ($disphour >= 12) {
                $dispampm = "pm";
                if ($disphour > 12) {
                    $disphour -= 12;
                }
            }
            
            if ($row['pc_hometext'] != "") {
                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
            }
            
            echo "<a href='javascript:oldEvt(" . attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']) . ")' title='" . attr($etitle) . "'>";
            echo "<b>" . text(xl($dayname) . ", " . oeFormatShortDate($row['pc_eventDate'])) . "</b> " . xlt("Status") .  "(";
            echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
            echo text("$disphour:$dispmin ") . xlt($dispampm) . " ";
            echo text($row['fname'] . " " . $row['lname']) . "</a><br>\n";
        }
            
        if (isset($pres) && $res != null) {
            if ($count < 1) {
                echo "&nbsp;&nbsp;" . xlt('None');
            }
            
            echo "</div>";
        }
    }
    echo '</div> <!-- This is required by expand_collapse_widget(). -->';
    // END of past appointments
?>
<script>
function oldEvt(apptdate, eventid) {
    let title = '<?php echo xla('Appointments'); ?>';
    dlgopen('../../main/calendar/add_edit_event.php?date=' + apptdate + '&eid=' + eventid, '_blank', 725, 500, '', title);
  }

function newEvt() {
    let title = '<?php echo xla('Appointments'); ?>';
    let url = '../../main/calendar/add_edit_event.php?patientid=<?php echo htmlspecialchars($pid, ENT_QUOTES); ?>';
    dlgopen(url, '_blank', 725, 500, '', title);
    return false;
}
</script>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
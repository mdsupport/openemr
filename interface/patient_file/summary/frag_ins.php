<?php
/**
 *
 * Patient summary screen fragment - Insurance.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_ins($pid, $info)
{
    $frag_html = "";
    ob_start();

    $insurance_count = 0;
    foreach (array('primary','secondary','tertiary') as $instype) {
        $enddate = 'Present';
        $query = "SELECT * FROM insurance_data WHERE " .
            "pid = ? AND type = ? " .
            "ORDER BY date DESC";
        $res = sqlStatement($query, array($pid, $instype));
        while ($row = sqlFetchArray($res)) {
            if ($row['provider']) {
                $insurance_count++;
            }
        }
    }
    
    if ($insurance_count > 0) {
        // Insurance expand collapse widget
        $widgetTitle = xl("Insurance");
        $widgetLabel = "insurance";
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
        
        if ($insurance_count > 0) {
?>
        <ul class="tabNav">
<?php
        ///////////////////////////////// INSURANCE SECTION
        $first = true;
        foreach (array('primary','secondary','tertiary') as $instype) {
            $query = "SELECT * FROM insurance_data WHERE " .
            "pid = ? AND type = ? " .
            "ORDER BY date DESC";
            $res = sqlStatement($query, array($pid, $instype));

            $enddate = 'Present';

            while ($row = sqlFetchArray($res)) {
                if ($row['provider']) {
                    $ins_description  = ucfirst($instype);
                                                $ins_description = xl($ins_description);
                    $ins_description  .= strcmp($enddate, 'Present') != 0 ? " (".xl('Old').")" : "";
                    ?>
                    <li <?php echo $first ? 'class="current"' : '' ?>><a href="#">
                                <?php echo htmlspecialchars($ins_description, ENT_NOQUOTES); ?></a></li>
                                <?php
                                $first = false;
                }

                $enddate = $row['date'];
            }
        }

                    // Display the eligibility tab
                    echo "<li><a href='#'>" .
                        htmlspecialchars(xl('Eligibility'), ENT_NOQUOTES) . "</a></li>";

?>
        </ul>
<?php } ?>

        <div class="tabContainer">
<?php
        $first = true;
        foreach (array('primary','secondary','tertiary') as $instype) {
            $enddate = 'Present';

            $query = "SELECT * FROM insurance_data WHERE " .
            "pid = ? AND type = ? " .
            "ORDER BY date DESC";
            $res = sqlStatement($query, array($pid, $instype));
            while ($row = sqlFetchArray($res)) {
                if ($row['provider']) {
?>
            <div class="tab <?php echo $first ? 'current' : '' ?>">
            <table border='0' cellpadding='0' width='100%'>
<?php
            $icobj = new InsuranceCompany($row['provider']);
            $adobj = $icobj->get_address();
            $insco_name = trim($icobj->get_name());
?>
                    <tr>
                     <td valign='top' colspan='3'>
                      <span class='text'>
                        <?php
                        if (strcmp($enddate, 'Present') != 0) {
                            echo htmlspecialchars(xl("Old"), ENT_NOQUOTES)." ";
                        }
                        ?>
                        <?php $tempinstype=ucfirst($instype);
                        echo htmlspecialchars(xl($tempinstype.' Insurance'), ENT_NOQUOTES); ?>
                        <?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
                        <?php echo htmlspecialchars(xl('from', '', ' ', ' ').$row['date'], ENT_NOQUOTES); ?>
                        <?php } ?>
                                <?php echo htmlspecialchars(xl('until', '', ' ', ' '), ENT_NOQUOTES);
                                echo (strcmp($enddate, 'Present') != 0) ? $enddate : htmlspecialchars(xl('Present'), ENT_NOQUOTES); ?>:</span>
                         </td>
                        </tr>
                        <tr>
                         <td valign='top'>
                          <span class='text'>
                            <?php
                            if ($insco_name) {
                                echo htmlspecialchars($insco_name, ENT_NOQUOTES) . '<br>';
                                if (trim($adobj->get_line1())) {
                                    echo htmlspecialchars($adobj->get_line1(), ENT_NOQUOTES) . '<br>';
                                    echo htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(), ENT_NOQUOTES);
                                }
                            } else {
                                echo "<font color='red'><b>".htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES)."</b></font>";
                            }
                            ?>
                          <br>
                            <?php echo htmlspecialchars(xl('Policy Number'), ENT_NOQUOTES); ?>:
                            <?php echo htmlspecialchars($row['policy_number'], ENT_NOQUOTES) ?><br>
                            <?php echo htmlspecialchars(xl('Plan Name'), ENT_NOQUOTES); ?>:
                            <?php echo htmlspecialchars($row['plan_name'], ENT_NOQUOTES); ?><br>
                            <?php echo htmlspecialchars(xl('Group Number'), ENT_NOQUOTES); ?>:
                            <?php echo htmlspecialchars($row['group_number'], ENT_NOQUOTES); ?></span>
                         </td>
                         <td valign='top'>
                            <span class='bold'><?php echo htmlspecialchars(xl('Subscriber'), ENT_NOQUOTES); ?>: </span><br>
                            <span class='text'><?php echo htmlspecialchars($row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname'], ENT_NOQUOTES); ?>
                        <?php
                        if ($row['subscriber_relationship'] != "") {
                            echo "(" . htmlspecialchars($row['subscriber_relationship'], ENT_NOQUOTES) . ")";
                        }
                        ?>
                      <br>
                        <?php echo htmlspecialchars(xl('S.S.'), ENT_NOQUOTES); ?>:
                        <?php echo htmlspecialchars($row['subscriber_ss'], ENT_NOQUOTES); ?><br>
                        <?php echo htmlspecialchars(xl('D.O.B.'), ENT_NOQUOTES); ?>:
                        <?php
                        if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") {
                            echo htmlspecialchars($row['subscriber_DOB'], ENT_NOQUOTES);
                        }
                        ?><br>
                        <?php echo htmlspecialchars(xl('Phone'), ENT_NOQUOTES); ?>:
                        <?php echo htmlspecialchars($row['subscriber_phone'], ENT_NOQUOTES); ?>
                      </span>
                     </td>
                     <td valign='top'>
                      <span class='bold'><?php echo htmlspecialchars(xl('Subscriber Address'), ENT_NOQUOTES); ?>: </span><br>
                      <span class='text'><?php echo htmlspecialchars($row['subscriber_street'], ENT_NOQUOTES); ?><br>
                        <?php echo htmlspecialchars($row['subscriber_city'], ENT_NOQUOTES); ?>
                        <?php
                        if ($row['subscriber_state'] != "") {
                            echo ", ";
                        }

                        echo htmlspecialchars($row['subscriber_state'], ENT_NOQUOTES); ?>
                        <?php
                        if ($row['subscriber_country'] != "") {
                            echo ", ";
                        }

                        echo htmlspecialchars($row['subscriber_country'], ENT_NOQUOTES); ?>
                        <?php echo " " . htmlspecialchars($row['subscriber_postal_code'], ENT_NOQUOTES); ?></span>

                    <?php if (trim($row['subscriber_employer'])) { ?>
                      <br><span class='bold'><?php echo htmlspecialchars(xl('Subscriber Employer'), ENT_NOQUOTES); ?>: </span><br>
                      <span class='text'><?php echo htmlspecialchars($row['subscriber_employer'], ENT_NOQUOTES); ?><br>
                        <?php echo htmlspecialchars($row['subscriber_employer_street'], ENT_NOQUOTES); ?><br>
                        <?php echo htmlspecialchars($row['subscriber_employer_city'], ENT_NOQUOTES); ?>
                        <?php
                        if ($row['subscriber_employer_city'] != "") {
                            echo ", ";
                        }

                        echo htmlspecialchars($row['subscriber_employer_state'], ENT_NOQUOTES); ?>
                        <?php
                        if ($row['subscriber_employer_country'] != "") {
                            echo ", ";
                        }

                        echo htmlspecialchars($row['subscriber_employer_country'], ENT_NOQUOTES); ?>
                        <?php echo " " . htmlspecialchars($row['subscriber_employer_postal_code'], ENT_NOQUOTES); ?>
                      </span>
                    <?php } ?>

                     </td>
                    </tr>
                    <tr>
                     <td>
                    <?php if ($row['copay'] != "") { ?>
                          <span class='bold'><?php echo htmlspecialchars(xl('CoPay'), ENT_NOQUOTES); ?>: </span>
                          <span class='text'><?php echo htmlspecialchars($row['copay'], ENT_NOQUOTES); ?></span>
                          <br />
                    <?php } ?>
                          <span class='bold'><?php echo htmlspecialchars(xl('Accept Assignment'), ENT_NOQUOTES); ?>:</span>
                          <span class='text'>
                    <?php
                                if ($row['accept_assignment'] == "TRUE") {
                                    echo xl("YES");
                                }
                                if ($row['accept_assignment'] == "FALSE") {
                                    echo xl("NO");
                                }
                    ?>
                  </span>
                <?php if (!empty($row['policy_type'])) { ?>
                  <br />
                          <span class='bold'><?php echo htmlspecialchars(xl('Secondary Medicare Type'), ENT_NOQUOTES); ?>: </span>
                          <span class='text'><?php echo htmlspecialchars($policy_types[$row['policy_type']], ENT_NOQUOTES); ?></span>
                            <?php } ?>
                             </td>
                             <td valign='top'></td>
                             <td valign='top'></td>
                           </tr>

                        </table>
                        </div>
                            <?php
                        } // end if ($row['provider'])
                        $enddate = $row['date'];
                        $first = false;
                    } // end while
        } // end foreach

        // Display the eligibility information
        echo "<div class='tab'>";
        show_eligibility_information($pid, true);
        echo "</div>";

            ///////////////////////////////// END INSURANCE SECTION
            ?>
            </div>
<?php } // ?>

<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
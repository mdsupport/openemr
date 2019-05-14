<?php
/**
 *
 * Patient summary screen fragment - Amendments.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_amend($pid, $info)
{
    $frag_html = "";
    ob_start();

    $widgetTitle = xlt('Amendments');
    $widgetLabel = "amendments";
    $widgetButtonLabel = xlt("Edit");
    $widgetButtonLink = $GLOBALS['webroot'] . "/interface/patient_file/summary/list_amendments.php?id=" . attr($pid);
    $widgetButtonClass = "rx_modal";
    $linkMethod = "html";
    $bodyClass = "summary_item small";
    $widgetAuth = acl_check('patients', 'amendment', '', 'write');
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

    $sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
    $result = sqlStatement($sql, array($pid));

    if (sqlNumRows($result) == 0) {
        echo " <div class='text'>&nbsp;&nbsp;" . xlt('None') . "</div>\n";
    }
    
    while ($row=sqlFetchArray($result)) {
        echo "&nbsp;&nbsp;";
        echo "<a class= '" . $widgetButtonClass . "' href='" . $GLOBALS['webroot'] . "/interface/patient_file/summary/add_edit_amendments.php?id=" . attr($row['amendment_id']) . "' onclick='top.restoreSession()'>" . text($row['amendment_date']);
        echo "&nbsp; " . text($row['amendment_desc']);
        
        echo "</a><br>\n";
    }
?>
    </div> <!-- This is required by expand_collapse_widget(). -->
    <script>
    $(".rx_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        var AddAmendment = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/add_edit_amendments.php"
        };
        var ListAmendments = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/list_amendments.php"
        };
        var title = '<?php echo xla('Amendments'); ?>';
        dlgopen('', 'editAmendments', 800, 300, '', title, {
            buttons: [
                {text: '<?php echo xla('Add'); ?>', close: false, style: 'primary  btn-sm', click: AddAmendment},
                {text: '<?php echo xla('List'); ?>', close: false, style: 'primary  btn-sm', click: ListAmendments},
                {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
            ],
            onClosed: 'refreshme',
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
    </script>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
<?php
/**
 *
 * Patient summary screen fragment - Advanced Directives.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_adir($pid, $info)
{
    $frag_html = "";
    ob_start();

    $widgetTitle = xl("Advance Directives");
    $widgetLabel = "directives";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "return advdirconfigure();";
    $widgetButtonClass = "";
    $linkMethod = "javascript";
    $bodyClass = "summary_item small";
    $widgetAuth = true;
    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
    $counterFlag = false; //flag to record whether any categories contain ad records
    $query = "SELECT id FROM categories WHERE name='Advance Directive'";
    $myrow2 = sqlQuery($query);
    if ($myrow2) {
        $parentId = $myrow2['id'];
        $query = "SELECT id, name FROM categories WHERE parent=?";
        $resNew1 = sqlStatement($query, array($parentId));
        while ($myrows3 = sqlFetchArray($resNew1)) {
            $categoryId = $myrows3['id'];
            $nameDoc = $myrows3['name'];
            $query = "SELECT documents.date, documents.id " .
                "FROM documents " .
                "INNER JOIN categories_to_documents " .
                "ON categories_to_documents.document_id=documents.id " .
                "WHERE categories_to_documents.category_id=? " .
                "AND documents.foreign_id=? " .
                "ORDER BY documents.date DESC";
            $resNew2 = sqlStatement($query, array($categoryId, $pid));
            $limitCounter = 0; // limit to one entry per category
            while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                $dateTimeDoc = $myrows4['date'];
                // remove time from datetime stamp
                $tempParse = explode(" ", $dateTimeDoc);
                $dateDoc = $tempParse[0];
                $idDoc = $myrows4['id'];
                echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
                attr_url($pid) . "&document_id=" .
                attr_url($idDoc) . "&as_file=true' onclick='top.restoreSession()'>" .
                text(xl_document_category($nameDoc)) . "</a> " .
                text($dateDoc);
                echo "<br>";
                $limitCounter = $limitCounter + 1;
                $counterFlag = true;
            }
        }
    }
    
    if (!$counterFlag) {
        echo "&nbsp;&nbsp;" . xlt('None');
    } ?>
      </div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
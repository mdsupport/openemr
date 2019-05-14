<?php
/**
 *
 * Patient summary screen fragment - Patient ID Card.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Display image in 'widget style'
function image_widget($doc_id, $doc_catg)
{
    global $pid, $web_root;
    $docobj = new Document($doc_id);
    $image_file = $docobj->get_url_file();
    $image_width = $GLOBALS['generate_doc_thumb'] == 1 ? '' : 'width=100';
    $extension = substr($image_file, strrpos($image_file, "."));
    $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp','.PNG','.JPG','.JPEG','.PNG','.BMP');
    if (in_array($extension, $viewable_types)) { // extension matches list
        $to_url = "<td> <a href = '$web_root" .
        "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false&original_file=true&disable_exit=false&show_original=true'" .
        " onclick='top.restoreSession();' class='image_modal'>" .
        " <img src = '$web_root" .
        "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false'" .
        " $image_width alt='" . attr($doc_catg) . ":" . attr($image_file) . "'>  </a> </td> <td valign='center'>" .
        text($doc_catg) . '<br />&nbsp;' . text($image_file) .
        "</td>";
    } else {
        $to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
            "&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "'" .
            " onclick='top.restoreSession()' class='css_button_small'>" .
            "<span>" .
            xlt("View") . "</a> &nbsp;" .
            text("$doc_catg - $image_file") .
            "</span> </td>";
    }
    
    echo "<table><tr>";
    echo $to_url;
    echo "</tr></table>";
}

function html_frag_pics($pid, $info)
{
    $sql_query = "select d.id, c.name from documents d
         INNER JOIN categories_to_documents cd on d.id = cd.document_id
         INNER JOIN categories c on c.id = cd.category_id
         WHERE d.foreign_id = ? and ((c.name like ?) OR (c.name like ?))
         ORDER BY c.name, d.date DESC";
    $rs = sqlStatement($sql_query, [$pid, $GLOBALS['patient_photo_category_name'], $GLOBALS['patient_id_category_name']]);
    if (!$rs) {
        // Nothing to display 
        return '';
    }

    $photos = [];
    while ($results = sqlFetchArray($rs)) {
        $photos[$results['id']] = $results['name'];
    }

    // If there is an ID Card or any Photos show the widget
    $frag_html = "";
    ob_start();

    echo '<div>'; // Begin images
    $widgetTitle = xl("ID Card") . '/' . xl("Photos");
    $widgetLabel = "photos";
    $linkMethod = "javascript";
    $bodyClass = "notab-right";
    $widgetAuth = false;
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

    foreach ($photos as $photo_doc_id => $doc_cat_name) {
        image_widget($photo_doc_id, $doc_cat_name);
    }

    echo '</div>'; // End images

?>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
<?php
/**
 *
 * Patient summary screen fragment - Tracks.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_trks($pid, $info)
{
    $frag_html = "";
    ob_start();

    // TRACK ANYTHING -----
    // track_anything expand collapse widget
    $widgetTitle = xl("Tracks");
    $widgetLabel = "track_anything";
    $widgetButtonLabel = xl("Tracks");
    $widgetButtonLink = "../../forms/track_anything/create.php";
    $widgetButtonClass = "";
    $widgetAuth = "";  // don't show the button
    $linkMethod = "html";
    $bodyClass = "notab";
    // check to see if any tracks exist
    $spruch = "SELECT id " .
        "FROM forms " .
        "WHERE pid = ? " .
        "AND formdir = ? ";
    $existTracks = sqlQuery($spruch, array($pid, "track_anything"));
    
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
?>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      <script>
          ajaxLoad("#track_anything_ps_expand", "track_anything_fragment.php");
      </script>
    </div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
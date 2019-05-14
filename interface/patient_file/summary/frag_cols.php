<?php
/**
 *
 * Patient summary screen fragment - Columns container of previous widgets(main).
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function html_frag_cols($pid, $cols)
{
    $frag_html = "";
    ob_start();
?>
<div class="container-fluid mt-2">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-8 px-1">
      <?php
      foreach ($cols['col1']->get_info() as $frkey => $objFr) {
          echo $objFr->get_html();
      }
      ?>
    </div>
    <div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4 px-1">
      <?php
      foreach ($cols['col2']->get_info() as $frkey => $objFr) {
          echo $objFr->get_html();
      }
      ?>
    </div>
  </div>
</div>
<?php
    $frag_html = ob_get_clean();
    return $frag_html;
}
?>
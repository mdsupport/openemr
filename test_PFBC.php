<?php

session_name("OpenEMR");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once($_SESSION['globals_if']);

use OpenEMR\Core\Assets;
use OpenEMR\OeUI\OeForm;

$objAssets = new Assets();
// Include model code here for form values and settings.
// Build list of assets to include and exclude as needed.

echo $objAssets->domdocBegin(['title' => 'Test of PFBC wrapper']);
// Head stuff below

// Begin body
echo '</head><body>';


$tFm = new OeForm();
$tFm->open ("test");
echo "<legend>Basic test of PFBC wrapper</legend>";
$tFm->Email ("Email Address:", "email", ["required" => 1]);
$tFm->Password ("Password:", "password", array("required" => 1));
$tFm->Checkbox ("", "remember", array("1" => "Remember me"));
$tFm->Button ("Login");
$tFm->Button ("Cancel", "button", array("onclick" => "history.go(-1);"));
$tFm->close (false);

?>
<script>
$(function() {
    $('.form-control')
    .addClass('form-control-sm py-0');
});
</script>
</body>
</html>

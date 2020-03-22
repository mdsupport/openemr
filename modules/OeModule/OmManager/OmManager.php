<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once (dirname(__DIR__, 3)."/interface/globals.php");

Use OpenEMR\Modules\OeModule;
Use OpenEMR\Modules\OeModuleManager;
use OpenEMR\Core\Assets;

$objAssets = new Assets();
// Include model code here for form values and settings.

$htmCards = '';
$objModMgr = new OeModuleManager();
$oeMods = $objModMgr->getAllModules();
foreach ($oeMods as $modName => $modProps) {
    $htmProps = '';
    /*
    foreach ($modProps as $pK => $pV) {
        $htmProps .= sprintf('%s:%s<br>', $pK, $pV);
    }
    */
    $htmCards .= sprintf(
        '<div class="card col m-1 p-0">
            <div class="card-header my-0 py-0">
                <div class="float-left">%s</div>
                <div class="oemod-setting float-right text-muted" data-modclass="%s" data-modtype="%s">
                    <i class="fa fa-cog"></i>
                </div>
            </div>
            <div class="card-body m-0 p-1">
              <span class="card-title h6 m-0 p-0">%s</span>
              <p class="card-text small my-0 py-0">
                  %s
              </p>
            </div>
            <div class="%s my-0 py-0">
              <small class="text-muted">v%s.</small>
            </div>
          </div>',
        $modProps['Name'], $modName, $modProps['type'], $modProps['Desc'], $htmProps,
        ($modProps['Version']=='' ? 'd-none':'card-footer'), $modProps['Version']
    );
}
// Wrap cards in container
$htmCards = sprintf(
    '<div class="container-fluid">
        <div class="row row-cols-4 text-center">
            %s
        </div>
    </div>',
    $htmCards
);
// Build list of assets to include and exclude as needed.

echo $objAssets->domdocBegin([
    'title' => 'OpenEMR Modules Manager',
    'assets_inc' => []
]);
// Insert html Head stuff below by ending and restarting php block 
?>

<?php
// Begin body
echo '</head>';
?>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Optional Modules</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">List</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>
<?php echo $htmCards ?>
<script>
$('div.oemod-setting').on('click', function() {
    if ($(this).data('modtype') == 'zhModule') {
        window.location = "../../../interface/modules/zend_modules/public/Installer";
    } else {
        window.location = 'OmManagerActions.php?mod='+encodeURIComponent($(this).data('modclass'));
    }
});
</script>
</body>
</html>
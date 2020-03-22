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

// Expect a module class
$modCls = urldecode($_GET['mod']);
// @TBD - Error handling

$objMod = new $modCls;
$aaModProps = [
    'Name' => $objMod->getProp('Name'),
];

// Build list of assets to include and exclude as needed.

echo $objAssets->domdocBegin([
    'title' => 'OpenEMR Module '.$aaModProps['Name'],
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
  <a class="navbar-brand" href="#"><?php echo $aaModProps['Name'] ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active mx-3">
        <a class="nav-link" href="#">Summary</a>
      </li>
      <li class="nav-item dropdown mx-3">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
          Actions
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="#">Install</a>
          <a class="dropdown-item" href="#">Configure</a>
          <a class="dropdown-item" href="#">Enable</a>
          <a class="dropdown-item" href="#">Upgrade</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Disable</a>
        </div>
      </li>
      <li class="nav-item mx-3">
        <a class="nav-link" href="OmManager.php" tabindex="-1">Module Manager</a>
      </li>
    </ul>
  </div>
</nav>
</body>
</html>
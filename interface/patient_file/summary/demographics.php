<?php
/**
 *
 * Patient summary screen.
*
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

session_name("OpenEMR");
session_start();

if (file_exists('patient_cards.php')) {
    header('Location:patient_cards.php');
    exit;
}

require_once($_SESSION['globals_if']);
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("../history/history.inc.php");
require_once("$srcdir/edi.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/options.js.php");
require_once("$srcdir/group.inc");
require_once('portal_fragment.php');

use OpenEMR\Core\Header;

if (isset($_GET['set_pid'])) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

class clsFragment {
    private $id, $title, $disp, $info, $html = '';

    /**
     * Required properties for use in demographics shell - id, title, disp
     * All other values needed by *fragment*.php should be passed in info array 
     *
     * @param string $id - Used to get fragment code from function html_frag_$id in frag_$id.php
     * @param string $title - For readability and future use
     * @param boolean $disp - Conditions to display the fragment 
     * @param array $info - Optional information needed by html_frag_$id
     */
    function __construct($id, $title, $disp = true, $info=[])
    {
        $this->id = attr($id);
        $this->title = attr($title);
        $this->disp = ($disp ? true : false);
        $this->info = $info;

        if ($this->disp) {
            require_once("frag_".$this->id.".php");
            $fn = 'html_frag_'.$this->id;
            $frag_html = (function_exists($fn) ? call_user_func($fn, $_SESSION['pid'], $info) : "Configuration error");
            $this->set_html($frag_html);
        }
    }

    public function set_html($strHtml = null)
    {
        $this->html = $strHtml;
    }

    public function get_info() {
        return $this->info;
    }

    public function get_html()
    {
        return $this->html;
    }
}

// Get patient/employer/insurance information.
//
$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
?>
<html>

<head>

    <?php Header::setupHeader(['common', 'qtip2']); ?>

<script type="text/javascript" language="JavaScript">

//Use AddAlert function to populate the following object
var intAlerts = 0;
var objAlerts = {}
var ajaxCalls = 0;
var alertsAPI;

function ajaxLoad(jqSel, datSrc, objArgs, fnCallback) {
 // Debug aid
    function setAlert(alert, action)
    {
        if (!('status' in objAlerts)) {
            objAlerts.status = {txt: 'Status'};
            objAlerts.status.catAlerts = [];
        }
        if (action == 'add') {
            objAlerts.status.catAlerts.push({txt: alert});
        } else {
            let status = objAlerts.status.catAlerts;
            let seqMax = status.length;
            let seq = 0;
            while (seq < seqMax ) {
                if (objAlerts.status.catAlerts[seq].txt == alert) {
                    objAlerts.status.catAlerts.splice(seq, 1);
                    break;
                }
                seq++;
            }
            if (status.length == 0) {
                delete objAlerts.status;
            }
        }
    }

    top.restoreSession();
    $(jqSel).html('<div class="h6"><?php echo xlt("Data fetch in progress.")?></div>');
    ajaxCalls++;
    setAlert(jqSel, 'add');
    $(jqSel).load(datSrc, objArgs, function() {
        setAlert(jqSel, 'remove');
        ajaxCalls--;
        if (typeof fnCallback === "function") {
            fnCallback;
        }
        showPtAlerts();
    });
}

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 400, 500);
  }

 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() { // @todo don't think this is used any longer!!
  dlgopen('../deleter.php?patient=<?php echo htmlspecialchars($pid, ENT_QUOTES); ?>', '_blank', 500, 450, '', '',{
      allowResize: false,
      allowDrag: false,
      dialogId: 'patdel',
      type: 'iframe'
  });
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
   top.clearPatient();
    <?php } else { ?>
   parent.left_nav.clearPatient();
    <?php } ?>
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

</script>

<script type="text/javascript">

function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'), ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'), ENT_QUOTES); ?>" );
        $("#"+div).hide();
    $.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'), ENT_QUOTES); ?>" );
        $("#"+div).show();
    $.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }
}

// edit prescriptions dialog.
// called from stats.php.
//
function editScripts(url) {
    var AddScript = function () {
        var iam = top.tab_mode ? top.frames.editScripts : window[0];
        iam.location.href = "<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo attr($pid);?>"
    };
    var ListScripts = function () {
        var iam = top.tab_mode ? top.frames.editScripts : window[0];
        iam.location.href = "<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo attr($pid); ?>"
    };

    let title = '<?php echo xla('Prescriptions'); ?>';
    let w = 810;
    <?php if ($GLOBALS['weno_rx_enable']) {
        echo 'w = 910;'; }?>


    dlgopen(url, 'editScripts', w, 300, '', '', {
        buttons: [
            {text: '<?php echo xla('Add'); ?>', close: false, style: 'primary  btn-sm', click: AddScript},
            {text: '<?php echo xla('List'); ?>', close: false, style: 'primary  btn-sm', click: ListScripts},
            {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
        ],
        onClosed: 'refreshme',
        allowResize: true,
        allowDrag: true,
        dialogId: 'editscripts',
        type: 'iframe'
    });
}

function doPublish() {
    let title = '<?php echo xla('Publish Patient to FHIR Server'); ?>';
    let url = top.webroot_url + '/phpfhir/providerPublishUI.php?patient_id=<?php echo attr($pid); ?>';

    dlgopen(url, 'publish', 'modal-mlg', 750, '', '', {
        buttons: [
            {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
        ],
        allowResize: true,
        allowDrag: true,
        dialogId: '',
        type: 'iframe'
    });
}

$(function(){
  var msg_updation='';
    <?php
    if ($GLOBALS['erx_enable']) {
        //$soap_status=sqlQuery("select soap_import_status from patient_data where pid=?",array($pid));
        $soap_status=sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')", array($pid));
        while ($row_soapstatus=sqlFetchArray($soap_status)) {
            //if($soap_status['soap_import_status']=='1' || $soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_patientfullmedication.php",
                dataType: "html",
                data: {
                    patient:<?php echo $row_soapstatus['pid']; ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            //}
            //elseif($soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_allergy.php",
                dataType: "html",
                data: {
                    patient:<?php echo $row_soapstatus['pid']; ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            if ($GLOBALS['erx_import_status_message']) { ?>
            if(msg_updation)
              alert(msg_updation);
            <?php
            }

            //}
        }
    }
    ?>
    // load divs
    /*
    $("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {});
    $("#pnotes_ps_expand").load("pnotes_fragment.php");
    $("#disclosures_ps_expand").load("disc_fragment.php");
    */
    ajaxLoad("#pnotes_ps_expand", "pnotes_fragment.php");
    ajaxLoad("#disclosures_ps_expand", "disc_fragment.php");

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      ajaxLoad("#clinical_reminders_ps_expand", "clinical_reminders_fragment.php");
    <?php } // end crw?>

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
    /*
      top.restoreSession();
      $("#patient_reminders_ps_expand").load("patient_reminders_fragment.php");
    */
        ajaxLoad("#patient_reminders_ps_expand", "patient_reminders_fragment.php");
    <?php } // end prw?>

    // Initialize labdata
    /*
    $("#labdata_ps_expand").load("labdata_fragment.php");
    */
    ajaxLoad("#labdata_ps_expand", "labdata_fragment.php");

    tabbify();

// modal for dialog boxes
    $(".large_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 1000, 600, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

// modal for image viewer
    $(".image_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 400, 300, '', '<?php echo xla('Patient Images'); ?>', {
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".deleter").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 600, 360, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            //onClosed: 'imdeleted',
            allowResize: false,
            allowDrag: false,
            dialogId: 'patdel',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".iframe1").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 350, 300, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
// for patient portal
  $(".small_modal").on('click', function(e) {
      e.preventDefault();e.stopPropagation();
      dlgopen('', '', 380, 200, '', '', {
          buttons: [
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
          ],
          allowResize: true,
          allowDrag: true,
          dialogId: '',
          type: 'iframe',
          url: $(this).attr('href')
      });
  });

});

//JavaScript stuff to do when a new patient is set.
//
function setMyPatient() {
 return;
 // Avoid race conditions with loading of the left_nav or Title frame.
 if (!parent.allFramesLoaded()) {
  setTimeout("setMyPatient()", 500);
  return;
 }
<?php if ((isset($_GET['set_pid']) ) && (isset($_GET['set_encounterid'])) && ( intval($_GET['set_encounterid']) > 0 )) {
    $encounter = intval($_GET['set_encounterid']);
    $_SESSION['encounter'] = $encounter;
    $query_result = sqlQuery("SELECT `date` FROM `form_encounter` WHERE `encounter` = ?", array($encounter)); ?>
 encurl = 'encounter/encounter_top.php?set_encounter=' + <?php echo js_url($encounter);?> + '&pid=' + <?php echo js_url($pid);?>;
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
  parent.left_nav.setEncounter(<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date'])))); ?>, <?php echo js_escape($encounter); ?>, 'enc');
    top.restoreSession();
  parent.left_nav.loadFrame('enc2', 'enc', 'patient_file/' + encurl);
    <?php } else { ?>
  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.setEncounter(<?php echo js_escape(attr(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date']))))); ?>, <?php echo js_escape($encounter); ?>, othername);
    top.restoreSession();
  parent.frames[othername].location.href = '../' + encurl;
    <?php } ?>
<?php } // end setting new encounter id (only if new pid is also set) ?>
}

$(window).on('load', function() {
    setMyPatient();
});

</script>

<style type="text/css">

#pnotes_ps_expand {
  height:auto;
  width:100%;
}

<?php
// This is for layout font size override.
$grparr = array();
getLayoutProperties('DEM', $grparr, 'grp_size');
if (!empty($grparr['']['grp_size'])) {
    $FONTSIZE = $grparr['']['grp_size'];
?>
/* Override font sizes in the theme. */
#DEM .groupname {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .label {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data td {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
<?php } ?>

/* qTip2 seems to ignore width setting suggested in documentation without this */
.qtip-custom {
    max-width: none !important;
}
</style>

</head>

<body class="body_top patient-demographics">
<?php
$thisauth = acl_check('patients', 'demo');
if (!$thisauth) {
    echo "<p>(" . htmlspecialchars(xl('Demographics not authorized'), ENT_NOQUOTES) . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

// Determine if the Vitals form is in use for this site and any vitals exist for this patient.
$existVitals = false;
$vitals_is_registered = sqlQuery("SELECT directory FROM registry WHERE directory=? AND state=?", ['vitals', 1]);
if ($vitals_is_registered) {
    $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid));
}

// Show Clinical Reminders for any user that has rules that are permitted.
$clin_rem_check = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
$clin_rem_show = (!empty($clin_rem_check) && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw'] &&
    acl_check('patients', 'alert'));

// Determine if track_anything form is in use for this site.
$track_is_registered = sqlQuery("SELECT directory FROM registry WHERE directory=? AND state=?", ['track_anything', 1]);

// All values used by fragments should be set before this statement.
$frag = [

    'bday' => new clsFragment('bday', 'Birthday Reminder', $GLOBALS['patient_birthday_alert'],
        ['popurl' => sprintf('../birthday_alert/birthday_pop.php?pid=%s&user_id=%s', attr($pid), attr($_SESSION['authId']))]
    ),

    'cdrs' => new clsFragment('cdrs', 'Clinical Rules', $GLOBALS['enable_cdr'],
        []
    ),
    
    'phdr' => new clsFragment('phdr', 'Patient Header', $thisauth),

    'pmenu' => new clsFragment('phdr2', 'Patient Menu'),

    'cols'  => new clsFragment('cols', 'Main container', true,
        [
            'col1' => new clsFragment('col1', 'col-9', true, [
                'bill' => new clsFragment('bill', 'Billing', (!$GLOBALS['hide_billing_widget']), []),
                'demo' => new clsFragment('demo', 'Demographics', acl_check('patients', 'demo'), []),
                'ins' => new clsFragment('ins', 'Insurance', acl_check('patients', 'demo'), []),
                'pnote' => new clsFragment('pnote', 'Notes', acl_check('patients', 'notes'), []),
                'rem' => new clsFragment('rem', 'Reminders',
                            (acl_check('patients', 'reminder') && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']), []),
                'disc' => new clsFragment('disc', 'Disclosures', acl_check('patients', 'disclosure'), []),
                'amend' => new clsFragment('amend', 'Amendments', ($GLOBALS['amendments'] && acl_check('patients', 'amendment')), []),
                'labs' => new clsFragment('labs', 'Disclosures', acl_check('patients', 'lab'), []),
                'vitals' => new clsFragment('vitals', 'Vitals', ($existVitals && acl_check('patients', 'med')), []),
                'trends' => new clsFragment('trends', 'Trends'),
                ]),

            'col2' => new clsFragment('col2', 'col-3', true, [
                'pics' => new clsFragment('pics', 'ID Card', $GLOBALS['patient_id_category_name'], []),
                'adir' => new clsFragment('adir', 'Advanced Directives', $GLOBALS['advance_directives_warning'], []),
                'crem' => new clsFragment('crem', 'Clinical Reminders', $clin_rem_show, []),
                'appt' => new clsFragment('appt', 'Appointments', (!$GLOBALS['disable_calendar'] && acl_check('patients', 'appt')), []),
                'iss' => new clsFragment('iss', 'Issues'),
                'trks' => new clsFragment('trks', 'Tracks', $track_is_registered),
                ]),
        ]
    ),

];

foreach ($frag as $frkey => $objFr) {
    echo $objFr->get_html();
}
?>

<script language='JavaScript'>
// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];
checkSkipConditions();
//Setup an alert by providing two objects with minimum property as 
//Category - id, txt
//Alert - seq(numeric), txt
//Allows strings as lazy arguments 
function setPtAlert(objCat, objAlert) {
  if (typeof(objCat) === 'string') {
      objCat = { txt: objCat };
  }
  if (typeof(objAlert) === 'string') {
      objAlert = { txt: objAlert };
  }
  var catId = (("id" in objCat) ? objCat.id : objCat.txt );
  if (!(catId in objAlerts)) {
      objAlerts[catId] = objCat;
  }
  if (!("catAlerts" in objAlerts[catId])) {
      objAlerts[catId]["catAlerts"] = [];
  }
  if ("seq" in objAlert) {
      objAlerts[catId]["catAlerts"][objAlert.seq] = objAlert.txt;
  } else {
      if (objAlert.txt.length > 0) {
          objAlerts[catId]["catAlerts"].push(objAlert);
          intAlerts++;
          $("#pt-alerts .badge").html(intAlerts);
      }
  }
  if (intAlerts > 0) {
    $("#pt-alerts span").removeClass("badge-dark badge-success").addClass("badge-danger");
    showPtAlerts();
  }
}
function listPtAlerts() {
    var htmReturn = '';
    for (var cat in objAlerts) {
        var objCat = objAlerts[cat];
        if ("isHtml" in objCat) {
            htmReturn += objCat.txt;
        } else {
            htmReturn += '<dt class="mt-2">'+objCat.txt+'</dt>';
        }
        var seq = 0;
        var seqMax = objCat.catAlerts.length;
        while (seq < seqMax ) {
            if ("isHtml" in objCat.catAlerts[seq]) {
                htmReturn += objCat.catAlerts[seq].txt;
            } else {
                htmReturn += '<dd class="my-0">'+objCat.catAlerts[seq].txt+'</dd>';
            }
            seq++;
        }
    }
    if (intAlerts > 0) {
        htmReturn = '<dl class="my-0">'+htmReturn+'</dl>';
    } else {
        htmReturn = '<?php echo xlt('None') ?>';
    }
    return htmReturn;
}
function showPtAlerts() {
    setTimeout(function() {
        // console.log ("Show alerts "+ajaxCalls+" / "+intAlerts+" "+((ajaxCalls == 0) && (intAlerts > 0)));
        if ((ajaxCalls == 0) && (intAlerts > 0)) {
            $("#pt-alerts").addClass("text-warning");
            $("#pt-alerts span").removeClass("badge-dark").addClass("badge-danger");
            $("#pt-alerts").qtip().show();
        } else {
            $("#pt-alerts span").removeClass("badge-dark").addClass("badge-warning");
        }
    }, 3000);
}

$(function() {
    $("#pt-alerts").qtip({
        content: {
            text: listPtAlerts,
            title: '<strong><?php echo xlt('Alerts').' / '.xlt('Reminders') ?></strong>',
            button: '<?php echo xlt('Close')?>'
        },
        hide: {
            delay: 3000
        },
        style: {
            classes: 'qtip-bootstrap qtip-custom',
            width: 600,
        }
    });
    alertsAPI = $("#pt-alerts").qtip('api');
    showPtAlerts();

    var pltips = $('.portal-login').qtip({
        content: {
            text: function(event, api) {
                $.ajax({ url: 'create_portallogin.php?portalsite='+$(this).data('portal')+'&patient='+$(this).data('pid') })
                    .done(function(html) {
                        api.set('content.text', html)
                    })
                    .fail(function(xhr, status, error) {
                        api.set('content.text', status + ': ' + error)
                    })

                return 'Loading...';
            }
        },
        style: {
            classes: 'qtip-bootstrap qtip-custom',
            width: 300,
        },
        show: {
            event: 'click'
        },
        hide: {
            event: 'click',
            delay: 100,
        }
    });
 	// Grab the first element in the tooltips array and access its qTip API
    var pltipsapi = pltips.qtip('api');
    $('body').on('click', 'form[name=portallogin] .css_button', function() {
        pltipsapi.hide();
    });

    // mdsupport - Apply bootstrap 4 styles
    $("div.demographics-box > table > tr > td").attr("width", "100%");
    $("div.main.container table")
    .addClass("table table-sm table-condensed table-striped m-0 p-0");
//    .css({"margin":0, "padding":0});
    $("div.main.container table td")
    .addClass("m-1 p-0 border-0");
//    .css({"margin":0, "padding":0, "border":0});
});
function alertSplit(strline)
{
    let seg = strline.split(':');
    if (seg.length > 1) {
        setPtAlert(seg[0], seg[1]);
    } else {
        if (strline.trim().length) {
           setPtAlert(strline, '');
        }
    }
}
//Intercept alerts
(function(alertproxy)
{
    window.alert = function() {
      arguments[0].split("\n").forEach(alertSplit);
      return false; // alertproxy.apply(this, arguments);
    };
  })(window.alert);
</script>

</body>
</html>

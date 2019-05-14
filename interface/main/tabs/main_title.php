<?php
/**
 * Provide data needed for main title block
 * 
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc");

$resp = [
    'status' => 'init',

    'pt' => [
        'pid' => (isset($_GET['pid']) ? intval($_GET['pid']) : ''),
        'name' => '',
        'pubpid' => '',
        'dob_age' => '',
    ],

    'enc' => [
        
    ]
];

$pt = getPatientData($resp['pt']['pid'], "fname, lname, pubpid, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
if (!$pt) {
    $resp['status'] = 'error';
    exit(json_encode($resp));
}

// Fill patient data
$date_of_death = is_patient_deceased($resp['pt']['pid'])['date_deceased'];
$resp['status'] = 'success'; 
$resp['pt']['name'] = trim($pt['fname'].' '.$pt['lname']);
$resp['pt']['pubpid'] = $pt['pubpid'];
$resp['pt']['dob_age'] = implode(' ', [
    xl('DOB').':', oeFormatShortDate($pt['DOB_YMD']),
    (empty($date_of_death) ? xl('Age') : xl('Age at death')). ":",
    (empty($date_of_death) ? getPatientAgeDisplay($pt['DOB_YMD']) : oeFormatAge($pt['DOB_YMD'], $date_of_death)),
]);

// Fill encounter data
$rs = sqlStatement(
    "SELECT fe.encounter, fe.date, cat.pc_catname FROM form_encounter AS fe
    LEFT JOIN openemr_postcalendar_categories cat on fe.pc_catid=cat.pc_catid
    WHERE fe.pid=?
    ORDER BY fe.date desc",
    [$resp['pt']['pid']]
);
while ($enc = sqlFetchArray($rs)) {
    $resp['enc'][] = [
        'encounter' => $enc['encounter'],
        'date' => oeFormatShortDate(date("Y-m-d", strtotime($enc['date']))),
        'cat' => xl_appt_category($enc['pc_catname']),
    ];
}

// Return response
echo json_encode($resp);

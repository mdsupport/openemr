<?php

/**
 * Active lists entries as encounter form.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2023-2025 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function LBFptlists_javascript() {
    global $pid, $encounter, $formid;
    $jsCode = null;
    
    // Must be a new form
    if ($formid !== 0) return;
    
    // Must not be a duplicate entry
    $fmrow = sqlQuery(
        "SELECT form_id FROM forms WHERE pid=? and encounter=? and formdir=? and deleted=0",
        [$pid, $encounter, 'LBFptlists']
    );
    if ($fmrow['form_id']) {
        $jsCode = "
            const idChart = document.getElementById('chart');
            if (idChart) {
              idChart.insertAdjacentHTML('beforebegin', `
                <div class='alert alert-danger text-center py-1 my-3' role='alert'>
                  Patient Information already added to this encounter. Press use that form for updates.
                </div>
              `);
            }
        ";
    } else {
        $rsSum = sqlStatement("
            WITH
            enc_iss_cats AS (
                SELECT option_id cat, seq cat_seq, title cat_desc FROM list_options
                WHERE list_id = 'encisscats'
            ),
            pt_iss_active AS (
                SELECT pid, `type` cat, disp_seq iss_seq, title iss_desc FROM lists
                WHERE enddate IS NULL
            )
            SELECT c.cat cat, GROUP_CONCAT(p.iss_desc ORDER BY iss_seq SEPARATOR ', ') issues
            FROM pt_iss_active p INNER JOIN enc_iss_cats c ON c.cat = p.cat
            WHERE p.pid = ?
            GROUP BY c.cat
            ORDER BY c.cat_seq",
            [$pid]
        );
        if (!$rsSum) return;
        while ($rowSum = sqlFetchArray($rsSum)) {
            $issCat = 'form_'.$rowSum['cat'];
            $issDesc = json_encode($rowSum['issues']);
            $jsCode = ($jsCode ?? "let chkIss = null;") .
                "chkIss = document.getElementById('$issCat');
                if (chkIss) {
                  chkIss.value = $issDesc;
                }
            ";
        }
    }
    
    if ($jsCode) {
        echo "
            document.addEventListener('DOMContentLoaded', function () {
                $jsCode
            });
        ";
    }
}

<?php

/**
 * Signature form view script for encounter module
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

// mdsupport - eSign Batch
// Add following conditions if needed
// - Encounters older than n days
//   AND DATEDIFF(CURDATE(), enc.date) > ?

$batchMax = 50;
$rsOpen = sqlQuery("
    SELECT GROUP_CONCAT(fm.encounter SEPARATOR ',') encs from forms fm
    INNER JOIN form_encounter enc ON fm.form_id = enc.id AND fm.encounter = enc.encounter
    LEFT OUTER JOIN esign_signatures sig ON fm.encounter = sig.tid
    WHERE fm.formdir = ?
      AND fm.deleted = 0
      AND sig.tid IS NULL
      AND ? IN (fm.provider_id, enc.provider_id, enc.supervisor_id)
      AND enc.encounter <> ?
    LIMIT ?
    ",
    [ 'newpatient', $_SESSION['authUserID'], $this->form->encounterId, $batchMax ]
);
$encsOpen = (isset($rsOpen['encs']) ? count(explode(',', $rsOpen['encs'])) : 0);
if ($encsOpen) {
    $encBatch = $this->form->encounterId . ',' . $rsOpen['encs'];
}
?>

<div id='esign-form-container'>
    <form id='esign-signature-form' method='post' action='<?php echo attr($this->form->action); ?>'>
    <div class="modal-header">
       <h6 class="modal-title"><?php echo xlt('eSign encounter');?></h6>
    </div>
    
    <div class="modal-body">

        <div class="gs-hide-element form-group">
              <label class="mb-0" for='password'><?php echo xlt('Encounter Signature');?></label>
              <input class='form-control' type='password' id='password' name='password' placeholder="<?php echo xlt("Your password is your signature"); ?>" />
        </div>

        <?php if ($this->form->showLock) { ?>
        <div class="form-group form-check">
              <input class="form-check-input" type="checkbox" id="lock" name="lock" />
              <label class="form-check-label" for='lock'>
                  <?php echo xlt('Prevent further edits to all forms in this encounter?');?>
              </label>
        </div>
        <?php } ?>

        <div class="form-group">
              <label class="mb-0" for='amendment'><?php echo xlt('Amendment');?></label>
              <textarea  class="form-control" rows="3" name='amendment' id='amendment' placeholder='<?php echo xla("Enter an amendment..."); ?>'></textarea>
        </div>

        <?php if ($encsOpen) { ?>
        <div class="form-group form-check">
              <input class="form-check-input" type="checkbox" id="chkBatchEncs" name="chkBatchEncs" />
              <label class="form-check-label" for='chkBatchEncs'>
                  <?php echo xlt('eSign'). ' '. $encsOpen . ' '. xlt('additional unsigned encounter(s)') . '?';?>
              </label>
        </div>
        <?php } ?>

        <!-- Google sign in for esign -->
        <?php if ($this->form->displayGoogleSignin) { ?>
          <div class="mb-3 mt-2">
            <div class="g_id_signin" data-type="standard" ></div>
            <div>
              <input type="hidden" id="used-google-signin" name="used_google_signin" value="">
              <input type="hidden" id="google-signin-token" name="google_signin_token" value="">
              <div id="google-signin" onclick="return gsi.do_google_signin();">
                  <!-- This message is displayed if the google platform API cannot render the button -->
                  <span id="google-signin-service-unreachable-alert" style="display:none;">
                      <?php echo xlt('Google Sign-In is enabled but the service is unreachable.');?>
                  </span>
              </div>
              <div id="google-signout">
                  <a href="#" onclick="gsi.signOut();"><?php echo xlt('Sign out');?></a>
              </div>
            </div>
          </div>
        <?php } ?>
        </div>

        <input type='hidden' id='table' name='table' value='<?php echo attr($this->form->table); ?>' />
        <input type='hidden' id='encounterId' name='encounterId' value='<?php echo attr($this->form->encounterId); ?>' />
        <input type='hidden' id='userId' name='userId' value='<?php echo attr($this->form->userId); ?>' />

        <div class="modal-footer">
        <input type='submit' class="btn btn-secondary btn-sm" value='<?php echo xla('Back'); ?>' id='esign-back-button' />
        <div class="form-group">
              <input type='button' class="btn btn-primary btn-sm" value='<?php echo xla('Sign'); ?>' id='esign-sign-button-encounter' />
        </div>
        </div>
    </form>
</div>

<script>
<?php if ($encsOpen) { ?>
  // Batch eSign - If checked, use batch otherwise use single
  let toggleBatchEncs = document.getElementById('chkBatchEncs');
  toggleBatchEncs.addEventListener('change', function() {
    let hiddenInput = document.getElementById('encounterId');
    hiddenInput.value = (this.checked ? <?php echo sprintf('"%s":"%s"', attr($encBatch), attr($this->form->encounterId));?>);
  });
<?php } ?>

<?php if ($this->form->displayGoogleSignin) { ?>
    // Google sign in for esign
    let gsi = Object.create(GoogleSigin);
    gsi.init(<?php echo js_escape($this->form->googleSigninClientID); ?>, {
      ele : '#esign-form-container',
      signin_btn : '#esign-sign-button-encounter',
      error_container : '#esign-signature-form'
    });
<?php } ?>
</script>
<!-- End -->

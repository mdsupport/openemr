<?php
/**
 *
 * Patient summary screen fragment - birthday.
 * WARNING - This fragment is called inline 
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
use OpenEMR\Reminder\BirthdayReminder;

function html_frag_bday($pid, $info)
{
    $frag_html = "";

    if ($GLOBALS['patient_birthday_alert']) {
        // To display the birthday alert:
        //  1. The patient is not deceased
        //  2. The birthday is today (or in the past depending on global selection)
        //  3. The notification has not been turned off (or shown depending on global selection) for this year
        $birthdayAlert = new BirthdayReminder($pid, $_SESSION['authId']);
        if ($birthdayAlert->isDisplayBirthdayAlert()) {
            // show the active reminder modal
            $frag_html = sprintf(
                "<script>
                    dlgopen('', 'bdayreminder', 300, 170, '', false, {
                        allowResize: false,
                        allowDrag: true,
                        dialogId: '',
                        type: 'iframe',
                        url: '%s'
                    });
                </script>",
                $info['popurl']
            );
        }
    }

    return $frag_html;
}
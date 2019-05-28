<?php
/**
 * Interface to multiple hybridauth providers.
 * 
 */

$ignoreAuth=true;
require_once("../globals.php");
require_once('hauth_providers.php');

use Hybridauth\Exception\Exception;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

/**
 * Map verified email returned by hybridauth connection to emr user.
 * Should be method for unified user class
 * 
 * @param string $email
 * @return array user
 */
function authEmailUser($email)
{
    $user = [
        'status' => 'init',
        'email' => $email
    ];

    // Check users with matching email or email_direct
    // TBD : Allow LIKE match for emails?
    $matches = sqlQuery(
        'SELECT MIN(id) id, MIN(username) name, MIN(authorized) authorized, COUNT(id) count, "users" type
        FROM users
        WHERE active=1 AND username>? AND email=? OR email_direct=?',
        [" ", $email, $email]
    );
    if ($matches['count'] == 0) {
        // Not sure if portal authorization should look at session 'autorized'
        $matches = sqlQuery(
            'SELECT MIN(pid) id, MIN(CONCAT_WS(" ", fname, lname)) name, 0 authorized, COUNT(pid) count, "patient_data" type
            FROM patient_data
            WHERE IFNULL(deceased_date,0)=0 AND hipaa_allowemail=? AND (email=? OR email_direct=?)',
            ['YES', $email, $email]
            );
    }
    if ($matches['count'] == 1) {
        $user = array_merge($user, $matches, ['status' => 'success']);
    } else {
        $user['status'] = (($matches['count'] == 0) ? 'notfound' : 'duplicates');
    }
    return $user;
}

try {
    // Get services object
    $objAuthSvcs = localAuthSvcs(true);

    /**
     * Initialize session storage.
     */
    $storage = new Session();

    /**
     * Hold information about provider when user clicks on Sign In.
     */
    if (isset($_GET['provider'])) {
        $storage->set('provider', $_GET['provider']);
        $storage->set('user', [
            'emrgroup' => $_GET['group']
        ]);

        /**
         * When invoked, `authenticate()` will redirect users to provider login page.
         * If successful, provider will redirect the users back to Authorization callback URL
         */
        $resp = $objAuthSvcs->authenticate($storage->get('provider'));
    } else {
        $provider = $storage->get('provider');
        if (empty($provider)) {
            // Unexpected call.  Die or Activate alternate code if permited
            die;
        } else {
            // This is a callback from the provider
            $resp = $objAuthSvcs->authenticate($provider);
            if ($resp) {
                $storage->delete('provider');
            }
        }
    }

    /**
     * When provider exists in the storage, clear storage.
     */
    if ($resp) {
        $token = $resp->getAccessToken()['access_token'];
        if ($resp->isConnected()) {
            $storage->set('conn', $token);
            $authProfile = $resp->getUserProfile();
            /*
            foreach ($authProfile as $pattr => $pvalue) {
                if (is_array($pvalue)) $pvalue = implode(' | ', $pvalue);
                printf('<div><div>%s</div><div>%s</div></div>', $pattr, $pvalue);
            }
            */
            
            $user = authEmailUser($authProfile->emailVerified);
            // Add other relevent profie settings before disconnecting
            $user['language'] = $authProfile->language;
            $user['provider'] = $provider;
            $storage->set('user', $user);
            
            // This will erase the current user authentication data from session
            $objAuthSvcs->disconnectAllAdapters();
        } else {
            $storage->set('user', ['status' => $resp]);
        }

        $user = $storage->get('user');
        if ($user['status'] == 'success') {
            // Set destination
            if ($user['type']=='users') {
                $users_url = sprintf(
                    '../main/main_screen.php?hauth=%s&site=%s&group=%s',
                    attr_url($token), attr_url($_SESSION['site_id']), attr_url($_GET['group'])
                    );
                HttpClient\Util::redirect($users_url);
            } else {
                // TBD - Portal login
                printf('%s %s', xlt('Please wait for your'), ($user['type']=='users' ? xlt('dashboard') : xlt('records')));
            }
        }
    }

    /**
     * Redirects user to referrer or the login page
     */
    $user = $storage->get('user');
    $error_hint= (isset($user['email']) ? attr_url($user['email']) : $provider);
    HttpClient\Util::redirect('login.php?hautherr='.$error_hint);
} catch (Exception $e) {
    echo $e->getMessage();
}

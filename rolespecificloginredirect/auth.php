<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication Plugin: User Redirect Plugin
 *
 * @package    auth_rolespecificloginredirect
 *
 * @copyright  2020 onwards Igor Nesterow <Igor.Nesterow@b-tu.de>
 * @copyright  2021 onwards Eleonora Kostova <kostoele@b-tu.de>
 * @copyright  based on Email Authentication Plugin by Martin Dougiamas (http://dougiamas.com)
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/authlib.php');

class auth_plugin_rolespecificloginredirect extends auth_plugin_base {
    /**
     * Constructor.
     */
    public function __construct() {

        $this->authtype = 'rolespecificloginredirect';
        $this->config = get_config('auth_rolespecificloginredirect');
    }

    public function loginpage_hook() {
        global $CFG, $SESSION, $frm, $user, $DB, $PAGE;

        $roles = array();

        $rolesindb = $DB->get_records('role');
        $roleswithurls = $DB->get_records('auth_rolespecificredir');

        foreach ($rolesindb as $key => $record) {
            $roles[$record->id] = $record->shortname;
        }

        $urltogo = '';

        $frm = data_submitted();

        if (empty($frm->username)) {
            parent::loginpage_hook();
            return;
        }

        $errorcode = 0;
        $logintoken = isset($frm->logintoken) ? $frm->logintoken : '';
        $user = authenticate_user_login($frm->username, $frm->password, false, $errorcode, $logintoken);

        if (empty($user)) {
            parent::loginpage_hook();
            return;
        }

        $roleassignments = $DB->get_records('role_assignments', array('userid' => $user->id));

        if (count($roleassignments) == 0) {
            parent::loginpage_hook();
            return;
        }

        foreach ($roleassignments as $assignment) {
            $role = $DB->get_record('role', array('id' => $assignment->roleid));
            if (in_array($role->shortname, $roles)) {
                $id = array_search($role->shortname, $roles);

                if (!empty($roleswithurls[$id])) {
                    if ((substr($roleswithurls[$id]->urltogo, 0, 4) == "http")) {

                        $urltogo = $roleswithurls[$id]->urltogo;
                    } else {
                        $urltogo = $CFG->wwwroot . $roleswithurls[$id]->urltogo;
                    }
                } else {
                    $urltogo = $CFG->wwwroot . '/';
                }

                break;
            }
        }

        if ($urltogo == '') {
            parent::loginpage_hook();
            return;
        }

        if (true || !isset($SESSION->wantsurl)) {
            $PAGE->requires->js_call_amd(
              'auth_rolespecificloginredirect/dynamicevent',
              'opennewwindow',
              array("urltogo" => $urltogo)
            );
            $SESSION->wantsurl = $urltogo;
        } else {
            parent::loginpage_hook();
            return;
        }

        return true;
    }


    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {

        return false;
    }

    public function logoutpage_hook() {
        global $CFG, $redirect;

        $CFG->logoutredir = $CFG->wwwroot . '/';
        if ($CFG->logoutredir) {
            $redirect = $CFG->logoutredir;
        }
    }
}

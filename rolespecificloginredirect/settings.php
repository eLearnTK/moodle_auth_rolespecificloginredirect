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

if ($ADMIN->fulltree) {

    global $DB, $PAGE;

    // Labels definition.
    $strroles = get_string('roles', 'auth_rolespecificloginredirect');
    $strsmallroles = get_string('roles_small', 'auth_rolespecificloginredirect');
    $strurls = get_string('urls', 'auth_rolespecificloginredirect');
    $strsmallurls = get_string('urls_small', 'auth_rolespecificloginredirect');
    $strurlexample = get_string('url_example', 'auth_rolespecificloginredirect');
    $strurldefault = get_string('url_default', 'auth_rolespecificloginredirect');
    $strpagenotfound = get_string('pagenotfound', 'auth_rolespecificloginredirect');

    // Introductory explanation.
    $settings->add(new admin_setting_heading(
        'auth_rolespecificloginredirect/pluginname',
        '',
        new lang_string('auth_rolespecificloginredirectdescription', 'auth_rolespecificloginredirect')
    ));

    // Reading the records of the roles in database and the used roles for this plugin.
    $roles = array();
    $rolesindb = $DB->get_records('role');

    $records = $DB->get_records('auth_rolespecificredir');

    foreach ($rolesindb as $key => $record) {
        if ($record->shortname !== "admin" && $record->shortname !== "frontpage") {
            $roles[$record->id] = $record->shortname;
        }
    }

    // Defining role selector setting.
    $rolepicker = new admin_setting_configselect(
        $strsmallroles,
        $strroles,
        " ",
        1,
        $roles
    );

    // Defining an input field for urltogo.
    $urlredir = new admin_setting_configtext_with_maxlength(
        $strsmallurls,
        $strurls,
        $strurlexample,
        $strurldefault,
        PARAM_URL,
        null,
        100
    );

    // Adding the defined settings to form.
    $settings->add($rolepicker);
    $settings->add($urlredir);

    $data = data_submitted();

    // On submit checking if url exists.
    $PAGE->requires->js_call_amd(
        'auth_rolespecificloginredirect/dynamicevent',
        'submittingSettings',
        array("rooturl" => $CFG->wwwroot)
    );

    if ($data) {
        if (isset(get_object_vars($data)['section'])) {
            if (get_object_vars($data)['section'] == 'authsettingrolespecificloginredirect') {

                // Defining the content of a row in record auth_rolespecificloginredirect.
                $dbcontent = new stdClass();
                $dbcontent->id = get_object_vars($data)['s__roles'];
                $dbcontent->role_id = get_object_vars($data)['s__roles'];
                $dbcontent->role = $roles[get_object_vars($data)['s__roles']];
                $dbcontent->urltogo = get_object_vars($data)['s__urls'];

                $record = $DB->get_record('auth_rolespecificredir', (array)$dbcontent);

                if ((substr($dbcontent->urltogo, 0, 4) == "http")) {
                    $link = $dbcontent->urltogo;
                } else {
                    $link = $CFG->wwwroot . $dbcontent->urltogo;
                }

                // Checks if a row is to be created or updated.
                if (!($DB->record_exists('auth_rolespecificredir',  array('id' => $dbcontent->id)))) {
                    if (!$record) {

                        $curl = new \curl();
                        $options = [
                            'CURLOPT_HEADER' => 0,
                        ];
                        $content = $curl->get($link, null, $options);
                        $info = $curl->get_info();

                        // If url does not exist an error will be displayed.
                        if (($info['http_code'] == 404)) {
                            $adminroot->errors['s__urls'] = new stdClass();
                            $adminroot->errors['s__urls']->data = $dbcontent->urltogo;
                            $adminroot->errors['s__urls']->id = $urlredir->get_id();
                            $adminroot->errors['s__urls']->error = $strpagenotfound;
                        } else {
                            $DB->insert_record_raw('auth_rolespecificredir', $dbcontent, false, false, true);
                        }
                    }
                } else {
                    $record = $DB->get_record('auth_rolespecificredir', ['id' => $dbcontent->id]);
                    $redirecturl = $CFG->wwwroot . "/admin/settings.php?section=authsettingrolespecificloginredirect";

                    if ($record->urltogo !== $dbcontent->urltogo) {

                        $record->urltogo = $dbcontent->urltogo;
                        if ((substr($record->urltogo, 0, 4) == "http")) {
                            $link = $record->urltogo;
                        } else {
                            $link = $CFG->wwwroot . $record->urltogo;
                        }

                        $curl = new \curl();
                        $options = [
                            'CURLOPT_HEADER' => 0,
                        ];
                        $content = $curl->get($link, null, $options);

                        $info = $curl->get_info();

                        // If url does not exist an error will be displayed.
                        if (($info['http_code'] == 404)) {

                            $adminroot->errors['s__urls'] = new stdClass();
                            $adminroot->errors['s__urls']->data = $dbcontent->urltogo;
                            $adminroot->errors['s__urls']->id = $urlredir->get_id();
                            $adminroot->errors['s__urls']->error = $strpagenotfound;
                        } else {
                            $DB->update_record_raw('auth_rolespecificredir', $record);
                        }
                    }
                }
            }
        }
    }
    // Changes content of input field, when the selected role is already saved in database.
    $PAGE->requires->js_call_amd('auth_rolespecificloginredirect/dynamicevent', 'dynamicChange', [$records]);
}

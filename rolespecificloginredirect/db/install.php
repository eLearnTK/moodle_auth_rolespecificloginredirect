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
 * On Install plugin enabled
 *
 * @package    auth_rolespecificloginredirect
 *
 * @copyright  2020 onwards Igor Nesterow <Igor.Nesterow@b-tu.de>
 * @copyright  2021 onwards Eleonora Kostova <kostoele@b-tu.de>
 * @copyright  based on Email Authentication Plugin by Martin Dougiamas (http://dougiamas.com)
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure.
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_auth_rolespecificloginredirect_install() {
    global $CFG;

    get_enabled_auth_plugins(true); // Fix the list of enabled auths.

    if (empty($CFG->auth)) {
        $authsenabled = array();
    } else {
        $authsenabled = explode(',', $CFG->auth);
    }

    if (!in_array('rolespecificloginredirect', $authsenabled)) {
        $authsenabled[] = 'rolespecificloginredirect';
        $authsenabled = array_unique($authsenabled);
        set_config('auth', implode(',', $authsenabled));
    }

    return true;
}

/**
 * Post installation recovery procedure.
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_auth_rolespecificloginredirect_install_recovery() {
}

// This link$link is part of Moodle - http://moodle.org/
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
 * Admin settings and defaults.
 *
 * @package    auth_rolespecificloginredirect
 *
 * @copyright  2020 onwards Igor Nesterow <Igor.Nesterow@b-tu.de>
 * @copyright  2021 onwards Eleonora Kostova <kostoele@b-tu.de>
 * @copyright  based on Email Authentication Plugin by Martin Dougiamas (http://dougiamas.com)
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define([], function () {
    return /** @alias module:auth_rolespecificloginredirect/dynamicevent */ {
        /**
         * Implements input's field text output.
         * @method dynamicChange
         */
        dynamicChange: function (js_code) {
            if (js_code) {
                if (document.getElementById("id_s__urls")) {
                    document.getElementById("id_s__roles")
                      .addEventListener("change", function (e) {
                        if (js_code[parseInt(document.getElementById("id_s__roles").value)]) {
                            document.getElementById("id_s__urls").value = js_code[
                                parseInt(document.getElementById("id_s__roles").value)]["urltogo"];
                        } else {
                            document.getElementById("id_s__urls").value = "/my";
                        }
                      });
                }
            }
        },
        /**
         * Checks the correctness of the URL on submit.
         * @method submittingSettings
         */
        submittingSettings: function (rooturl) {
            if (document.getElementById("id_s__urls")) {
                document.getElementById("adminsettings").onsubmit = function () {
                    var urlinputsvalue = document.getElementById("id_s__urls").value;
                    if (urlinputsvalue.substring(0, 4) == "http") {
                        var link = urlinputsvalue;
                    } else {
                        var link = rooturl + "" + urlinputsvalue;
                    }
                    var xmlhttp = new XMLHttpRequest();

                    xmlhttp.open("HEAD", link, true);
                    xmlhttp.onreadystatechange = function () {
                        if (xmlhttp.status != 200) {
                            window.open(
                              link,
                              "Checking Page",
                              "width=550,height=700,left=150,top=200"
                            );
                        }
                    };
                    xmlhttp.send(null);
                };
            }
        },
        /**
         * Shows Pop-Up Window.
         * @method opennewwindow
         */
        opennewwindow: function (url) {
            window.open(url);
        },
        };
 });

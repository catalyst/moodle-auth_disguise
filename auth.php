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
 * Disguised user authentication plugin - allows creation/linking/deletion of
 * disguises linked to another Moodle account.
 *
 * @package     auth_disguise
 * @copyright   2023 Catalyst IT {@link https://catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * Plugin for Disguised users.
 */
class auth_plugin_disguise extends auth_plugin_base {


    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'disguise';
        $this->config = get_config('auth_disguise');
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_disguise() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Do not allow any login.
     *
     */
    function user_login($username, $password) {
        return false;
    }

    /**
     * No password updates.
     */
    function user_update_password($user, $newpassword) {
        return false;
    }

    /**
     * No passwords should be stored.
     */
    function prevent_local_passwords() {
        return true;
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    function is_internal() {
        // All these disguise users are internal to Moodle.
        return true;
    }

    /**
     * No changing of passwords.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * No password resetting.
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Plugin should not be manually set, but managed elsewhere.
     *
     * Temporarily set to true for UI during development. TODO: Disable.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Returns information on how the specified user can change their password.
     * User accounts with authentication type set to disguise have no passwords.
     *
     * @param stdClass $user A user object
     * @return string[] An array of strings with keys subject and message
     */
    public function get_password_change_info(stdClass $user) : array {
        $site = get_site();

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();

        $message = get_string('emailpasswordchangeinfodisabled', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));

        return [
            'subject' => $subject,
            'message' => $message
        ];
    }
}



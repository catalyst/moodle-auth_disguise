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

namespace auth_disguise\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\user
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user {

    /**
     * This function will return the id of disguised user which is linked to the real user.
     * If there is no linked user, it will link the real user id with an unlinked disguised user.
     * IF there is no unlinked disguised user, it will create a new disguised user and link it with the real user.
     *
     */
    public static function get_linked_disguised_user($contextid, $realuserid): \stdClass {
        global $DB;
        $user = new \stdClass();

        // Hardcode the user ID for now, for analysing feasibility.
        $user = $DB->get_record('user', ["id" => 3]);

        // Return existing linked user.

        // If there is no linked user, map existing unlinked disguised user.

        // If there is no unlinked disguised user, create new pool of users and map one of them.

        return $user;
    }

    public static function link_user($contextid, $realuserid, $disguiseduserid) {
        // Link the user with the disguised user.
    }

    public static function unlink_user($contextid, $realuserid) {
        // Perform transferring data and clean up.
        // Unlink the user with the disguised user.
    }

    public static function get_disguised_user($contextid, $realuserid) {
        // Return the disguised user.
    }

    public static function create_disguised_users() {
        // Return all the disguised users.
    }

    public static function prune_user_disguise($realuserid) {
        // Return all the disguised users.
    }

    /**
     * Do we need to transfer custom fields as they may be used in availability restriction?
     * Or can we bypass the restriction somehow?
     * It will reveal the real user if the data is unique?
     *
     */
    public static function clone_custom_fields($realuserid, $disguiseduserid) {
        // Transfer custom fields from real user to disguised user.
    }

    /**
     * Login in as disguised user.
     *
     */
    public static function disguise_as($contextid, $realuserid, $disguiseduserid) {
        // Stop if the user is already disguised.
        if (isset($_SESSION['USERINDISGUISE'])) {
            // Check if the context is the same.
            // If not the same we may allow new disguise.
            return;
        }

        // SESSION.
        $_SESSION = array();
        $_SESSION['DISGUISESESSION'] = clone($GLOBALS['SESSION']);
        $GLOBALS['SESSION'] = new \stdClass();
        $_SESSION['SESSION'] =& $GLOBALS['SESSION'];

        // Avoid using REALUSER as it may mess up 'loginas'.
        $_SESSION['USERINDISGUISE'] = clone($GLOBALS['USER']);

        // DISGUISED USER.
        $disguiseduser = get_complete_user_data('id', $disguiseduserid);
        $disguiseduser->userindisguise = $_SESSION['USERINDISGUISE'];
        \core\session\manager::set_user($disguiseduser);

        // Change login info, similar to loginas  in outputrenderers?.
    }

    public static function back_to_real_user() {
        // Or should we ask user to logout instead.
        if (isset($_SESSION['USERINDISGUISE'])) {
            $_SESSION['SESSION'] = clone($_SESSION['DISGUISESESSION']);
            \core\session\manager::set_user($_SESSION['USERINDISGUISE']);
            unset($_SESSION['USERINDISGUISE']);
            unset($_SESSION['DISGUISESESSION']);
        }
    }

}

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

require_once($CFG->dirroot . '/user/lib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\user
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise_user {

    /**
     * This function will return the id of disguised user which is linked to the real user.
     * If there is no linked user, it will link the real user id with an unlinked disguised user.
     * IF there is no unlinked disguised user, it will create a new disguised user and link it with the real user.
     *
     */
    public static function get_disguise_for_user($contextid, $realuserid) {
        // Return existing linked user.
        $disguise = self::get_mapped_disguise($contextid, $realuserid);

        // If there is no linked user, map existing unlinked disguised user.
        if (!$disguise) {
            $disguise = self::get_unmapped_disguise($contextid);
            if ($disguise) {
                self::map_user($contextid, $realuserid, $disguise->id);
            }
        }

        // If there is no unlinked disguised user, create new pool of users and map one of them.
        if (!$disguise) {
            $disguise = self::create_disguise($contextid);
            self::map_user($contextid, $realuserid, $disguise->id);
        }

        return $disguise;
    }

    public static function map_user($contextid, $realuserid, $disguiseid) {
        global $DB;

        // Sanity check if the user is already mapped.
        if (self::get_mapped_disguise($contextid, $realuserid)) {
            Debugging('User is already mapped.', DEBUG_DEVELOPER);
            return;
        }

        // Link the user with the disguised user.
        $DB->insert_record('auth_disguise_user_map', [
            'contextid' => $contextid,
            'userid' => $realuserid,
            'disguiseid' => $disguiseid,
        ]);

        // Remove disguise from the unmapped disguise pool.
        $DB->delete_records('auth_disguise_unmapped_disg', [
            'contextid' => $contextid,
            'disguiseid' => $disguiseid,
        ]);
    }

    public static function unmap_user($contextid, $realuserid) {
        // Perform transferring data and clean up.
        // Unlink the user with the disguised user.
    }

    public static function get_mapped_disguise($contextid, $realuserid) {
        global $DB;

        // Get disguise mapping.
        $disuisemap = $DB->get_record('auth_disguise_user_map', [
            'contextid' => $contextid,
            'userid' => $realuserid
        ]);

        // Return the disguise user.
        if ($disuisemap) {
            return $DB->get_record('user', ['id' => $disuisemap->disguiseid]);
        } else {
            return null;
        }
    }

    public static function get_unmapped_disguise($contextid) {
        global $DB;

        // List of the unmapped disguise.
        $disguises = $DB->get_records('auth_disguise_unmapped_disg', [
            'contextid' => $contextid,
        ]);

        // Return the first unmapped disguise.
        return reset($disguises);
    }

    public static function create_disguise($contextid) {
        global $DB, $CFG;

        // Create a new disguise.
        $user = new \stdClass();

        $user->firstname = time();
        $user->lastname = 'Disguise';
        $user->username = sha1(time() . '_' . $contextid);
        $email = $user->username . "@example.com";
        $email = \core_user::clean_field($email, 'email');
        $user->email = $email;
        $user->auth = 'disguise';
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->password = '';
        $user->confirmed = 1;
        $user->id = user_create_user($user, false);

        // Add the new disguise to the pool.
        $DB->insert_record('auth_disguise_unmapped_disg', [
            'contextid' => $contextid,
            'disguiseid' => $user->id,
        ]);

        return $user;
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
    public static function clone_custom_fields($realuserid, $disguiseid) {
        // Transfer custom fields from real user to disguised user.
    }

}

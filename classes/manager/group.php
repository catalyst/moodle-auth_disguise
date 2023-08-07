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
 * Class auth_disguise\manager\group
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group {

    /**
     * clone real user's group to disguised user.
     *
     */
    public static function clone_groups_to_disguised_user($courseid, $realuserid, $disguiseduserid) {
        // Check if the link between real user and disguised user is valid.

        // Get all groups of real user.

        // Transfer all groups of real user to disguised user.
        // Condition to transfer group:

        // Remove any group which does not belong to real user.
        // Condition or any delay to remove those groups.
    }

    /**
     * Remove a specified group from disguised user.
     *
     */
    public static function remove_group_from_disguised_user($courseid, $disguiseduserid, $groupid){
        // Remove a group of disguised user.
    }

    /**
     * Remove all groups from disguised user.
     *
     */
    public static function remove_groups_from_disguised_user($courseid, $disguiseduserid){
        // Remove all groups of disguised user.
    }


}

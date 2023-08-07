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
 * Class auth_disguise\manager\cohort
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort {

    /**
     * Clone cohort of real user to disguised user.
     * Why do we need it? availability restriction
     * Or bypass restriction somehow?
     */
    public static function clone_cohorts_to_disguised_user($realuserid, $disguiseduserid) {
        // Check if the link between real user and disguised user is valid.

        // Get all cohorts of real user.

        // Transfer all cohorts of real user to disguised user.
        // Condition to transfer cohort:
    }

    /**
     * Remove all cohorts from disguised user.
     *
     */
    public static function remove_cohorts_from_disguised_user($disguiseduserid) {
        // Remove all cohorts of disguised user.
    }



}

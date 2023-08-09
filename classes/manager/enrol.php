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
 * Class auth_disguise\manager\role
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol {

    public static function enrol_disguise($contextid, $realuserid, $disguiseid) {
        // Check if the context is course context.
        $context = \context::instance_by_id($contextid);

//        if ($context->contextlevel == CONTEXT_MODULE) {
//            // Get parent of the context.
//
//        }

        // Check if the user

        // Get courseid from the context
        $courseid = $context->instanceid;
        $course = \get_course($courseid);

        // Get enrolment method of the course.
        $enrolment = \enrol_get_plugin('disguise');
        $id = $enrolment->add_instance($course, []);

    }

    /**
     * Do we clone the role assignments when a user is disguised?
     * Or simply assign a predefined role to the disguised user?
     *
     */
    public static function clone_role_from_real_user($courseid, $realuserid, $disguiseduserid) {
        // Check if the link between real user and disguised user is valid.

        // Get all role assignments of real user.

        // Clone all role assignments of real user to disguised user.
        // Condition to clone role assignments:
    }

    /**
     * Remove role assignment from disguised user in a course.
     *
     */
    public static function remove_roles_from_disguised_user($courseid, $disguiseduserid) {
        // Remove a role assignment of disguised user.
    }

}

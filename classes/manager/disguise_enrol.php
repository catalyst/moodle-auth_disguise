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

use core\check\performance\debugging;

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\role
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise_enrol {

    public static function enrol_disguise($contextid, $realuserid, $disguiseid) {
        global $DB;

        // Check if real user.
        $user = get_complete_user_data('id', $realuserid);
        if ($user->auth == 'disguise') {
            debugging('Real user id is invalid. This should not happen', DEBUG_DEVELOPER);
            return;
        }

        // Course from the context.
        $context = disguise_context::get_course_context($contextid);
        $course = \get_course($context->instanceid);

        // Check if disguise user is already enrolled.
        if (self::is_enrolled($disguiseid, $context->id)) {
            return;
        }

        // Check if the enrolment method is already added to the course.
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'disguise') {
                $enrolinstance = $instance;
                break;
            }
        }

        // Disguise Enrolment.
        $enrolplugin = \enrol_get_plugin('disguise');
        if (empty($enrolinstance)) {
            // Create a disguise enrolment instance.
            $id = $enrolplugin->add_instance($course);

            // Get the enrolment instance.
            $enrolinstance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);
        }
        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);

        $enrolplugin->enrol_user($enrolinstance, $disguiseid, $role->id);

    }

    public static function is_enrolled($userid, $contextid) {
        $coursecontext = disguise_context::get_course_context($contextid);
        // Check if real user is enrolled in the course.
        $courses = enrol_get_all_users_courses($userid);
        return array_key_exists($coursecontext->instanceid, $courses);
    }
}

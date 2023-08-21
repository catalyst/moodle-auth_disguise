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
 * Class auth_disguise\manager\disguise_enrol
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise_enrol {

    public static function create_enrol_disguise_instance($contextid) {
        // Course from the context.
        $context = disguise_context::get_course_context($contextid);
        $course = \get_course($context->instanceid);

        // To update or create new instance.
        $enrolplugin = \enrol_get_plugin('disguise');

        // Check if the enrolment method is already added to the course.
        $instances = array_merge(enrol_get_instances($course->id, true), enrol_get_instances($course->id, false));
        foreach ($instances as $instance) {
            if ($instance->enrol === 'disguise') {
                // Enrolment method already added. Do nothing.
                $enrolplugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                return $instance->id;
            }
        }

        // Create new enrol instance.
        return $enrolplugin->add_instance($course);
    }

    public static function disable_enrol_disguise_instance($contextid) {
        // Course from the context.
        $context = disguise_context::get_course_context($contextid);
        $course = \get_course($context->instanceid);

        // Update enrolment method.
        $enrolplugin = \enrol_get_plugin('disguise');
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'disguise') {
                // Disable enrolment method.
                $enrolplugin->update_status($instance, ENROL_INSTANCE_DISABLED);
                return;
            }
        }
    }

    public static function enrol_disguise($contextid, $realuserid, $disguiseid) {
        global $DB;

        // Check if real user.
        $user = get_complete_user_data('id', $realuserid);
        if ($user->auth == 'disguise') {
            debugging('Real user id is invalid. This should not happen', DEBUG_DEVELOPER);
            return;
        }

        // Check if disguise user is already enrolled.
        if (self::is_enrolled($disguiseid, $contextid)) {
            return;
        }

        // Enrol disguise user.
        $enrolinstanceid = self::create_enrol_disguise_instance($contextid);
        $enrolinstance = $DB->get_record('enrol', array('id' => $enrolinstanceid), '*', MUST_EXIST);
        // TODO: Same role as real user.
        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $enrolplugin = \enrol_get_plugin('disguise');
        $enrolplugin->enrol_user($enrolinstance, $disguiseid, $role->id);
    }

    public static function is_enrolled($userid, $contextid) {
        $coursecontext = disguise_context::get_course_context($contextid);
        // Check if real user is enrolled in the course.
        $courses = enrol_get_all_users_courses($userid);
        return array_key_exists($coursecontext->instanceid, $courses);
    }
}

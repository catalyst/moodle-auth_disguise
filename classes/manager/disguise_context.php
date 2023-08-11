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
 * Class auth_disguise\manager\context
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise_context {
    public static function get_course_context($contextid) {
        global $DB;
        $context = \context::instance_by_id($contextid);

        // Get parent course context if it is course module context.
        if ($context->contextlevel == CONTEXT_MODULE) {
            // Get the course module.
            $cm = $DB->get_record('course_modules', ['id' => $context->instanceid], '*', MUST_EXIST);
            // Get the course context.
            $context = \context_course::instance($cm->course);
        }

        return $context;
    }

}

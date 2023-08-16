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

    // Check if disguise mode exists for context.
    public static function disguise_mode_exists($contextid) {
        global $DB;
        return $DB->record_exists('auth_disguise_ctx_mode', ['contextid' => $contextid]);
    }

    // Insert disguise mode.
    public static function insert_disguise_context_mode($contextid, $mode) {
        global $DB;
        $record = new \stdClass();
        $record->contextid = $contextid;
        $record->disguises_mode = $mode;

        return $DB->insert_record('auth_disguise_ctx_mode', $record);
    }

    // Update disguise mode.
    public static function update_disguise_context_mode($contextid, $mode) {
        global $DB;
        $record = new \stdClass();
        $record->id = $contextid;
        $record->disguises_mode = $mode;

        return $DB->update_record('auth_disguise_ctx_mode', $record);
    }

    // Check if there is same naming set
    public static function get_naming_set_record($namingset) {
        global $DB;
        return $DB->get_record('auth_disguise_naming_set', ['naming' => $namingset]);
    }

    // Insert naming set
    public static function insert_naming_set($namingset) {
        global $DB;
        $record = new \stdClass();
        $record->naming = $namingset;

        return $DB->insert_record('auth_disguise_naming_set', $record);
    }

    // Set naming set for context
    public static function save_naming_set_for_context($contextid, $namingset) {
        global $DB;

        // Check if naming set exists
        $namingsetrecord = self::get_naming_set_record($namingset);

        if (!$namingsetrecord) {
            $namingsetid = self::insert_naming_set($namingset);
        } else {
            $namingsetid = $namingsetrecord->id;
        }

        // Check if naming set exists for context
        $contextnamingset = self::get_naming_set_for_context($contextid);

        // Insert if there is no naming set for context
        if (!$contextnamingset) {
            $record = new \stdClass();
            $record->contextid = $contextid;
            $record->namingsetid = $namingsetid;
            return $DB->insert_record('auth_disguise_naming_context', $record);
        } else {
            $contextnamingset->namingsetid = $namingsetid;
            return $DB->update_record('auth_disguise_naming_context', $contextnamingset);
        }


    }

    // Get naming set for context
    public static function get_naming_set_for_context($contextid) {
        global $DB;
        $contextnamingset = $DB->get_record('auth_disguise_naming_context', ['contextid' => $contextid]);
        if ($contextnamingset) {
            return $DB->get_record('auth_disguise_naming_set', ['id' => $contextnamingset->namingsetid]);
        } else {
            return false;
        }
    }

}

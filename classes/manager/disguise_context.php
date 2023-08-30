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

    /**
     * Get course context from given context id.
     * If the provided context id is a course module context, it will find the parent course context.
     * This is used to get disguise mode for at parent course level.
     *
     * @param int $contextid context id of either a course or a course module.
     * @return \context_course
     */
    public static function get_course_context(int $contextid): \context_course {
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

    /**
     * Check if disguise mode exists for a given context.
     *
     * @param $contextid
     * @return bool
     */
    public static function disguise_context_mode_exists(int $contextid): bool {
        global $DB;
        return $DB->record_exists('auth_disguise_ctx_mode', ['contextid' => $contextid]);
    }

    /**
     * Insert disguise mode for a given context.
     *
     * @param int $contextid context id
     * @param int $mode disguise mode
     * @return bool
     */
    public static function insert_disguise_context_mode(int $contextid, int $mode): bool {
        global $DB;

        $record = new \stdClass();
        $record->contextid = $contextid;
        $record->disguises_mode = $mode;

        return (bool) $DB->insert_record('auth_disguise_ctx_mode', $record);
    }

    /**
     * Update disguise mode for a given context.
     *
     * @param int $contextid context id
     * @param int $mode disguise mode
     * @return bool
     */
    public static function update_disguise_context_mode(int $contextid, int $mode): bool {
        global $DB;
        return $DB->set_field('auth_disguise_ctx_mode', 'disguises_mode', $mode,
            ['contextid' => $contextid]);
    }


    // #################################### DISGUISE SET ####################################

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
        $contextnamingset = self::get_context_naming_set($contextid);

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

    public static function get_context_naming_set($contextid) {
        global $DB;
        return $DB->get_record('auth_disguise_naming_context', ['contextid' => $contextid]);;
    }

    // Get naming set for context
    public static function get_naming_set_for_context($contextid) {
        global $DB;
        $contextnamingset = self::get_context_naming_set($contextid);
        if ($contextnamingset) {
            return $DB->get_record('auth_disguise_naming_set', ['id' => $contextnamingset->namingsetid]);
        } else {
            return false;
        }
    }

}

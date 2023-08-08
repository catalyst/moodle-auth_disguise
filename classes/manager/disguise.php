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

require_once($CFG->dirroot . '/auth/disguise/lib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\user
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise {
    public static function is_disguise_enabled() {
        // Check plugin config.
        if (!get_config('auth_disguise','feature_status_site')) {
            return false;
        }

        return true;
    }

    public static function get_disguise_mode_for_context($context)
    {
        global $DB;
        $params = ['contextid' => $context->id];
        return $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', $params);
    }

    public static function is_disguise_enabled_for_context($context) {
        if (!self::is_disguise_enabled()) {
            return false;
        }

        // Check if disguise is enabled for this context.
        $disguisemode = self::get_disguise_mode_for_context($context);
        if ($disguisemode == AUTH_DISGUISE_MODE_DISABLED) {
            return false;
        }

        return true;
    }

    public static function apply_user_disguise() {

        global $USER, $PAGE, $DB;

        // Check if user disguise is enabled.

        // We will need to use Hook API to do this instead of callback.

        // Check if user disguise is enabled

        if (isloggedin() && !isguestuser() ) {
            debugging('User is logged in, but not as a guest. Disguising user.', DEBUG_DEVELOPER);

            // Context ID.
            try {
                // This will throw error if context is empty when AJAX_SCRIPT is true.
                // $PAGE->context is a magic getter, and it is really annoying to do null check, as isset always return false.
                $context = $PAGE->context;
            } catch (Exception $e) {
                debugging('Context is empty.', DEBUG_DEVELOPER);
                return;
            }

            // Check context level to determine whether the disguise is needed.
            switch ($context->contextlevel) {
                case CONTEXT_SYSTEM:
                    $disguiseenabled = false;
                    break;
                case CONTEXT_COURSE:
                    // Exclude site course.
                    if ($context->instanceid == SITEID) {
                        $disguiseenabled = false;
                    } else {
                        $disguiseenabled = true;
                    }

                    // Check if disguise is enabled for this course.

                    break;
                case CONTEXT_MODULE:
                    // Check if disguise is enabled for this module.

                    $disguiseenabled = true;
                    break;
                case CONTEXT_BLOCK:
                    $disguiseenabled = false;
                    break;
                case CONTEXT_USER:
                    $disguiseenabled = false;
                    break;
                default:
                    debugging('Unsupported context.', DEBUG_DEVELOPER);
                    return;
            }

            if (!$disguiseenabled) {
                debugging('Disguise is not enabled for this context.', DEBUG_DEVELOPER);
                return;
            }

            // Find a disguised user.
            $disguiseduser = user_manager::get_linked_disguised_user($context->id, $USER->id);

            // Use disguise.
            if (!$disguiseduser) {
                debugging('No disguised user found.', DEBUG_DEVELOPER);
                return;
            }

            // Disguise.
            user_manager::disguise_as($context->id, $USER->id, $disguiseduser->id);

            // Enrolment.

            // Role/Permission.

            // Group.

            // Cohort
        }
    }

}

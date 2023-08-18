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
require_once($CFG->dirroot . '/lib/datalib.php');

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

    public static function is_page_type_supported($page) {
        // List of supported page types.
        $supportedpagetypes = [
            'site-',
            'course-',
            'mod-',
        ];

        // Check if the page type is supported.
        foreach ($supportedpagetypes as $pagetype) {
            if (strpos($page->pagetype, $pagetype) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function get_disguise_mode_for_context(int $contextid) {
        global $DB;
        $params = ['contextid' => $contextid];
        return $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', $params);
    }

    // Check if disguise is allowed for subcontext.
    public static function is_disguise_allowed_for_subcontext(int $coursecontextid) {
        $coursecontext = disguise_context::get_course_context($coursecontextid);
        $disguisemode = self::get_disguise_mode_for_context($coursecontext->id);
        if ($disguisemode !== AUTH_DISGUISE_MODE_DISABLED) {
            return true;
        }
    }

    public static function is_disguise_enabled_for_context(int $contextid) {
        if (!self::is_disguise_enabled()) {
            return false;
        }

        // Check if the disguise is applied everywhere in the course.
        $coursecontext = disguise_context::get_course_context($contextid);
        $disguisemode = self::get_disguise_mode_for_context($coursecontext->id);

        // Course context.
        if ($contextid === $coursecontext->id) {
            if ($disguisemode == AUTH_DISGUISE_MODE_COURSE_EVERYWHERE) {
                return true;
            } else {
                return false;
            }
        }

        // Module context.

        // Disabled if the course is not set to allow disguise.
        if ($disguisemode == AUTH_DISGUISE_MODE_DISABLED) {
            return false;
        }

        // Check if disguise is enabled for this context.
        $disguisemode = self::get_disguise_mode_for_context($contextid);
        if ($disguisemode == AUTH_DISGUISE_MODE_DISABLED) {
            return false;
        }

        return true;
    }

    public static function is_disguise_enabled_for_user(int $contextid, int $userid) {
        global $SESSION;

        // Check if disguise is enabled for this context.
        if (!self::is_disguise_enabled_for_context($contextid)) {
            return false;
        }

        // Check if user is already disguised.
        $user = get_complete_user_data('id', $userid);
        if ($user->auth == 'disguise') {
            return false;
        }

        // If the context is ignored, then disguise is disabled.
        if (isset($SESSION->ignoreddisguisecontext) && $SESSION->ignoreddisguisecontext == $contextid) {
            return false;
        }

        // Only allow disguise if user is enrolled in the course.
        if (!disguise_enrol::is_enrolled($userid, $contextid)) {
            return false;
        }

        // Disabled for site admin.
        if (is_siteadmin($user->id)) {
            return false;
        }

        $coursecontext = disguise_context::get_course_context($contextid);
        $course = get_course($coursecontext->instanceid);
        $courseinlist = new \core_course_list_element($course);
        $coursecontacts = $courseinlist->get_course_contacts();
        // Check if user is a course contact.
        if (array_key_exists($userid, $coursecontacts)) {
            return false;
        }

        return true;
    }

    public static function disguise_user($contextid, $realuserid) {
        // Get disguise.
        $disguise = disguise_user::get_disguise_for_user($contextid, $realuserid);

        // Check disguise.
        if (!$disguise) {
            // This should not happen.
            debugging('Disguise user not found.', DEBUG_DEVELOPER);
            return;
        }

        // SESSION.
        $_SESSION = array();
        $_SESSION['DISGUISESESSION'] = clone($GLOBALS['SESSION']);
        $GLOBALS['SESSION'] = new \stdClass();
        $_SESSION['SESSION'] =& $GLOBALS['SESSION'];

        // Avoid using REALUSER as it may mess up 'loginas'.
        $_SESSION['USERINDISGUISE'] = clone($GLOBALS['USER']);
        $_SESSION['DISGUISECONTEXT'] = $contextid;

        // Disguise.
        $disguiseduser = get_complete_user_data('id', $disguise->id);
        $disguiseduser->userindisguise = $_SESSION['USERINDISGUISE'];
        \core\session\manager::set_user($disguiseduser);

        // Enrol.
        disguise_enrol::enrol_disguise($contextid, $realuserid, $disguise->id);
    }

    public static function prompt_to_disguise($contextid) {
        global $PAGE;
        $url = new \moodle_url('/auth/disguise/prompt_to_disguise.php', [
            'returnurl' => $PAGE->url->out(),
            'contextid' => $contextid,
        ]);
        redirect($url);
    }

    public static function prompt_back_to_real_user_if_required($contextid) {
        global $PAGE;

        // Check if user is disguised.
        if (!isset($_SESSION['USERINDISGUISE']) || !isset($_SESSION['DISGUISECONTEXT'])) {
            return;
        }

        if ($contextid== $_SESSION['DISGUISECONTEXT']) {
            return;
        }

        // Check if the context is changed.
        // If the disguise context is a course context, changes in course module context should not change the disguise.
        $coursecontext = disguise_context::get_course_context($contextid);
        if ($coursecontext->id == $_SESSION['DISGUISECONTEXT']) {
            return;
        }

        // Show a prompt to the user if they are not already disguised.
        // Get referer page
        $referer = clean_param($_SERVER['HTTP_REFERER'], PARAM_URL);
        $url = new \moodle_url('/auth/disguise/prompt_to_real_id.php', [
            'returnurl' => $referer,
            'nexturl' => $PAGE->url->out(),
            'contextid' => $contextid,
        ]);
        redirect($url);

    }

    public static function back_to_real_user($contextid) {
        // Go back to real user if the context change.
        // TODO: Check if the context is a child of the disguise context.
        if ($contextid != $_SESSION['DISGUISECONTEXT']) {
            $_SESSION['SESSION'] = clone($_SESSION['DISGUISESESSION']);
            \core\session\manager::set_user($_SESSION['USERINDISGUISE']);
            unset($_SESSION['USERINDISGUISE']);
            unset($_SESSION['DISGUISESESSION']);
            unset($_SESSION['DISGUISECONTEXT']);
        }
    }

}

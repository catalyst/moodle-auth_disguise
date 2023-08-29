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

use moodle_page;

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

    /** Supported page types */
    const SUPPORTED_PAGE_TYPES = [
        'site-',
        'course-',
        'mod-',
    ];

    /**
     * Check if disguise is enabled.
     *
     * @return bool whether disguise is enabled
     */
    public static function is_disguise_enabled() {
        global $CFG;
        return !empty($CFG->userdisguise);
    }

    /**
     * Check if the page type is supported.
     *
     * @param moodle_page $page page to check
     * @return bool whether the page type is supported
     */
    public static function is_page_type_supported(moodle_page $page) {
        // Check if the page type is supported.
        foreach (self::SUPPORTED_PAGE_TYPES as $pagetype) {
            if (strpos($page->pagetype, $pagetype) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the disguise mode for the context.
     *
     * @param int $contextid context id
     * @param string $disguisemode disguise mode
     */
    public static function set_disguise_mode_for_context(int $contextid, string $disguisemode) {
        global $DB;
        // Check if the context already has a disguise mode.
        $params = ['contextid' => $contextid];
        if ($DB->record_exists('auth_disguise_ctx_mode', $params)) {
            $DB->set_field('auth_disguise_ctx_mode', 'disguises_mode', $disguisemode, $params);
            return;
        }

        // Otherwise, insert a new record.
        $params = [
            'contextid' => $contextid,
            'disguises_mode' => $disguisemode,
        ];
        $DB->insert_record('auth_disguise_ctx_mode', $params);
    }

    /**
     * Return the disguise mode for the context.
     *
     * @param int $contextid context id
     * @return string return the disguise mode or false if not found
     */
    public static function get_disguise_mode_for_context(int $contextid) {
        global $DB;
        $params = ['contextid' => $contextid];
        return $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', $params) ?? AUTH_DISGUISE_MODE_DISABLED;
    }

    /**
     * Check if disguise is allowed for its sub contexts.
     * That is, if parent context has disguise disabled,
     * then the disguise should also be disabled for its sub contexts.
     *
     * @param int $contextid context id
     * @return bool whether disguise is allowed for sub contexts
     */
    public static function is_disguise_allowed_for_subcontext(int $contextid) {
        // Check if disguise is enabled site wide.
        if (!self::is_disguise_enabled()) {
            return false;
        }

        $disguisemode = self::get_disguise_mode_for_context($contextid);
        return $disguisemode !== AUTH_DISGUISE_MODE_DISABLED;
    }

    /**
     * Check if disguise is forced for its sub contexts.
     *
     * @param int $contextid context id
     * @return bool whether disguise is forced for sub contexts
     */
    public static function is_disguise_forced_for_subcontext(int $contextid) {
        // Check if disguise is enabled site wide.
        if (!self::is_disguise_enabled()) {
            return false;
        }

        $disguisemode = self::get_disguise_mode_for_context($contextid);
        return $disguisemode === AUTH_DISGUISE_MODE_COURSE_EVERYWHERE;
    }

    /**
     * Check if disguise is optional, that is user can choose to disguise or not.
     *
     *
     * @param int $contextid context id
     * @return bool whether disguise is optional for sub contexts
     */
    public static function is_disguise_optional_for_context(int $contextid) {
        // Check if disguise is enabled site wide.
        if (!self::is_disguise_enabled()) {
            return false;
        }

        // Get course context.
        $coursecontext = disguise_context::get_course_context($contextid);

        $disguisemode = self::get_disguise_mode_for_context($coursecontext->id);
        return $disguisemode === AUTH_DISGUISE_MODE_COURSE_OPTIONAL;
    }

    /**
     * Disguise is optional for this context and user to chose not to use disguise
     *
     *
     */
    public static function ignore_disguise_for_context(int $contextid) {
        global $SESSION;
        // Check if disguise is optional for the context.
        if (self::is_disguise_optional_for_context($contextid)) {
            $SESSION->ignoreddisguisecontext = $contextid;

        }
    }

    /**
     * Check if disguise is ignored for the context.
     *
     * @param int $contextid context id
     * @return bool whether disguise is ignored for the context
     */
    public static function is_context_ignored_for_disguise(int $contextid) {
        global $SESSION;
        return isset($SESSION->ignoreddisguisecontext) && $SESSION->ignoreddisguisecontext == $contextid;
    }

    /**
     * Check if disguise is enabled for the context.
     *
     * @param int $contextid context id
     * @return bool whether disguise is enabled for the context
     */
    public static function is_disguise_enabled_for_context(int $contextid) {
        // Check if disguise is enabled site wide.
        if (!self::is_disguise_enabled()) {
            return false;
        }

        // If the context is ignored, then disguise is disabled.
        if (self::is_context_ignored_for_disguise($contextid)) {
            return false;
        }

        // Check if the disguise is applied everywhere in the course.
        $coursecontext = disguise_context::get_course_context($contextid);
        $disguisemode = self::get_disguise_mode_for_context($coursecontext->id);

        switch ($disguisemode) {
            case AUTH_DISGUISE_MODE_COURSE_EVERYWHERE:
                return true;
            case AUTH_DISGUISE_MODE_DISABLED:
                return false;
        }
        // The disguise mode is set as optional or forced in activities only.
        // If the current context is course context, then disguise is disabled.
        if ($contextid === $coursecontext->id) {
            return false;
        }

        // If the context is activity. We will check if it is optional or forced.

        // Force disguise mode for activities.
        if ($disguisemode === AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY) {
            return true;
        }

        // Otherwise, it is optional.
        //Check if disguise is enabled for this activity context.
        $disguisemode = self::get_disguise_mode_for_context($contextid);
        if ($disguisemode === AUTH_DISGUISE_MODE_DISABLED) {
            return false;
        }

        return true;
    }

    /**
     * Check if disguise is enabled for the user in a given context.
     *
     * @param int $contextid context id
     * @param int $userid user id
     * @return bool whether disguise is enabled for the user
     */
    public static function is_disguise_enabled_for_user(int $contextid, int $userid) {
        global $SESSION;

        // Check if disguise is enabled for this context.
        if (!self::is_disguise_enabled_for_context($contextid)) {
            return false;
        }

        // Disabled for site admin.
        if (is_siteadmin($userid)) {
            return false;
        }

        // Check if user is already disguised.
        $user = get_complete_user_data('id', $userid);
        if ($user->auth == 'disguise') {
            return false;
        }

        // Only allow disguise if user is enrolled in the course.
        if (!disguise_enrol::is_enrolled($userid, $contextid)) {
            return false;
        }

        // Disabled if user is one of course contacts.
        $coursecontext = disguise_context::get_course_context($contextid);
        $course = get_course($coursecontext->instanceid);
        $courseinlist = new \core_course_list_element($course);
        $coursecontacts = $courseinlist->get_course_contacts();
        if (array_key_exists($userid, $coursecontacts)) {
            return false;
        }

        // Otherwise, disguise is enabled for this user.
        return true;
    }

    /**
     * Disguise user for the given context.
     *
     * @param int $contextid context id
     * @param int $realuserid real user id
     */
    public static function disguise_user(int $contextid, int $realuserid) {
        // Sanity check
        if (!self::is_disguise_enabled_for_user($contextid, $realuserid)) {
            return;
        }

        // Get disguise.
        $disguise = disguise_user::get_disguise_for_user($contextid, $realuserid);

        // Check disguise.
        if (!$disguise) {
            // This should not happen.
            debugging('Disguise user not found.', DEBUG_DEVELOPER);
            return;
        }

        // New session for disguise.
        $_SESSION = array();
        $_SESSION['DISGUISESESSION'] = clone($GLOBALS['SESSION']);
        $GLOBALS['SESSION'] = new \stdClass();
        $_SESSION['SESSION'] =& $GLOBALS['SESSION'];

        // Avoid using 'REALUSER' as it may mess up 'loginas'.
        $realuser = get_complete_user_data('id', $realuserid);
        $_SESSION['USERINDISGUISE'] = $realuser;
        $_SESSION['DISGUISECONTEXT'] = $contextid;

        // Disguise.
        $disguiseduser = get_complete_user_data('id', $disguise->id);
        \core\session\manager::set_user($disguiseduser);

        // Enrol the disguise.
        disguise_enrol::enrol_disguise($contextid, $realuserid, $disguise->id);
    }

    /**
     * Go back to real user if the context is changed.
     *
     * @param int $contextid context id
     */
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

    /**
     * Create a prompt so that user know that they are accessing a page where disguise is enabled.
     *
     * @param int $contextid context id
     *
     */
    public static function prompt_to_disguise(int $contextid) {
        global $PAGE;
        $referer = clean_param($_SERVER['HTTP_REFERER'], PARAM_URL);

        // Check if disguise is optional in this context.
        $optionaldisguise = self::is_disguise_optional_for_context($contextid);

        $url = new \moodle_url('/auth/disguise/prompt_to_disguise.php', [
            'returnurl' => $optionaldisguise ? $PAGE->url->out() : $referer,
            'nexturl' => $PAGE->url->out(),
            'contextid' => $contextid,
            'optional' => $optionaldisguise,
        ]);
        redirect($url);
    }

    /**
     * Prompt user to go back to their real id if they are leaving the disguise context.
     *
     * @param int $contextid context id
     */
    public static function prompt_back_to_real_user(int $contextid) {
        global $PAGE;

        // Do not show the prompt if user is not in disguise.
        if (!isset($_SESSION['USERINDISGUISE']) || !isset($_SESSION['DISGUISECONTEXT'])) {
            return;
        }

        // Do not show the prompt if user is already in the disguise context.
        if ($contextid === $_SESSION['DISGUISECONTEXT']) {
            return;
        }

        // Check if the context is changed.
        // If the disguise context is a course context, changes in course module context should not change the disguise.
        $coursecontext = disguise_context::get_course_context($contextid);
        if ($coursecontext->id == $_SESSION['DISGUISECONTEXT']) {
            return;
        }

        // Show a prompt to the user if they are not already disguised.
        // The referer page is used as the return url, if user don't want to go back to their real id.
        $referer = clean_param($_SERVER['HTTP_REFERER'], PARAM_URL);
        $url = new \moodle_url('/auth/disguise/prompt_to_real_id.php', [
            'returnurl' => $referer,
            'nexturl' => $PAGE->url->out(),
            'contextid' => $contextid,
        ]);
        redirect($url);
    }

}

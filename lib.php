<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use auth_disguise\manager\disguise;

// Constants for user disguise modes.
define('AUTH_DISGUISE_MODE_DISABLED', 0);

// Course modes.
define('AUTH_DISGUISE_MODE_COURSE_OPTIONAL', 100);
define('AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY', 101);
define('AUTH_DISGUISE_MODE_COURSE_EVERYWHERE', 102);

// Course module modes.
define('AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE', 200);
define('AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE', 201);

/**
 * Add in form elements to course module configuration to allow for user
 * disguise modules to be configured. Set the form value(s) from their saved
 * database entries for this context or set the default.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function auth_disguise_coursemodule_standard_elements($formwrapper, $mform) {

    // If user disguises are disabled site-wide, abort.
    if (!get_config('auth_disguise','feature_status_site')) {
        return;
    }

    // Add form field (and any existing data) for User Disguises activity mode.
    $modulename = $formwrapper->get_current()->modulename;

    // User disguises are currently limited to forum activities only.
    if ($modulename == 'forum') {
        global $DB;

        // Add the options to the form.
        $choices = array();
        $choices[AUTH_DISGUISE_MODE_DISABLED] = get_string('module_mode_disabled', 'auth_disguise');
        $choices[AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE] = get_string('module_mode_peersafe', 'auth_disguise');
        $choices[AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE] = get_string('module_mode_instructorsafe', 'auth_disguise');
        $mform->addElement('header', 'disguises_options', get_string('title', 'auth_disguise'));
        $mform->addElement('select', 'disguises_mode', get_string('disguises_mode_module', 'auth_disguise'), $choices);
        $mform->setType('disguises_mode', PARAM_RAW);

        // Retrieve saved value for this context (if any) or set the default.
        $context = context_module::instance($formwrapper->get_coursemodule()->id);
        $dbparams = ['contextid' => $context->id];
        $fields = '*';
        if ($dcmode = $DB->get_record('auth_disguise_ctx_mode', $dbparams, $fields)) {
            $mform->setDefault('disguises_mode', $dcmode->disguises_mode);
        } else {
            $mform->setDefault('disguises_mode', 0); // Disabled
        }
    }
}

/**
 * Add in form elements to course configuration to allow for user disguise
 * modules to be configured. Set the form value(s) from their saved database
 * entries for this context or set the default.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function auth_disguise_course_standard_elements($formwrapper, $mform) {

    // If user disguises are disabled site-wide, abort.
    if (!get_config('auth_disguise','feature_status_site')) {
        return;
    }

    global $DB;

    // Add the options to the form.
    $choices = array();
    $choices[AUTH_DISGUISE_MODE_DISABLED] = get_string('course_mode_disabled', 'auth_disguise');
    $choices[AUTH_DISGUISE_MODE_COURSE_OPTIONAL] = get_string('course_mode_optional', 'auth_disguise');
    $choices[AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY] = get_string('course_mode_modules_only', 'auth_disguise');
    $choices[AUTH_DISGUISE_MODE_COURSE_EVERYWHERE] = get_string('course_mode_everywhere', 'auth_disguise');
    $mform->addElement('header', 'disguises_options', get_string('title', 'auth_disguise'));
    $mform->addElement('select', 'disguises_mode', get_string('disguises_mode_course', 'auth_disguise'), $choices);
    $mform->setType('disguises_mode', PARAM_RAW);

    // Retrieve saved value for this context (if any) or set the default.
    $course = $formwrapper->get_course();
    if (!empty($course)) {
        $coursecontext = context_course::instance($course->id);
        $context = $coursecontext;
    } else {
        $coursecontext = null;
        $context = $categorycontext;
    }

    $dbparams = ['contextid' => $context->id];
    $fields = '*';
    if ($dcmode = $DB->get_record('auth_disguise_ctx_mode', $dbparams, $fields)) {
        $mform->setDefault('disguises_mode', $dcmode->disguises_mode);
    } else {
        $mform->setDefault('disguises_mode', 0); // Disabled
    }
}

/**
 * Process user disguises configuration from submitted form and save it to the
 * database.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return void
 *
 * See plugin_extend_coursemodule_edit_post_actions in
 * https://github.com/moodle/moodle/blob/master/course/modlib.php
 */

function auth_disguise_coursemodule_edit_post_actions($data, $course) {
    global $DB;

    // If user disguises are disabled site-wide, abort.
    if (!get_config('auth_disguise','feature_status_site')) {
        return;
    }

    // if there is no data, then there is nothing to do.
    if (empty($data->disguises_mode)) {
        debugging('No data to process for user disguises.', DEBUG_DEVELOPER);
        return;
    }

    // Add or update disguise mode for course module and context.
    $context = context_module::instance($data->coursemodule);
    $dbparams = ['contextid' => $context->id];
    $fields = '*';
    if (!$dcmode = $DB->get_record('auth_disguise_ctx_mode', $dbparams, $fields)) {
        
        $insert = new \stdClass();
        $insert->contextid = $context->id;
        $insert->disguises_mode = $data->disguises_mode;
        $DB->insert_record('auth_disguise_ctx_mode', $insert);
        // DEBUG: Remove this later.
        \core\notification::add('Disguise mode added.', \core\notification::INFO);
    } else {
        if ($dcmode->disguises_mode != $data->disguises_mode) {
            $dcmode->disguises_mode = $data->disguises_mode;
            $DB->update_record('auth_disguise_ctx_mode', $dcmode);
            // DEBUG: Remove this later.
            \core\notification::add('Disguise mode updated.', \core\notification::INFO);
        }
    }
    return $data;
}

/**
 * Process user disguises configuration from submitted form and save it to the
 * database.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return void
 *
 * See plugin_extend_coursemodule_edit_post_actions in
 * https://github.com/moodle/moodle/blob/master/course/modlib.php
 */
function auth_disguise_course_edit_post_actions($data, $oldcourse) {

    // If user disguises are disabled site-wide, abort.
    if (!get_config('auth_disguise','feature_status_site')) {
        return;
    }

    global $DB;

    // Add or update disguise mode for course and context.
    $context = context_course::instance($data->id);
    $dbparams = ['contextid' => $context->id];
    $fields = '*';
    if (!$dcmode = $DB->get_record('auth_disguise_ctx_mode', $dbparams, $fields)) {
        $insert = new \stdClass();
       	$insert->contextid = $context->id;
        $insert->disguises_mode = $data->disguises_mode;
        $DB->insert_record('auth_disguise_ctx_mode', $insert);
        // DEBUG: Remove this later.
        \core\notification::add('Disguise mode added.', \core\notification::INFO);
    } else {
        if ($dcmode->disguises_mode != $data->disguises_mode) {
            $dcmode->disguises_mode = $data->disguises_mode;
            $DB->update_record('auth_disguise_ctx_mode', $dcmode);
            // DEBUG: Remove this later.
            \core\notification::add('Disguise mode updated.', \core\notification::INFO);
        }
    }
    return $data;
}

/**
 * Ensure auth_disguise_after_require_login() called even in edge cases.
 *
 * TODO: Determine if any edge cases exist, and whether or not to call after_require_login.
 *
function auth_disguise_after_config() {
    if (isloggedin() && !isguestuser()) {
        auth_disguise_after_require_login();
    }
}
 */

/**
 * On every page, check if redirection to disguise prompt/switching required.
 *
 * Performance note: Heavy lifting should be avoided in this call.

 * @param object|integer $courseorid Course to checked.
 * @param bool $autologinguest Are guests automatically logged in.
 * @param context $cm Context/Course Module?
 * @param string $setwantsurltome Requested URL.
 * @param bool $preventredirect Stop Moodle redirects.
 */
function auth_disguise_after_require_login($courseorid = null, $autologinguest = null, $cm = null,
                                           $setwantsurltome = null, $preventredirect = null) {

    global $DB;
    global $USER;

    // Do not process if it is not under a course context (or the one below course context)
    if (is_null($courseorid)) {
        return;
    }

    // Get current course.
    if (is_object($courseorid)) {
        $course = $courseorid;
    } else {
        $course = get_course($courseorid);
    }

    // Determine whether it is a course or module context.
    if (!is_null($cm)) {
        $context = context_module::instance($cm->id);
    } else {
        $context = context_course::instance($course->id);
    }

    // User disguise is disabled.
    if (!disguise::is_disguise_enabled_for_context($context)) {
        return;
    }

    $course = null;
    $dcmode_course = false;
    $dcmode_module = false;
    $isuserdisguised = $USER->auth === 'disguise'?true:false;

    // Determine and setup non-site course, module context objects and dcmodes.
    if (!empty($courseorid)) {
        if (is_object($courseorid)) {
            // TODO: Course-context disguises are not implemented yet.
            $course = $courseorid;
        } else if ($courseorid == SITEID) {
            // TODO: Site-wide disguises not implemented yet.
        } else {
            // TODO: Course-context disguises are not implemented yet.
            $course = $DB->get_record('course', array('id' => $courseorid), '*', MUST_EXIST);
        }
        if ($cm) {
            if ($cm->course != $course->id) {
                throw new coding_exception('course and cm parameters in ' .
                    'auth_disguise_after_require_login() call do not match!!');
            }
            // Fetch disguises mode entry record if it exists for module context.
            $cmcontext = context_module::instance($cm->id, MUST_EXIST);
            $dbparams = ['contextid' => $cmcontext->id];
            $dcmode_module = $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', $dbparams);
            // TODO: Check access details to $cm via fast_modinfo after any redirects (not here).
        }
    }

    // Fetch disguises mode entry record if it exists for non-site course context.
    // TODO: Write logic checking course custom fields data structure (dependent on config).
    // TODO: Then, implement the same data structure above for courses, and duplicate/replace.
    if ($course) {
        $coursecontext = context_course::instance($course->id, MUST_EXIST);
        $dbparams = ['contextid' => $coursecontext->id];
        $dcmode_course = $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', $dbparams);
    }

    // If no disguise mode records exist or modes are disabled...
    if (!$dcmode_course && !$dcmode_module ) {
        // ...and user is disguised...
        if ($isuserdisguised) {
            // TODO: Implement appropriate checks not to redirect, e.g. Teacher/editing mode/etc.
            if (is_siteadmin($USER->id)) {
                \core\notification::add('DEBUG: auth/disguise: Not redirecting - is_siteadmin().', \core\notification::INFO);
            } else {
                // ...then the user is about to leave disguised mode; send them to a warning.
                redirect('/auth/disguise/prompt.php');
            }
        } else {
            return;
        }
    // Else, course or module has disguises enabled (more than just completely disabled).
    } else if ($cm) {
        /* Logic if you're at module context level:
         * 
         * Module Mode disabled & user not disguised -> No action, unless course 
         * Module Mode enabled & user not disguised -> Prompt to switch identity
         * Module Mode disabled & user disguised -> Prompt to switch identity
         * Module Mode enabled & user disguised -> Prompt to confirm/choose identity
         *
         * TODO: Logic needs re-doing, with updated course options...
         */
        if (!$isuserdisguised && !$dcmode_module) {
            return;
        } else {
            // TODO: Implement appropriate checks not to redirect, e.g. Teacher/editing mode/etc.
            if (is_siteadmin($USER->id)) {
                \core\notification::add('DEBUG: auth/disguise: Not redirecting - is_siteadmin().', \core\notification::INFO);
            } else {
                redirect('/auth/disguise/prompt.php');
            }
        }
    } else {
        /* Logic if you're at course context level:
         * 
         * Course Mode disabled & user not disguised -> No action
         * Course Mode disabled & user disguised -> Prompt to switch identity
         * Course Mode enabled & user not disguised -> Prompt to switch identity
         * Course Mode enabled & user disguised -> Prompt to confirm/choose identity
         *
         */
        // TODO: Write logic checking course custom fields data structure (dependent on config).
        // TODO: Then, implement the same data structure above for courses, and duplicate/replace.
    }

    // TODO: What about checking course category level? Is this part of "site" or courseid = catid?

}

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
use auth_disguise\manager\disguise_context;
use auth_disguise\manager\disguise_enrol;
use auth_disguise\manager\disguise_keyword;

// Constants for user disguise modes.
define('AUTH_DISGUISE_MODE_DISABLED', "0");

// Course modes.
define('AUTH_DISGUISE_MODE_COURSE_OPTIONAL', "100");
define('AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY', "101");
define('AUTH_DISGUISE_MODE_COURSE_EVERYWHERE', "102");

// Course module modes.
define('AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE', "200");
define('AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE', "201");

// Switch ID.
define('AUTH_DISGUISE_CONTINUE_WITH_CURRENT_ID', 1);
define('AUTH_DISGUISE_SWITCH_TO_DISGUISE_ID', 2);
define('AUTH_DISGUISE_GO_BACK_TO_PREVIOUS_PAGE', 3);
define('AUTH_DISGUISE_SWITCH_TO_REAL_ID', 4);

// Keywords per page.
define('AUTH_DISGUISE_KEYWORDS_PER_PAGE', 10);
define('AUTH_DISGUISE_KEYWORD_ITEMS_PER_PAGE', 100);

/**
 * Add in form elements to course module configuration to allow for user
 * disguise modules to be configured. Set the form value(s) from their saved
 * database entries for this context or set the default.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function auth_disguise_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;

    // If disguise is not enabled, abort.
    if (!disguise::is_disguise_enabled()) {
        return;
    }

    // Get course context.
    $course = $formwrapper->get_course();
    $context = context_course::instance($course->id);

    // If disguise is allowed for the activities of the course.
    if (!disguise::is_disguise_allowed_for_subcontext($context->id)) {
        return;
    }

    // Add form field (and any existing data) for User Disguises activity mode.
    $modulename = $formwrapper->get_current()->modulename;

    // User disguises are currently limited to forum activities only.
    if ($modulename == 'forum') {

        // Add the options to the form.
        $choices = [
            AUTH_DISGUISE_MODE_DISABLED => get_string('module_mode_disabled', 'auth_disguise'),
            AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE => get_string('module_mode_peersafe', 'auth_disguise'),
            AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE => get_string('module_mode_instructorsafe', 'auth_disguise'),
        ];

        // If the disguise is forced, remove the disabled option.
        if (disguise::is_disguise_forced_for_subcontext($context->id)) {
            unset($choices[AUTH_DISGUISE_MODE_DISABLED]);
        }

        $mform->addElement('header', 'disguises_options', get_string('title', 'auth_disguise'));
        $mform->addElement('select', 'disguises_mode', get_string('disguises_mode_module', 'auth_disguise'), $choices);
        $mform->setType('disguises_mode', PARAM_RAW);

        // Default mode.
        $module = $formwrapper->get_coursemodule();
        if (!empty($module->id)) {
            $context = context_module::instance($module->id);
            $defaultmode = $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', ['contextid' => $context->id]) ?? 0;
        } else {
            $defaultmode = 0;
        }
        $mform->setDefault('disguises_mode', $defaultmode);
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
    global $DB;

    // If disguise is not enabled, abort.
    if (!disguise::is_disguise_enabled()) {
        return;
    }

    // Add the options to the form.
    $choices =[
        AUTH_DISGUISE_MODE_DISABLED             => get_string('course_mode_disabled', 'auth_disguise'),
        AUTH_DISGUISE_MODE_COURSE_OPTIONAL      => get_string('course_mode_optional', 'auth_disguise'),
        AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY  => get_string('course_mode_modules_only', 'auth_disguise'),
        AUTH_DISGUISE_MODE_COURSE_EVERYWHERE    => get_string('course_mode_everywhere', 'auth_disguise')
    ];
    $mform->addElement('header', 'disguises_options', get_string('title', 'auth_disguise'));
    $mform->addElement('select', 'disguises_mode', get_string('disguises_mode_course', 'auth_disguise'), $choices);
    $mform->setType('disguises_mode', PARAM_RAW);

    $course = $formwrapper->get_course();
    if (!empty($course->id)) {
        $context = context_course::instance($course->id);
        $defaultmode = $DB->get_field('auth_disguise_ctx_mode', 'disguises_mode', ['contextid' => $context->id]) ?? 0;
    } else {
        $defaultmode = AUTH_DISGUISE_MODE_DISABLED;
    }

    // Default mode.
    $mform->setDefault('disguises_mode', $defaultmode);

    // Disabled naming set for now.
    return;

    // Naming set.
    $keywordsrecords = disguise_keyword::get_keyword_records();
    // List form keyword from keyword records.
    $keywords = [];
    foreach ($keywordsrecords as $record) {
        $keywords[$record->id] = $record->keyword;
    }

    $options = array(
        'multiple' => true,
        'noselectionstring' => get_string('no_naming_set', 'auth_disguise'),
    );
    $mform->addElement('autocomplete', 'naming_set', get_string('naming_set', 'auth_disguise'),
        $keywords, $options);
    // Set default.
    if ($context) {
        $namingset = disguise_context::get_naming_set_for_context($context->id);
        $defaultnaming = $namingset->naming ?? '';
    } else {
        $defaultnaming = '';
    }
    $mform->setDefault('naming_set', $defaultnaming);
}

/**
 * Process user disguises configuration from submitted form and save it to the database.
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

    // If disguise is not enabled, abort.
    if (!disguise::is_disguise_enabled()) {
        return $data;
    }
    // if there is no data, then there is nothing to do.
    if (!isset($data->disguises_mode)) {
        return $data;
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
    } else {
        if ($dcmode->disguises_mode != $data->disguises_mode) {
            $dcmode->disguises_mode = $data->disguises_mode;
            $DB->update_record('auth_disguise_ctx_mode', $dcmode);
        }
    }
    return $data;
}

/**
 * Process user disguises configuration from submitted form and save it to the database.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return void
 *
 * See plugin_extend_coursemodule_edit_post_actions in
 * https://github.com/moodle/moodle/blob/master/course/modlib.php
 */
function auth_disguise_course_edit_post_actions($data, $oldcourse) {
    // If disguise is not enabled, abort.
    if (!disguise::is_disguise_enabled()) {
        return $data;
    }

    // Add or update disguise mode for course and context.
    $context = context_course::instance($data->id);
    if (!disguise_context::disguise_context_mode_exists($context->id)) {
        // Add disguise mode for course.
        disguise_context::insert_disguise_context_mode($context->id, $data->disguises_mode);
    } else {
        // Update disguise mode for course.
        disguise_context::update_disguise_context_mode($context->id, $data->disguises_mode);
    }

    // Add enrolment instance for course.
    if ($data->disguises_mode != AUTH_DISGUISE_MODE_DISABLED) {
        disguise_enrol::create_enrol_disguise_instance($context->id);
    } else {
        disguise_enrol::disable_enrol_disguise_instance($context->id);
    }

    // Disabled naming set for now.
    return $data;

    // Save Naming Sets.
    // Convert naming set array to string.
    $namingset = implode(',', $data->naming_set);
    disguise_context::save_naming_set_for_context($context->id, $namingset);

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
    global $USER, $PAGE;

    // The 'require login' may be called by other callbacks and they are in different context, so we need to exclude them.
    if (!disguise::is_page_type_supported($PAGE)) {
        return;
    }

    // Do not process if it is not under a course context (or the one below course context)
    if (empty($courseorid)) {
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

    // Back to real user if required.
    disguise::prompt_back_to_real_user($context->id);

    // Check if disguise is enabled for this user.
    if (!disguise::is_disguise_enabled_for_user($context->id, $USER->id)) {
        return;
    }

    // Show a prompt to the user if they are not already disguised.
    disguise::prompt_to_disguise($context->id);
}

function auth_disguise_after_config() {
    $settingsection = optional_param('section', '', PARAM_ALPHAEXT);

    // GET requests only.
    if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] !== 'GET')) {
        return;
    }

    // Experimental settings page.
    if ($settingsection !== 'experimentalsettings') {
        return;
    }

    // Enable auth disguise and enrol disguise if required.
    $authclass = \core_plugin_manager::resolve_plugininfo_class('auth');
    $enrolclass = \core_plugin_manager::resolve_plugininfo_class('enrol');

    $authclass::enable_plugin('disguise', disguise::is_disguise_enabled());
    $enrolclass::enable_plugin('disguise', disguise::is_disguise_enabled());
}

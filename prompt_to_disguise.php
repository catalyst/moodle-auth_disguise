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
 * Display a prompt to whether switch to disguise or not.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/auth/disguise/lib.php');
use auth_disguise\manager\disguise;

redirect_if_major_upgrade_required();

$returnurl = optional_param('returnurl', "", PARAM_LOCALURL);
$nexturl = optional_param('nexturl', "", PARAM_LOCALURL);
$contextid = optional_param('contextid', 0, PARAM_INT);
$switchidentity = optional_param('switchidentity', 0, PARAM_INT);
$optional = optional_param('optional', 0, PARAM_BOOL);

// Set page url/
$PAGE->set_url('/auth/disguise/prompt_to_disguise.php');

// Context/
$context = context::instance_by_id($contextid);
$PAGE->set_context($context);

// Check if $USER is logged in.
isloggedin() || redirect($CFG->wwwroot . '/login/index.php');

// Not switching identity, so just continue.
if ($switchidentity == AUTH_DISGUISE_CONTINUE_WITH_CURRENT_ID) {
    disguise::ignore_disguise_for_context($contextid);
    redirect($returnurl);
}

// Switch ID.
if ($switchidentity == AUTH_DISGUISE_SWITCH_TO_DISGUISE_ID) {
    disguise::disguise_user($contextid, $USER->id);
    redirect($nexturl);
}

// Set up PAGE.
$context = context::instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_url('/auth/disguise/prompt_to_disguise.php');
$PAGE->set_pagelayout('admin');

// Check if it is course context.
if ($context->contextlevel == CONTEXT_COURSE) {
    $course = $DB->get_record('course', ['id' => $context->instanceid], '*', MUST_EXIST);
    $PAGE->set_course($course);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
} else if ($context->contextlevel == CONTEXT_MODULE) {
    $cm = $DB->get_record('course_modules', ['id' => $context->instanceid], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $PAGE->set_cm($cm, $course);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title', 'auth_disguise'));

// Alert Box.
if ($optional) {
    echo $OUTPUT->notification(get_string('switch_to_optional_disguise_id_warning', 'auth_disguise'), 'warning');
} else {
    echo $OUTPUT->notification(get_string('switch_to_forced_disguise_id_warning', 'auth_disguise'), 'warning');
}

// Continue button.
echo $OUTPUT->single_button(
    new moodle_url('/auth/disguise/prompt_to_disguise.php', [
            'contextid' => $contextid,
            'switchidentity' => AUTH_DISGUISE_CONTINUE_WITH_CURRENT_ID,
            'returnurl' => $returnurl,
    ]),
    get_string('continue_with_current_id', 'auth_disguise'),
    'get'
);

// Switch identity button.
echo $OUTPUT->single_button(
    new moodle_url('/auth/disguise/prompt_to_disguise.php', [
            'contextid' => $contextid,
            'switchidentity' => AUTH_DISGUISE_SWITCH_TO_DISGUISE_ID,
            'nexturl' => $nexturl,
    ]),
    get_string('switch_to_disguise_id', 'auth_disguise'),
    'get'
);

echo $OUTPUT->footer();

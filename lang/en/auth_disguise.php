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

/**
 * Strings for component 'auth_nologin', language 'en'.
 *
 * @package   auth_disguise
 * @copyright 2023 Catalyst  {@link https://catalyst-au.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_disguisedescription'] = 'Auxiliary plugin that allows users with Moodle accounts to have disguises where permitted.';
$string['pluginname'] = 'User Disguises';
$string['privacy:metadata'] = 'The Disguised user authentication plugin needs its privacy metadata reviewed.';

$string['title'] = 'User Disguises';

$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';

$string['feature_status_site'] = 'Feature status (site-wide) [EXPERIMENTAL]';
$string['feature_status_site_desc'] = 'This determines whether user disguises are enabled for use site-wide. It controls the display of settings regarding modification of user disguise feature settings throughout the site.';

// Course disguise context modes - Currently only disabled and modules_only are valid.
// TODO: Extend and implement for option and everywhere course modes.
$string['disguises_mode_course'] = 'User Disguises Mode (Course)';
$string['course_mode_disabled'] = 'Disabled - No user disguises permitted in course or any activity.';
$string['course_mode_optional'] = 'Optional - Disguises may be used in course, or set by activity.';
$string['course_mode_modules_only'] = 'Modules only - Disguise may only be used where set by activity.';
$string['course_mode_everywhere'] = 'Everywhere - User disguises applied to course and all activities.';

// Course module disguise context modes - Currently only disabled and instructorsafe are valid - and partially implemented.
// TODO: Complete implementation an extend to peersafe by implementing unmasking functionality.
$string['disguises_mode_module'] = 'User Disguises Mode (Activity)';
$string['module_mode_disabled'] = 'Disabled - No user disguises permitted in this activity.';
$string['module_mode_peersafe'] = 'Peer-Safe - Students in this activity appear disguised to other students.';
$string['module_mode_instructorsafe'] = 'Instructor-Safe - Students in this activity appear disguised to students/instructors.';

$string['switch_id_warning'] = 'You are requesting access to, or leaving, a page where user disguises are enabled.';
$string['continue_with_current_id'] = 'Continue with current ID';
$string['switch_to_disguise_id'] = 'Switch to disguise ID';

$string['keyword_page'] = 'Manage keywords';

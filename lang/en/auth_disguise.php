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

$string['title'] = 'User Disguises';

$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';

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

// Privacy Provider.
$string['privacy:metadata:database:auth_disguise_user_map'] = 'Mapping between users and disguise';
$string['privacy:metadata:database:auth_disguise_user_map:userid'] = 'User ID';
$string['privacy:metadata:database:auth_disguise_user_map:disguiseid'] = 'ID of disguise user';
$string['privacy:metadata:database:auth_disguise_unmapped_disg'] = 'Un-allocated disguise users';
$string['privacy:metadata:database:auth_disguise_unmapped_disg:disguiseid'] = 'ID of disguise user';

// Switch ID.
$string['switch_to_forced_disguise_id_warning'] = 'You are requesting access to a page where user disguises are forced. If you chose to continue with your current ID, you will be redirected to previous page. Otherwise, you can switch to a disguise ID to access the page.';
$string['switch_to_optional_disguise_id_warning'] = 'You are requesting access to a page where user disguises are optional. You can choose to continue with your current ID, or switch to a disguise ID.';
$string['switch_to_real_id_warning'] = 'You are requesting access to a page where user disguises are disabled.';
$string['continue_with_current_id'] = 'Continue with current ID';
$string['switch_to_disguise_id'] = 'Switch to disguise ID';
$string['switch_to_real_id'] = 'Switch to real ID';
$string['go_back_to_previous_page'] = 'Go back to previous page';

// Keyword management strings.
$string['keyword_page'] = 'Manage keywords';
$string['keyword'] = 'Keyword';
$string['items'] = 'Items';
$string['items_help'] = 'List of comma separated items '.
$string['keywords'] = 'Keywords';
$string['new_keyword'] = 'New keyword';
$string['count'] = 'Number of items';
$string['action'] = 'Action';

// Item page strings.
$string['item_page'] = 'Manage items';
$string['new_item'] = 'New item';
$string['item'] = 'Item';

// Disguise set.
$string['naming_set'] = 'Disguise set';
$string['no_naming_set'] = 'Please select a keyword to build naming set';

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
 * Admin settings and defaults.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig && \auth_disguise\manager\disguise::is_disguise_enabled()) {

    // Display plugin description.
    $settings->add(new admin_setting_heading('auth_disguise/pluginname', '',
        new lang_string('auth_disguisedescription', 'auth_disguise')));

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('disguise');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            get_string('auth_fieldlocks_help', 'auth'), false, false);

    $ADMIN->add('authsettings', new admin_category('auth_disguise', get_string('pluginname', 'auth_disguise')));
    $ADMIN->add('auth_disguise', $settings);
    // To prevent the settings from being displayed twice.
    $settings = null;

    // External admin page to define keywords.
//    $ADMIN->add('auth_disguise',
//        new admin_externalpage(
//            'auth_disguise_keyword',
//            get_string('keyword_page', 'auth_disguise'),
//            new moodle_url($CFG->wwwroot . '/auth/disguise/keyword.php')
//        )
//    );

}

if ($hassiteconfig) {
    // Experimental settings for user disguise.
    $setting = new admin_setting_configcheckbox('userdisguise',
        new lang_string('pluginname', 'auth_disguise'),
        new lang_string('feature_status_site_desc', 'auth_disguise'),
        0);

    // Find the experimental settings category.
    $category = $ADMIN->locate('experimentalsettings');
    if ($category) {
        $category->add($setting);
    }
}

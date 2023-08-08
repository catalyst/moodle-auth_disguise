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

    public static function is_disguise_enabled_for_user($context, $user) {
        global $SESSION;
        // Check if disguise is enabled for this context.
        if (!self::is_disguise_enabled_for_context($context)) {
            return false;
        }

        // Check if user is already disguised.
        if ($user->auth == 'disguise') {
            return false;
        }

        // If the context is ignored, then disguise is disabled.
        if ($SESSION->ignoreddisguisecontext == $context->id) {
            return false;
        }

        // Disabled for site admin.
//        if (is_siteadmin($user->id)) {
//            return false;
//        }

        return true;
    }

}

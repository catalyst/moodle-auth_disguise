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

namespace auth_disguise\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Privacy Subsystem implementation for auth_disguise.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('auth_disguise_user_map', [
            'userid' => 'privacy:metadata:database:auth_disguise_user_map:userid',
            'disguiseid' => 'privacy:metadata:database:auth_disguise_user_map:disguiseid',
        ], 'privacy:metadata:database:auth_disguise_user_map');

        $collection->add_database_table('auth_disguise_unmapped_disg', [
            'disguiseid' => 'privacy:metadata:database:auth_disguise_unmapped_disg:disguiseid',
        ], 'privacy:metadata:database:auth_disguise_unmapped_disg');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        // Real user id.
        $sql = "SELECT c.id
                  FROM {auth_disguise_user_map} adum
                  JOIN {context} c ON c.instanceid = adum.userid
                 WHERE contextlevel = :contextuser AND c.instanceid = :userid";
        $contextlist->add_from_sql($sql, ['contextuser' => CONTEXT_USER, 'userid' => $userid]);

        // Disguise user is a fake user, so it does not contain personal user details.
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        // None of the table data should be exported.
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        // None of the table data should be deleted.
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // None of the table data should be deleted.
    }

    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $params = [
            'contextid' => $context->id
        ];

        $sql = "SELECT userid
                  FROM {auth_disguise_user_map}
                 WHERE contextid = :contextid";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
        // None of the table data should be deleted.
    }


}

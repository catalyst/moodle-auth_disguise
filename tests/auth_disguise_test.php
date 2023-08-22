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

namespace tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the auth_disguise class.
 *
 * @package enrol_disguise
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_disguise_test extends \advanced_testcase {

    /**
     * Enable the auth plugin.
     */
    protected function enable_auth() {
        $auths = get_enabled_auth_plugins();
        if (!in_array('disguise', $auths)) {
            $auths[] = 'disguise';
        }
        set_config('auth', implode(',', $auths));
    }

    /*
     * Do not allow user to log in with disguise auth.
     */
    public function test_user_login() {
        global $CFG;

        $this->resetAfterTest();

        // Prevent standard logging.
        $oldlog = ini_get('error_log');
        ini_set('error_log', "$CFG->dataroot/testlog.log");

        // Moodle require HTTP_USER_AGENT to show in the error log.
        $_SERVER['HTTP_USER_AGENT'] = 'no browser';

        // Create a user.
        $this->getDataGenerator()->create_user([
            'username' => 'username',
            'password' => 'password',
            'email' => 'email@example.com',
            'auth' => 'disguise'
        ]);

        // Enable the plugin.
        $this->enable_auth();

        // Try to login with correct username and password.
        $reason = null;
        $sink = $this->redirectEvents();
        $result = authenticate_user_login('username', 'password', false, $reason);
        $sink->close();
        $this->assertFalse($result);
        $this->assertEquals(AUTH_LOGIN_FAILED, $reason);

        // Restore error log.
        ini_set('error_log', $oldlog);
    }

    /**
     * Do not allow to change password.
     */
    public function test_user_update_password() {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user([
            'username' => 'username',
            'password' => 'password',
            'email' => 'email@example.com',
            'auth' => 'disguise'
        ]);

        $disguiseauth = get_auth_plugin('disguise');
        $this->assertFalse($disguiseauth->user_update_password($user, 'newpassword'));
    }

    /**
     * Do not store password.
     */
    public function test_prevent_local_passwords() {
        $this->resetAfterTest();
        $disguiseauth = get_auth_plugin('disguise');
        $this->assertTrue($disguiseauth->prevent_local_passwords());
    }

    /**
     * Do not change password.
     */
    public function test_can_change_password() {
        $this->resetAfterTest();
        $disguiseauth = get_auth_plugin('disguise');
        $this->assertFalse($disguiseauth->can_change_password());
    }

    /**
     * Do not reset password.
     */
    public function test_can_reset_password() {
        $this->resetAfterTest();
        $disguiseauth = get_auth_plugin('disguise');
        $this->assertFalse($disguiseauth->can_reset_password());
    }

    /**
     * Do not sync external data.
     */
    public function test_sync_users() {
        $this->resetAfterTest();
        $disguiseauth = get_auth_plugin('disguise');
        $this->assertTrue($disguiseauth->is_internal());
    }

    /**
     * Do not manually set.
     */
    public function test_can_be_manually_set() {
        $this->resetAfterTest();
        $disguiseauth = get_auth_plugin('disguise');
        $this->assertFalse($disguiseauth->can_be_manually_set());
    }

}

<?php

namespace tests\manager;

use auth_disguise\manager\disguise;
use context_course;
use context_module;
use context_system;
use moodle_page;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the auth_disguise class.
 *
 * @package auth_disguise
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class disguise_test extends \advanced_testcase {

    /**
     * Test is_disguise_enabled.
     *
     */
    public function test_is_disguise_enabled() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->assertFalse(disguise::is_disguise_enabled());
        set_config('userdisguise', 1);
        $this->assertTrue(disguise::is_disguise_enabled());
    }

    /**
     * Data provider for test_is_page_type_supported.
     * @return array
     */
    public function is_page_type_supported_provider() {
        return [
            ['site-index', true],
            ['course-view', true],
            ['mod-forum-view', true],
            ['mod-forum-view', true],
            ['my-index', false],
        ];
    }

    /**
     * @dataProvider is_page_type_supported_provider
     * @param string $pagetype page type
     * @param bool $expected whether the page type is supported
     */
    public function test_is_page_type_supported($pagetype, $expected) {
        $this->resetAfterTest();
        $page = new moodle_page();
        $page->set_pagetype($pagetype);
        $this->assertEquals($expected, disguise::is_page_type_supported($page));
    }

    /**
     * Data provider for test_set_disguise_mode_for_context.
     * @return array
     */
    public function set_disguise_mode_for_context_provider() {
        return [
            // Disabled at course context.
            [
                'coursemode' => AUTH_DISGUISE_MODE_DISABLED,
                'modmode' => AUTH_DISGUISE_MODE_DISABLED,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_DISABLED,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_DISABLED,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE,
            ],

            // Optional at course context.
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_OPTIONAL,
                'modmode' => AUTH_DISGUISE_MODE_DISABLED,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => false,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => true,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_OPTIONAL,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => true,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_OPTIONAL,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => true,
            ],

            // Course module only.
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY,
                'modmode' => AUTH_DISGUISE_MODE_DISABLED,
                'enabledcoursecontext' => false,
                'enabledmodcontext' => false,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => false,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE,
                'enabledcoursecontext' => false,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => false,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_MODULES_ONLY,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE,
                'enabledcoursecontext' => false,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => false,
                'optionalforcourseandmod' => false,
            ],

            // Disguise everywhere in the course.
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_EVERYWHERE,
                'modmode' => AUTH_DISGUISE_MODE_DISABLED,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => true,
                'optionalforcourseandmod' => false,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_EVERYWHERE,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => true,
                'optionalforcourseandmod' => false,
            ],
            [
                'coursemode' => AUTH_DISGUISE_MODE_COURSE_EVERYWHERE,
                'modmode' => AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE,
                'enabledcoursecontext' => true,
                'enabledmodcontext' => true,
                'allowedsubcontexts' => true,
                'forcedsubcontexts' => true,
                'optionalforcourseandmod' => false,
            ],
        ];
    }

    /**
     * Test set_disguise_mode_for_context.
     *
     * @dataProvider set_disguise_mode_for_context_provider
     *
     * @covers disguise::set_disguise_mode_for_context
     * @covers disguise::get_disguise_mode_for_context
     * @covers disguise::is_disguise_allowed_for_subcontext
     * @covers disguise::is_disguise_forced_for_subcontext
     * @covers disguise::is_disguise_enabled_for_context
     *
     */
    public function test_set_disguise_mode_for_context($coursemode, $modmode,
                                                       $enabledcoursecontext = false, $enabledmodcontext = false,
                                                       $allowedsubcontexts = false, $forcedsubcontexts = false,
                                                       $optionalforcourseandmod = false) {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Enable user disguise.
        set_config('userdisguise', 1);

        // Course context.
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);

        // Create a forum.
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $modcontext = context_module::instance($forum->cmid);

        // Set the disguise mode.
        disguise::set_disguise_mode_for_context($coursecontext->id, $coursemode);
        disguise::set_disguise_mode_for_context($modcontext->id, $modmode);

        // Check the disguise mode.
        $this->assertEquals($coursemode, disguise::get_disguise_mode_for_context($coursecontext->id));
        $this->assertEquals($modmode, disguise::get_disguise_mode_for_context($modcontext->id));

        // Check if disguise is enabled for context.
        $this->assertEquals($enabledcoursecontext, disguise::is_disguise_enabled_for_context($coursecontext->id));
        $this->assertEquals($enabledmodcontext, disguise::is_disguise_enabled_for_context($modcontext->id));

        // Check if disguise is allowed for subcontexts.
        $this->assertEquals($allowedsubcontexts, disguise::is_disguise_allowed_for_subcontext($coursecontext->id));

        // Check if disguise is forced for subcontexts.
        $this->assertEquals($forcedsubcontexts, disguise::is_disguise_forced_for_subcontext($coursecontext->id));

        // Check if disguise is optional for course and mod.
        $this->assertEquals($optionalforcourseandmod, disguise::is_disguise_optional_for_context($coursecontext->id));
        $this->assertEquals($optionalforcourseandmod, disguise::is_disguise_optional_for_context($modcontext->id));
    }

    /**
     * Test if disguise is enabled for user.
     *
     * @return void
     */
    public function test_is_disguise_enabled_for_user() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Enable user disguise.
        set_config('userdisguise', 1);

        // Course with disquise enabled.
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        disguise::set_disguise_mode_for_context($coursecontext->id, AUTH_DISGUISE_MODE_COURSE_EVERYWHERE);

        // User.
        $user = $this->getDataGenerator()->create_user();

        // User is not enrolled in the course.
        $this->assertFalse(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Enrol user in course.
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->assertTrue(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Assign user as teacher - course contact.
        $teacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        role_assign($teacherrole->id, $user->id, $coursecontext->id);
        $this->assertFalse(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Un-assign teacher role.
        role_unassign($teacherrole->id, $user->id, $coursecontext->id);
        $this->assertTrue(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Set user as site admin.
        set_config('siteadmins', $user->id);
        $this->assertFalse(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Unset site admin.
        set_config('siteadmins', '');
        $this->assertTrue(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // User is a disguise.
        $DB->set_field('user', 'auth', 'disguise', array('id' => $user->id));
        $this->assertFalse(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));
    }

    public function test_disguise_user() {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Enable user disguise.
        set_config('userdisguise', 1);

        // Course with disquise enabled.
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);
        disguise::set_disguise_mode_for_context($coursecontext->id, AUTH_DISGUISE_MODE_COURSE_EVERYWHERE);

        // User.
        $user = $this->getDataGenerator()->create_user();
        self::setUser($user);
        $usersession = $GLOBALS['SESSION'];

        // Enrol user in course.
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->assertTrue(disguise::is_disguise_enabled_for_user($coursecontext->id, $user->id));

        // Disguise user.
        disguise::disguise_user($coursecontext->id, $user->id);

        // Check if user is disguised.
        $this->assertNotEmpty($_SESSION['DISGUISESESSION']);
        $this->assertNotEmpty($_SESSION['USERINDISGUISE']);
        $this->assertNotEmpty($_SESSION['DISGUISECONTEXT']);

        // Session.
        $this->assertEquals($user->id, $_SESSION['USERINDISGUISE']->id);
        $this->assertEquals($coursecontext->id, $_SESSION['DISGUISECONTEXT']);
        $this->assertEquals($usersession, $_SESSION['DISGUISESESSION']);

        // User.
        $this->assertNotEquals($user->id, $USER->id);

        // Change context.
        $sitecontext = context_system::instance();
        disguise::back_to_real_user($sitecontext->id);

        // Session.
        $this->assertFalse(isset($_SESSION['DISGUISESESSION']));
        $this->assertFalse(isset($_SESSION['USERINDISGUISE']));
        $this->assertFalse(isset($_SESSION['DISGUISECONTEXT']));

        // User.
        $this->assertEquals($user->id, $USER->id);

    }

}

<?php

namespace manager;

use auth_disguise\manager\disguise_context;
use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the auth_disguise class.
 *
 * @package auth_disguise
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class disguise_context_test extends \advanced_testcase {

    /**
     * @covers disguise_context::get_course_context
     */
    public function test_get_course_context() {
        $this->resetAfterTest();

        // Course context.
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);

        // Create a forum.
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $modcontext = context_module::instance($forum->cmid);

        // Get the course context from module context.
        $context = disguise_context::get_course_context($modcontext->id);
        $this->assertEquals($coursecontext->id, $context->id);

        // Get the course context from course context.
        $context = disguise_context::get_course_context($coursecontext->id);
        $this->assertEquals($coursecontext->id, $context->id);
    }

    /**
     * Test insert or update disguise mode.
     *
     * @covers disguise_context::get_course_context
     * @covers disguise_context::insert_disguise_context_mode
     * @covers disguise_context::update_disguise_context_mode
     */
    public function test_set_disguise_context_mode() {
        $this->resetAfterTest();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a forum.
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $modcontext = context_module::instance($forum->cmid);

        // Get the course context from module context.
        $context = disguise_context::get_course_context($modcontext->id);

        // Check if disguise mode exists.
        $this->assertFalse(disguise_context::disguise_context_mode_exists($context->id));

        // Insert disguise mode.
        disguise_context::insert_disguise_context_mode($context->id, AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE);

        // Check if disguise mode exists.
        $this->assertEquals(AUTH_DISGUISE_MODE_COURSE_MODULE_PEER_SAFE,
            disguise_context::disguise_context_mode_exists($context->id));

        // Update disguise mode.
        disguise_context::update_disguise_context_mode($context->id, AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE);

        // Check if disguise mode exists.
        $this->assertEquals(AUTH_DISGUISE_MODE_COURSE_MODULE_INSTRUCTOR_SAFE,
            disguise_context::disguise_context_mode_exists($context->id));
    }

}

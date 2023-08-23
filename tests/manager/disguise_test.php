<?php

namespace tests\manager;

use auth_disguise\manager\disguise;
use context_course;
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

}
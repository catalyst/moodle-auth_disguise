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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_disguise\manager\disguise_keyword;
use auth_disguise\output\keyword_collection;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Setup page
defined('MOODLE_INTERNAL') || die();
admin_externalpage_setup('auth_disguise_keyword');

require_admin();

$sortby     = optional_param('sort', 'name', PARAM_ALPHA);
$sorthow    = optional_param('dir', 'ASC', PARAM_ALPHA);
$page       = optional_param('page', 0, PARAM_INT);

if (!in_array($sortby, array('name', 'status'))) {
    $sortby = 'name';
}

if ($sorthow != 'ASC' and $sorthow != 'DESC') {
    $sorthow = 'ASC';
}

if ($page < 0) {
    $page = 0;
}

// Build the page output
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('keyword_page', 'auth_disguise'));

// Create new keyword button.
echo $OUTPUT->single_button(
    new moodle_url('/auth/disguise/newkeyword.php'),
    get_string('new_keyword', 'auth_disguise'),
    'get'
);

// Get the list of keywords.
$output = $PAGE->get_renderer('auth_disguise');
$records = disguise_keyword::get_keyword_records();
$keywords = new keyword_collection($records);
$totalcount = count($records);

$keywords->sort       = $sortby;
$keywords->dir        = $sorthow;
$keywords->page       = $page;
$keywords->perpage    = BADGE_PERPAGE;
$keywords->totalcount = $totalcount;

echo $output->render($keywords);

echo $OUTPUT->footer();

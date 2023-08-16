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

use auth_disguise\manager\disguise_keyword;

require_once(__DIR__ . '/../../config.php');

require_login();

// Require the keyword parameter.
$keyword = required_param('keyword', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/auth/disguise/new_item.php');
$PAGE->set_heading(get_string('new_item', 'auth_disguise'));
$PAGE->set_title(get_string('new_item', 'auth_disguise'));

$form = new auth_disguise\form\item();
$form->set_data(['keyword' => $keyword]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/auth/disguise/item.php', ['keyword' => $keyword]));
} else if ($data = $form->get_data()) {
    $keyword = $data->keyword;
    $name = $data->name;
    disguise_keyword::add_item_to_keyword($keyword, $name);

    redirect(new moodle_url('/auth/disguise/item.php', ['keyword' => $keyword]));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();

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

namespace auth_disguise\form;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/formslib.php');

use moodleform;

class keyword extends moodleform {

        public function definition() {
            $mform = $this->_form;

            $mform->addElement('header', 'general', get_string('keyword', 'auth_disguise'));

            // Keyword
            $mform->addElement('text', 'keyword', get_string('keyword', 'auth_disguise'));
            $mform->setType('keyword', PARAM_TEXT);
            $mform->addRule('keyword', null, 'required', null, 'client');

            // Items
            $mform->addElement('textarea', 'items', get_string('items', 'auth_disguise'), array('cols' => 50, 'rows' => 7));
            $mform->setType('items', PARAM_TEXT);
            $mform->addRule('items', null, 'required', null, 'client');
            $mform->addHelpButton('items', 'items', 'auth_disguise');

            // Submit
            $this->add_action_buttons(true, get_string('save'));
        }

        public function validation($data, $files) {
            $errors = parent::validation($data, $files);

            $items = explode(',', $data['items']);
            if (count($items) < 2) {
                $errors['items'] = get_string('itemserror', 'auth_disguise');
            }

            return $errors;
        }
}
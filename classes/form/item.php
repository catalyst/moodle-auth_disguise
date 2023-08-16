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

class item extends moodleform {

        public function definition() {
            $mform = $this->_form;

            $mform->addElement('header', 'general', get_string('items', 'auth_disguise'));

            // Hidden keyword
            $mform->addElement('hidden', 'keyword');
            $mform->setType('keyword', PARAM_TEXT);

            // item
            $mform->addElement('text', 'name', get_string('item', 'auth_disguise'));
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', null, 'required', null, 'client');

            // Submit
            $this->add_action_buttons(true, get_string('save'));
        }

}
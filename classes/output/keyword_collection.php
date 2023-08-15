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

namespace auth_disguise\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/auth/disguise/lib.php');

use renderable;

/**
 * Collection of keywords.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class keyword_collection implements renderable {

    /** @var string how are the data sorted */
    public $sort = 'name';

    /** @var string how are the data sorted */
    public $dir = 'ASC';

    /** @var int page number to display */
    public $page = 0;

    /** @var int number of keywords to display per page */
    public $perpage = AUTH_DISGUISE_KEYWORDS_PER_PAGE;

    /** @var int the total number of keywords to display */
    public $totalcount = null;

    /** @var array list of keywords */
    public $keywords = array();

    /**
     * Initializes the list of keywords to display
     *
     * @param array $keywords keywords to render
     */
    public function __construct($keywords) {
        $this->keywords = $keywords;
    }
}

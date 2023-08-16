<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

use auth_disguise\manager\disguise_keyword;
use auth_disguise\output\item_collection;
use auth_disguise\output\keyword_collection;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Renderer for the list of keywords.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_disguise_renderer extends plugin_renderer_base {
    protected function render_keyword_collection(keyword_collection $collection) {
        $paging = new paging_bar($collection->totalcount, $collection->page, $collection->perpage, $this->page->url, 'page');
        $htmlpagingbar = $this->render($paging);

        $table = new html_table();

        $table->head = array(
            get_string('keywords', 'auth_disguise'),
            get_string('count', 'auth_disguise'),
            get_string('action', 'auth_disguise')
        );

        foreach ($collection->keywords as $keyword) {
            $name = $keyword->keyword;

            $count = disguise_keyword::count_keyword_item($name);
            // Embed link to item page with count number
            $count = html_writer::link(
                new moodle_url('/auth/disguise/item.php', array('keyword' => $name)),
                $count
            );

            $action = "";
            $row = [$name, $count, $action];

            $table->data[] = $row;
        }

        $htmltable = html_writer::table($table);

        return $htmlpagingbar . $htmltable . $htmlpagingbar;
    }

    protected function render_item_collection(item_collection $collection) {
        $paging = new paging_bar($collection->totalcount, $collection->page, $collection->perpage, $this->page->url, 'page');
        $htmlpagingbar = $this->render($paging);

        // Get items from the keyword
        $items = $collection->items;

        $table = new html_table();

        $table->head = array(
            get_string('items', 'auth_disguise'),
            get_string('action', 'auth_disguise')
        );

        foreach ($items as $item) {
            $name = $item->name;
            $action = "";
            $row = [$name, $action];

            $table->data[] = $row;
        }

        $htmltable = html_writer::table($table);

        return $htmlpagingbar . $htmltable . $htmlpagingbar;
    }
}
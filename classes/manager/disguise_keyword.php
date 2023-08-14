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

namespace auth_disguise\manager;

use core\check\performance\debugging;

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\disguise_keyword
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disguise_keyword {

    public static function create_keyword($keyword, $items) {
        global $DB;

        $record = new \stdClass();
        $record->keyword = $keyword;

        // New keyword.
        $id = $DB->insert_record('auth_disguise_naming_keyword', $record);

        // Save each item.
        foreach ($items as $item) {
            $record = new \stdClass();
            $record->keywordid = $id;
            $record->name = $item;
            $DB->insert_record('auth_disguise_naming_item', $record);
        }
    }

    public static function get_keyword($keyword) {
        global $DB;

        $record = $DB->get_record('auth_disguise_naming_keyword', array('keyword' => $keyword));
        return $record;
    }

    // Generate items for color keyword
    public static function create_color_keyword() {
        $keyword = "color";
        $items = [
            "red", "blue", "green", "yellow", "orange", "purple", "pink", "brown", "black", "white", "grey", "gray"
        ];
        self::create_keyword($keyword, $items);
    }

    // Generate items for animal keyword
    public static function create_animal_keyword() {
        $keyword = "animal";
        // List of jungle animals.
        $items = [
            "tiger", "lion", "leopard", "jaguar", "cheetah", "cougar",
            "panther", "lynx", "bobcat", "ocelot", "caracal",
            "serval", "puma", "snow leopard", "clouded leopard"
        ];
        self::create_keyword($keyword, $items);
    }

    // Generate items for fruit keyword
    public static function create_fruit_keyword() {
        $keyword = "fruit";
        $items = [
            "apple", "banana", "orange", "grape", "strawberry", "blueberry",
            "raspberry", "blackberry", "mango", "pineapple", "watermelon",
            "kiwi", "papaya", "pear", "peach", "plum", "cherry", "coconut",
            "lime", "lemon", "grapefruit", "apricot", "avocado", "fig",
            "guava", "lychee", "nectarine", "olive", "pomegranate", "tangerine"
        ];
        self::create_keyword($keyword, $items);
    }

    // Generate items for country keyword
    public static function create_country_keyword() {
        $keyword = "country";
        $items = ['Australia', 'Canada', 'China', 'France', 'Germany', 'India', 'Indonesia', 'Italy', 'Japan', 'Mexico',
            'Russia', 'South Korea', 'Spain', 'Turkey', 'United Kingdom', 'United States'
        ];
        self::create_keyword($keyword, $items);
    }

    // Build name from keywords
    public static function build_name($keywords) {
        global $DB;

        $name = "";
        foreach ($keywords as $keyword) {
            // Retrieve keyword record.
            $record = $DB->get_record('auth_disguise_naming_keyword', array('keyword' => $keyword));
            if (!$record) {
                continue;
            }

            // Retrieve items for keyword.
            $items = $DB->get_records('auth_disguise_naming_item', array('keywordid' => $record->id));
            if (empty($items)) {
                continue;
            }
            $name .= $items[array_rand($items)]->name . " ";
        }
        return $name;
    }

}

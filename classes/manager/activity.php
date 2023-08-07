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

defined('MOODLE_INTERNAL') || die();

/**
 * Class auth_disguise\manager\activity
 *
 * @author  Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity {

    /**
     * Clone disguised user's grade to real user.
     * Do we transfer activity attempt such as forum post, quiz attempt, assignment submission ...
     * from disguise to real user at the end of the 'day'?
     * Or transfer grade only?, which may be messed up with grade recalculation as user does not real submission.
     * submission evidence for auditing purposes?
     *
     */
    public static function clone_activities_to_real_user($courseid, $realuserid, $disguiseduserid)
    {
        // Check if the link between real user and disguised user is valid.

        // Get all activities of disguised user.

        // Clone all activities of disguised user to real user.
        // Condition to clone activity:
    }

    /**
     * Remove activities from disguised user in a course.
     *
     */
    public static function remove_activities_from_disguised_user($courseid, $disguiseduserid) {
        // Remove an activity of disguised user.
    }

}

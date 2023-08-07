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

/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package    auth_disguise
 * @copyright  2023 Catalyst IT {@link https://catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

redirect_if_major_upgrade_required();

require_login();

$return = optional_param('returnurl', 0, PARAM_LOCALURL);

// Set up PAGE.
// TODO: Improve page layout, style, navigation crumbs, and appearance.
$context = context_system::instance();
$OUTPUT = $PAGE->get_renderer('admin');
$PAGE->set_context($context);
$PAGE->set_title(get_string('title', 'auth_disguise'));
$PAGE->set_heading(get_string('title', 'auth_disguise'));
$PAGE->set_url('/auth/disguise/prompt.php');
unset($url);
echo $OUTPUT->header();

// TODO: Replace proof-of-concept stub page below with real content, lang
// strings, functionality.
?>

<html>
    <head>
        <h3>Prompt</h3>
        <p>You are requesting access to, or leaving, a page where user disguises are enabled.</p>
        <p>You have the following options:
        <ul>
            <li><b>Continue to the page with your current identity</b></li>
            <ul>
            <li><a href="<?php echo $return; ?>">Continue</a></li>
            </ul>
            <li><b>Switch identity</b></li>
            <ul>
            <li><a href="<?php echo $return; ?>">Switch Identity</a></li>
            </ul>
        </ul>
        </p>
    </head>
</html>
<?php
echo $OUTPUT->footer();
?>

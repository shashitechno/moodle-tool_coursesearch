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
 *  Course search
 *
 * @package    tool_coursesearch
 * @copyright  2013 Shashikant Vaishanv
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once('coursesearch_setting_form.php');
require_login();
admin_externalpage_setup('toolcoursesearch');
// Create the form.
$form     = new coursesearch_settings_form();
$formdata = new stdClass();
// If we have valid input.
if ($data = $form->get_data()) {
    $solroptions = array();
    if ($data->solrhost) {
        $formdata->solrhost = $data->solrhost;
        set_config('solrhost', $data->solrhost, 'tool_coursesearch');
    }
    if ($data->solrport) {
        $formdata->solrport = $data->solrport;
        set_config('solrport', $data->solrport, 'tool_coursesearch');
    }
    if ($data->solrpath) {
        $formdata->solrpath = $data->solrpath;
        set_config('solrpath', $data->solrpath, 'tool_coursesearch');
    }
}
$form->set_data($formdata);
// Otherwise display the settings form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('solrconfig', 'tool_coursesearch'));
$info = format_text(get_string('coursesearchintro', 'tool_coursesearch'), FORMAT_MARKDOWN);
echo $OUTPUT->box($info);
$renderer = $PAGE->get_renderer('tool_coursesearch');
echo $renderer->moodleform(new coursesearch_settings_form());
echo $OUTPUT->footer();

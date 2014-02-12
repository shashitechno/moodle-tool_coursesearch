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
$PAGE->requires->js_init_call('M.tool_coursesearch.init');
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
    if ($data->solrusername) {
        $formdata->solrusername = $data->solrusername;
        set_config('solrusername', $data->solrusername, 'tool_coursesearch');
    } else {
        set_config('solrusername', "", 'tool_coursesearch');
    }
    if ($data->solrpassword) {
        $formdata->solrpassword = $data->solrpassword;
        set_config('solrpassword', $data->solrpassword, 'tool_coursesearch');
    } else {
        set_config('solrpassword', "", 'tool_coursesearch');
    }
    if (isset($data->enablespellcheck)) {
        $formdata->enablespellcheck = $data->enablespellcheck;
        set_config('enablespellcheck', $data->enablespellcheck, 'tool_coursesearch');
    }
    if (isset($data->overviewindexing)) {
        $formdata->overviewindexing = $data->overviewindexing;
        set_config('overviewindexing', $data->overviewindexing, 'tool_coursesearch');
    }
    if (isset($data->summaryindexing)) {
        $formdata->summaryindexing = $data->summaryindexing;
        set_config('summaryindexing', $data->summaryindexing, 'tool_coursesearch');
    }
    if (isset($data->solrerrormessage)) {
        $formdata->solrerrormessage = $data->solrerrormessage;
        set_config('solrerrormessage', $data->solrerrormessage, 'tool_coursesearch');
    }
}
$form->set_data($formdata);
// Otherwise display the settings form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('solrconfig', 'tool_coursesearch'));
$info = format_text(get_string('coursesearchintro', 'tool_coursesearch'), FORMAT_MARKDOWN);
echo $OUTPUT->box($info);
if (data_submitted()) {
    echo $OUTPUT->notification(get_string('changessaved', 'tool_coursesearch'), 'notifysuccess');
}
echo $form->display();
echo $OUTPUT->footer();
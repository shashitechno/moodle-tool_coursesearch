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
 * 
 *
 * @package    tool
 * @subpackage coursesearch
 * @copyright  2013 Shashikant Vaishnav  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('coursesearch_setting_form.php');

require_login();
admin_externalpage_setup('toolcoursesearch');

// Create the form.
$form = new coursesearch_settings_form();

// If we have valid input.
if ($data = $form->get_data()) {
	$solroptions = array();
    if ($data->solrhost) {
        $solroptions['solrhost'] = $data->solrhost;
    }
    if ($data->solrport) {
        $solroptions['solrport'] = $data->solrport;
    }
               //  apropriate action	
  }

// Otherwise display the settings form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('solrconfig', 'tool_coursesearch'));

$info = format_text(get_string('coursesearchintro', 'tool_coursesearch'), FORMAT_MARKDOWN);
echo $OUTPUT->box($info);

$form->display();

echo $OUTPUT->footer();

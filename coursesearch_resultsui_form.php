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
 * @package    tool_coursesearch
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
/**
 * Definition course search results display form.
 *
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursesearch_resultsui_form extends moodleform
{
    /**
     * Define setting form.
     */
    protected function definition() {
        global $CFG, $PAGE;
        $mform    = $this->_form;
        $instance = $this->_customdata;
        $mform->addElement('text', 'search', get_string('searchcourses', 'tool_coursesearch'));
        $mform->addHelpButton('search', 'advancecoursesearch', 'tool_coursesearch');
        $mform->addRule('search', get_string('emptyqueryfield', 'tool_coursesearch'), 'required', null, 'client');
        $mform->setType('search', PARAM_TEXT);
        $mform->addElement('advcheckbox', 'filtercheckbox', '', 'Disable all filters');
        $mform->disabledIf('searchfromtime', 'filtercheckbox', 'checked');
        $mform->disabledIf('searchtilltime', 'filtercheckbox', 'checked');
        $mform->disabledIf('sortmenu', 'filtercheckbox', 'checked');
        $mform->addHelpButton('filtercheckbox', 'filtercheckbox', 'tool_coursesearch');
        $mform->addElement('header', 'filterresults', get_string('filterresults', 'tool_coursesearch'));
        $mform->setExpanded('filterresults', false);
        $mform->addElement('date_time_selector', 'searchfromtime', get_string('searchfromtime', 'tool_coursesearch'), array(
            'optional' => true
        ));
        $mform->setDefault('searchfromtime', 0);
        $mform->addHelpButton('searchfromtime', 'searchfromtime', 'tool_coursesearch');
        $mform->addElement('date_time_selector', 'searchtilltime', get_string('searchtilltime', 'tool_coursesearch'), array(
            'optional' => true
        ));
        $mform->setDefault('searchtilltime', 0);
        $mform->addHelpButton('searchtilltime', 'searchtilltime', 'tool_coursesearch');
        $mform->addElement('header', 'sortresults', get_string('sortheading', 'tool_coursesearch'));
        $mform->setExpanded('sortresults', false);
        $mform->addElement('select', 'sortmenu', get_string('sortby', 'tool_coursesearch'), array(
            'score' => 'By relevance',
            'shortname' => 'By shortname',
            'startdate' => 'Newest'
        ));
        $mform->addHelpButton('sortmenu', 'sortmenu', 'tool_coursesearch');
        $this->add_action_buttons(false, get_string('go', 'tool_coursesearch'));
    }
}
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
 * Transfer form
 *
 * @package    tool_coursesearch
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
/**
 * Definition course search settings form.
 *
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursesearch_settings_form extends moodleform
{
    /**
     * Define setting form.
     */
    protected function definition() {
        global $CFG;
        $mform    = $this->_form;
        $instance = $this->_customdata;
        $mform->addElement('header', 'course', get_string('solrheading', 'tool_coursesearch'));
        $mform->addElement('text', 'solrhost', get_string('solrhost', 'tool_coursesearch'));
        $mform->setType('solrhost', PARAM_HOST);
        $mform->setDefault('solrhost', 'localhost');
        $mform->addElement('text', 'solrport', get_string('solrport', 'tool_coursesearch'));
        $mform->setType('solrport', PARAM_INT);
        $mform->setDefault('solrport', 8983);
        $mform->addElement('text', 'solrpath', get_string('solrpath', 'tool_coursesearch'));
        $mform->setType('solrpath', PARAM_ALPHANUMEXT);
        $mform->setDefault('solrpath', "/solr");
        $mform->addRule('solrhost', get_string('required'), 'required', null);
        $mform->addRule('solrport', get_string('required'), 'required', null);
        $mform->addRule('solrpath', get_string('required'), 'required', null);
        $mform->addElement('button', 'solr-btn-ping', get_string("pingstatus", 'tool_coursesearch'));
        $mform->addElement('header', 'coursesearch', get_string('actions', 'tool_coursesearch'));
        $mform->addElement('button', 'solr-btn-loadcontent', get_string('loadcontent', 'tool_coursesearch'));
        $mform->addElement('button', 'solr-btn-optimize', get_string('optimize', 'tool_coursesearch'));
        $mform->addElement('button', 'solr-btn-deleteall', get_string('delete', 'tool_coursesearch'));
        $mform->addElement('header', 'coursesearch', get_string('options', 'tool_coursesearch'));
        $mform->addElement('advcheckbox', 'enablespellcheck', get_string('enablespellcheck', 'tool_coursesearch'));
        $mform->addElement('advcheckbox', 'overviewindexing', get_string('overviewindexing', 'tool_coursesearch'));
        $mform->addElement('advcheckbox', 'summaryindexing', get_string('summaryindexing', 'tool_coursesearch'));
        $mform->setType('enablespellcheck', PARAM_BOOL);
        $mform->setType('overviewindexing', PARAM_BOOL);
        $mform->setType('summaryindexing', PARAM_BOOL);
        $mform->setType('enablespellcheck', PARAM_BOOL);
        $this->add_action_buttons(false, get_string('savesettings', 'tool_coursesearch'));
    }
}

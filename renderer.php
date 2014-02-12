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
 * Defines the renderer for the course search plugin.
 *
 * @package    tool
 * @subpackage course search
 * @copyright  Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . "/course/renderer.php");
class tool_coursesearch_renderer extends core_course_renderer
{
    /**   @override
     * Renders html to display a course search form
     *
     * @param string $value default value to populate the search field
     * @param string $format display format - 'plain' (default), 'short' or 'navbar'
     * @return string
     */
    public function course_search_form($value = '', $format = 'plain') {
        global $CFG, $PAGE;
        if ($this->validateplugindepedency() && $format == 'plain') {
            require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/coursesearch_resultsui_form.php");
            require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php");
            $ob = new tool_coursesearch_locallib();
            if ($ob->tool_coursesearch_pingsolr()) {
                $this->page->requires->js_init_call('M.tool_coursesearch.auto', $ob->tool_coursesearch_autosuggestparams());
                $this->page->requires->js_init_call('M.tool_coursesearch.sort');
                $mform = new coursesearch_resultsui_form(
                    new moodle_url("/$CFG->admin/tool/coursesearch/example.php"), null, 'post', null, array(
                    "id" => "searchformui"
                ));
                $mform->display();
            } else {
                return parent::course_search_form($value, $format);
            }
        } else {
            return parent::course_search_form($value, $format);
        }
    }
    /**
     * Renders html to display search result page
     *
     * @param array $searchcriteria may contain elements: search, blocklist, modulelist, tagid
     * @return string
     */
    public function search_courses($searchcriteria) {
        global $CFG, $PAGE;
        $content = '';
        if (!empty($searchcriteria)) {
            require_once($CFG->libdir . '/coursecatlib.php');
            $displayoptions = array(
                'sort' => array(
                    'displayname' => 1
                )
            );
            $perpage        = optional_param('perpage', 0, PARAM_RAW);
            if ($perpage !== 'all') {
                $displayoptions['limit']  = ((int) $perpage <= 0) ? $CFG->coursesperpage : (int) $perpage;
                $page                     = optional_param('page', 0, PARAM_INT);
                $displayoptions['offset'] = $displayoptions['limit'] * $page;
            }
            $displayoptions['paginationurl']      = new moodle_url('/course/search.php', $searchcriteria);
            $displayoptions['paginationallowall'] = true;
            $courses                              = array();
            $class                                = 'course-search-result';
            $chelper                              = new coursecat_helper();
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT)->set_courses_display_options(
                $displayoptions)->set_search_criteria($searchcriteria)->set_attributes(array(
                'class' => $class
            ));
            if ($this->validateplugindepedency()) {
                require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php");
                $ob = new tool_coursesearch_locallib();
                if ($ob->tool_coursesearch_pingsolr()) {
                    $results    = $ob->tool_coursesearch_search($displayoptions);
                    $qtime      = $results->responseHeader->QTime;
                    $response   = $results->grouped->courseid;
                    $resultinfo = array();
                    foreach ($response->groups as $doclists => $doclist) {
                        foreach ($doclist->doclist->docs as $doc) {
                            $doc->id = $doc->courseid;
                            foreach ($doc as $key => $value) {
                                $resultinfo[$key] = $value;
                            }
                            $obj[$doc->courseid] = json_decode(json_encode($resultinfo), false);
                            if (($obj[$doc->courseid]->visibility) == '0') {
                                context_helper::preload_from_record($obj[$doc->courseid]);
                                if (!has_capability('moodle/course:viewhiddencourses', context_course::instance($doc->courseid))) {
                                    unset($obj[$doc->courseid]);
                                }
                            }
                            if (isset($obj[$doc->courseid])) {
                                $courses[$doc->courseid] = new course_in_list($obj[$doc->courseid]);
                            }
                        }
                    }
                    $totalcount = $ob->tool_coursesearch_coursecount($response);
                } else {
                    if (!get_config('tool_coursesearch', 'solrerrormessage')) {
                        global $OUTPUT;
                        $content .= $OUTPUT->notification(get_string('solrpingerror', 'tool_coursesearch'), 'notifyproblem');
                    }
                    $courses    = coursecat::search_courses($searchcriteria, $chelper->get_courses_display_options());
                    $totalcount = coursecat::search_courses_count($searchcriteria);
                }
            } else {
                $courses    = coursecat::search_courses($searchcriteria, $chelper->get_courses_display_options());
                $totalcount = coursecat::search_courses_count($searchcriteria);
            }
            foreach ($searchcriteria as $key => $value) {
                if (!empty($value)) {
                    $class .= ' course-search-result-' . $key;
                }
            }
            $courseslist = $this->coursecat_courses($chelper, $courses, $totalcount);
            if (!$totalcount) {
                if (!empty($searchcriteria['search'])) {
                    $content .= $this->heading(get_string('nocoursesfound', '', $searchcriteria['search']));
                } else {
                    $content .= $this->heading(get_string('novalidcourses'));
                }
            } else {
                $content .= $this->heading(get_string('searchresults') . ": $totalcount");
                if (isset($qtime)) {
                    $content .= html_writer::tag('em', "Query Time " . $qtime / (1000) . " Seconds");
                }
                $content .= $courseslist;
            }
            if (!empty($searchcriteria['search'])) {
                $content .= $this->box_start('generalbox mdl-align');
                $content .= $this->course_search_form($searchcriteria['search']);
                $content .= $this->box_end();
            }
        } else {
            $content .= $this->course_search_form();
            if (!($content === '')) {
                $content .= $this->box_start('generalbox mdl-align');
                $content .= html_writer::tag('div', get_string("searchhelp"), array(
                    'class' => 'searchhelp'
                ));
                $content .= $this->box_end();
            }
        }
        if (isset($results->spellcheck->suggestions->collation)) {
            $didyoumean = $results->spellcheck->suggestions->collation->collationQuery;
        } else {
            $didyoumean = false;
        }
        if ($didyoumean != false) {
            echo html_writer::tag('h3', get_string('didyoumean', 'tool_coursesearch') . html_writer::link(
                new moodle_url('example.php?search=' . rawurlencode($didyoumean)), $didyoumean) . '?');
        }
        return $content;
    }
    public function validateplugindepedency() {
        global $CFG;
        $libfile = "$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php";
        if (file_exists($libfile) && array_key_exists('coursesearch', get_plugin_list('tool'))) {
            return true;
        }
        return false;
    }
}
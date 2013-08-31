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
 * methods that talks between Solr's instance and SOlrPhpClient Library
 *
 * @package    coursesearch
 * @copyright  2013 Shashikant Vaishnav  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../config.php');
require_once("SolrPhpClient/Apache/Solr/Service.php");
require_once("solrlib.php");
require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/SolrPhpClient/Apache/Solr/HttpTransport/Curl.php");
class tool_coursesearch_locallib
{
    public function tool_coursesearch_ping($options) {
        $arr  = array();
        $solr = new tool_coursesearch_solrlib();
        if ($solr->connect($options, true)) {
            $arr['status'] = 'ok';
        } else {
            $arr['status'] = 'error';
        }
        print(json_encode($arr));
        exit();
    }
    public function tool_coursesearch_index($prev) {
        $this->tool_coursesearch_load_all($this->tool_coursesearch_solr_params(), $prev);
        exit();
    }
    public function tool_coursesearch_deleteall() {
        $options = $this->tool_coursesearch_solr_params();
        $arr     = array();
        $solr    = new tool_coursesearch_solrlib();
        if ($solr->connect($options, true)) {
            if ($solr->deleteall()) {
                $arr['status'] = 'ok';
            } else {
                $arr['status'] = 'error';
            }
        } else {
            $arr['status']  = 'error';
            $arr['code']    = $solr->get_errorcode();
            $arr['message'] = $solr->get_errormessage();
        }
        print(json_encode($arr));
        exit();
    }
    public function tool_coursesearch_optimize() {
        $options = $this->tool_coursesearch_solr_params();
        $arr     = array();
        $solr    = new tool_coursesearch_solrlib();
        if ($solr->connect($options, true)) {
            if ($solr->optimize()) {
                $arr['status'] = 'ok';
            } else {
                $arr['status'] = 'error';
            }
        } else {
            $arr['status']  = 'error';
            $arr['code']    = $solr->get_errorcode();
            $arr['message'] = $solr->get_errormessage();
        }
        print(json_encode($arr));
        exit();
    }
    /**
     * Return the array of solr configuration
     * @return array of solr configuration values 
     */
    public function tool_coursesearch_solr_params() {
        $options              = array();
        $options['solr_host'] = get_config('tool_coursesearch', 'solrhost');
        $options['solr_port'] = get_config('tool_coursesearch', 'solrport');
        $options['solr_path'] = get_config('tool_coursesearch', 'solrpath');
        return $options;
    }
    /**
     * Return indexing statics in json
     *
     * @param array $options configuration of solr
     * @param string $prev previous id where solr need to start the index for very start its 1
     * @return string 
     */
    public function tool_coursesearch_load_all($options, $prev) {
        global $DB, $CFG;
        $documents   = array();
        $cnt         = 0;
        $batchsize   = 10;
        $last        = "";
        $found       = false;
        $end         = false;
        $percent     = 0;
        $sql         = 'SELECT id FROM mdl_course ORDER BY id';
        $courses     = $DB->get_records_sql($sql);
        $courses     = array_values($courses);
        $coursecount = count($courses);
        for ($idx = 1; $idx < $coursecount; $idx++) {
               set_time_limit(0);
            $courseid = $courses[$idx]->id;
            $last     = $courseid;
            $percent  = (floatval($idx) / floatval($coursecount - 1)) * 100;
            if ($prev && !$found) {
                if ($courseid === $prev) {
                    $found = true;
                }
                continue;
            }
            if ($idx === $coursecount - 2) {
                $end = true;
            }
            $documents[] = $this->tool_coursesearch_build_document($options, $this->tool_coursesearch_get_courses($courseid));
            $cnt++;
            if ($cnt == $batchsize) {
                $this->tool_coursesearch_solr_course($options, $documents, false, false);
                $cnt       = 0;
                $documents = array();
                break;
            }
        }
        if ($documents) {
            $this->tool_coursesearch_solr_course($options, $documents, false, false);
        }
        if ($end) {
            $this->tool_coursesearch_solr_course($options, false, false, false);
            printf("{\"last\": \"%s\", \"end\": true, \"percent\": \"%.2f\"}", $last, $percent);
        } else {
            printf("{\"last\": \"%s\", \"end\": false, \"percent\": \"%.2f\"}", $last, $percent);
        }
    }
    /**
     * Return array of object containing the info about the particular course
     *
     * @param string courseid the course which needs to be indexed
     * @return Object  
     */
    public function tool_coursesearch_get_courses($courseid) {
        global $DB, $CFG;
        $courses = $DB->get_record('course', array(
            'id' => $courseid
        ), 'id,idnumber,fullname,shortname,summary,startdate,visible');
        return $courses;
    }
    /**
     * Return object of solr content to be indexed
     *
     * @param array $options configuration of solr
     * @param object $course_info having the other attributes about the particular course
     * @return object 
     */
    /* One course may have multiple attachments so we need use a random unique id
    unique id that is based on current macro time. */
    public function tool_coursesearch_build_document($options, $courseinfo) {
        global $DB, $CFG;
        $doc = new Apache_Solr_Document();
        $doc->setField('id', uniqid($courseinfo->id));
        $doc->setField('idnumber', $courseinfo->idnumber);
        $doc->setField('courseid', $courseinfo->id);
        $doc->setField('fullname', $courseinfo->fullname);
        $doc->setField('summary', $courseinfo->summary);
        $doc->setField('shortname', $courseinfo->shortname);
        $doc->setField('startdate', $this->tool_coursesearch_format_date($courseinfo->startdate));
        $doc->setField('visibility', $courseinfo->visible);
        $files = $this->tool_coursesearch_overviewurl($courseinfo->id);
        if (get_config('tool_coursesearch', 'overviewindexing')) {
            $solr = new tool_coursesearch_solrlib();
            if ($solr->connect($options, true)) {
                foreach ($files as $file) {
                    $url      = "{$CFG->wwwroot}/pluginfile.php/{$file->get_contextid()}/course/overviewfiles";
                    $filename = rawurlencode($file->get_filename());
                    $fileurl  = $url . $file->get_filepath() . $filename;
                    $solr->extract($fileurl, array(
                        'literal.id' => uniqid($courseinfo->id),
                        'literal.filename' => $filename,
                        'literal.courseid' => $courseinfo->id,
                        'literal.fullname' => $courseinfo->fullname,
                        'literal.summary' => $courseinfo->summary,
                        'literal.shortname' => $courseinfo->shortname,
                        'literal.stardate' => $this->tool_coursesearch_format_date($courseinfo->startdate),
                        'literal.visibility' => $courseinfo->visible
                    ));
                }
            }
        }
        return $doc;
    }
    /**
     * Return the date in proper format
     *
     * @param string to be formatted
     * @return string 
     */
    public function tool_coursesearch_format_date($thedate) {
        return gmdate("Y-m-d\TH:i:s\Z", $thedate);
    }
    /**
     * Return void
     *
     * @param array $options configuration of solr
     * @param object $documents documents attribtes to be served to solr
     * @param boolean $commit whether to commit or not?
     * @param boolean $optimize whether to optimize or not?
     * @return string 
     */
    public function tool_coursesearch_solr_course($options, $documents, $commit = true, $optimize = false) {
        try {
            $solr = new tool_coursesearch_solrlib();
            if ($solr->connect($options, true)) {
                if ($documents) {
                    $solr->adddocuments($documents);
                }
                if ($commit) {
                    $solr->commit();
                }
                if ($optimize) {
                    $solr->optimize();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /**
     * Return files array of all the overview files
     *
     * @param int courseid 
     * @return array 
     */
    public function tool_coursesearch_overviewurl($courseid) {
        $context = context_course::instance($courseid);
        $fs      = get_file_storage();
        $files   = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        return $files;
    }
    /**
     * Return filename @string of summary file.
     *
     * @param int courseid 
     * @return string filename 
     */
    public function tool_coursesearch_summaryfilename($courseid) {
        $context  = context_course::instance((int) $courseid);
        $fs       = get_file_storage();
        $files    = $fs->get_area_files($context->id, 'course', 'summary', false, 'filename', false);
        $filename = '';
        foreach ($files as $file) {
            $filename = $file->get_filename();
        }
        return $filename; // TODO Its doesn't looks relevent to add irrelevent file names. is it really ?.
    }
    public function tool_coursesearch_query($qry, $offset, $count, $fq, $sortby, $options) {
        global $CFG;
        $response = null;
        $options  = $this->tool_coursesearch_solr_params();
        $solr     = new tool_coursesearch_solrlib();
        if ($solr->connect($options, true)) {
            $params            = array();
            $params['defType'] = 'edismax';
            $params['qf']      = 'idnumber^10 fullname^8 shortname^5 summary^3.5 startdate^1.5 content filename';
            if (empty($qry) || $qry == '*' || $qry == '*:*') {
                $params['q.alt'] = "*:*";
                $qry             = '';
            }
            $params['fq']                         = $fq;
            $params['fl']                         = '*,score';
            $params['hl']                         = 'on';
            $params['hl.fl']                      = 'fullname';
            $params['hl.snippets']                = '3';
            $params['hl.fragsize']                = '50';
            $params['sort']                       = $sortby;
            $params['spellcheck.onlyMorePopular'] = 'false';
            $params['spellcheck.extendedResults'] = 'false';
            $params['spellcheck.collate']         = 'true';
            $params['spellcheck.count']           = '1';
            $params['spellcheck']                 = 'true';
            $params['group']                      = 'true';
            $params['group.field']                = 'courseid';
            $params['group.ngroups']              = 'true';
            $response                             = $solr->search($qry, $offset, $count, $params);
            if (!$response->getHttpStatus() == 200) {
                $response = null;
            }
        }
        return $response;
    }
    /**
     * takes the params offset and count from plugin & reponse to render
     *
     * @param array $array offset & count 
     * @return Apache_solr_response object
     */
    public function tool_coursesearch_search($array) {
        $config = $this->tool_coursesearch_solr_params();
        $qry    = stripslashes(optional_param('search', '', PARAM_TEXT));
        $offset = isset($array['offset']) ? $array['offset'] : 0;
        $count  = isset($array['limit']) ? $array['limit'] : 20; // TODO input from user how many results perpage.
        $fq     = (isset($_GET['fq'])) ? $_GET['fq'] : '';
        $sort   = (isset($_GET['sort'])) ? $_GET['sort'] : '';
        $order  = (isset($_GET['order'])) ? $_GET['order'] : '';
        $isdym  = (isset($_GET['isdym'])) ? $_GET['isdym'] : 0;
        $fqitms = $fq;
        $out    = array();
        if (!$qry) {
            $qry = '';
        }
        if ($sort && $order) {
            $sortby = $sort . ' ' . $order;
        } else {
            $sortby = '';
            $order  = '';
        }
        if ($qry) {
            $results = self::tool_coursesearch_query($qry, $offset, $count, $fqitms, $sortby, $config);
            if ($results) {
                $response = $results->grouped->courseid;
                $header   = $results->responseHeader;
                echo 'Query Time ' . $header->QTime / 1000 . ' Seconds';
                if (isset($results->spellcheck->suggestions->collation)) {
                    $didyoumean = $results->spellcheck->suggestions->collation->collationQuery;
                } else {
                    $didyoumean = false;
                }
                $out['qtime'] = false;
                $outputinfo   = true;
                if ($outputinfo) {
                    $out['qtime'] = sprintf(("%.3f"), $header->QTime / 1000);
                }
                if ($didyoumean != false) {
                    echo html_writer::tag('h3', get_string('didyoumean', 'tool_coursesearch') . html_writer::link(
                    new moodle_url('search.php?search=' . rawurlencode($didyoumean)), $didyoumean) . '?');
                }
            }
            return $response;
        }
    }
    /**
     * gives the count of results. we filter the hidden course by iterating through courses.
     *
     * @param object Apache_solr_response
     * @return int count
     */
    public function tool_coursesearch_coursecount($response) {
        $count = $response->ngroups;
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
                        $count -= 1;
                    }
                }
            }
        }
        return $count;
    }
    /**
     * Returns the error code either 0, 02 or 12.
     * 0 Everything is okay 
     * 1 Admin tool is not installed
     * 02 Ping to solr failed.
     * 12 Both issues 
     * @param void
     * @return int errorcode
     */
    public function tool_coursesearch_pluginchecks() {
        $errorcode = 0;
        global $CFG;
        $obj = new tool_coursesearch_solrlib();
        if (!array_key_exists('coursesearch', get_plugin_list('tool'))) {
            $errorcode = 1;
        }
        if (!$obj->connect($this->tool_coursesearch_solr_params(), true)) {
            $errorcode .= 2;
        }
        return $errorcode;
    }
}
/**
 * Return boolean
 *
 * Course create handler trigger when a course is created.
 * @param coursedata object
 */
function tool_coursesearch_course_created_handler($obj) {
    try {
        $ob      = new tool_coursesearch_locallib();
        $options = $ob->tool_coursesearch_solr_params();
        $doc     = $ob->tool_coursesearch_build_document($options, $obj);
        $solr    = new tool_coursesearch_solrlib();
        if ($solr->connect($options, true)) {
            if ($doc) {
                $solr->addDocument($doc);
                return true;
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}
/**
 * Return boolean
 *
 * Course event handler trigger when a course is deleted.
 * @param coursedata object
 */
function tool_coursesearch_course_deleted_handler($obj) {
    try {
        $ob   = new tool_coursesearch_locallib();
        $solr = new tool_coursesearch_solrlib();
        if ($solr->connect($ob->tool_coursesearch_solr_params(), true)) {
            $solr->deletebyquery($obj->id);
            $solr->commit();
        }
        return true;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}
/**
 * Return boolean
 *
 * Course event handler. trigger when a course is updated.
 * @param coursedata object
 */
function tool_coursesearch_course_updated_handler($obj) {
    if (tool_coursesearch_course_deleted_handler($obj) && tool_coursesearch_course_created_handler($obj)) {
        return true;
    }
}

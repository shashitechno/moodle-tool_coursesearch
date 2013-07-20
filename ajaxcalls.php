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
	 * functions that talks between Solr's instance and SOlrPhpClient Library
	 *
	 * Other main libraries:
	 * Basic-solr-functions.class.inc.php general fucnctions that handles indexing
	 * @package    coursesearch
	 * @copyright  2013 Shashikant Vaishnav  {@link http://moodle.com}
	 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
	 */   
	require_once(dirname(__FILE__) . '/../../../config.php');
	require_once("SolrPhpClient/Apache/Solr/Service.php");
	require_once("lib/Basic-solr-functions.class.inc.php");

	//require_sesskey();

	$action = optional_param('action','ping', PARAM_STRINGID);

		// check the action and behave accrodingly

	switch($action){

		case 'ping': tool_coursesearch_ping();
		break;
		case 'index': tool_coursesearch_index();
		break;
		case 'optimize': tool_coursesearch_optimize();
		break;
		case 'deleteall': tool_coursesearch_deleteAll();
		break;
		case 'search': tool_coursesearch_search();
		break;
		
	}

	function tool_coursesearch_ping()
	{  
		$options = tool_coursesearch_get_options();
		$arr = array();

		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			$arr['status']='ok';
		}
		else {
			$arr['status']='error';
		}

		print(json_encode($arr));

		exit();
	}	
	function tool_coursesearch_index()
	{
		$prev = optional_param('prev',1,PARAM_TEXT);
		tool_coursesearch_load_all(tool_coursesearch_params(), $prev);
		exit();  
	}
	function tool_coursesearch_deleteAll()
	{
		$options = tool_coursesearch_params();
		$arr = array();
		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			if ($solr->deleteall()) {
				$arr['status']='ok';
			}
			else {
				$arr['status']='error';
			}
		}
		else {
			$arr['status']='error';
			$arr['code']=$solr->getLastErrorCode();
			$arr['message']=$solr->getLastErrorMessage();
		}

		print(json_encode($arr));
		exit();
	}	
	function tool_coursesearch_optimize() {
		$options = tool_coursesearch_params();
		$arr = array();

		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			if ($solr->optimize()) {
				$arr['status']='ok';
			}
			else {
				$arr['status']='error';
			}
		}
		else {
			$arr['status']='error';
			$arr['code']=$solr->getLastErrorCode();
			$arr['message']=$solr->getLastErrorMessage();
		}

		print(json_encode($arr));
		exit();
	}


	      /**
	 * Return the solr configuration array
	 *
	 * @return array Array of {@link $config} params
	 */
	      function tool_coursesearch_get_options()
	      {
	      	$options = array();
	      	$options['solr_host'] = optional_param('host','localhost',PARAM_TEXT);
	      	$options['solr_port'] = optional_param('port',8983,PARAM_INT);
	      	$options['solr_path']=  optional_param('path','/solr',PARAM_TEXT);
	      	return $options;
	      }
	
		  /**
	 * Return the array of solr configuration
	 * @return array of solr configuration values 
	 */
		  function tool_coursesearch_params(){
		  	$options = array();
		  	$options['solr_host']= get_config('tool_coursesearch','solrhost');
		  	$options['solr_port']= get_config('tool_coursesearch','solrport');
		  	$options['solr_path']= get_config('tool_coursesearch','solrpath');  
		  	return $options;
		  }
	  /**
	 * Return indexing statics in json
	 *
	 * @param array $options configuration of solr
	 * @param string $prev previous id where solr need to start the index for very start its 1
	 * @return string 
	 */

	  function tool_coursesearch_load_all($options, $prev) {
	  	global $DB,$CFG;
	  	$documents = array();
	  	$cnt = 0;
	  	$batchsize = 100;
	  	$last = "";
	  	$found = FALSE;
	  	$end = FALSE;
	  	$percent = 0;

	  	$sql = 'SELECT id FROM mdl_course';
	  	$courses =  $DB->get_records_sql($sql);
	  	$coursecount = count($courses);
	  	for ($idx = 1; $idx <= $coursecount; $idx++) {
	  		$courseid = $courses[$idx]->id;
	  		$last = $courseid;
	  		$percent = (floatval($idx) / floatval($coursecount)) * 100;
	  		if ($prev && !$found) {
	  			if ($courseid === $prev) {
	  				$found = TRUE;
	  			}
	  			continue;
	  		}

	  		if ($idx === $coursecount) {
	  			$end = TRUE;
	  		}

	  		$documents[] = tool_coursesearch_build_document($options, tool_coursesearch_get_courses($courseid));
	  		$richtypes[]=array();
	  		$context = context_course::instance($courseid);

	  		$cnt++;
	  		if ($cnt == $batchsize) {
	  			tool_coursesearch_solr_course( $options, $documents, FALSE, FALSE);
	  			$cnt = 0;
	  			$documents = array();
	  			break;
	  		}
	  	}

	  	if ( $documents ) {
	  		tool_coursesearch_solr_course( $options, $documents , FALSE, FALSE);
	  	}

	  	if ($end) {
	  		tool_coursesearch_solr_course($options, FALSE, TRUE, FALSE);
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
	  function tool_coursesearch_get_courses($courseid)
	  {
	  	global $DB,$CFG;
	  	$courses = $DB->get_record('course',array('id' => $courseid ));
	  	return $courses;	
	  }
	  /**
	 * Return object of solr content to be indexed
	 *
	 * @param array $options configuration of solr
	 * @param object $course_info having the other attributes about the particular course
	 * @return object 
	 */
	  function tool_coursesearch_build_document( $options, $course_info ) {
	  	global $DB,$CFG;
	  	$doc = new Apache_Solr_Document();
	  	$doc->setField( 'id', $course_info->id );
	  	$doc->setField( 'fullname', $course_info->fullname);
	  	$doc->setField( 'summary', $course_info->summary );
	  	$doc->setField( 'shortname', $course_info->shortname );
	  	$doc->setField( 'date', tool_coursesearch_format_date($course_info->startdate) );
	  	$doc->setField('visibility', $course_info->visible);
	  	return $doc;
	  }
	  /**
	 * Return the date in proper format
	 *
	 * @param string to be formatted
	 * @return string 
	 */
	  function tool_coursesearch_format_date( $thedate ) {
	  	$datere = '/(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2}:\d{2})/';
	  	$replstr = '${1}T${2}Z';
	  	return preg_replace($datere, $replstr, $thedate);
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
	  function tool_coursesearch_solr_course( $options, $documents, $commit = true, $optimize = false) {
	  	try {
	  		$solr = new Solr_basic();
	  		if ($solr->connect($options, true)) {

	  			if ($documents) {
	  				$solr->addDocuments( $documents );
	  			}

	  			if ($commit) {
	  				$solr->commit();
	  			}

	  			if ($optimize) {
	  				$solr->optimize();
	  			}
	  		}
	  	} catch ( Exception $e ) {
	  		echo $e->getMessage();
	  	}
	  }

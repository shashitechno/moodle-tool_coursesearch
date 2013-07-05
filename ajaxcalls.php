

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
 * Library of functions for database manipulation.
 *
 * Other main libraries:
 * - weblib.php - functions that produce web output
 * - moodlelib.php - general-purpose Moodle functions
 *
 * @package    coursesearch
 * @copyright  2013 Shashikant Vaishnav  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */   
    require_once(dirname(__FILE__) . '/../../../config.php');
    require_once("SolrPhpClient/Apache/Solr/Service.php");
    require_once("lib/solr.class.inc.php");
	
	 require_sesskey();

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
        $options = solr_get_options();
		$arr = array();

		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			$arr['status']='ok';
		}
		else {
			$arr['status']='ko';
		}
		
		print(json_encode($arr));
		
		exit();
    }	
    function tool_coursesearch_index()
    {
	    $prev = POSTGET('prev');
		solr_load_all(solr_params(), $prev);
		exit();  
    }
    function tool_coursesearch_deleteAll()
	 {
		$options = solr_params();
		$arr = array();
		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			if ($solr->deleteall()) {
				$arr['status']='ok';
			}
		    else {
				$arr['status']='ko';
			}
	 }
	else {
		$arr['status']='ko';
		$arr['code']=$solr->getLastErrorCode();
		$arr['message']=$solr->getLastErrorMessage();
		}

		print(json_encode($arr));
		exit();
	}	
    function tool_coursesearch_optimize() {
		$options = solr_params();
		$arr = array();

		$solr = new Solr_basic();
		if ($solr->connect($options, true)) {
			if ($solr->optimize()) {
				$arr['status']='ok';
			}
			else {
				$arr['status']='ko';
			}
		}
		else {
			$arr['status']='ko';
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
    function solr_get_options()
    {
	   $options = array();
	   $options['mss_solr_host']=POSTGET('host');
	   $options['mss_solr_port']=POSTGET('port');
	   $options['mss_solr_path']=  POSTGET('path');
			
			return $options;
   }
  /**
 * Return the value retrieved by either  POST or GET action
 *
 * @param string $param a value need to be retrived
 * @return string 
 */
    function POSTGET($param){
    	if (isset($_POST[$param]) && $_POST[$param]!="")
     	return $_POST[$param];
	    if (isset($_GET[$param]) && $_GET[$param]!="")
	    return $_GET[$param];
	    return "";
    }
	  /**
 * Return the array of solr configuration
 * @return array of solr configuration values 
 */
    function solr_params(){
	   $options = array();
         $options['mss_solr_host']= get_config('tool_coursesearch','solrhost');
	   $options['mss_solr_port']= get_config('tool_coursesearch','solrport');
	   $options['mss_solr_path']= get_config('tool_coursesearch','solrpath');  
           return $options;
    }
  /**
 * Return indexing statics in json
 *
 * @param array $options configuration of solr
 * @param string $prev previous id where solr need to start the index for very start its 1
 * @return string 
 */

    function solr_load_all($options, $prev) {
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

		$documents[] = solr_build_document($options, get_course($courseid) );
		$cnt++;
		if ($cnt == $batchsize) {
			solr_course( $options, $documents, FALSE, FALSE);
			$cnt = 0;
			$documents = array();
			break;
		}
	}

	if ( $documents ) {
		solr_course( $options, $documents , FALSE, FALSE);
	}

	if ($end) {
		solr_course($options, FALSE, TRUE, FALSE);
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
    function get_course($courseid)
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
    function solr_build_document( $options, $course_info ) {
	    global $DB,$CFG;
		$doc = new Apache_Solr_Document();
		$doc->setField( 'id', $course_info->id );
		$doc->setField( 'fullname', $course_info->fullname);
		$doc->setField( 'summary', $course_info->summary );
		$doc->setField( 'shortname', $course_info->shortname );
		$doc->setField( 'date', solr_format_date($course_info->startdate) );
	    return $doc;
    }
  /**
 * Return the date in proper format
 *
 * @param string to be formatted
 * @return string 
 */
    function solr_format_date( $thedate ) {
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
    function solr_course( $options, $documents, $commit = true, $optimize = false) {
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
	
	/*
 * Search functions
*/
function mss_query( $qry, $offset, $count, $fq, $sortby, $options) {
	$response = NULL;
//	$facet_fields = array();
	$options = solr_params(); // uncommented in 2.0.3

	$solr = new Solr_basic();
	if ($solr->connect($options, true)) {

	//	$facets = $options['mss_facets'];
	//	$aFacets = explode(',', $facets);

	/*	foreach($aFacets as $facet_field) {
			$facet_field_add = $facet_field . "_str";
			if ($facet_field=='category') $facet_field_add = 'categories';
			if ($facet_field=='tag') $facet_field_add = 'tags';
			if ($facet_field=='author') $facet_field_add = 'author';
			if ($facet_field=='type') $facet_field_add = 'type';
			$facet_field_add =  strtolower(str_replace(' ', '_', $facet_field_add));
			$facet_fields[] = $facet_field_add;
		}
*/
		$params = array();
		$params['defType'] = 'dismax';
		$params['qf'] = 'id^5 fullname^10 shortname^5 summary^3.5 startdate^1.5'; // TODO : Add "_srch" custom fields ?
		/*
		2.0.3 change:
		added this section to _srch versions for each custom field and each custom taxonomy that's checked in the plugin options area
		*/
		//$facet_search = $options['mss_facets_search'];
		//if ($facet_search) {
		/*	$cust_array = array();
			$aCustom = explode(',', $options["mss_custom_fields"]);
			if (count($aCustom)>0) {
				foreach($aCustom as $aCustom_item){
					$cust_array[] = $aCustom_item . '_srch';
				}
			}
			$aCustom = explode(',', $options["mss_custom_taxonomies"]);
			if (count($aCustom)>0) {
				foreach($aCustom as $aCustom_item){
					$cust_array[] = $aCustom_item . '_srch';
				}
			}
			if (count($cust_array)>0) {
				foreach($cust_array as $custom_item){
					$params['qf'] .= " $custom_item^3";
				}
			}
		//}     */
					
		if (empty($qry) || $qry=='*' || $qry=='*:*') {
			$params['q.alt']="*:*";
			$qry = '';
		}
				print($qry);
		/* end 2.0.3 change added section */
		//var_dump($params['qf']);
		$params['pf'] = 'fullname^15 shortname^10';
	//	$params['facet'] = 'true';
		//$params['facet.field'] = $facet_fields;
	//	$params['facet.mincount'] = '1';
		$params['fq'] = $fq;
		$params['fl'] = '*,score';
		$params['hl'] = 'on';
		$params['hl.fl'] = 'summary';
		$params['hl.snippets'] = '3';
		$params['hl.fragsize'] = '50';
		$params['sort'] = $sortby;
		$params['spellcheck.onlyMorePopular'] = 'true';
		$params['spellcheck.extendedResults'] = 'false';
		$params['spellcheck.collate'] = 'true';
		$params['spellcheck.count'] = '1';
		$params['spellcheck'] = 'true';
		//$params['debug'] = 'true';
		
		//if ($facet_on_tags) {
		//	$number_of_tags = $options['mss_max_display_tags'];
		//	$params['f.tags.facet.limit'] = $number_of_tags;
		//}

		$response = $solr->search($qry, $offset, $count, $params);
		//print($response->getRawResponse());
		if ( ! $response->getHttpStatus() == 200 ) {
			$response = NULL;
		}
	}
	return $response;
}
function tool_coursesearch_search() {
	$plugin_mss_settings = solr_get_options();
	 
	$qry = stripslashes($_GET['s']);
	$offset = (isset($_GET['offset'])) ? $_GET['offset'] : 0;
	$count = (isset($_GET['count'])) ? $_GET['count'] :10;
	$fq = (isset($_GET['fq'])) ? $_GET['fq'] : '';
	$sort = (isset($_GET['sort'])) ? $_GET['sort'] : '';
	$order = (isset($_GET['order'])) ? $_GET['order'] : '';
	$isdym = (isset($_GET['isdym'])) ? $_GET['isdym'] : 0;
    $fqitms = '';
	$out = array();

	if ( ! $qry ) {
		$qry = '';
	}

	if ( $sort && $order ) {
		$sortby = $sort . ' ' . $order;
	} else {
		$sortby = '';
		$order = '';
	}

	
	if ($qry) {
		$results = mss_query( $qry, $offset, $count, $fqitms, $sortby, $plugin_mss_settings );
        
		if ($results) {
			$response = $results->response;
			echo $results->getRawResponse();
			$header = $results->responseHeader;
			$teasers = get_object_vars($results->highlighting);
			if (is_object($results->spellcheck))
			$didyoumean = $results->spellcheck->suggestions->collation;
			else
			$didyoumean= false;
  
			$out['hits'] = sprintf(__("%d"), $response->numFound);
			$out['qtime'] = false;
			if ($output_info) {
				$out['qtime'] = sprintf(__("%.3f"), $header->QTime/1000);
			}
			$out['dym'] = false;
			if ($didyoumean && !$isdym && $dym_enabled) {
				$dymout = array();
				$dymout['term'] = htmlspecialchars($didyoumean);
				$dymout['link'] = htmlspecialchars(sprintf(__("?s=%s&isdym=1"), urlencode($didyoumean)));
				$out['dym'] = $dymout;
			}
		}
	}	// calculate the number of pages
			
	return $out;
}


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
 * File handles the ajax calls that comes by module.js
 *
 * @package    coursesearch
 * @copyright  2013 Shashikant Vaishnav  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../config.php');
require_once("locallib.php");
require_sesskey();
$action = optional_param('action', 'none', PARAM_STRINGID); // Check the action and behave accrodingly.
$obj    = new tool_coursesearch_locallib();
switch ($action) {
    case 'ping':
        $obj->tool_coursesearch_ping($arr = tool_coursesearch_get_options());
        break;
    case 'index':
        $prev = optional_param('prev', 0, PARAM_TEXT);
        $obj->tool_coursesearch_index($prev);
        break;
    case 'optimize':
        $obj->tool_coursesearch_optimize();
        break;
    case 'deleteall':
        $obj->tool_coursesearch_deleteall();
        break;
    case 'none':
        break; // Do nothing.
}
/**
 * Return the solr configuration array
 *
 * @return array Array of {@link $config} params
 */
function tool_coursesearch_get_options() {
    $options              = array();
    $options['solr_host'] = optional_param('host', 'localhost', PARAM_HOST);
    $options['solr_port'] = optional_param('port', 8983, PARAM_INT);
    $options['solr_path'] = optional_param('path', '/solr', PARAM_PATH);
    return $options;
}
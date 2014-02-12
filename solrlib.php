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
defined('MOODLE_INTERNAL') || die;
class tool_coursesearch_solrlib
{
    private $solr = null;
    private $solrhost = null;
    private $solrport = null;
    private $solrpath = null;
    private $errorcode = "";
    private $errormessage = "";
    public function get_errorcode() {
        return $this->errorcode;
    }
    public function get_errormessage() {
        return $this->errormessage;
    }
    public function connect($options, $ping = false, $path = '') {

        // Get the connection options.
        $this->solrhost = $options['solr_host'];
        $this->solrport = $options['solr_port'];
        $this->solrpath = $options['solr_path'];
        // Double check everything has been set.
        if (!($this->solrhost and $this->solrport and $this->solrpath)) {
            $this->errorcode    = -1;
            $this->errormessage = "Invalid Solr Params";
            return false;
        }
        // Create the solr service object.
        try {
            require_once($path . "SolrPhpClient/Apache/Solr/HttpTransport/Curl.php");
            $httptransport = new Apache_Solr_HttpTransport_Curl();
            $this->solr    = new Apache_Solr_Service($this->solrhost, $this->solrport, $this->solrpath, $httptransport);
            $this->solr->setAuthenticationCredentials(
                get_config('tool_coursesearch', 'solrusername'), get_config('tool_coursesearch', 'solrpassword'));

        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
        // If we want to check if the server is alive, ping it.
        if ($ping) {
            try {

                if (!$this->solr->ping(2000)) {
                    $this->errorcode    = -1;
                    $this->errormessage = "Ping failed !";
                    return false;
                }
            } catch (Exception $e) {
                $this->errorcode    = $e->getCode();
                $this->errormessage = $e->getMessage();
                return false;
            }
        }
        return true;
    }
    public function commit() {
        try {
            $this->solr->commit();
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function optimize() {
        try {
            $this->solr->optimize();
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function deletebyquery($courseid) {
        try {
            $this->solr->deleteByQuery('courseid:'.$courseid);
            $this->solr->commit();
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function adddocuments($documents) {
        try {
            $this->solr->addDocuments($documents);
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function adddocument($document) {
        try {
            $this->solr->addDocument($document);
            $this->solr->commit();
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function deleteall($optimize = false) {
        try {
            $this->solr->deleteByQuery('*:*');
            if (!$this->commit()) {
                return false;
            }
            if ($optimize) {
                return $this->optimize();
            }
            return true;
        } catch (Exception $e) {
            $this->errorcode    = $e->getCode();
            $this->errormessage = $e->getMessage();
            return false;
        }
    }
    public function deletebyid($docid) {
        try {
            $this->solr->deleteById($docid);
            $this->solr->commit();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function extract($url, $array, $docs = null) {
        try {
            $this->solr->extract($url, $array, $docs);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function search($qry, $offset, $count, $params) {
        return $this->solr->search($qry, $offset, $count, $params);
    }
}
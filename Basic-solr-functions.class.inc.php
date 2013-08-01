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
Class Solr_basic {

    private $_solr = null;
    private $_solrHost = null;
    private $_solrPort = null;
    private $_solrPath = null;
    private $_lastErrorCode = "";
    private $_lastErrorMessage = "";

    public function Mss_Solr () {}

    public function getLastErrorCode() {
        return $this->_lastErrorCode;
    }

    public function getLastErrorMessage() {
        return $this->_lastErrorMessage;
    }

    public function connect($options, $ping = false,$path='') {
        // get the connection options
        $this->_solrHost = $options['solr_host'];
        $this->_solrPort = $options['solr_port'];
        $this->_solrPath = $options['solr_path'];

        // double check everything has been set
        if ( ! ($this->_solrHost and $this->_solrPort and $this->_solrPath) ) {
            $this->_lastErrorCode = -1;
            $this->_lastErrorMessage = "Invalid Solr Params";
            return false;
        }

        // create the solr service object
        try {
            require_once($path. "SolrPhpClient/Apache/Solr/HttpTransport/Curl.php");           
            $httpTransport = new Apache_Solr_HttpTransport_Curl();
            $this->_solr = new Apache_Solr_Service($this->_solrHost, $this->_solrPort, $this->_solrPath, $httpTransport);
        } catch ( Exception $e ) {
            $this->_lastErrorCode = $e->getCode();
            $this->_lastErrorMessage = $e->getMessage();
            return false;
        }

        // if we want to check if the server is alive, ping it
        if ($ping) { 
            try {
                if (!$this->_solr->ping()) {
                    $this->_lastErrorCode = -1;
                    $this->_lastErrorMessage = "Ping failed !";
                    return false;
                }
            } catch ( Exception $e ) {
                $this->_lastErrorCode = $e->getCode();
                $this->_lastErrorMessage = $e->getMessage();
                return false;
            }
        }
        return true;
    }

    public function commit() {
        try {
            $this->_solr->commit();
            return true;
        } catch ( Exception $e ) {
            $this->_lastErrorCode = $e->getCode();
            $this->_lastErrorMessage = $e->getMessage();
            return false;
        }
    }

    public function optimize() {
        try {
            $this->_solr->optimize();
            return true;
        } catch ( Exception $e ) {
            $this->_lastErrorCode = $e->getCode();
            $this->_lastErrorMessage = $e->getMessage();
            return false;
        }
    }

    public function addDocuments($documents) {
        try {
            $this->_solr->addDocuments($documents);
            return true;
        } catch ( Exception $e ) {
            $this->_lastErrorCode = $e->getCode();
            $this->_lastErrorMessage = $e->getMessage();
            return false;
        }
    }


    public function deleteAll($optimize = false) {
        try {
            $this->_solr->deleteByQuery('*:*');
            if (!$this->commit()) {
               return false;
           }
           if ($optimize) { 
            return $this->optimize();
        }
        return true;
    } catch ( Exception $e ) {
        $this->_lastErrorCode = $e->getCode();
        $this->_lastErrorMessage = $e->getMessage();
        return false;
    }
}

public function deleteById( $doc_id ) {
    try {
        $this->_solr->deleteById( $doc_id );
        $this->_solr->commit();
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }
}
public function extract($url,$array,$docs=null)
{
    try
    {
        $this->_solr->extract($url,$array,$docs);
    }
    catch(Exception $e){
        echo $e->getMessage();
    }
}

public function search($qry, $offset, $count, $params) {
    return $this->_solr->search($qry, $offset, $count, $params);
}
}
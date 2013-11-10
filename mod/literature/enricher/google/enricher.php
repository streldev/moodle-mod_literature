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


require_once(dirname(dirname(__FILE__)) . '/enricher.php');
require_once(dirname(__FILE__) . '/src/Google_Client.php');
require_once(dirname(__FILE__) . '/src/contrib/Google_BooksService.php');


/**
 * Google Books enricher
 *
 * This enricher adds covers to the literature entries
 *
 * @package    mod_literature_enricher
 * @subpackage aws
 * @copyright  2013 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_enricher_google extends literature_enricher {
	
    private $apikey;
    
    /**
     * Constructor to initialize configurable parameters
     * @param name prefix for setting variables
     */
    
    public function __construct($settingname = 0) {
    	global $CFG;
    	
    	$setting = $settingname.'_apikey';
    	$this->apikey = $CFG->$setting;
        
        $client = new Google_Client();
        $client->setDeveloperKey($this->apikey);
        $client->setApplicationName("moodle-mod-literature");
        $service = new Google_BooksService($client);
        $this->volumes = $service->volumes;

    }

    /**
     * @see literature_enricher::enrich()
     */
    public function enrich($literature) {
         
        $this->setSearchParams($literature);
        $results = $this->volumes->listVolumes($this->searchTerm, $this->optParams);
        print_r($results);
        exit();
    }

    /**
     * @see literature_enricher::enrich_preview()
     */
    public function enrich_preview($literature) {
       
        $this->setSearchParams($literature);
        $results = $this->volumes->listVolumes($this->searchTerm, $this->optParams);
        print_r($results);
        exit();
    }
    
    private function setSearchParams($literature) {
        
        if (!empty($literature->isbn13)) {
            $isbn = $literature->isbn13;
        } elseif (!empty ($literature->isbn10)) {
            $isbn = $literature->isbn10;
        } else {
            return false;
        }
        
        $this->searchTerm = 'isbn:' . $isbn;
        $this->optParams['maxResults'] = 1;        
        
        return true;
    }

}
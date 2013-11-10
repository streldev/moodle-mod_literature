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
require_once('AmazonECS.class.php');

/**
 * Amazon ECS enricher
 *
 * This enricher adds covers to the literature entries
 *
 * @package    mod_literature_enricher
 * @subpackage aws
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_enricher_aws extends literature_enricher {
	
    private $apikey;
    private $secretkey;
    private $associateTag;
    
    /**
     * Constructor to initialize configurable parameters
     * @param name prefix for setting variables
     */
    
    public function __construct($settingname = 0) {
    	global $CFG;
    	
    	$setting = $settingname.'_apikey';
    	$this->apikey = $CFG->$setting;
    	
    	$setting = $settingname.'_secretkey';
    	$this->secretkey = $CFG->$setting;
    	
    	$setting = $settingname.'_associatetag';
    	$this->associateTag = $CFG->$setting;

    }

    /**
     * @see literature_enricher::enrich()
     */
    public function enrich($literature) {

        $isbn = (!empty($literature->isbn10)) ? $literature->isbn10 : null;
        if ($isbn == null) {
            if (!empty($literature->isbn13)) {
                $isbn = $literature->isbn13;
            } else {
                return false; // No isbn found
            }
        }
        $imageurl = self::get_cover_for_isbn($isbn);
        if ($imageurl) {
            $literature->coverpath = $imageurl;
            return true;
        }
        return false;
    }

    /**
     * @see literature_enricher::enrich_preview()
     */
    public function enrich_preview($literature) {

        $isbn = (!empty($literature->isbn10)) ? $literature->isbn10 : null;
        if ($isbn == null) {
            if (!empty($literature->isbn13)) {
                $isbn = $literature->isbn13;
            }
        }

        $imageurl = $this->get_coverurl_for_isbn($isbn);
        if ($imageurl) {
            $literature->coverpath = $imageurl;
        }
    }

    /**
     * Get the url of a cover for a literature entry
     *
     * @param string $isbn The isbn of the literature entry
     * @return boolean|string false or the url
     */
    public function get_coverurl_for_isbn($isbn) {
        $url = $this->get_cover_by_isbn_and_country($isbn, 'de');
        if(!$url) {
            $url = $this->get_cover_by_isbn_and_country($isbn, 'com');
        }
        return $url;

    }

    /**
     * Get a cover url by isbn and countrycode
     *
     * @param string $isbn The isbn
     * @param string $countrycode The countrycode (Example: de, com, co.uk)
     * @return boolean|string false or url
     */
    private function get_cover_by_isbn_and_country($isbn, $countrycode) {

        $client = new AmazonECS($this->apikey, $this->secretkey, $countrycode, $this->associateTag);

        $client->associateTag($this->associateTag);
        $response = $client->category('Books')->responseGroup('Images')->lookup($isbn);

        if (empty($response->Items->Item)) {
            return false;
        }

        $item = $response->Items->Item;

        if (is_array($item)) {

            if (!empty($item[0]->MediumImage->URL)) {
                $imageurl = $item[0]->MediumImage->URL;
            } else {
                return false;
            }
        } else {
            if (!empty($item->MediumImage->URL)) {
                $imageurl = $item->MediumImage->URL;
            } else {
                return false;
            }
        }

        return $imageurl;
    }

    /**
     * Save cover for isbn in plugin
     * @param string $isbn The isbn
     * @return boolean|string false or the path of the save file
     */
    public function get_cover_for_isbn($isbn) {

        $imageurl = $this->get_coverurl_for_isbn($isbn);
        if ($imageurl) {
            $path = $this->save_cover($imageurl, $isbn);
            return $path;
        } else {
            return false;
        }
    }

    // NOTE: Not productiv
    public function get_desc_for_isbn($isbn) {

        $client = new AmazonECS($this->apikey, $this->secretkey, 'DE', $this->associateTag);

        $response = $client->category('Books')->responseGroup('EditorialReview')->lookup($isbn);
        $item = $response->Items->Item;
        if (is_array($item)) {
            $desc = $item[0]->EditorialReviews->EditorialReview->Content;
        } else {
            $desc = $item->EditorialReviews->EditorialReview->Content;
        }

        return $desc;
    }

}
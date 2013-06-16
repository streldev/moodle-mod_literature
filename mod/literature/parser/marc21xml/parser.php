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


require_once(dirname(dirname(__FILE__)) . '/parser.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/dbobject/literature.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/dbobject/link.php');

/**
 * Parser for the MARC21 format
 *
 * @package    mod_literature_parser
 * @subpackage marc21xml
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_parser_marc21xml implements literature_parser {

    /**
     * The mapping of the parser
     * @var array
     */
    private $mapping;

    /**
     * The constructor
     */
    public function __construct() {

        $this->mapping = array(
            '020' => array('field' => 'isbn', 'handler' => 'map_single', 'args' => '$a'),
            '022' => array('field' => 'issn', 'handler' => 'map_single', 'args' => '$a'),
            '041' => array('field' => 'language', 'handler' => 'map_single', 'args' => '$a'),
            '100' => array('field' => 'person', 'handler' => 'map_single', 'args' => '$a'),
            '245' => array('field' => 'title', 'handler' => 'map_multi', 'args' =>
                array('$a' => 'title', '$b' => 'subtitle', '$c' => 'authors')),
            '260' => array('field' => 'publication', 'handler' => 'map_multi', 'args' =>
                array('$a' => 'place', '$b' => 'publisher', '$c' => 'date')),
            '300' => array('field' => 'format', 'handler' => 'map_multi', 'args' =>
                array('$a' => 'extent', '$e' => 'accompany')),
            '440' => array('field' => 'series', 'handler' => 'map_single', 'args' => '$a'),
            '500' => array('field' => 'note', 'handler' => 'map_single', 'args' => '$a'),
            '505' => array('field' => 'content', 'handler' => 'map_single', 'args' => '$a'),
            '520' => array('field' => 'summary', 'handler' => 'map_multi', 'args' =>
                array('$a' => 'main', '$b' => 'expansion')),
            '650' => array('field' => 'topic', 'handler' => 'map_multi', 'args' =>
                array('$a' => 'topic', '$x' => 'subdiv')),
            '856' => array('field' => 'link', 'handler' => 'map_multi', 'args' => 
                array('$u' => 'url', '$y' => 'text', '$3' => 'alttext'))
        );
    }

    /**
     * @see literature_parser_abstract::parse()
     * @param SimpleXMLElement $xmlrecord
     */
    public function parse($xmlrecord, $titlelink) {
        $parsedfields = array();

        $parsedfields['type'] = $this->get_type($xmlrecord);

        foreach ($xmlrecord->datafield as $field) {

            $code = $field->attributes()->tag->__toString();

            if (isset($this->mapping[$code])) {

                $handler = $this->mapping[$code]['handler'];
                $value = $this->$handler($field, $this->mapping);
                if($value) {
                    $parsedfields[$this->mapping[$code]['field']][] = $value;
                }
            }
        }

        $literature = $this->build_literature_class($parsedfields, $titlelink);

        return $literature;
    }

    /**
     * Get type of entry
     *
     * @param string $xmlrecord XML record of the entry
     * @return int the type of the record (BOOK=1, ELECTRONC=2, MISC=3)
     */
    private function get_type($xmlrecord) {

        $chararray = str_split($xmlrecord->leader);
        $typecode = $chararray[6];

        switch ($typecode) {

            case 'a' :
                $type = literature_dbobject_literature::BOOK; // Book
                break;
            case 'm' :
                $type = literature_dbobject_literature::ELECTRONIC; // Electronic Resource
                break;
            default :
                $type = literature_dbobject_literature::MISC; // All the other stuff (CDROM, ...)
        }

        return $type;
    }

    /**
     * Parse single subfield
     * @param string $field The field
     */
    private function map_single($field) {

        $result = array();

        $code = $field->attributes()->tag->__toString();

        foreach ($field->subfield as $subfield) {

            $subcode = '$' . $subfield->attributes()->code->__toString();

            if ($this->mapping[$code]['args'] == $subcode) {

                $result = $subfield->__toString();
                return $result;
            }
        }
        return false;
    }

    /**
     * Parse multiple subfields
     * @param unknown_type $field
     */
    private function map_multi($field) {

        $code = $field->attributes()->tag->__toString();

        $results[$this->mapping[$code]['field']] = array();

        foreach ($field->subfield as $subfield) {
            $subcode = '$' . $subfield->attributes()->code->__toString();

            if (isset($this->mapping[$code]['args'][$subcode])) {
                $propname = $this->mapping[$code]['args'][$subcode];
                $results[$propname] = $subfield->__toString();
            }
        }
        if(!empty($results)) {
            return $results;
        } else  {
            return false;
        }
    }

    /**
     * Build a object of class literature_dbobject_literature from the parsed data
     * @param array $parsedfields
     * @param string $titlelink
     * @return literature_dbobject_literature
     */
    private function build_literature_class($parsedfields, $titlelink = null) {

        $id = null;
        $type = $parsedfields['type'];
        if(empty($parsedfields['title'][0]['title'])) {
            return false;
        }
        $title = $parsedfields['title'][0]['title'];
        $subtitle = (isset($parsedfields['title'][0]['subtitle'])) ? $parsedfields['title'][0]['subtitle'] : null;
        $authors = $this->get_authors($parsedfields);
        $publisher = (isset($parsedfields['publication'][0]['publisher'])) ? $parsedfields['publication'][0]['publisher'] : null;
        $published = (isset($parsedfields['publication'])) ? $this->make_time($parsedfields['publication']) : null;
        $series = (isset($parsedfields['series'])) ? $parsedfields['series'][0] : null;
        // get isbns
        if (isset($parsedfields['isbn'][0])) {
            $isbns = $this->get_isbns($parsedfields['isbn']);
            $isbn10 = $isbns->isbn10;
            $isbn13 = $isbns->isbn13;
        } else {
            $isbn10 = null;
            $isbn13 = null;
        }
        $issn = (isset($parsedfields['issn'])) ? $this->get_issn($parsedfields['issn']) : null;
        $coverpath = null;

        if (isset($parsedfields['summary'])) {
            $description = $parsedfields['summary'][0]['main'];
        } else if (isset($parsedfields['content'][0])) {
            $description = $parsedfields['content'][0];
        } else {
            $description = null;
        }
        $links = $this->get_links($parsedfields);
        $format = (isset($parsedfields['format'][0]['extent'])) ? $parsedfields['format'][0]['extent'] : null;
        $refs = 0;

        $literature = new literature_dbobject_literature($id, $type, $title, $subtitle, $authors, $publisher,
                $published, $series, $isbn10, $isbn13, $issn, $coverpath, $description, $links, $format,
                $titlelink, $refs);

        return $literature;
    }
    
    /**
     * Extract links from marc21 fields
     */
    private function get_links($fields) {
                
        // If no links found return empty array
        if(!isset($fields['link'])) {
            return array();
        }
        
        $links = array();
        foreach ($fields['link'] as $link) {
            if(empty($link['url'])) {
                continue; // No valid url
            }
            $text = (empty($link['text'])) ? (empty($link['alttext'])) ? $link['url'] : $link['alttext'] : $link['text'];
            $links[] = new literature_dbobject_link(null, null, $text, $link['url']);
        }
        
        return $links;
    }

    /**
     * Extract isbn10 and isbn13
     * @param array $isbns Array of string containing the isbns
     * @return stdClass whith isbn10 and isbn13 as attributes if found
     */
    private function get_isbns($isbns) {

        $result = new stdClass();
        $result->isbn10 = null;
        $result->isbn13 = null;

        foreach ($isbns as $isbn) {
            $cleanisbn = preg_replace("/[^0-9Xx]/", '', $isbn);
            if (strlen($cleanisbn) == 10) {
                $result->isbn10 = $cleanisbn;
            } else if (strlen($cleanisbn) == 13) {
                $result->isbn13 = $cleanisbn;
            }
        }

        return $result;
    }

    /**
     * Extract issns
     * @param array $issns Array of string
     * @return string of issns
     */
    private function get_issn($issns) {

        $cleanissns = '';
        foreach ($issns as $issn) {

            $cleanissns .= preg_replace("/[^0-9Xx]/", '', $issn) . ' ';
        }

        return $cleanissns;
    }

    /**
     * Get authors
     *
     * Authors can exist in different fields so check them all
     *
     * @param array $parsedfields Array with parsed fields
     * @return string|null if found authors; null otherwise
     */
    private function get_authors($parsedfields) {

        $authors = "";

        if (isset($parsedfields['title'][0]['authors'])) {

            $authors = $parsedfields['title'][0]['authors'];
        } else if (isset($parsedfields['person'])) {

            foreach ($parsedfields['person'] as $person) {

                $authors .= $person . ' ';
            }
        } else {
            $authors = null;
        }

        return $authors;
    }

    private function make_time($publication) {

        if (isset($publication[0]['date'])) {
            $date = preg_replace("/[^0-9]/", '', $publication[0]['date']);
        } else {
            $date = null;
        }

        return $date;
    }

}
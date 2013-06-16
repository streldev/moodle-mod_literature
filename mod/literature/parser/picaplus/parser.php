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
 * Parser for the PICA+ format
 *
 * @package    mod_literature_parser
 * @subpackage picaplus
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_parser_picaplus implements literature_parser {

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
            '002@' => array('field' => 'type', 'map' => 'single', 'args' => '$0'),
            '004A' => array('field' => 'isbn', 'map' => 'multi', 'args' => 
                    array('$0' => 'isbn10', '$A' => 'isbn13')),
            '005A' => array('field' => 'issn', 'map' => 'single', 'args' => '$0'),
            '010@' => array('field' => 'lang', 'map' => 'single', 'args' => '$a'),
            '021A' => array('field' => 'title', 'map' => 'multi', 'args' => 
                    array('$a' => 'title', '$d' => 'subtitle', '$h' => 'authors')),
            '033A' => array('field' => 'publisher', 'map' => 'multi', 'args' =>
                    array('$p' => 'place', '$n' => 'publisher')),
            '011@' => array('field' => 'date', 'map' => 'single', 'args' => '$a'),
            // '034I' => 'format',
            //'034K' => array('$a' => 'accompany'),
            // '440' => array('field' => 'series', 'handler' => 'map_single', 'args' => '$a'),welches feld???
            '020F' => array('field' => 'summary', 'map' => 'single', 'args' => '$a'),
            '009Q' => array('field' => 'link', 'map' => 'multi', 'args' =>
                    array('$u' => 'url', '$y' => 'text'))
            );
                
    }

    /**
     * @see literature_parser_abstract::parse()
     */
    public function parse($entries, $titlelink = null) {

        $parsedobject = new stdClass();
        foreach ($entries as $entry) {

            $entry = trim($entry);
            $code = substr($entry, 0, 4);
            $data = substr($entry, 4);

            if (key_exists($code, $this->mapping)) {

                $map = $this->mapping[$code];
                $mapCallback = 'map_' . $map['map'];
                $fieldTitle = $map['field'];
                
                // Check if field exists allready
                if(empty($parsedobject->$fieldTitle)) {
                    $parsedobject->$fieldTitle = array();
                }
                
                // Get the field value
                $value = $this->$mapCallback($data,$map['args']);
                if($value) {
                    $parsedobject->$fieldTitle[] = $value;
                }
                
            }
        }

        $id = null;
        $type = isset($parsedobject->type[0]) ? $this->get_type($parsedobject->type[0]) : literature_dbobject_literature::MISC;
        $title = isset($parsedobject->title[0]['title']) ? $parsedobject->title[0]['title'] : null;
        $subtitle = isset($parsedobject->title[0]['subtitle']) ? $parsedobject->title[0]['subtitle'] : null;
        $authors = isset($parsedobject->title[0]['authors']) ? $parsedobject->tile[0]['authors'] : null;
        $publisher = isset($parsedobject->publisher[0]['publisher']) ? $parsedobject->publisher[0]['publisher'] : null;
        $published = isset($parsedobject->date[0]) ? $parsedobject->date[0] : null;
        $series = null; // TODO in later version
        $isbn10 = $this->get_isbn10($parsedobject->isbn);
        $isbn13 = $this->get_isbn13($parsedobject->isbn);
        $issn = isset($parsedobject->issn[0]) ? $parsedobject->issn[0] : null;
        $coverpath = null;
        $description = isset($parsedobject->summary[0]) ? $parsedobject->summary[0] : null;
        $links = $this->get_links($parsedobject->link);
        $format = null; // TODO in later version
        $refs = 0;

        // TODO in later versions remove this ugly workaround
        $title = preg_replace('/@/', '', $title);
        $subtitle = preg_replace('/@/', '', $subtitle);

        return new literature_dbobject_literature($id, $type, $title, $subtitle, $authors, $publisher,
                $published, $series, $isbn10, $isbn13, $issn, $coverpath, $description, $links, $format,
                $titlelink, $refs);
    }
    
    /**
     * Parse single subfield
     * @param string $data The datastring for the subfield
     * @param string $arg The code of the subfield
     */
    private function map_single($data, $arg) {

        $pattern = "/$arg([^$]*)/g";
        $matches = array();
        if(preg_match($pattern, $data, $matches)) {
            return $matches[1][0];
        }
        return false;     
    }

    /**
     * Parse multiple subfields
     * @param unknown_type $field
     */
    private function map_multi($data, $args) {

        $values = array();
        foreach ($args as $code => $title) {
            $value = $this->map_single($data, $code);
            if($value) {
                $values[$title] = $value;
            }
        }
        if(!empty($values)) {
            return $values;
        } else {
            return false;
        }
    }

    private function get_links($linkArray) {
        $links = array();
        foreach ($linkArray as $link) {
            if(empty($link['url'])) {
                continue; // No valid url
            }
            $text = (empty($link['text'])) ? $link['url'] : $link['text'];
            $links[] = new literature_dbobject_link(null, null, $text, $link['url']);
        }
        return $links;
    }

    /**
     * Extract isbn from string
     * @param string $isbns
     * @return stdClass with isbn10 or isbn13 as attribute if found
     */
    private function get_isbn10($isbns) {

        $result = '';
        foreach ($isbns as $isbn) {
            $cleanisbn10 = preg_replace("/[^0-9Xx]/", '', $isbn['isbn10']);
            if (strlen($cleanisbn10) == 10) {
                $result .= $cleanisbn10 . ' ';
            }
        }
        if (strlen($result) < 0) {
            return trim($result);
        } else {
            return null;
        }
    }
    
    /**
     * Extract isbn from string
     * @param string $isbns
     * @return stdClass with isbn10 or isbn13 as attribute if found
     */
    private function get_isbn13($isbns) {

        $result = '';
        foreach ($isbns as $isbn) {
            $cleanisbn13 = preg_replace("/[^0-9Xx]/", '', $isbn['isbn13']);
            if (strlen($cleanisbn13) == 13) {
                $result .= $cleanisbn13 . ' ';
            }
        }
        if (strlen($result) < 0) {
            return trim($result);
        } else {
            return null;
        }
    }

    /**
     * Extract type of literature
     * @param string $typestring
     * @return int
     * @see literature_dbobject_literature::BOOK
     * @see literature_dbobject_literature::ELECTRONIC
     * @see literature_dbobject_literature::MISC
     */
    private function get_type($typestring) {

        if (substr($typestring, 0, 2) == 'Aa') {

            return literature_dbobject_literature::BOOK;
        } else if (substr($typestring, 0, 1 == 'O')) {

            return literature_dbobject_literature::ELECTRONIC;
        } else {

            return literature_dbobject_literature::MISC;
        }
    }

}
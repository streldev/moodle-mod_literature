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
            '002@' => array('$0' => 'type'),
            '004A' => array('$0' => 'isbn10', '$A' => 'isbn13', '$c' => null, '$f' => null, 'g' => null),
            '005A' => array('$0' => 'issn', '$c' => null),
            '010@' => array('$a' => 'language'),
            '021A' => array('$a' => 'title', '$d' => 'subtitle', '$h' => 'authors', '$e' => null,
                '$f' => null, '$e' => null, '$n' => null),
            '033A' => array('$p' => 'place', '$n' => 'publisher'),
            '011@' => array('$a' => 'date', '$b' => null, '$n' => null),
            // '034I' => 'format',
            '034K' => array('$a' => 'accompany'),
            // '440' => array('field' => 'series', 'handler' => 'map_single', 'args' => '$a'),welches feld???
            '020F' => array('$a' => 'summary'),
            '009Q' => array('$a' => 'url', '$y' => 'urltext', '$T' => null, '$b' => null, '$c' => null,
                '$d' => null, '$f' => null, '$g' => null, '$h' => null, '$i' => null, '$j' => null,
                '$k' => null, '$l' => null, '$m' => null, '$n' => null, '$p' => null, '$q' => null,
                '$r' => null, '$s' => null, '$t' => null, '$u' => null, '$v' => null, '$w' => null,
                '$x' => null, '$z' => null, '$2' => null, '$4' => null));
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
                $fields = $this->split_data_string($data, $map);

                foreach ($map as $subfieldcode => $title) {

                    if (!empty($title) && key_exists($subfieldcode, $fields) && !empty($fields[$subfieldcode])) {

                        $parsedobject->$title = $fields[$subfieldcode];
                    }
                }
            }
        }

        $id = -1;
        $type = isset($parsedobject->type) ? $this->get_type($parsedobject->type) : literature_dbobject_literature::MISC;
        $title = isset($parsedobject->title) ? $parsedobject->title : null;
        $subtitle = isset($parsedobject->subtitle) ? $parsedobject->subtitle : null;
        $authors = isset($parsedobject->authors) ? $parsedobject->authors : null;
        $publisher = isset($parsedobject->publisher) ? $parsedobject->publisher : null;
        $published = isset($parsedobject->date) ? $parsedobject->date : null;
        $series = null; // TODO in later version
        $isbn10 = isset($parsedobject->isbn10) ? $parsedobject->isbn10 : null;
        $isbn10 = preg_replace("/[^0-9Xx]/", "", $isbn10);
        $isbn13 = isset($parsedobject->isbn13) ? $parsedobject->isbn13 : null;
        $isbn13 = preg_replace("/[^0-9Xx]/", "", $isbn13);
        $issn = isset($parsedobject->issn) ? $parsedobject->issn : null;
        $coverpath = null;
        $description = isset($parsedobject->summary) ? $parsedobject->summary : null;
        $linktoread = isset($parsedobject->url) ? $parsedobject->url : null;
        $format = null; // TODO in later version
        $links = 0;

        // TODO in later versions remove this ugly workaround
        $title = preg_replace('/@/', '', $title);
        $subtitle = preg_replace('/@/', '', $subtitle);

        return new literature_dbobject_literature($id, $type, $title, $subtitle, $authors, $publisher, $published,
                        $series, $isbn10, $isbn13, $issn, $coverpath, $description, $linktoread, $format, $titlelink, $links);
    }

    /**
     * Splites the subfields in an array
     * @param string $datastring String with the subfields
     * @param array $map The map for the field
     * @return array An string array containing the subfields
     */
    private function split_data_string($datastring, $map) {

        foreach ($map as $key => $value) {
            $datastring = str_replace($key, '\#' . $key, $datastring);
        }

        $fields = explode('\#', $datastring);
        array_shift($fields);

        $result = array();
        foreach ($fields as $field) {
            $key = substr($field, 0, 2);
            $result[$key] = substr($field, 2);
        }

        return $result;
    }

    /**
     * Extract isbn from string
     * @param string $isbns
     * @return stdClass with isbn10 or isbn13 as attribute if found
     */
    private function get_isbns($isbns) {

        $result = new stdClass();

        $result->isbn10 = null;
        $result->isbn13 = null;

        $cleanisbn = preg_replace("/[^0-9Xx]/", '', $isbns);
        if (strlen($cleanisbn) == 10) {
            $result->isbn10 = $cleanisbn;
        } else if (strlen($cleanisbn) == 13) {
            $result->isbn13 = $cleanisbn;
        }

        return $result;
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
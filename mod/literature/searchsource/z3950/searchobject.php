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


require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/parser/marc21xml/parser.php');
require_once('dbobject.php');

/**
 * The search object for the "z3950" searchsources
 *
 * Implements the search logic
 *
 * @package    mod_literature_searchsource
 * @subpackage z3950
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_z3950_searchobject {

    public function __construct($id = null) {

        if ($id != null) {

            if (!$info = literature_searchsource_load_info($id)) {
                print_error('error:searchsource:configrefnotfound', 'literature');
            }

            if (!$source = literature_searchsource_z3950_dbobject::load($info->instance)) {
                print_error('error:searchsource:confignotfound', 'literature');
            }

            $this->host = $source->host;

            if (!empty($source->user) && !empty($source->pass)) {
                $this->options = array('user' => $source->user, 'password' => $source->pass,'charset' => 'utf-8');
            } else {
                $this->options = array('charset' => 'marc-8');
            }
        }

        $this->syntax = 'MARC21';
    }

    /**
     * Search in the searchsource
     * @param stdClass $data The search data
     * @param int $from
     * @param int $to
     * @return array An array of literature_dbobject_literature objects
     */
    public function search($data, $from, $to) {

        $maxentries = $to - $from;

        // Extract searchdata
        $data0 = $data->search_group0;
        $data1 = (isset($data->search_group1)) ? $data->search_group1 : null;
        $data2 = (isset($data->search_group2)) ? $data->search_group2 : null;
        $data3 = (isset($data->search_group3)) ? $data->search_group3 : null;

        $searchdata = array($data0, $data1, $data2, $data3);

        $query = $this->parse_query($searchdata);

        $this->results = null;

        if (!$id = $this->connect()) {
            print_error('errorconnectingserver', 'searchsource_z3950');
        }

        if (!function_exists('yaz_syntax')) {
            print_error('yaznotinstalled', 'searchsource_z3950');
        }

        yaz_syntax($id, $this->syntax);

        yaz_range($id, 1, $maxentries);

        yaz_search($id, "rpn", $query);
        
        yaz_wait();

        if (yaz_errno($id)) {
            $error = yaz_error($id);
            print_error($error);
            return false;
        } else {

            for ($i = $from; $i <= $maxentries; $i++) {

                $records[] = yaz_record($id, $i, "xml; charset=marc-8,utf-8");
            }

            foreach ($records as $record) {

                if ($record) {
                    $xmlrecord = new SimpleXMLElement($record);
                    $parser = new literature_parser_marc21xml();
                    $result = $parser->parse($xmlrecord, null);
                    if($result) {
                        $this->results[] = $result;
                    }
                }
            }

            return $this->results;
        }
    }

    /**
     * Connect to yaz server
     */
    private function connect() {
        if (!function_exists('yaz_connect')) {
            print_error('yaznotinstalled', 'searchsource_z3950');
        }
        $id = yaz_connect($this->host, $this->options);
        if (!$id) {
            return false;
        }
        return $id;
    }

    /**
     * Build query from search data
     * @param array $data The field array from the search form
     * @return strin query
     */
    private function parse_query($data) {

        $validfields = array();

        $operators = "";
        $attributes = "";

        // Get valid fields
        for ($i = 0; $i < 4; $i++) {
            if (!empty($data[$i]['search_field'])) {
                $field = $data[$i];
                $field['group'] = $i;
                $validfields[] = $field;
            }
        }

        if (count($validfields) < 1) {
            print_error('error:noterm', 'literature');
        }

        if (count($validfields) == 1) { // no operators
            $code = $validfields[0]['field_type'];
            $text = $validfields[0]['search_field'];

            list($operators, $attributes) = $this->split_search_string($text, $code);

            $query = '@attrset bib-1 ' . $operators . $attributes;
        } else if (count($validfields) > 1) { // operators
            for ($i = 0; $i < count($validfields); $i++) {

                $field = $validfields[$i];

                list($splitoperators, $splitattributes) = $this->split_search_string($field['search_field'], $field['field_type']);

                if ($i != count($validfields) - 1) {
                    $operators = $splitoperators . $field['field_connect'] . $operators;
                }

                $attributes .= ' ' . $splitattributes;
            }

            $query = '@attrset bib-1 ' . $operators . ' ' . $attributes;
        } else { // no valid fields --> print error
            $query = false;
        }
        return $query;
    }

    /**
     * Split the search terms
     * @param string $text Search terms
     * @param int $code The code of the index
     * @return array An array of querys for each term
     */
    private function split_search_string($text, $code) {

        $words = explode(' ', $text);
        $wordcount = count($words);

        if ($wordcount > 1) {

            $counter = 0;
            $operators = '';
            $attributes = '';

            foreach ($words as $word) {

                $counter++;

                if ($counter < $wordcount) {
                    $operators = '@and ' . $operators;
                }
                $attributes .= '@attr 1=' . $code . ' ' . $word . ' ';
            }

            $queryarray = array($operators, $attributes);
        } else {

            $queryarray = array('', '@attr 1=' . $code . ' ' . $text);
        }

        return $queryarray;
    }

}
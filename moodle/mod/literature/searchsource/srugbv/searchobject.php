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


require_once(dirname(dirname(dirname(__FILE__))) . '/parser/marc21xml/parser.php');
require_once('lib.php');
require_once('dbobject.php');

/**
 * The search object for the "srugbv" searchsources
 *
 * Implements the search logic
 *
 * @package    mod_literature_searchsource
 * @subpackage srugbv
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_srugbv_searchobject {

    /**
     * The sru server for the literature search
     *
     * Is set to "http://sru.gbv.de/gvk"
     * @var string
     */
    private $server = 'http://sru.gbv.de/gvk';

    /**
     * Load the available indizes from the server
     * @return array An array with the different indzes
     */
    public function get_index_info() {

        $url = $this->server . '?operation=explain';
        $xml = simplexml_load_file($url, null, null, 'http://www.loc.gov/zing/srw/');
        $data = $xml->record->recordData;
        $xmldata = $data->children('http://explain.z3950.org/dtd/2.0/');
        $indexinfo = $xmldata->explain->indexInfo;
        $indices = array();
        foreach ($indexinfo->index as $index) {
            $entry = new stdClass();
            $entry->title = $index->title;
            $entry->name = $index->map->name;
            $entry->set = $index->map->name->attributes()->set;
            $indices[] = $entry;
        }

        return $indices;
    }

    /**
     * Search in the searchsource
     * @param stdClass $data The search data
     * @param int $from
     * @param int $to
     * @return array An array of literature_dbobject_literature objects
     */
    public function search($data, $from, $to) {

        $data0 = $data->search_group0;
        $data1 = (isset($data->search_group1)) ? $data->search_group1 : null;
        $data2 = (isset($data->search_group2)) ? $data->search_group2 : null;
        $data3 = (isset($data->search_group3)) ? $data->search_group3 : null;
        $searchdata = array($data0, $data1, $data2, $data3);

        $sourceid = $data->source;

        if (!$sourceinfo = literature_searchsource_load_info($sourceid)) {
            print_error('error:searchsource:configrefnotfound', 'literature');
        }

        if (!$source = literature_searchsource_srugbv_dbobject::load($sourceinfo->instance)) {
            print_error('error:searchsource:confignotfound', 'literature');
        }

        $query = $this->build_query($searchdata, $data->set, $source->bibcode);
        if ($query) {
            $maxentry = $to - $from;
            $url = $this->server . '?version=1.1&operation=searchRetrieve&startRecord=' . $from .
                    '&maximumRecords=' . $maxentry . '&query=' . $query;
        }

        $xml = new SimpleXMLElement($url, 0, true, 'http://www.loc.gov/zing/srw/');
        $parser = new literature_parser_marc21xml();
        $results = array();

        if (!empty($xml->records->record)) {

            foreach ($xml->records->record as $record) {

                $recorddata = $record->recordData->children('http://www.loc.gov/MARC21/slim');
                $ppn = $recorddata->record->controlfield[0];
                $titlelink = 'http://gso.gbv.de/PPNSET?PPN=' . $ppn;
                $results[] = $parser->parse($recorddata->record, $titlelink);
            }
        }

        return $results;
    }

    /**
     * Build the query for the search
     * @param stdClass $searchdata The data from the search form
     * @param string $set Identifier of the index set
     * @param string $bibcode The bibcode of the bib in which to search
     * @return string The search query
     */
    private function build_query($searchdata, $set, $bibcode) {

        $validfields = array();

        $query = '';

        // Get valid fields
        for ($i = 0; $i < 4; $i++) {
            if (!empty($searchdata[$i]['search_field'])) {
                $field = $searchdata[$i];
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

            $query = $set . '.' . $code . '%3D' . urlencode($text);
        } else if (count($validfields) > 1) { // operators

            for ($i = 0; $i < count($validfields); $i++) {

                $field = $validfields[$i];

                if ($i != 0) {
                    $lastfield = $validfields[$i - 1];
                    $query .= '+' . $lastfield['field_connect'] . '+' . $set . '.' . $field['field_type'] . '%3D' . urlencode($field['search_field']);
                } else {
                    $query .= $set . '.' . $field['field_type'] . '%3D' . urlencode($field['search_field']);
                }
            }
        } else { // no valid fields
            return false;
        }

        $bibcode = str_pad($bibcode, 4, '0', STR_PAD_LEFT);
        $query .= '+and+pica.bib%3D' . $bibcode;
        return $query;
    }

}
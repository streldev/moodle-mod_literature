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


require_once('lib.php');
require_once('dbobject.php');

/**
 * The search object for the "opacxml" searchsources
 *
 * Implements the search logic
 *
 * @package    mod_literature_searchsource
 * @subpackage opacxml
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_opacxml_searchobject {

    /**
     * The url of the opac server
     * @var string
     */
    private $server;

    /**
     * @param int $id
     */
    public function __construct($id) {

        if (!$info = literature_searchsource_load_info($id)) {
            print_error('error:searchsource:loadinfo', 'searchsource_opacxml');
        }
        if (!$source = literature_searchsource_opacxml_dbobject::load($info->instance)) {
            print_error('error:searchsource:loadinstance', 'searchsource_opacxml');
        }
        $this->server = $source->server;
    }

    /**
     * Search in a searchsource
     * @param stdClass $data The search data
     * @param int $from
     * @param int $to
     */
    public function search($data, $from, $to) {

        $query = $this->build_query($data, $from, $to);
        if ($query) {
            $url = $this->server . $query;
        } else {
            print_error('error:no:searchparameters', 'searchsource_opacxml');
        }

        $parserfile = dirname(dirname(dirname(__FILE__))) . '/parser/picaplus/parser.php';
        if (!file_exists($parserfile)) {
            print_error('error:parser:picanotfound', 'searchsource_opacxml');
        }

        require_once($parserfile);
        $parser = new literature_parser_picaplus();

        $xml = new SimpleXMLElement($url, 0, true, null);

        if (!isset($xml->SET)) {
            print_error('error:searchsource:config', 'searchsource_opacxml');
        }

        $results = array ();
        foreach ($xml->SET->SHORTTITLE as $shorttitle) {

            $ppn = $shorttitle->attributes()->PPN;
            $url = $this->server . '/XML=1/PRS=PP/PPN?PPN=' . $ppn;
            $content = file_get_contents($url);
            $entries = explode('<br />', $content);
            array_shift($entries);
            $len = count($entries) - 1;
            $string = $entries[$len];
            $strings = explode('</', $string);
            $entries[$len] = array_shift($strings);

            $titlelink = $this->server . '/PPN?PPN=' . $ppn;
            $result = $parser->parse($entries, $titlelink);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Build the search query
     * @param stdClass $searchdata The data from the search form
     * @param int $from
     * @param int $to
     */
    private function build_query($searchdata, $from = 1, $to = 10) {

        if (!empty($searchdata->searchfield)) {

            $query = '/XML=1.0/FRST=' . $from . '/LAST=' . $to . '/TTL=1/CMD?ACT=SRCHA&IKT=1016&SRT=RLV&TRM=' .
                     $searchdata->searchfield;
        } else {
            return false;
        }

        return $query;
    }

}

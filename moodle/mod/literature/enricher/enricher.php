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
 * Enricher Interface
 *
 * This interface have to be implemented by each subplugin of type "enricher"
 *
 * @package    mod_literature_enricher
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface literature_enricher {

    /**
     * Enrich the entries saved in the plugin
     * @param literature_dbobject_literature $literature The entry to enrich
     */
    public function enrich($literature);

    /**
     * Enrich the searchresults
     * @param literature_dbobject_literature $result The searchresult to enrich
     */
    public function enrich_preview($result);
}
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
 * Library for the searchsource "opacxml"
 *
 * Implements neccesary functions for subplugins of type "searchsource"
 *
 * @package    mod_literature_searchsource
 * @subpackage opacxml
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Process the form of searchsource view form
 * @param array $formdata The form data
 * @return array with the defaultvalues and a url for the form
 */
function literature_searchsource_opacxml_processform($formdata) {

    if (!empty($formdata['submitgroup']['save'])) {

        $source = new literature_searchsource_opacxml_dbobject();
        $source->name = trim($formdata['name']);
        $source->server = trim($formdata['server']);

        if ($formdata['id'] != -1) {
            $id = $formdata['id'];
            literature_searchsource_upddate($id, $source);
        } else {
            if (!$id = literature_searchsource_add('opacxml', $source)) {
                print_error('error:config:failedtoadd', 'searchsource_opacxml');
            }
        }

        $defaultvalues = new stdClass();
        $url = new moodle_url('/mod/literature/searchsource/index.php');
    } else {

        $defaultvalues = new stdClass();
        $url = new moodle_url('/mod/literature/searchsource/index.php');
    }

    return array($defaultvalues, $url);
}

/**
 * Make formdata for sarch form after quicksearch
 *
 * @param string $text The searchtems
 * @param int $sourceid The id of the source to search in
 * @param int $course The id of the course
 * @param int $section The id of the section
 * @return stdClass The data for the search form
 */
function literature_searchsource_opacxml_build_formdata($text, $sourceid, $course, $section) {

    $formdata = new stdClass();
    $formdata->searchfield = $text;
    $formdata->type = 'opacxml';
    $formdata->source = $sourceid;

    $formdata->course = $course;
    $formdata->section = $section;

    return $formdata;
}

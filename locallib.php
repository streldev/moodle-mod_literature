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
 * Internal library of functions for module literature
 *
 * All the literature specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('lib.php');
// Converter lib
require_once(dirname(__FILE__) . '/converter/lib.php');
// Enricher lib
require_once(dirname(__FILE__) . '/enricher/lib.php');
// Searchsource lib
require_once(dirname(__FILE__) . '/searchsource/lib.php');
// Literature dbobject
require_once(dirname(__FILE__) . '/dbobject/literature.php');

/**
 * Call the selected suplguin of type "searchsource" and search
 * 
 * @param stdClass $data The searchparameters
 * @param int $from
 * @param int $to
 * @return array of Literature objects @see literature_dbobject_literature
 */
function literature_search($data, $from, $to) {

    // Extract pluginname
    if (empty($data->type)) {
        print_error('error:searchsource:typemissing', 'literature');
    }
    $sourcetype = $data->type;

    // Check if file exists
    $filepath = dirname(__FILE__) . '/searchsource/' . $sourcetype . '/searchobject.php';
    if (!file_exists($filepath)) {
        print_error('error:searchsource:notinstalled', 'literature', null, $sourcetype);
    }

    require_once($filepath);

    // Make pluginclass
    $classname = 'literature_searchsource_' . $sourcetype . '_searchobject';
    $searchobject = new $classname($data->source);

    if (!$results = $searchobject->search($data, $from, $to)) {
        return array();
    }

    return $results;
}

//---------------------------------------------------------------------------------------
// Display in course

/**
 * Build list view of a literature item
 * @param literature_dbobject_literature $item The literature item
 * @return string A html element for the item
 */
function literature_view_list($item) {

    switch ($item->type) {

        case literature_dbobject_literature::BOOK :
            $html = literature_htmlfactory_book($item, true);
            break;

        case literature_dbobject_literature::ELECTRONIC :
            $html = literature_htmlfactory_electronic($item, true);
            break;

        default:
            $html = literature_htmlfactory_misc($item, true);
    }
    return format_text($html);
}

/**
 * Build full view of a literature item
 * @param literature_dbobject_literature $item The literature item
 * @return string A html element for the item
 */
function literature_view_full($item) {

    switch ($item->type) {

        case literature_dbobject_literature::BOOK :
            $html = literature_htmlfactory_book($item);
            break;

        case literature_dbobject_literature::BOOK :
            $html = literature_htmlfactory_electronic($item);
            break;

        default:
            $html = literature_htmlfactory_misc($item);
    }
    
    return format_text($html);
}

/**
 * HTML Factory for Literature items of type BOOK
 * 
 * @param literature_dbobject_literature $item The Literature object of type BOOK
 * @param boolean $short Build list view?
 * @param boolean $aslistelement Build as list element? (<li>...</li>)
 * @param boolean $addcheckbox Add a checkbox?
 */
function literature_htmlfactory_book($item, $short = false, $aslistelement = false, $addcheckbox = false) {
    global $CFG, $SESSION;

    // Sanitze the stuff
    literature_sanitize_class($item);
    
    // This is not unused!!!
    $shortfields = array('title', 'authors', 'published', 'publisher', 'isbn');

    if (!is_callable('inShortBook')) {

        /**
         * Check if attribute should be displayed
         * @param string $name Name of the attribute
         */
        function inShortBook($name) {
            global $shortfields, $short;

            if ($short) {
                return key_exists($name, $shortfields);
            } else {
                return true;
            }
        }

    }


    // Get coverpath
    if (!$short) {
        if ($item->coverpath) {
            $coverpath = $item->coverpath;
        } else {
            $coverpath = $CFG->wwwroot . '/mod/literature/pix/nocover.jpg';
        }
    }

    // Get Checkbox
    if ($addcheckbox) {

        if (isset($SESSION->literature_search_selected) && key_exists($item->id, $SESSION->literature_search_selected) && $SESSION->literature_search_selected[$item->id]) {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1" checked></input>';
        } else {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1"></input>';
        }
    }

    // If subtitle is set then concat with title
    $title = $item->title;
    if (!empty($item->subtitle)) {
        $title .= ' - ' . $item->subtitle;
    }

    // Get the isbns
    if (!empty($item->isbn10)) {
        if (!empty($item->isbn13)) {
            $isbn = $item->isbn10 . ', ' . $item->isbn13;
        } else {
            $isbn = $item->isbn10;
        }
    } else if (!empty($item->isbn13)) {
        $isbn = $item->isbn13;
    } else {
        $isbn = null;
    }

    // Build the html elements
    $html = '<div class="literature">';

    // Add checkbox
    if ($addcheckbox) {
        $html .= $checkbox;
    }

    if (!$short) {
        // Cover
        $image = '<div class="lit_image"><img src="' . $coverpath . '"></div>';
        $html .= $image;

        $html .= '<div class="data">';
    } else {
        $html .= '<div class="data_short">';
    }


    // Title
    if (isset($item->titlelink) && inShortBook('title')) {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b></span>' .
                '<a href="' . $item->titlelink . '" target="_blank">' . $title . '</a><br />';
    } else {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b>' . $title . '</span><br />';
    }
    $html .= $tit;

    // Authors
    if (isset($item->authors) && inShortBook('authors')) {
        $authors = '<span class="lit_author"><b>' . get_string('authors', 'literature') . '</b>' . $item->authors . '</span><br />';
        $html .= $authors;
    }

    // Publisher
    if (isset($item->publisher) && inShortBook('publisher')) {
        $publisher = '<span class="lit_published"><b>' . get_string('publisher', 'literature') . '</b>' . $item->publisher . '</span><br />';
        $html .= $publisher;
    }

    // Published
    if (isset($item->published) && inShortBook('published')) {
        $published = '<span class="lit_published"><b>' . get_string('published', 'literature') . '</b>' . $item->published . '</span><br />';
        $html .= $published;
    }

    // ISBN
    if (isset($isbn) && inShortBook('isbn')) {
        $isbn = '<span class="lit_published"><b>' . get_string('isbn', 'literature') . '</b>' . $isbn . '</span><br />';
        $html .= $isbn;
    }

    // Format
    if (isset($item->format) && inShortBook('format')) {
        $format = '<span class="lit_format"><b>' . get_string('format:', 'literature') . '</b>' . $item->format . '</span><br />';
        $html .= $format;
    }

    // Links
    if (!empty($item->links) && inShortBook('link')) {
        foreach($item->links as $link) {
             $html .= '<span class="lit_link"><b>' . get_string('link', 'literature') . '</b>' .
                '<a href="' . $link->url . '" target="_blank">' . $link->text . '</a></span><br />';
        }
    }

    // Description
    if (isset($item->description) && inShortBook('description')) {
        $description = '<span class="lit_description"><b>' . get_string('description:', 'literature') . '</b>' . $item->description . '</span><br />';
        $html .= $description;
    }

    $html .= '</div>';

    // Clear float from image
    if (!$short) {
        $clearfloat = '<div class="clear" />';
        $html .= $clearfloat;
    }

    // Main div end tag
    $html .= '</div><br />';

    // If item should be list element then add list element tags
    if ($aslistelement) {
        $html = '<li class="lit_entry">' .
                '<div class="lit_entry">' .
                $html .
                '</div>' .
                '</li>';
    }

    '<div class="lit_entry">' .
            '<input type="hidden" name="select[' . $item->id . ']" value="0" />';

    return $html;
}

/**
 * HTML Factory for Literature items of type ELECTRONIC
 *
 * @param literature_dbobject_literature $item The Literature object of type ELECTRONIC
 * @param boolean $short Build list view?
 * @param boolean $aslistelement Build as list element? (<li>...</li>)
 * @param boolean $addcheckbox Add a checkbox?
 */
function literature_htmlfactory_electronic($item, $short = false, $aslistelement = false, $addcheckbox = false) {
    global $CFG, $SESSION;
    
    // Sanitze the stuff
    literature_sanitize_class($item);

    // This is not unused!!!
    $shortfields = array('title', 'authors', 'published', 'publisher', 'isbn');

    if (!is_callable('inShortElectronic')) {

        /**
         * Check if attribute should be displayed
         * @param string $name Name of the attribute
         */
        function inShortElectronic($name) {
            global $shortfields, $short;

            if ($short) {

                return key_exists($name, $shortfields);
            } else {
                return true;
            }
        }

    }


    // Get coverpath
    if (!$short) {
        if ($item->coverpath) {
            $coverpath = $item->coverpath;
        } else {
            $coverpath = $CFG->wwwroot . '/mod/literature/pix/nocover.jpg';
        }
    }

    // Get Checkbox
    if ($addcheckbox) {

        if (isset($SESSION->literature_search_selected) && key_exists($item->id, $SESSION->literature_search_selected) && $SESSION->literature_search_selected[$item->id]) {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1" checked></input>';
        } else {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1"></input>';
        }
    }

    // If subtitle is set then concat with title
    $title = $item->title;
    if (!empty($item->subtitle)) {
        $title .= ' - ' . $item->subtitle;
    }

    // Get the isbns
    if (!empty($item->isbn10)) {
        if (!empty($item->isbn13)) {
            $isbn = $item->isbn10 . ', ' . $item->isbn13;
        } else {
            $isbn = $item->isbn10;
        }
    } else if (!empty($item->isbn13)) {
        $isbn = $item->isbn13;
    } else {
        $isbn = null;
    }

    // Build the html elements
    $html = '<div class="literature">';

    // Add checkbox
    if ($addcheckbox) {
        $html .= $checkbox;
    }

    // Cover
    if (!$short) {
        $image = '<div class="lit_image"><img src="' . $coverpath . '"></div>';
        $html .= $image;
        $html .= '<div class="data">';
    } else {
        $html .= '<div class="data_short">';
    }




    // Title
    if (isset($item->titlelink) && inShortElectronic('title')) {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b></span>' .
                '<a href="' . $item->titlelink . '" target="_blank">' . $title . '</a><br />';
    } else {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b>' . $title . '</span><br />';
    }
    $html .= $tit;

    // Authors
    if (isset($item->authors) && inShortElectronic('authors')) {
        $authors = '<span class="lit_author"><b>' . get_string('authors', 'literature') . '</b>' . $item->authors . '</span><br />';
        $html .= $authors;
    }

    // Publisher
    if (isset($item->publisher) && inShortElectronic('publisher')) {
        $publisher = '<span class="lit_published"><b>' . get_string('publisher', 'literature') . '</b>' . $item->publisher . '</span><br />';
        $html .= $publisher;
    }

    // Published
    if (isset($item->published) && inShortElectronic('published')) {
        $published = '<span class="lit_published"><b>' . get_string('published', 'literature') . '</b>' . $item->published . '</span><br />';
        $html .= $published;
    }

    // ISBN
    if (isset($isbn) && inShortElectronic('isbn')) {
        $isbn = '<span class="lit_published"><b>' . get_string('isbn', 'literature') . '</b>' . $isbn . '</span><br />';
        $html .= $isbn;
    }

    // Format
    if (isset($item->format) && inShortElectronic('format')) {
        $format = '<span class="lit_format"><b>' . get_string('format:', 'literature') . '</b>' . $item->format . '</span><br />';
        $html .= $format;
    }

    // Links
    if (!empty($item->links) && inShortElectronic('link')) {
        foreach($item->links as $link) {
             $html .= '<span class="lit_link"><b>' . get_string('link', 'literature') . '</b>' .
                '<a href="' . $link->url . '" target="_blank">' . $link->text . '</a></span><br />';
        }
    }

    // Description
    if (isset($item->description) && inShortElectronic('description')) {
        $description = '<span class="lit_description"><b>' . get_string('description:', 'literature') . '</b>' . $item->description . '</span><br />';
        $html .= $description;
    }

    $html .= '</div>';


    // Clear float from image
    if (!$short) {
        $clearfloat = '<div class="clear" />';
        $html .= $clearfloat;
    }

    // Main div end tag
    $html .= '</div><br />';

    // If item should be list element then add list element tags
    if ($aslistelement) {
        $html = '<li class="lit_entry">' .
                '<div class="lit_entry">' .
                $html .
                '</div>' .
                '</li>';
    }

    return $html;
}

/**
 * HTML Factory for Literature items of type MISC
 *
 * @param literature_dbobject_literature $item The Literature object of type MISC
 * @param boolean $short Build list view?
 * @param boolean $aslistelement Build as list element? (<li>...</li>)
 * @param boolean $addcheckbox Add a checkbox?
 */
function literature_htmlfactory_misc($item, $short = false, $aslistelement = false, $addcheckbox = false) {
    global $CFG, $SESSION;
    
    // Sanitze the stuff
    literature_sanitize_class($item);

    // This is not unused!!!
    $shortfields = array('title', 'authors', 'published', 'publisher', 'isbn');

    if (!is_callable('inShortMisc')) {

        /**
         * Check if attribute should be displayed
         * @param string $name Name of the attribute
         */
        function inShortMisc($name) {
            global $shortfields, $short;

            if ($short) {

                return key_exists($name, $shortfields);
            } else {
                return true;
            }
        }

    }


    // Get coverpath
    if (!$short) {
        if ($item->coverpath) {
            $coverpath = $item->coverpath;
        } else {
            $coverpath = $CFG->wwwroot . '/mod/literature/pix/nocover.jpg';
        }
    }

    // Get Checkbox
    if ($addcheckbox) {

        if (isset($SESSION->literature_search_selected) && key_exists($item->id, $SESSION->literature_search_selected) && $SESSION->literature_search_selected[$item->id]) {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1" checked></input>';
        } else {
            $checkbox = '<input type="checkbox" name="select[' . $item->id . ']" value="1"></input>';
        }
    }

    // If subtitle is set then concat with title
    $title = $item->title;
    if (!empty($item->subtitle)) {
        $title .= ' - ' . $item->subtitle;
    }

    // Get the isbns
    if (!empty($item->isbn10)) {
        if (!empty($item->isbn13)) {
            $isbn = $item->isbn10 . ', ' . $item->isbn13;
        } else {
            $isbn = $item->isbn10;
        }
    } else if (!empty($item->isbn13)) {
        $isbn = $item->isbn13;
    } else {
        $isbn = null;
    }

    // Build the html elements
    $html = '<div class="literature">';

    // Add checkbox
    if ($addcheckbox) {
        $html .= $checkbox;
    }

    // Cover
    if (!$short) {
        $image = '<div class="lit_image"><img src="' . $coverpath . '"></div>';
        $html .= $image;
        $html .= '<div class="data">';
    } else {
        $html .= '<div class="data_short">';
    }

    

    // Title
    if (isset($item->titlelink) && inShortMisc('title')) {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b></span>' .
                '<a href="' . $item->titlelink . '" target="_blank">' . $title . '</a><br />';
    } else {
        $tit = '<span class="lit_title"><b>' . get_string('title', 'literature') . '</b>' . $title . '</span><br />';
    }
    $html .= $tit;

    // Authors
    if (isset($item->authors) && inShortMisc('authors')) {
        $authors = '<span class="lit_author"><b>' . get_string('authors', 'literature') . '</b>' . $item->authors . '</span><br />';
        $html .= $authors;
    }

    // Publisher
    if (isset($item->publisher) && inShortMisc('publisher')) {
        $publisher = '<span class="lit_published"><b>' . get_string('publisher', 'literature') . '</b>' . $item->publisher . '</span><br />';
        $html .= $publisher;
    }

    // Published
    if (isset($item->published) && inShortMisc('published')) {
        $published = '<span class="lit_published"><b>' . get_string('published', 'literature') . '</b>' . $item->published . '</span><br />';
        $html .= $published;
    }

    // ISBN
    if (isset($isbn) && inShortMisc('isbn')) {
        $isbn = '<span class="lit_published"><b>' . get_string('isbn', 'literature') . '</b>' . $isbn . '</span><br />';
        $html .= $isbn;
    }

    // Format
    if (isset($item->format) && inShortMisc('format')) {
        $format = '<span class="lit_format"><b>' . get_string('format:', 'literature') . '</b>' . $item->format . '</span><br />';
        $html .= $format;
    }

    // Links
    if (!empty($item->links) && inShortMisc('link')) {
        foreach($item->links as $link) {
             $html .= '<span class="lit_link"><b>' . get_string('link', 'literature') . '</b>' .
                '<a href="' . $link->url . '" target="_blank">' . $link->text . '</a></span><br />';
        }
    }

    // Description
    if (isset($item->description) && inShortMisc('description')) {
        $description = '<span class="lit_description"><b>' . get_string('description:', 'literature') . '</b>' . $item->description . '</span><br />';
        $html .= $description;
    }

    $html .= '</div>';

    // Clear float from image
    if (!$short) {
        $clearfloat = '<div class="clear" />';
        $html .= $clearfloat;
    }

    // Main div end tag
    $html .= '</div><br />';

    // If item should be list element then add list element tags
    if ($aslistelement) {
        $html = '<li class="lit_entry">' .
                '<div class="lit_entry">' .
                $html .
                '</div>' .
                '</li>';
    }

    return $html;
}

//---------------------------------------------------------------------------------------
// Print Lists and Literature

/**
 * Build the html view of the results
 * @param array $results Array of Literature objects @see literature_dbobject_literature
 * @param int $from Start with entry $from
 * @param int $count Display $count entries
 * @return string The html view of the results
 */
function literature_result_print($results, $from = 0, $count = 5) {
    global $SESSION;

    if (empty($results)) {
        return literature_html_build_list(array(), get_string('noresults'));
    }

    $html = '<div class="results">';


    $max = $from + $count;
    if (count($results) <= $max) {
        $max = count($results);
        // Set as last site
        $SESSION->literature_search_last = 1;
    } else {
        $SESSION->literature_search_last = 0;
    }

    $counter = 0;
    $htmllistitems = array();
    foreach ($results as $item) {

        // loop control
        if ($counter < $from) {
            $counter++;
            continue;
        } else if ($counter >= $max) {
            break;
        } else {
            $counter++;
        }

        switch ($item->type) {

            // Book
            case literature_dbobject_literature::BOOK :
                $htmllistitems[] = literature_htmlfactory_book($item, false, true, true);
                break;

            // Journal
            case literature_dbobject_literature::ELECTRONIC :
                $htmllistitems[] = literature_htmlfactory_electronic($item, false, true, true);
                break;

            // Misc
            default:
                $htmllistitems[] = literature_htmlfactory_misc($item, false, true, true);
        }
    }

    $html .= literature_html_build_list($htmllistitems);
    $html .= '</div>';

    return $html;
}

/**
 * Build html view of a list
 * @param array $items The list items
 * @param boolean $selectable Add a checkbox to each item?
 * @param int $start Start with item $start
 * @param int $end Show till item $end
 */
function literature_print_literaturelist($items, $selectable = true, $start = 0, $end = false) {

    if (empty($items)) {
        return literature_html_build_list(array(), get_string('nolist', 'literature'));
    }

    $end = ($end) ? $end : count($items) - 1;

    $htmllistitems = array();

    for ($i = $start; $i <= $end; $i++) {

        // If a item is empty, +1 to end // should not happen
        if (empty($items[$i])) {
            $end++;
            continue;
        }

        $item = $items[$i];

        switch ($item->type) {

            // Book
            case literature_dbobject_literature::BOOK :
                $htmllistitems[] = literature_htmlfactory_book($item, false, true, $selectable);
                break;

            // Journal
            case literature_dbobject_literature::ELECTRONIC :
                $htmllistitems[] = literature_htmlfactory_electronic($item, false, true, $selectable);
                break;

            // Misc
            default:
                $htmllistitems[] = literature_htmlfactory_misc($item, false, true, $selectable);
        }
    }

    return literature_html_build_list($htmllistitems);
}

/**
 * Build html view of list informations
 * @param array $listinfos Array of ListInfo objects @see literature_dbobject_listinfo
 * @param boolean $incourse In kontext moodle course?
 * @param int $course The course id
 * @param int $section The section id
 * @return string A html list with the informations
 */
function literature_print_listinfos($listinfos, $incourse, $course = null, $section = null) {

    // Build listitems -- item = literature list
    $items = literature_build_listitem($listinfos, $incourse, $course, $section, 'list_info');
    // Build list from items -- list = overview of literature lists
    $list = literature_html_build_list($items, get_string('nolists', 'literature'));
    // return list
    return $list;
}

/**
 * Build the html view of the listinfos
 * @param array $array Array of ListInfo objects @see literature_dbobject_listinfo
 * @param boolean $incourse In kontext course?
 * @param int $courseid The id of the course
 * @param int $section Thee id of the section
 * @param string $class Class parameter of the li object
 */
function literature_build_listitem($array, $incourse, $courseid, $section, $class='list_item') {
    global $CFG;
    $items = array();
    $counter = 0;
    
    literature_recurisve_sanitize($array);
    
    foreach ($array as $listentry) {

        $counter++;

        $item = '<li class="' . $class . '">' .
                '<div>' .
                '<input type="checkbox" name="select[' . $listentry->id . ']" value="1">';

        if ($incourse) {

            $item .= '<a href="' . $CFG->wwwroot . '/mod/literature/list/view.php?course=' . $courseid .
                    '&section=' . $section . '&id=' . $listentry->id . '">';
        } else {

            $item .= '<a href="' . $CFG->wwwroot . '/mod/literature/list/view.php?id=' . $listentry->id . '">';
        }

        $item .= '<span class="lit_listname">' . $listentry->name . '</span>' .
                '</a>';

        $date = userdate($listentry->created, get_string('strftimedate', 'langconfig'));
        $item .= '<br>' .
                '<b>' . get_string('created:', 'literature') . '</b>' . $date . '<br>' .
                '<b>' . get_string('description:', 'literature') . '</b>' . $listentry->description .
                // Thumbnails TODO in later version
                '</div>' .
                "</li>\n";

        $items[] = $item;
    }

    return $items;
}

/**
 * Save search results in db
 * 
 * @param array $results The search results
 * @return int The timestamp connected to the results
 */
function literature_db_insert_results($results) {
    global $DB, $USER;

    $table = 'literature_lit_temp';
    $tableLink = 'literature_links_temp';
    $userid = $USER->id;
    $timestamp = time();

    foreach ($results as $result) {

        $result->timestamp = $timestamp;
        $result->userid = $userid;
        literature_enricher_enrich_preview($result);
        $id = $DB->insert_record($table, $result);
        if (!$id) {
            // TODO warning
            continue;
        }
        // Insert links
        foreach ($result->links as $link) {
            $link->lit_id = $id;
            if(!$DB->insert_record($tableLink, $link)) {
                // TODO log error
            }
        }
    }

    return $timestamp;
}

/**
 * Load search results from db
 * @param int $timestamp The timestamp of the results
 */
function literature_db_load_results($timestamp) {
    global $DB, $USER;

    $table = 'literature_lit_temp';
    $results = $DB->get_records($table, array('userid' => $USER->id, 'timestamp' => $timestamp));
    
    $linkTable = 'literature_links_temp';
    foreach($results as $result) {
        $links = $DB->get_records($linkTable, array('lit_id' => $result->id));
        $result->links = $links;
    }
    return $results;
}

/**
 * Load search result by id from db
 * @param int $timestamp The timestamp of the result
 * @param int $id The id of the result
 */
function literature_db_load_result_by_id($timestamp, $id) {
    global $DB, $USER;

    $table = 'literature_lit_temp';
    $result = $DB->get_record($table, array('id' => $id, 'userid' => $USER->id, 'timestamp' => $timestamp));
    
    $linkTable = 'literature_links_temp';
    $links = $DB->get_records($linkTable, array('lit_id' => $result->id));
    $result->links = $links;

    return $result;
}

//###########################################################################//
//																			 //	
//		Helper Functions													 //
//																			 //
//###########################################################################//

/**
 * Make a html list 
 * @param array $htmllistitems Array of html <li> objects
 * @param string $message If $htmllistitems is empty $message is shown
 * @return string HTML list or message
 */
function literature_html_build_list($htmllistitems, $message = null) {

    // If no message is given use the standard message
    if (!$message) {
        $message = get_string('empty', 'literature');
    }

    if (empty($htmllistitems)) {

        return $message;
    }

    $htmllist = '<ul class="literature list">';

    foreach ($htmllistitems as $htmllistitem) {

        $htmllist .= $htmllistitem;
    }

    $htmllist .= '</ul>';
    return $htmllist;
}

/**
 * Make a Literature object form a stdClass with the same attributes
 * @param stdClass $item
 * @return literature_dbobject_literature
 */
function literature_cast_stdClass_literature($item) {
    
    $id = (!empty($item->id)) ? $item->id : null;
    $type = (!empty($item->type)) ? $item->type : null;
    $title = (!empty($item->title)) ? $item->title : null;
    $subtitle = (!empty($item->subtitle)) ? $item->subtitle : null;
    $authors = (!empty($item->authors)) ? $item->authors : null;
    $publisher = (!empty($item->publisher)) ? $item->publisher : null;
    $published = (!empty($item->published)) ? $item->published : null;
    $series = (!empty($item->series)) ? $item->series : null;
    $isbn10 = (!empty($item->isbn10)) ? $item->isbn10 : null;
    $isbn13 = (!empty($item->isbn13)) ? $item->isbn13 : null;
    $issn = (!empty($item->issn)) ? $item->issn : null;
    $coverpath = (!empty($item->coverpath)) ? $item->coverpath : null;
    $description = (!empty($item->description)) ? $item->description : null;
    $links = (!empty($item->links)) ? $item->links : array();
    $format = (!empty($item->format)) ? $item->format : null;
    $titlelink = (!empty($item->titlelink)) ? $item->titlelink : null;
    $refs = (!empty($item->refs)) ? $item->refs : 0;

    return new literature_dbobject_literature($id, $type, $title, $subtitle,
            $authors, $publisher, $published, $series, $isbn10, $isbn13,
            $issn, $coverpath, $description, $links, $format, $titlelink, $refs);
}



// The Sanitizer

function literature_sanitize_class(&$class) {
    
    $reflect = new ReflectionClass($class);
    $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    
    
    foreach ($props as $prop) {
        if($prop->isStatic()) {
            continue;
        }
        $propName = $prop->getName();
        $value = $class->$propName;
        literature_recurisve_sanitize($value);
        $class->$propName = $value;
    }

}

function literature_recurisve_sanitize(&$thing) {
     if(is_array($thing)) {
         foreach ($thing as $item) {
             literature_recurisve_sanitize($item);
         }
     } else if (is_object ($thing)) {
         $thing = literature_sanitize_class($thing);
     } else {
         $thing = format_string($thing);
     }
}
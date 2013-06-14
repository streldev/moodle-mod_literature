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
 * Searchsource Library
 *
 * Contains general functions for the "searchsource" subplugins
 *
 * @package    mod_literature_searchsource
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get infos about all installed searchsource subplugins
 *
 * @return multitype:string an assoc with entries of the type $dirname => $typename
 */
function literature_searchsource_get_types() {

    $basedir = dirname(__FILE__);
    $pattern = $basedir . '/*';
    $dirnames = glob($pattern, GLOB_ONLYDIR);

    $types = array();
    foreach ($dirnames as $dirname) {
        $dirname = basename($dirname);
        if (literature_searchsource_check($dirname)) {

            $identifier = 'searchsource_' . $dirname;
            $types[$dirname] = get_string('pluginname', $identifier);
        }
    }

    return $types;
}

/**
 * Validates a searchsoruce subplugin
 *
 * @param string $dirname the dirname of the suplugin
 * @return boolean True if folder contains a valid searchsouce subplugin; false otherwise
 */
function literature_searchsource_check($dirname) {

    $basedir = dirname(__FILE__);
    $plugindir = $basedir . '/' . $dirname;
    $searchfile = $plugindir . '/searchobject.php';
    $dbfile = $plugindir . '/dbobject.php';
    $langidentifier = 'searchsource_' . $dirname;
    $searchclass = 'literature_searchsource_' . $dirname . '_searchobject';
    $dbclass = 'literature_searchsource_' . $dirname . '_dbobject';
    $dbfunctions = array('insert', 'update', 'delete', 'load');

    // Check if searchfile exists
    if (!file_exists($searchfile)) {
        return false;
    }

    // Check if dbfile exists
    if (!file_exists($dbfile)) {
        return false;
    }

    // Check if pluginname exists in language file
    if ('[[ pluginname ]]' == get_string('pluginname', $langidentifier)) {
        return false;
    }

    // Check if searchclass exists
    require_once($searchfile);
    if (!class_exists($searchclass)) {
        return false;
    }

    // Check if dbclass exists
    require_once($dbfile);
    if (!class_exists($dbclass)) {
        return false;
    }

    // Check if function search exists in searchobject
    if (!method_exists($searchclass, 'search')) {
        return false;
    }

    // Check if all needed functions are implemented in the dbobject
    foreach ($dbfunctions as $functionname) {
        if (!method_exists($dbclass, $functionname)) {
            return false;
        }
    }

    return true;
}

/**
 * Delete a searchsource in db
 *
 * @param int $id The id of the searchsource
 * @return boolean true
 */
function literature_searchsource_delete($id) {
    global $DB;

    $table = 'literature_searchsource';
    if (!$sourceinfo = $DB->get_record($table, array('id' => $id))) {
        return true;
    }

    $dbobjectpath = $sourceinfo->type . '/dbobject.php';
    if (!file_exists($dbobjectpath)) {
        print_error('error:searchsource:dbobjectmissing', 'literature');
    }
    require_once($dbobjectpath);

    $dbobjectname = 'literature_searchsource_' . $sourceinfo->type . '_dbobject';
    $dbobjectname::delete($id);

    $result = $DB->delete_records($table, array('id' => $id));

    return $result;
}

/**
 * Add a searchsource in db
 *
 * @param string $type the type/dirname of the corresponding subplugin
 * @param class $dbobject The dbobject of the subplugin
 * @return boolean|int false or id
 */
function literature_searchsource_add($type, $dbobject) {
    global $DB;

    if (!$instanceid = $dbobject->insert()) {
        return false;
    }

    $table = 'literature_searchsource';

    $record = new stdClass();
    $record->type = $type;
    $record->instance = $instanceid;

    return $DB->insert_record($table, $record, true);
}

/**
 * Update a searchsource in db
 *
 * @param int $id The id of the searchsource
 * @param class $dbobject The dbobject of the searchsource
 */
function literature_searchsource_upddate($id, $dbobject) {

    if (!$info = literature_searchsource_load_info($id)) {
        print_error('error:searchsource:confignotfound', 'literature');
    }
    $dbobject->id = $info->instance;

    return $dbobject->update();
}

/**
 * Load the informations about a searchsource
 *
 * @param int $id The id of the searchsource
 * @return boolean|fieldset false or the informations about the searchsource. Following fields are in the
 * fieldset: id, type(dirname), instance(id of entry in the searchsource specific db table)
 */
function literature_searchsource_load_info($id) {
    global $DB;

    $table = 'literature_searchsource';

    return $DB->get_record($table, array('id' => $id));
}

/**
 * Get informations about the configured searchsources
 *
 * @return multiple:fieldsets containing the searchsource specific fields and the type of the searchsource
 *  of all available searchsources. The keys of the returned array are the ids of the searsources.
 */
function literature_searchsource_get_available() {
    global $DB;

    $table = 'literature_searchsource';

    $sourceinfos = $DB->get_records($table);

    $sources = array();
    foreach ($sourceinfos as $sourceinfo) {

        $dbname = 'searchsource_' . $sourceinfo->type;
        $source = $DB->get_record($dbname, array('id' => $sourceinfo->instance));
        $source->type = $sourceinfo->type;
        $sources[$sourceinfo->id] = $source;
    }
    return $sources;
}

/**
 * Load search form of a searchsource
 *
 * @param int $id The id of the searchsource
 * @return instance of the search form
 */
function literature_searchsource_load_searchform($id) {
    global $DB;

    $table = 'literature_searchsource';

    $sourceinfo = $DB->get_record($table, array('id' => $id));

    $formpath = $sourceinfo->type . '/search_form.php';
    $formname = 'literature_searchsource_' . $sourceinfo->type . '_search_form';

    require_once($formpath);

    $customdata = new stdClass();
    $customdata->id = $id;
    return new $formname('search.php?XDEBUG_PROFILE', $customdata);
}

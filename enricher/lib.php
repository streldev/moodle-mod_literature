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
 * Enricher Library
 *
 * This file contains general functions for the subplugins of type "enricher"
 *
 * @package    mod_literature_enricher
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get all folders of installed enrichers
 * @return multiple:string Dirnames of the installed enrichers
 */
function literature_enricher_get_folders() {

    $pattern = dirname(__FILE__) . '/*';
    $dirnames = glob($pattern, GLOB_ONLYDIR);
    $enricherdirs = array();
    foreach ($dirnames as $dirname) {
        $enricherdir = basename($dirname);
        if (literature_enricher_check($enricherdir)) {
            $enricherdirs[] = $enricherdir;
        }
    }
    return $enricherdirs;
}

/**
 * Check if a valid enricher contains a settings file
 * @param string $dirname The dirname of the enricher
 * @return True if settings file exist; false otherwise
 */
function literature_enricher_check_settings($dirname) {

	$pattern = dirname(__FILE__) . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'settings.php';
	return file_exists($pattern) ? true : false;
}

/**
 * Check if a given dir contains a valid enricher
 *
 * @param string $dirname The dirname of the enricher
 * @return boolean True if the dir contains a valid enricher; false otherwise
 */
function literature_enricher_check($dirname) {

    // check if file exists
    $filename = dirname(__FILE__) . '/' . $dirname . '/enricher.php';
    if (!file_exists($filename)) {
        return false;
    }

    // check if class exists
    require_once($filename);
    $classname = 'literature_enricher_' . $dirname;
    if (!class_exists($classname)) {
        return false;
    }

    // check if class extends the abstract class enricher
    $abstract = 'literature_enricher';
    $parentclass = get_parent_class($classname);
    if ($abstract !== $parentclass) {
        return false;
    }

    return true;
}

/**
 * Load enricher by name
 * @param string $name Name/dirname of the enricher
 * @return Instance of the enricher
 */
function literature_enricher_load_by_name($name) {

    $requiredfile = dirname(__FILE__) . '/' . $name . '/enricher.php';

    if (!file_exists($requiredfile)) {
        print_error('error:enricher:notinstalled', 'literature', null, $name);
    }

    require_once($requiredfile);
    $classname = 'literature_enricher_' . $name;
    if (!class_exists($classname)) {
        print_error('error:enricher:classnotfound', 'literature', null, $name);
    }
    $enricher = new $classname($classname);

    return $enricher;
}

/**
 * Enrich the literature
 * @param literature_dbobject_literature $literature
 */
function literature_enricher_enrich($literature) {
    global $CFG;

    $enricherdirs = literature_enricher_get_folders();
    foreach ($enricherdirs as $dirname) {

        $settingname = 'literature_enricher_' . $dirname;
        if (!isset($CFG->$settingname) || $CFG->$settingname == 1) {
            $enricher = literature_enricher_load_by_name($dirname);
            $enricher->enrich($literature);
        }
    }
}

/**
 * Enrich a searchresult
 * @param stdClass $result
 */
function literature_enricher_enrich_preview($result) {
    global $CFG;

    $enricherdirs = literature_enricher_get_folders();
    foreach ($enricherdirs as $dirname) {
        $name = basename($dirname);

        $settingname = 'literature_enricher_' . $name;
        if (!isset($CFG->$settingname) || $CFG->$settingname == 1) {
            $enricher = literature_enricher_load_by_name($name);
            $enricher->enrich_preview($result);
        }
    }
}

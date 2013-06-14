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
 * Converter Library
 *
 * The library file for the Converter Subplugins.
 *
 * @package    mod_literature_converter
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Check if dir contains a valid export class
 * @param string $dirname Dirname of the converter to check
 * @return boolean true if $dirname contains valid export class; false otherwise
 */
function literature_converter_export_check($dirname) {

    $fulldirpath = dirname(__FILE__) . '/' . $dirname;

    // check for export.php
    $filename = $fulldirpath . '/export.php';
    if (!file_exists($filename)) {
        return false;
    }

    // check if class exists
    require_once($filename);
    $classname = 'literature_conv_' . $dirname . '_export';
    if (!class_exists($classname)) {
        return false;
    }

    // check if class implements interface
    $interfacename = 'literature_conv_format_export';
    $interfaces = class_implements($classname);
    if (!in_array($interfacename, $interfaces)) {
        return false;
    }

    return true;
}

/**
 * Check if dir contains a valid import class
 *
 * @param string $dirname Dirname of the converter to check
 * @return boolean true if $dirname contains valid import class; false otherwise
 */
function literature_converter_import_check($dirname) {

    $fulldirpath = dirname(__FILE__) . '/' . $dirname;

    // check for import.php
    $filename = $fulldirpath . '/import.php';
    if (!file_exists($filename)) {
        return false;
    }

    require_once($filename);

    // check if class exists
    $classname = 'literature_conv_' . $dirname . '_import';
    if (!class_exists($classname)) {
        return false;
    }

    // check if class implements interface
    $interfacename = 'literature_conv_format_import';
    $interfaces = class_implements($classname);
    if (!in_array($interfacename, $interfaces)) {
        return false;
    }

    return true;
}

/**
 * Return the folders of converter subplugins
 * @return Array of all converter subplugin folders
 */
function literature_converter_get_folders() {

    $basedir = dirname(__FILE__);
    $pattern = $basedir . '/*';
    $fullpaths = glob($pattern, GLOB_ONLYDIR);
    $converterdirs = array();
    foreach ($fullpaths as $fullpath) {
        $converterdirs[] = basename($fullpath);
    }
    return $converterdirs;
}

/**
 * Return infos about installed import formats
 *
 * @return array An array of classes containing infos about the installed import formats
 */
function literature_converter_get_import_formats() {

    // Load installed converters
    $converterdirs = literature_converter_get_folders();

    // Check for each converter if a importer is implemented
    $importerdirs = array();
    foreach ($converterdirs as $dirname) {
        if (literature_converter_import_check($dirname)) {
            $importerdirs[] = $dirname;
        }
    }

    // Build the result
    $formatinfos = array();
    foreach ($importerdirs as $dirname) {
        // Load importer
        $importer = literature_converter_load_importer($dirname);
        if ($importer) {
            $importinfo = new stdClass();
            $importinfo->name = $importer->get_formatname();
            $importinfo->shortname = $dirname;
            $importinfo->extension = $importer->get_extension();
            $formatinfos[] = $importinfo;
        }
    }

    return $formatinfos;
}

/**
 * Return infos about installed export formats
 *
 * @return array An array of classes containing infos about the installed export formats
 */
function literature_converter_get_export_formats() {

    // Load installed converters
    $converterdirs = literature_converter_get_folders();
    // Check for each converter if a importer is implemented
    $exporterdirs = array();
    foreach ($converterdirs as $dirname) {
        if (literature_converter_export_check($dirname)) {
            $exporterdirs[] = $dirname;
        }
    }

    // Build the result
    $formatinfos = array();
    foreach ($exporterdirs as $dirname) {
        // Load importer
        $exporter = literature_converter_load_exporter($dirname);
        if ($exporter) {

            $exportinfo = new stdClass();
            $exportinfo->name = $exporter->get_formatname();
            $exportinfo->shortname = $dirname;
            $exportinfo->extension = $exporter->get_extension();
            $formatinfos[] = $exportinfo;
        }
    }

    return $formatinfos;
}

/**
 * Return the extensions of the installed exporters
 *
 * @return array An array of extensions of the installed exporters
 */
function literature_converter_get_import_extensions() {

    // Load installed converters
    $converterdirs = literature_converter_get_folders();

    // Check for each converter if a importer is implemented
    $importerdirs = array();
    foreach ($converterdirs as $dirname) {
        if (literature_converter_import_check($dirname)) {
            $importerdirs[] = $dirname;
        }
    }

    // Build the result
    $extensions = array();
    foreach ($importerdirs as $dirname) {
        // Load importer
        $importer = literature_converter_load_importer($dirname);
        if ($importer) {
            $extensions[] = $importer->get_extension();
        }
    }

    return $extensions;
}

/**
 * Load an exporter for a given format
 *
 * @param string $format The dirname of the format
 * @return boolean|class False if no valid exporter for the given format is installed;
 *  instance of the exporter otherwise
 */
function literature_converter_load_exporter($format) {

    // Check if exporter is valid
    $dirname = strtolower($format);
    if (!literature_converter_export_check($dirname)) {
        return false;
    }

    // Check and load exporter class
    $filename = dirname(__FILE__) . '/' . $dirname . '/export.php';
    require_once($filename);
    $convertername = 'literature_conv_' . $format . '_export';
    if (!class_exists($convertername)) {
        return false;
    }

    return new $convertername;
}

/**
 * Load an importer for a given format
 *
 * @param string $formatname The dirname/name of the format
 * @return boolean|class False if no valid importer for the given format is installed;
 *  instance of the importer otherwise
 */
function literature_converter_load_importer($formatname) {

    // Check if importer is installed
    $dirname = strtolower($formatname);
    if (!literature_converter_import_check($dirname)) {
        return false;
    }

    // Check and load importer class
    $filename = dirname(__FILE__) . '/' . $dirname . '/import.php';
    require_once($filename);
    $convertername = 'literature_conv_' . $formatname . '_import';
    if (!class_exists($convertername)) {
        return false;
    }
    return new $convertername;
}

/**
 * Return an instance of an importer supporting the given extension
 *
 * @param string $extension A fileextension of the form '.bib'
 * @return class|boolean If extension is supported the instance of an importer; false otherwise
 */
function literature_converter_load_importer_by_extension($extension) {

    $formats = literature_converter_get_import_formats();
    $found = false;

    foreach ($formats as $format) {
        $importer = literature_converter_load_importer($format->name);
        if ($importer->get_extension() == $extension) {
            $found = true;
            break;
        }
    }

    if ($found) {
        return $importer;
    } else {
        return false;
    }
}

/**
 * Serialize literature entries in a file
 *
 * @param literature_dbobject_literature[] $items An array of instances of the class literature_dbobject_literature
 * @param string $filename The filename of the target file
 * @param string $extension The fileextension of the target file
 * @return stored_file|boolean If succesful the stored_file; false otherwise
 */
function literature_converter_serialize_array($items, $filename, $extension) {
    global $USER;

    $formated = "";

    foreach ($items as $item) {

        foreach ($item as $line) {

            $formated .= $line . "\n";
        }

        $formated .= "\n";
    }

    $context = get_context_instance(CONTEXT_USER, $USER->id);

    $fs = get_file_storage();

    // Prepare file record object
    $fileinfo = array(
        'contextid' => $context->id, // ID of context
        'component' => 'mod_literature', // usually = table name
        'filearea' => 'export', // usually = table name
        'itemid' => 0, // usually = ID of row in table
        'filepath' => '/', // any path beginning and ending in /
        'filename' => $filename . $extension); // any filename
    // Delete if exists
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'],
            $fileinfo['filepath'], $fileinfo['filename']);
    if ($file) {
        $file->delete();
    }

    ob_clean();
    // Create file
    if (!$file = $fs->create_file_from_string($fileinfo, $formated)) {
        print_error('error:exporter:couldnotcreatefile', 'literature');
    }

    return $file;
}


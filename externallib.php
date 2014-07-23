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


require_once($CFG->libdir . "/externallib.php");
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/dbobject/literature.php');
require_once(dirname(__FILE__) . '/dbobject/literaturelist.php');
require_once(dirname(__FILE__) . '/dbobject/listinfo.php');

/**
 * Literature external file
 *
 * This file is part of the web service api and contains the functions
 * accessible by the web services
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_literature_external extends external_api {
    //----------------------------------------------------------------------------------
    //
    // Import Service
    //

    // Import Info Function

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_import_formats_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * The function itself
     * @return string welcome message
     */
    public static function get_import_formats() {

        $formats = literature_converter_get_import_formats();

        $results = array();
        foreach ($formats as $format) {
            $formatasarray = array();
            $formatasarray['name'] = $format->name;
            $formatasarray['shortname'] = $format->shortname;
            $formatasarray['extension'] = $format->extension;

            $results[] = $formatasarray;
        }
        return $results;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_import_formats_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                array(
            'name' => new external_value(PARAM_TEXT, 'The name of the format'),
            'shortname' => new external_value(PARAM_TEXT, 'The shortname of the format'),
            'extension' => new external_value(PARAM_TEXT, 'The file extension of the format')), 'Format'));
    }

    // Import Function

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function import_parameters() {

        return new external_function_parameters(
                array(
            'data' => new external_single_structure(
                    array(
                'name' => new external_value(PARAM_TEXT, 'Filename'),
                'extension' => new external_value(PARAM_TEXT, 'Extension'),
                'content' => new external_value(PARAM_TEXT, 'Content in base 64')
                    )
            )
                )
        );
    }

    /**
     * Import Literature
     * @param array $data The data to import
     * @return  array Status information
     */
    public static function import($data) {
        global $DB, $USER;

        $result = array();

        //Note: don't forget to validate the context and check capabilities
        // Validate the parameters
        if (empty($data['name']) || empty($data['content']) || empty($data['extension'])) {

            $result['code'] = -4;
            // TODO get_string
            $result['msg'] = 'Your request was not valid: A attribute of $data seems to be empty!';
            return $result;
        }

        // Get service id to lookup if user is allowed
        if (!$service = $DB->get_record('external_services', array('name' => 'Import Service'))) {

            $result['code'] = -7;
            // TODO get_string
            $result['msg'] = 'FATAL ERROR: It seems the DB entry of the service is missing!';
            return $result;
        }

        // Check if user is authorised to use webservice
        $conditions = array('externalserviceid' => $service->id, 'userid' => $USER->id);
        if (!$DB->record_exists('external_services_users', $conditions)) {

            $result['code'] = -8;
            $result['msg'] = 'User is not authorised to use Import Service';
            return $result;
        }

        // Decode filecontent
        $data['content'] = base64_decode($data['content']);

        // Import literature as list
        if (!$importer = literature_converter_load_importer_by_extension($data['extension'])) {

            $result['code'] = -1;
            // TODO get_string
            $result['msg'] = 'Format not Supported. Please check result of literature_mod_get_import_formats()!';
            return $result;
        }

        // Import Data
        if (!$literatures = $importer->import($data['content'])) {

            $result['code'] = -2;
            // TODO get_string
            $result['msg'] = 'File was not a valid ' . $importer->formatname . ' file!';
            return $result;
        }

        // Add literature list
        $created = time();
        $listinfo = new literature_dbobject_listinfo(null, $data['name'], $USER->id, $created, null, $created, 0);
        $list = new literature_dbobject_literaturelist($listinfo, $literatures);

        if (!$list->insert()) {

            $result['code'] = -3;
            // TODO get_string
            $result['msg'] = 'Error: Insert list to user failed. Pleas contact Developer or Admin!';
            return $result;
        }

        $result['code'] = 0;
        $result['msg'] = 'List ' . $data['name'] . ' was imported successful.';
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function import_returns() {
        return new external_single_structure(
                array(
            'code' => new external_value(PARAM_INT, 'Result code'),
            'msg' => new external_value(PARAM_TEXT, 'Result message')
                ), 'Result Message');
    }

    //----------------------------------------------------------------------------------
    //
    // Export
    //

    // Export Info Function

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_export_formats_parameters() {
        // FUNCTIONNAME_parameters() always return an external_function_parameters().
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
                // a external_description can be: external_value, external_single_structure or external_multiple structure
                array()
        );
    }

    /**
     * The function itself
     * @return string welcome message
     */
    public static function get_export_formats() {

        //Note: don't forget to validate the context and check capabilities

        $formats = literature_converter_get_export_formats();

        $results = array();
        foreach ($formats as $format) {
            $formatasarray = array();
            $formatasarray['name'] = $format->name;
            $formatasarray['shortname'] = $format->shortname;
            $formatasarray['extension'] = $format->extension;

            $results[] = $formatasarray;
        }
        return $results;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_export_formats_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                array(
            'name' => new external_value(PARAM_TEXT, 'The name of the format'),
            'shortname' => new external_value(PARAM_TEXT, 'The shortname of the format'),
            'extension' => new external_value(PARAM_TEXT, 'The file extension of the format')
                ), 'format')
        );
    }

    // Get User Lists Function

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_literature_lists_parameters() {

        return new external_function_parameters(
                array(
            'userid' => new external_value(PARAM_INT, 'Userid'),
                )
        );
    }

    /**
     * Get literature lits from user
     * @param int $userid The id of the user
     * @return array
     */
    public static function get_literature_lists($userid) {
        global $DB, $USER;

        $result = array();
        $result['code'] = 0;
        $result['msg'] = 'Success';
        $result['count'] = 0;
        $result['lists'] = array();

        $allowedlistinfos = array();

        //Check if User exists
        if (!$DB->record_exists('user', array('id' => $userid))) {

            $result['code'] = -1;
            // TODO get_string
            $result['msg'] = 'User not found!';
            return $result;
        }

        // User is list owner
        if ($USER->id == $userid) {
            $allowedlistinfos = literature_dbobject_listinfo::load_by_userid($USER->id);

            // Get public lists
        } else {
            $listinfos = $DB->get_records('literature_lists', array('userid' => $userid));

            foreach ($listinfos as $listinfo) {
                if ($listinfo->public) {
                    $allowedlistinfos[] = $listinfo;
                }
            }
        }

        $lists = array();
        foreach ($allowedlistinfos as $listinfo) {
            $allowedlist = array();
            $allowedlist['name'] = $listinfo->name;
            $allowedlist['desc'] = $listinfo->description;
            $allowedlist['created'] = $listinfo->created;

            $lists[$listinfo->id] = $allowedlist; // changed
        }
        $result['lists'] = $lists;
        $result['count'] = count($lists);

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_literature_lists_returns() {
        return new external_single_structure(
                array(
            'code' => new external_value(PARAM_INT, 'Return code'),
            'msg' => new external_value(PARAM_TEXT, 'Status message'),
            'count' => new external_value(PARAM_INT, 'Count of lists returned'),
            'lists' => new external_multiple_structure(
                    new external_single_structure(
                    array(
                'name' => new external_value(PARAM_TEXT, 'The name of the list'),
                'desc' => new external_value(PARAM_TEXT, 'The description of the list'),
                'created' => new external_value(PARAM_INT, 'The date the list was created (as timestamp)')
                    ), 'list')
            )
                )
        );
    }

    // Export Function
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function export_parameters() {

        return new external_function_parameters(
                array(
            'listid' => new external_value(PARAM_INT, 'Listid'),
            'format' => new external_value(PARAM_TEXT, 'Format')
                )
        );
    }

    /**
     * Export literature
     * @param int $listid
     * @param string $format
     * @return array
     */
    public static function export($listid, $format) {
        global $USER;

        $result = array();
        $result['code'] = null;
        $result['msg'] = null;
        $result['content'] = null;
        $result['filename'] = null;

        if (empty($listid) || empty($format)) {

            $result['code'] = -1;
            $result['msg'] = 'A parameter seems to be empty!';
            return $result;
        }

        if (!$list = literature_dbobject_literaturelist::load_by_id($listid)) {

            $result['code'] = -2;
            $result['msg'] = 'List with id ' . $listid . ' does not exist!';
            return $result;
        }

        //Check if user owns list or list is public
        if (!$list->info->public && !$list->userid == $USER->id) {

            $result['code'] = -4;
            $result['msg'] = 'Access denied!';
            return $result;
        }

        if (!$exporter = literature_converter_load_exporter($format)) {

            $result['code'] = -3;
            $result['msg'] = 'Export format ' . $format . ' is not supported!';
            return $result;
        }

        // Export
        $content = $exporter->export($list->items, null, false);
        // Encode
        $result['content'] = base64_encode($content);

        // Set resultdata
        $result['code'] = 0;
        $result['msg'] = 'List with id ' . $listid . ' successful exported.';
        $result['filename'] = $list->info->name . $exporter->extension;

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function export_returns() {

        return new external_single_structure(
                array(
            'code' => new external_value(PARAM_INT, 'Result code'),
            'msg' => new external_value(PARAM_TEXT, 'Result message'),
            'content' => new external_value(PARAM_TEXT, 'Filecontent'),
            'filename' => new external_value(PARAM_TEXT, 'Filename')
                ), 'Result Message');
    }

}

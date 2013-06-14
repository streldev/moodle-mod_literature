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
 * Script to display and process literature list import form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once('import_form.php');


require_login();
$context = context_user::instance($USER->id);
require_capability('mod/literature:manage', $context);

$url = new moodle_url('/mod/literature/list/import.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$mform = new literature_list_import_form();

$data = $mform->get_data();

if (empty($data)) {

    // Form was not submitted, display form

    // Extend navigation
    $node = $PAGE->navigation->find('literature_importlists', navigation_node::TYPE_CONTAINER);
    if ($node) {
        $node->make_active();
    }

    $PAGE->set_title(get_string('importlist', 'literature'));
    $PAGE->set_heading(get_string('importlist', 'literature'));
    $PAGE->set_pagelayout('standard');

    // Output page
    echo $OUTPUT->header();

    $mform->display();

    // Finish the page
    echo $OUTPUT->footer();


} else if ($mform->is_submitted()) {

    // Form submitted, process formdata

    $container = file_get_drafarea_files($data->import);
    if ($container) {

        foreach ($container->list as $fileinfo) {

            $infos = pathinfo($fileinfo->filename);
            $extension = $infos['extension'];
            $extension = '.' . $extension;

            // Load importer
            if (!$importer = literature_converter_load_importer_by_extension($extension)) {
                $a = new stdClass();
                $extensions = literature_converter_get_import_extensions();
                $a->extensions = '';
                foreach ($extensions as $ext) {
                    $a->extensions .= $ext . ' ';
                }
                $a->yourextension = $extension;
                print_error('error:importer:extensionnotsupported', 'literature', $PAGE->url, $a);
            }

            // Create List
            $desc = (empty($data->list_desc) ? null : $data->list_desc);
            $time = time();
            $listinfo = new literature_dbobject_listinfo(null, $data->list_name, $USER->id, $time, $desc, $time, 0);
            $list = new literature_dbobject_literaturelist($listinfo, array());
            if (!$listid = $list->insert()) {
                $name = $data->list_name;
                print_error('error:list:insert', 'literature', $PAGE->url, $name);
            }

            // workaround
            $args = explode('/', $fileinfo->url);

            $fs = get_file_storage();

            if (!$file = $fs->get_file($context->id, 'user', 'draft', $args[8], '/', $fileinfo->filename)) {
                literature_dbobject_literaturelist::del_by_id($listid);
                print_error('error:file:getafterupload', 'literature', $PAGE->url, $fileinfo);
            }

            $content = $file->get_content();
            if ($content) {
                $file->delete();
            } else {
                literature_dbobject_literaturelist::del_by_id($listid);
                print_error('error:file:emptycontent', 'literature', $PAGE->url, $fileinfo);
            }

            if (!$literatures = $importer->import($content)) {
                literature_dbobject_literaturelist::del_by_id($listid);
                print_error('error:importer:import', 'literature', $PAGE->url, $fileinfo);
            }

            $failedtoinsert = array();
            foreach ($literatures as $literature) {
                $litid = $literature->insert();
                if ($litid) {
                    if (!literature_dbobject_literaturelist::add_literature($listid, $litid)) {
                        $literature->delete();
                        $failedtoinsert[] = $literature;
                    }
                } else {
                    $failedtoinsert[] = $literature;
                }
            }

            if (count($failedtoinsert) > 0) {
                $errorurl = new moodle_url('/mod/literature/list/index.php');
                $titles = '';
                foreach ($failedtoinsert as $item) {
                    $titles .= $item->title . ', ';
                }
                print_error('error:lit:insertmultiple', 'literature', $errorurl, $titles);
            }
        }

        $url = new moodle_url('/mod/literature/list/index.php');
        redirect($url);
    }



} else {

    // Form was cancelled, rediret to list index.

    $url = new moodle_url('/mod/literature/list/index.php');
    redirect($url);
}

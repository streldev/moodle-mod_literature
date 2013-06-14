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
 * The script to display and process the literature import form
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once('import_form.php');


$listid = required_param('listid', PARAM_INT);

require_login();
$context = context_user::instance($USER->id);
require_capability('mod/literature:manage', $context);

$url = new moodle_url('/mod/literature/lit/import.php');
$url->param('listid', $listid);
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


$mform = new literature_lit_import_form();

// --------------------------------------------------------------------------------------
// Form was canncelled


if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('id', $listid);
    redirect($url);
}

// --------------------------------------------------------------------------------------
// Process formadata

if ($mform->is_submitted()) {

    $data = $mform->get_data();

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

            // workaround
            $args = explode('/', $fileinfo->url);

            $fs = get_file_storage();
            $file = $fs->get_file($context->id, 'user', 'draft', $args[8], '/', $fileinfo->filename);

            $content = $file->get_content();
            if ($content) {
                $file->delete();
            } else {
                print_error('error:file:emptycontent', 'literature', $PAGE->url, $fileinfo);
            }

            if (!$literatures = $importer->import($content)) {
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
                $titles = '';
                foreach ($failedtoinsert as $item) {
                    $titles .= $item->title . ', ';
                }
                print_error('error:lit:insertmultiple', 'literature', $PAGE->url, $titles);
            }
        }

        $url = new moodle_url('/mod/literature/list/view.php');
        $url->param('id', $listid);
        redirect($url);
    }
}


// Extend navigation
$node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER);
if ($node) {
    $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
    foreach ($listinfos as $listinfo) {
        $url = new moodle_url($CFG->wwwroot . '/mod/literature/list/view.php');
        $url->param('id', $listinfo->id);

        $listnode = $node->add(
                $listinfo->name, $url, navigation_node::TYPE_ACTIVITY
        );

        if ($listid == $listinfo->id) {
            $importnode = $listnode->add(
                    get_string('import', 'literature'), $PAGE->url, navigation_node::TYPE_ACTIVITY
            );
            $importnode->make_active();
        }
    }
}

$data = new stdClass();
$data->listid = $listid;

$PAGE->set_title(get_string('importlit', 'literature'));
$PAGE->set_heading(get_string('import', 'literature'));
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();

$mform->set_data($data);
$mform->display();

// Finish the page
echo $OUTPUT->footer();
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
 * Script to display and process literature list export form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once('export_form.php');


$url = new moodle_url('/mod/literature/list/export.php');
$PAGE->set_url($url);

require_login();
$context = context_system::instance();
require_capability('mod/literature:view', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


///////////////////////////////////////////////////////////////////////////////
// Process Form
///////////////////////////////////////////////////////////////////////////////

$mform = new literature_list_export_form();

if ($mform->is_cancelled()) {

    // Form was canceled, redirect to list index.
    $url = new moodle_url('/mod/literature/list/index.php');
    redirect($url);
}

if ($mform->is_submitted()) {

    // Form was submitted, process formdata
    $formdata = $mform->get_data();
    $format = $formdata->format;

    if (empty($SESSION->literature_listsselected)) {
        $errorurl = new moodle_url('/mod/literature/list/index.php');
        print_error('error:export:nolists', 'literature', $errorurl);
    }

    $listids = $SESSION->literature_listsselected;

    $lists = array();
    foreach ($listids as $listid) {

        $list = literature_dbobject_literaturelist::load_by_id($listid);

        if (!$list->info->public && $list->info->userid != $USER->id) {
            continue;
        }

        $lists[] = $list;
    }


    if (!$exporter = literature_converter_load_exporter($format)) {
        $errorurl = new moodle_url('/mod/literature/list/index.php');
        print_error('error:exporter:notinstalled', 'literature', $errorurl, $format);
    }


    // Export in one file
    if (!empty($_POST['inonefile'])) {

        $items = array();

        foreach ($lists as $list) {
            foreach ($list->items as $item) {
                $items[] = $item;
            }
        }

        $filename = 'export';
        if ($file = $exporter->export($items, $filename)) {
            ob_clean();
            send_stored_file($file, 10, 0, true);
        } else {
            $errorurl = new moodle_url('/mod/literature/list/index.php');
            print_error('error:exporter:export', 'literature', $errorurl);
        }

        // Export in one file per list
    } else {

        $files = array();
        foreach ($lists as $list) {

            $filename = str_replace(' ', '_', $list->info->name);
            if ($file = $exporter->export($list->items, $filename)) {
                $files[] = $file;
            } else {
                $errorurl = new moodle_url('/mod/literature/list/index.php');
                print_error('error:exporter:export', 'literature', $errorurl);
            }
        }

        $data = new stdClass();
        $data->files = $files;
    }

    foreach ($lists as $list) {

        $data->listinfos[] = $list->info;
    }




} else {

    // Form was not submitted, build new form

    if (empty($SESSION->literature_listsselected)) {
        print_error('error:session:nolists', 'literature');
    } else {

        $listids = $SESSION->literature_listsselected;
        $data = new stdClass();
        $data->listinfos = array();

        foreach ($listids as $listid) {

            $listinfo = literature_dbobject_listinfo::load_by_id($listid);

            if (!$listinfo->public && $listinfo->userid != $USER->id) {
                continue;
            }

            $data->listinfos[] = $listinfo;
        }
    }
}


////////////////////////////////////////////////////////////////////////////////
// Extend Navigation
////////////////////////////////////////////////////////////////////////////////

if ($node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER)) {
    $exportnode = $node->add(
            get_string('export', 'literature'), $PAGE->url, navigation_node::TYPE_ACTIVITY
    );
    $exportnode->make_active();
}


////////////////////////////////////////////////////////////////////////////////
// Display Form
////////////////////////////////////////////////////////////////////////////////

$mform = new literature_list_export_form(null, $data);

$PAGE->set_title(get_string('title:exportlists', 'literature'));
$PAGE->set_heading(get_string('export', 'literature'));
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();

$mform->display();

// Finish the page
echo $OUTPUT->footer();


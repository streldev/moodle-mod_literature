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
 * The script to display and process the literature export form
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/dbobject/literature.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/listinfo.php');
require_once('export_form.php');

$listid = required_param('listid', PARAM_INT);

require_login();
$context = context_user::instance($USER->id);
require_capability('mod/literature:view', $context);

$url = new moodle_url('/mod/literature/lit/export.php');
$url->param('listid', $listid);
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


$mform = new literature_lit_export_form();

if ($mform->is_cancelled()) {

    $url = new moodle_url('/mod/literature/list/view.php?id=' . $listid);
    redirect($url);
}

if ($mform->is_submitted()) {

    $format = required_param('format', PARAM_TEXT);
    $filename = required_param('filename', PARAM_TEXT);

    if (empty($SESSION->literature->litselected)) {

        $errorurl = new moodle_url('/mod/literature/list/index.php');
        print_error('error:nolitselected', 'literature', $errorurl);

    } else {

        $literature = array();
        foreach ($SESSION->literature->litselected as $litid) {

            if (!$literature[] = literature_dbobject_literature::load_by_id($litid)) {
                $errorurl = new moodle_url('/mod/literature/list/index.php');
                print_error('error:lit:loadfailed', 'literature', $errorurl, $litid); // TODO as notify in later version
            }
        }
    }

    $exporter = literature_converter_load_exporter($format);

    $file = $exporter->export($literature, $filename);
    if ($file) {
        send_stored_file($file, 0, null, true);
    } else {
        print_error('error:exporter:export', 'literature');
    }
}



if (empty($SESSION->literature->litselected)) {

    $errorurl = new moodle_url('/mod/literature/list/index.php');
    print_error('error:nolitselected', 'literature', $errorurl);

} else {

    $litids = $SESSION->literature->litselected;
    $data = new stdClass();
    $data->lit = array();

    foreach ($litids as $litid) {
        if (!$data->lit[] = literature_dbobject_literature::load_by_id($litid)) {
            $errorurl = new moodle_url('/mod/literature/list/index.php');
            print_error('error:lit:loadfailed', 'literature', $errorurl, $litid); // TODO as notify in later version
        }
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
            $exportnode = $listnode->add(
                    get_string('export', 'literature'), $PAGE->url, navigation_node::TYPE_ACTIVITY
            );
            $exportnode->make_active();
        }
    }
}

$mform = new literature_lit_export_form(null, $data);

$defaultvalues = new stdClass();
$defaultvalues->listid = $listid;

$PAGE->set_title(get_string('title:exportlit', 'literature'));
$PAGE->set_heading(get_string('export', 'literature'));
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();

$mform->set_data($defaultvalues);
$mform->display();

// Finish the page
echo $OUTPUT->footer();

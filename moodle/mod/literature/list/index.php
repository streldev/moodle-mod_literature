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
 * Script to display and process literature list overview form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/dbobject/listinfo.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once('index_form.php');


$url = new moodle_url('/mod/literature/list/index.php');
$PAGE->set_url($url);

require_login();
$context = context_user::instance($USER->id);
require_capability('mod/literature:view', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


////////////////////////////////////////////////////////////////////////////////
// Process formdata
////////////////////////////////////////////////////////////////////////////////
if (!empty($_POST)) {

    // Performe selected action
    if (!empty($_POST['btn_go'])) {


        $listids = (!empty($_POST['select'])) ? $_POST['select'] : null;

        switch ($_POST['act_select']) {

            case 'del' :

                require_capability('mod/literature:manage', $context);
                if ($listids != null) {

                    foreach ($listids as $id => $isselected) {
                        if ($isselected) {
                            literature_dbobject_literaturelist::del_by_id($id);
                        }
                    }
                } else {
                    $message = get_string('notify:nolistselected', 'literature');
                }
                break;

            case 'exp' :

                if ($listids != null) {
                    if (empty($SESSION->literature)) {
                        $SESSION->literature = new stdClass();
                    }
                    $SESSION->literature->listsselected = array();
                    foreach ($listids as $id => $isselected) {
                        if ($isselected) {
                            $SESSION->literature->listsselected[] = $id;
                        }
                    }

                    $url = new moodle_url('/mod/literature/list/export.php');
                    redirect($url);
                } else {
                    $message = get_string('notify:nolistselected', 'literature');
                }
                break;

            case 'imp' :

                $url = new moodle_url('/mod/literature/list/import.php');
                redirect($url);
                break;

            case 'add' :

                $url = new moodle_url('/mod/literature/list/add.php');
                redirect($url);
                break;

            default :
                $message = get_string('notify:novalidaction', 'literature');
        }
    }
}


////////////////////////////////////////////////////////////////////////////////
// Extend Navigation
// ATTENTION: This has to stay under the processing section because of mysterious sideefects
////////////////////////////////////////////////////////////////////////////////

if ($node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER)) {
    $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
    foreach ($listinfos as $listinfo) {
        $url = new moodle_url($CFG->wwwroot . '/mod/literature/list/view.php');
        $url->param('id', $listinfo->id);

        $node->add(
                $listinfo->name, $url, navigation_node::TYPE_ACTIVITY
        );
    }
}

////////////////////////////////////////////////////////////////////////////////
// Display form
////////////////////////////////////////////////////////////////////////////////

// Load own lists
$listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);

$data = new stdClass();
$data->listinfos = $listinfos;

$mform = new literature_list_index_form(null, $data);

// Set page data
$PAGE->set_title(get_string('title:listoverview', 'literature'));
$PAGE->set_heading(get_string('managelists', 'literature'));

// Output page
echo $OUTPUT->header();
if (!empty($message)) {
    echo $OUTPUT->notification($message);
}

$mform->display();

// Finish the page
echo $OUTPUT->footer();




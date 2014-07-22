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
 * Script to display and process literature list add form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('add_form.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');


$url = new moodle_url('/mod/literature/list/add.php');
$PAGE->set_url($url);


require_login();
$context = context_user::instance($USER->id);
require_capability('mod/literature:manage', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


$mform = new literature_add_form();

// Process formdata
if ($mform->is_submitted()) {

    $mform->is_validated();
    $formdata = $mform->get_data();

    if (!isset($formdata->btn_cancel)) {

        $desc = (empty($formdata->desc)) ? null : $formdata->desc;
        $time = time();
        $listinfo = new literature_dbobject_listinfo(null, $formdata->name, $USER->id, $time, $desc, $time, $formdata->public);
        if (!$listinfo->insert()) {
            $name = $formdata->name;
            print_error('error:list:insert', 'literature', $PAGE->url, $name);
        }

        if (isset($formdata->btn_saveandsearch)) {

            $url = new moodle_url('/mod/literature/lit/search.php');
            $url->param('search', 'false');
            $url->param('listid', $listinfo->id);
            redirect($url);
        } else {
            $url = new moodle_url('/mod/literature/list/index.php');
            redirect($url);
        }

    } else if (isset($formdata->btn_cancel)) {

        $url = new moodle_url('/mod/literature/list/index.php');
        redirect($url);

    } else {
        die(); // Unvalid submit of form
    }
}

// Extend navigation
$node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER);
if ($node) {
    $addnode = $node->add(get_string('addlist', 'literature'), $PAGE->url, navigation_node::TYPE_ACTIVITY);
    $addnode->make_active();
}

// Set page data
$PAGE->set_title(get_string('addlist', 'literature'));
$PAGE->set_heading(get_string('managelists', 'literature'));

// Output page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('addlist', 'literature'));

$mform->display();

// Finish the page
echo $OUTPUT->footer();



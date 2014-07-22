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
 * The script to display and process the configuration form for searchsources of type "z3950"
 *
 * @package    mod_literature_searchsource
 * @subpackage z3950
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');
require_once('view_form.php');
require_once('dbobject.php');

$id = optional_param('id', -1, PARAM_INT);

$url = new moodle_url('/mod/literature/searchsource/z3950/view.php');
$url->param('id', $id);
$PAGE->set_url($url);

require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

// Check if admin
$admins = get_admins();
$isadmin = false;
foreach ($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}
if (!$isadmin) {
    die();
}

$form = new literature_searchsource_z3950_form();


if ($form->is_cancelled()) {
    $url = new moodle_url('/mod/literature/searchsource/index.php');
    redirect($url);
} else if ($form->is_submitted()) {

    $data = $_POST;
    $data['id'] = $id;
    list($defaultvalues, $customdata, $url) = literature_searchsource_z3950_processform($data);

    if ($url) {
        redirect($url);
    }
} else if ($id != -1) {

    if (!$sourceinfo = literature_searchsource_load_info($id)) {
        $link = new moodle_url('/mod/literature/searchsource/index.php');
        print_error('error:searchsource:configrefnotfound', 'literature', $link);
    }
    if (!$source = literature_searchsource_z3950_dbobject::load($sourceinfo->instance)) {
        $link = new moodle_url('/mod/literature/searchsource/index.php');
        print_error('error:searchsource:confignotfound', 'literature', $link);
    }

    $defaultvalues = new stdClass();
    $defaultvalues->name = $source->name;
    $defaultvalues->host = $source->host;
    $defaultvalues->user = $source->user;
    $defaultvalues->password = $source->password;
    $defaultvalues->fieldgroup = array();

    $counter = 0;
    foreach ($source->profile as $profilefield) {

        $defaultvalues->fieldgroup[$counter]['code'] = $profilefield->code;
        $defaultvalues->fieldgroup[$counter]['text'] = $profilefield->text;
        $counter++;
    }


    $fields = count($defaultvalues->fieldgroup);
    if ($fields < 10) {
        $fields = 10;
    }

    $customdata = new stdClass();
    $customdata->fields = $fields;

    // Set page data
    $PAGE->set_title(get_string('editsource', 'searchsource_z3950'));
    $PAGE->set_heading($source->name);
} else {

    $customdata = new stdClass();
    $customdata->fields = 10;

    // Set page data
    $PAGE->set_title(get_string('editsource', 'searchsource_z3950'));
    $PAGE->set_heading(get_string('newsource', 'searchsource_z3950'));
}


// Extend Navigation
$node = $PAGE->navigation->find('literature_managesource', navigation_node::TYPE_CONTAINER);
if ($node) {
    $node->make_active();

    // Add Sources as childs
    $sources = literature_searchsource_get_available();
    foreach ($sources as $globalsrcid => $source) {
        $url = new moodle_url($CFG->wwwroot . '/mod/literature/searchsource/' . $source->type . '/view.php');
        $url->param('id', $globalsrcid);
        $sourcenode = $node->add(
                $source->name, $url, navigation_node::TYPE_ACTIVITY
        );
        if ($globalsrcid == $id) {
            $sourcenode->make_active();
        }
    }
}

$form = new literature_searchsource_z3950_form('view.php?id=' . $id, $customdata);


// Output page
echo $OUTPUT->header();

if (!empty($defaultvalues)) {
    $form->set_data($defaultvalues);
}
$form->display();


// Finish the page
echo $OUTPUT->footer();



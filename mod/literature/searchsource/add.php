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
 * Script to display and process the searchsource add form
 *
 * @package    mod_literature_searchsource
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once('add_form.php');


$url = new moodle_url('/mod/literature/searchsource/add.php');
$PAGE->set_url($url);

require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

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


$mform = new literature_search_add_form();
$data = $mform->get_data();

if ($mform->is_submitted()) {

    $type = $data->typegroup['sourcetype'];
    $url = new moodle_url('/mod/literature/searchsource/' . $type . '/view.php');
    redirect($url);
}

// Extend navigation
$node = $PAGE->navigation->find('literature_managesource', navigation_node::TYPE_CONTAINER);
if ($node) {

    $node->make_active();

    // Add Sources as childs
    $sources = literature_searchsource_get_available();
    foreach ($sources as $globalsrcid => $source) {
        $url = new moodle_url($CFG->wwwroot . '/mod/literature/searchsource/' . $source->type . '/view.php');
        $url->param('id', $globalsrcid);
        $node->add(
                $source->name, $url, navigation_node::TYPE_ACTIVITY
        );
    }
}

// Set page data
$PAGE->set_title(get_string('addsource', 'literature'));
$PAGE->set_heading(get_string('addsource', 'literature'));

// Output page
echo $OUTPUT->header();

$mform->display();

// Finish the page
echo $OUTPUT->footer();

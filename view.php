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
 * Prints a particular instance of literature
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/dbobject/literature.php');
require_once($CFG->dirroot . '/lib/formslib.php');

class literature_view_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('html', $this->_customdata);
    }

}

$id = required_param('id', PARAM_INT);


$cm = get_coursemodule_from_id('literature', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$literature = $DB->get_record('literature', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/literature:view', $context);


$lit = literature_dbobject_literature::load_by_id($literature->litid);


add_to_log($course->id, 'literature', 'view', "view.php?id={$cm->id}", $literature->name, $cm->id);


$PAGE->set_url('/mod/literature/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($literature->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


// Output starts here
echo $OUTPUT->header();

$html = literature_view_full($lit);
$form = new literature_view_form(null, $html);
$form->display();

// Finish the page
echo $OUTPUT->footer($course);




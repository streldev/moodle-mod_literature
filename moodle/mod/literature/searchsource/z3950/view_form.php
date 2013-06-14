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


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * The configuration form for searchsources of type "z3950"
 * *
 * @package    mod_literature_searchsource
 * @subpackage z3950
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_z3950_form extends moodleform {

    /**
     * @see moodleform::definition()
     */
    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('header', 'source', get_string('pluginname', 'searchsource_z3950'));

        // Name
        $mform->addElement('text', 'name', get_string('name', 'searchsource_z3950'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('name', 'help:name', 'searchsource_z3950');

        // Host
        $mform->addElement('text', 'host', get_string('host', 'searchsource_z3950'), array('size' => 35));
        $mform->addRule('host', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('host', 'help:host', 'searchsource_z3950');

        // User
        $mform->addElement('text', 'user', get_string('user', 'searchsource_z3950'));

        // Password
        $mform->addElement('password', 'password', get_string('password', 'searchsource_z3950'));

        // Profile
        $mform->addElement('header', 'profile', get_string('profile', 'searchsource_z3950'));

        if (!empty($data->fields)) {

            for ($i = 0; $i < $data->fields; $i++) {

                $fieldgroup = array();
                $fieldgroup[] = &$mform->createElement('text', 'code', null, array('size' => 8));
                $fieldgroup[] = &$mform->createElement('text', 'text');
                if ($i == 0) {
                    $mform->addGroup($fieldgroup, 'fieldgroup[' . $i . ']', get_string('default', 'searchsource_z3950'));
                    $mform->addHelpButton('fieldgroup[' . $i . ']', 'help:profile', 'searchsource_z3950');
                    $mform->addGroupRule('fieldgroup[0]', get_string('required'), 'required', null, 'client');
                } else {
                    $mform->addGroup($fieldgroup, 'fieldgroup[' . $i . ']');
                }
            }
        }

        $list = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5');
        $addgroup = array();
        $addgroup[] = $mform->createElement('select', 'count', null, $list);
        $addgroup[] = $mform->createElement('submit', 'addfield', get_string('addfield', 'searchsource_z3950'));
        $mform->addGroup($addgroup, 'addgroup');

        $mform->closeHeaderBefore('submitgroup');

        $submitgroup = array();
        $submitgroup[] = &$mform->createElement('submit', 'save', get_string('save', 'searchsource_z3950'));
        $submitgroup[] = &$mform->createElement('cancel');

        $mform->addGroup($submitgroup, 'submitgroup');
    }

}
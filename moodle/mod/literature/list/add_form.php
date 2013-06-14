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


require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * The list add form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_add_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // General
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name
        $mform->addElement('text', 'name', get_string('name'), array('size' => '40'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        // Description
        $mform->addElement('textarea', 'desc', get_string('description', 'literature'), array('rows' => 5, 'cols' => 90));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('desc', PARAM_TEXT);
        } else {
            $mform->setType('desc', PARAM_CLEANHTML);
        }

        // Is public?
        $mform->addElement('advcheckbox', 'public', get_string('ispublic', 'literature'), null, null, array(0, 1));
        $mform->addHelpButton('public', 'help:addlist:public', 'literature');

        $mform->closeHeaderBefore('btn_save');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'btn_save', get_string('save', 'literature'));
        $buttonarray[] = &$mform->createElement('submit', 'btn_saveandsearch', get_string('saveandsearch', 'literature'));
        $buttonarray[] = &$mform->createElement('submit', 'btn_cancel', get_string('cancel'));
        $mform->addGroup($buttonarray);
    }

}

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


require_once($CFG->libdir . '/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');

/**
 * The literature list view form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_list_view_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // Listinfo
        $mform->addElement('header', 'lit_list_header', get_string('listinfo', 'literature'));

        // Name
        $mform->addElement('text', 'name', get_string('name'), array('size' => '40'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        if (!empty($this->_customdata->name)) {
            $mform->setDefault('name', $this->_customdata->name);
        }

        // Description
        $mform->addElement('textarea', 'desc', get_string('description', 'literature'), array('rows' => 5, 'cols' => 90));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('desc', PARAM_TEXT);
        } else {
            $mform->setType('desc', PARAM_CLEANHTML);
        }
        if (!empty($this->_customdata->desc)) {
            $mform->setDefault('desc', $this->_customdata->desc);
        }

        // Is public?
        $mform->addElement('advcheckbox', 'public', get_string('ispublic', 'literature'), null, null, array(0, 1));
        $mform->addHelpButton('public', 'help:addlist:public', 'literature');
        if (!empty($this->_customdata->public)) {
            $mform->setDefault('public', $this->_customdata->public);
        }

        // Save
        $mform->addElement('submit', 'btn_save', get_string('save', 'literature'));

        // Items
        $mform->addElement('header', 'lit_items_header', get_string('lit', 'literature'));
        $list = literature_print_literaturelist($this->_customdata->content);
        $mform->addElement('html', $list);

        // Actions
        if ($this->_customdata->incourse) {

            $mform->addElement('header', 'lit_list_header', get_string('settings', 'literature'));
            $opt_list = get_string('view_as_list', 'literature');
            $opt_full = get_string('view_as_full', 'literature');
            $fields = array('0' => $opt_list, '1' => $opt_full);
            $mform->addElement('select', 'view', get_string('postas', 'literature'), $fields);
            $mform->closeHeaderBefore('btn_post');

            $mform->addElement('submit', 'btn_post', get_string('post', 'literature'));
        } else {

            $mform->addElement('header', 'act_header', get_string('actions', 'literature'));

            $items = array();
            $items['sel'] = get_string('actionsel', 'literature');
            $items['del'] = get_string('delete', 'literature');
            $items['imp'] = get_string('importlit', 'literature');
            $items['exp'] = get_string('export', 'literature');
            $items['add'] = get_string('addlit', 'literature');

            $actionarray = array();
            $actionarray[] = &$mform->createElement('select', 'act_select', null, $items);
            $actionarray[] = &$mform->createElement('submit', 'btn_go', get_string('ok'));

            $mform->addGroup($actionarray);
        }
    }

}

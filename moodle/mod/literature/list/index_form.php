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
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * The literature list overview form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_list_index_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'list_overview', get_string('lists', 'literature'));

        $lists = literature_print_listinfos($this->_customdata->listinfos, false);
        $mform->addElement('html', $lists);

        $mform->addElement('header', 'act_header', get_string('actions', 'literature'));

        $items = array();
        $items['sel'] = get_string('actionsel', 'literature');
        $items['del'] = get_string('delete', 'literature');
        $items['imp'] = get_string('importlist', 'literature');
        $items['exp'] = get_string('export', 'literature');
        $items['add'] = get_string('addlist', 'literature');

        $actionarray = array();
        $actionarray[] = &$mform->createElement('select', 'act_select', null, $items);
        $actionarray[] = &$mform->createElement('submit', 'btn_go', get_string('ok'));

        $mform->addGroup($actionarray);
    }

}
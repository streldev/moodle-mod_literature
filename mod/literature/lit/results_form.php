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


defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/listinfo.php');

/**
 * The search result form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_results_form extends moodleform {

    public function definition() {
        global $USER, $SESSION;

        $mform = $this->_form;

        // Load results
        if (isset($SESSION->literature_search_timestamp)) {
            $timestamp = $SESSION->literature_search_timestamp;
            $results = literature_db_load_results($timestamp);
        } else {
            print_error('error:search:timestampnotfound', 'literature');
        }

        // ------------------------------------------------------------------------------
        // View results
        $mform->addElement('header', 'results_header', get_string('results', 'literature'));
        if (isset($SESSION->literature_search_from) && $SESSION->literature_search_from != 0) {
            $value = get_string('prevresults', 'literature');
            $backbutton = '<center><div class="literature_control"><input type="submit" name="back" value="' . $value . '"></input></div></center>';
            $mform->addElement('html', $backbutton);
        }
        if (!isset($SESSION->literature_search_from)) {
            $list = literature_result_print($results);
        } else {
            $list = literature_result_print($results, $SESSION->literature_search_from);
        }
        $mform->addElement('html', $list);
        if (empty($SESSION->literature_search_last) || !$SESSION->literature_search_last) {
            $value = get_string('nextresults', 'literature');
            $nextbutton = '<center><div class="literature_control"><input type="submit" name="next" value="' . $value . '"></input></div></center>';
            $mform->addElement('html', $nextbutton);
        }

        if (!$this->_customdata->incourse) {

            // ------------------------------------------------------------------------------
            // Save
            $mform->addElement('header', 'result_save', get_string('actions', 'literature'));
            $inputarray0 = array();
            $inputarray0[] = &$mform->createElement('text', 'new_list_name', null, array('size' => '30'));
            $inputarray0[] = &$mform->createElement('submit', 'btn_new_list', get_string('addtonewlist', 'literature'));
            $mform->addGroup($inputarray0, 'new_list_group');
            $mform->setDefault('new_list_group[new_list_name]', get_string('new_list', 'literature'));

            $inputarray1 = array();
            $lists = literature_dbobject_listinfo::load_by_userid($USER->id);
            $listnames = $this->get_list_arrays($lists);

            if (!empty($lists)) {
                $inputarray1[] = &$mform->createElement('select', 'select_list', null, $listnames);
                $inputarray1[] = &$mform->createElement('submit', 'btn_existing_list', get_string('addtolist', 'literature'));
                $mform->addGroup($inputarray1, 'lists_group');

                if (!empty($this->_customdata->listid)) {

                    $listid = $this->_customdata->listid;
                    if (array_key_exists($listid, $listnames)) {

                        $mform->setDefault('lists_group[select_list]', $listid);
                    }
                }
            }
        } else {

            // ------------------------------------------------------------------------------
            // Post
            $mform->addElement('header', 'lit_list_header', get_string('settings', 'literature'));
            $opt_list = get_string('view_as_list', 'literature');
            $opt_full = get_string('view_as_full', 'literature');
            $fields = array('0' => $opt_list, '1' => $opt_full);
            $mform->addElement('select', 'view', get_string('postas', 'literature'), $fields);
            $mform->closeHeaderBefore('btn_post');

            $mform->addElement('submit', 'btn_post', get_string('post', 'literature'));
        }

        // Hidden
        $mform->addElement('hidden', 'incourse', $this->_customdata->incourse);
        $mform->addElement('hidden', 'source');
    }

    /**
     * Build assoc for select
     * @param array $lists
     * @return array
     */
    private function get_list_arrays($lists) {

        $listarray = array();

        foreach ($lists as $list) {
            $listarray[$list->id] = $list->name;
        }

        if (!count($listarray)) {
            $listarray[0] = get_string('nolists', 'literature');
        }

        return $listarray;
    }

}

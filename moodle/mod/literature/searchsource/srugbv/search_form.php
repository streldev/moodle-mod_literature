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
require_once('dbobject.php');
require_once('searchobject.php');
require_once('lib.php');

/**
 * The search form for searchsources of type "srugbv"
 *
 * @package    mod_literature_searchsource
 * @subpackage srugbv
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_srugbv_search_form extends moodleform {

    /**
     * @see moodleform::definition()
     */
    public function definition() {

        $mform = $this->_form;

        // ------------------------------------------------------------------------------
        // Adding the "search" fieldset
        $mform->addElement('header', 'search_header', get_string('search', 'literature'));

        $searchobject = new literature_searchsource_srugbv_searchobject();
        $indices = $searchobject->get_index_info();
        $indexset = $this->select_index_set($indices);
        $list = array();
        foreach ($indexset->content as $index) {
            $list[$index->name] = $index->title;
        }

        $connectors = literature_searchsource_srugbv_build_searchconnectors();

        // ------------------------------------------------------------------------------
        // Adding searchfield 0
        $inputarray0 = array();
        // Field select => search in different fields for the term
        $inputarray0[] = &$mform->createElement('select', 'field_type', null, $list);
        // Search term textfield
        $inputarray0[] = &$mform->createElement('text', 'search_field', null, array('size' => '40'));
        // Connectors
        $inputarray0[] = &$mform->createElement('select', 'field_connect', null, $connectors);
        $mform->addGroup($inputarray0, 'search_group0');
        array_shift($list); // shift first key --> all

        // ------------------------------------------------------------------------------
        // Adding searchfield 1
        $inputarray1 = array();
        // Field select => search in different fields for the term
        $inputarray1[] = &$mform->createElement('select', 'field_type', null, $list);
        // Search term textfield
        $inputarray1[] = &$mform->createElement('text', 'search_field', null, array('size' => '40'));
        // Connectors
        $inputarray1[] = &$mform->createElement('select', 'field_connect', null, $connectors);
        $mform->addGroup($inputarray1, 'search_group1');
        // Set default value to field_type1
        $mform->setDefault('search_group1[field_type]', array_shift($list));

        // ------------------------------------------------------------------------------
        // Adding searchfield 2
        $inputarray2 = array();
        // Field select => search in different fields for the term
        $inputarray2[] = &$mform->createElement('select', 'field_type', null, $list);
        // Search term textfield
        $inputarray2[] = &$mform->createElement('text', 'search_field', null, array('size' => '40'));
        // Connectors
        $inputarray2[] = &$mform->createElement('select', 'field_connect', null, $connectors);
        $mform->addGroup($inputarray2, 'search_group2');
        // Set default value to field_type1
        $mform->setDefault('search_group2[field_type]', array_shift($list));

        // ------------------------------------------------------------------------------
        // Adding searchfield 3
        $inputarray3 = array();
        // Field select => search in different fields for the term
        $inputarray3[] = &$mform->createElement('select', 'field_type', null, $list);
        // Search term textfield
        $inputarray3[] = &$mform->createElement('text', 'search_field', null, array('size' => '40'));
        $mform->addGroup($inputarray3, 'search_group3');
        // Set default value to field_type1
        $mform->setDefault('search_group3[field_type]', array_shift($list));

        // ------------------------------------------------------------------------------
        // Adding search and cancel button
        $mform->closeHeaderBefore('btn_search');
        $this->add_action_buttons(true, 'Search');

        // Hidden
        $mform->addElement('hidden', 'type', 'srugbv');
        $mform->addElement('hidden', 'source', $this->_customdata->id);
        $mform->addElement('hidden', 'set', $indexset->name);

        $mform->addElement('hidden', 'section');
        $mform->setDefault('section', -1);

        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', -1);
    }

    /**
     * Select the best index set
     *
     * @param array $indices An array of indzies
     * @return stdClass The index set with the most indizes
     */
    private function select_index_set($indices) {

        $sets = array();
        foreach ($indices as $index) {

            $key = (string) $index->set;
            if (!key_exists($key, $sets)) {
                $sets[$key] = array();
            }
        }

        foreach ($indices as $index) {
            $entry = new stdClass();
            $entry->title = (string) $index->title;
            $entry->name = (string) $index->name;
            $sets[(string) $index->set][] = $entry;
        }

        $bigestset = null;
        $counter = 0;
        foreach ($sets as $setname => $set) {
            if ($counter < count($set)) {
                $bigestset = new stdClass();
                $bigestset->name = $setname;
                $bigestset->content = $set;
                $counter = count($set);
            }
        }

        return $bigestset;
    }

}
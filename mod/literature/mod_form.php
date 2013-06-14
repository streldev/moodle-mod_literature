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
 * The main literature configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__).'/dbobject/listinfo.php');
require_once('locallib.php');

/**
 * Module instance settings form
 */
class mod_literature_mod_form extends moodleform_mod {

	/**
	 * Overwriting the moodleform_mod constructor due to a change of the $action parameter
	 *
	 * @param unknown_type $current
	 * @param unknown_type $section
	 * @param unknown_type $cm
	 * @param unknown_type $course
	 */
	function __construct($current, $section, $cm, $course) {
		global $CFG;
		$this->current   = $current;
		$this->_instance = $current->instance;
		$this->_section  = $section;
		$this->_cm       = $cm;
		$this->course	 = $course;
		if ($this->_cm) {
			$this->context = get_context_instance(CONTEXT_MODULE, $this->_cm->id);
		} else {
			$this->context = get_context_instance(CONTEXT_COURSE, $course->id);
		}
		$this->_modname = 'literature';
		$this->init_features();
		parent::moodleform('../mod/literature/redirect.php?course='.$course->id.'&section='.$section.'&return=0');
	}
	
	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $USER;
		
		$mform = $this->_form;

		//-------------------------------------------------------------------------------
		// Adding the "quicksearch" fieldset
		$mform->addElement('header', 'quicksearch', get_string('quicksearch', 'literature'));
		
		$inputarray = array();
		
		// Source select => different OPACs
		$sources = literature_searchsource_get_available();
		$list = array();
		foreach ($sources as $globalid => $source) {
			$list[$globalid] = $source->name;
		}
		$inputarray[] = &$mform->createElement('select', 'source', null, $list);
		
		// Search term textfield
		$inputarray[] = &$mform->createElement('text', 'search_field');
		$mform->addGroup($inputarray, 'search_group');
		
		// Buttons for search and redirect to extended search
		$buttonarray = array();
		$buttonarray[] = &$mform->createElement('submit', 'btn_search', get_string('search','literature'));
		$buttonarray[] = &$mform->createElement('submit', 'btn_extended', get_string('extendedsearch','literature'));
		$mform->addGroup($buttonarray);
		
		
		//-------------------------------------------------------------------------------
		// Literaturelists 
		$mform->addElement('header', 'lists', get_string('lists', 'literature'));
		$listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
		$lists = literature_print_listinfos($listinfos, true, $this->course->id, $this->_section);
		$mform->addElement('html', $lists);
		$mform->closeHeaderBefore('btn_post_lists');
		$buttonarray = array();
		$buttonarray[] = &$mform->createElement('submit', 'btn_post_lists', get_string('postlists','literature'));
		$mform->addGroup($buttonarray);
		
		
		//-------------------------------------------------------------------------------
		// Hidden fields
		$mform->addElement('hidden', 'course', $this->course);
		$mform->addElement('hidden', 'section', $this->_section);
		
		
		// QuickForm workaround
		$mform->addElement('hidden', 'update', 0);
		$mform->setType('update', PARAM_INT);
	}
	
	
	
}

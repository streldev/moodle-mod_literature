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
 * The search form for searchsources of type "opacxml"
 *
 * @package    mod_literature_searchsource
 * @subpackage opacxml
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_opacxml_search_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        // ------------------------------------------------------------------------------
        // Adding the "search" fieldset
        $mform->addElement('header', 'search_header', get_string('search', 'literature'));

        // ------------------------------------------------------------------------------
        // Adding searchfield
        // Search term textfield
        $mform->addElement('text', 'searchfield', get_string('text', 'literature'), array('size' => '40'));
        $mform->addRule('searchfield', get_string('required'), 'required', null, 'client');

        // ------------------------------------------------------------------------------
        // Adding search and cancel button
        $mform->closeHeaderBefore('btn_search');
        $this->add_action_buttons(true, 'Search');

        // Hidden
        $mform->addElement('hidden', 'type', 'opacxml');
        $mform->addElement('hidden', 'source', $this->_customdata->id);

        $mform->addElement('hidden', 'section');
        $mform->setDefault('section', -1);

        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', -1);
    }

}
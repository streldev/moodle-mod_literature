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
 * The search form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_search_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        // ------------------------------------------------------------------------------
        // Adding the "source" fieldset
        $mform->addElement('header', 'source_header', get_string('source', 'literature'));
        // Source select => different OPACs
        $sources = literature_searchsource_get_available();
        $list = array();
        foreach ($sources as $globalid => $source) {
            $list[$globalid] = $source->name;
        }

        // No JS
        $mform->addElement('html', '<noscript>');
        $sourcegroup = array();
        $sourcegroup[] = &$mform->createElement('select', 'source', null, $list);
        $sourcegroup[] = &$mform->createElement('submit', 'loadform', get_string('load', 'literature'));
        $mform->addGroup($sourcegroup, 'sourcegroup', get_string('sel_source', 'literature'));
        $mform->addElement('html', '</noscript>');

        // JS
        $sourcegroup = array();
        $sourcegroup[] = &$mform->createElement('select', 'source', null, $list, array('onChange' => 'M.core_formchangechecker.set_form_submitted(); this.form.submit();'));
        $mform->addGroup($sourcegroup, 'sourcegroup', get_string('sel_source', 'literature'));
    }

}
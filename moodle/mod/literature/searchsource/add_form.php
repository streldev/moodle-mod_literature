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
 * The searchsource add form
 *
 * @package    mod_literature_searchsource
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_search_add_form extends moodleform {

    /**
     * @see moodleform::definition()
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'addsource', get_string('srctype', 'literature'));

        $types = literature_searchsource_get_types();
        $sources['-1'] = get_string('selecttype', 'literature');
        foreach ($types as $key => $value) {
            $sources[$key] = $value;
        }

        // No JS
        $mform->addElement('html', '<noscript>');
        $typegroup = array();
        $typegroup[] = &$mform->createElement('select', 'sourcetype', null, $sources);
        $typegroup[] = &$mform->createElement('submit', 'loadform', get_string('loadform', 'literature'));
        $mform->addGroup($typegroup, 'typegroup');
        $mform->addElement('html', '</noscript>');

        // JS
        $typegroup = array();
        $typegroup[] = &$mform->createElement('select', 'sourcetype', null, $sources,
                array('onChange' => 'if (this.selectedIndex) M.core_formchangechecker.set_form_submitted(); this.form.submit();'));
        $mform->addGroup($typegroup, 'typegroup');
    }

}
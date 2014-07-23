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

/**
 * The literature import form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_lit_import_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'importheader', get_string('importlit', 'literature'));

        $accepted_types = literature_converter_get_import_extensions();
        $options = array ('subdirs' => 0, 'maxbytes' => $CFG->userquota, 'maxfiles' => 10, 'accepted_types' => $accepted_types);
        $mform->addElement('filepicker', 'mod_literature_import', get_string('files'), null, $options);
        $mform->addRule('mod_literature_import', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('import', 'literature'));

        // Hidden
        $mform->addElement('hidden', 'listid');
    }

}

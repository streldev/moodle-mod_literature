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
 * The literature export form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_lit_export_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'litinfo', get_string('litinfo', 'literature'));

        if (!empty($this->_customdata->lit)) {
            $lits = $this->_customdata->lit;
        } else {
            $lits = array();
        }

        foreach ($lits as $lit) {
            $html = '<div class="litinfo">' .
                    '<span><b>' . get_string('title', 'literature') . '</b>' . $lit->title . '</span></br>' .
                    '<span><b>' . get_string('authors', 'literature') . '</b>' . $lit->authors . '</span>' .
                    '</div><br />';

            $mform->addElement('html', $html);
        }

        $mform->addElement('header', 'exportset', get_string('exportset', 'literature'));

        $formats = literature_converter_get_export_formats();
        $formatnames = array();
        foreach ($formats as $format) {
            $formatnames[$format->name] = $format->name;
        }

        // Format
        $mform->addElement('select', 'format', get_string('format', 'literature'), $formatnames);

        // Filename
        $mform->addElement('text', 'filename', get_string('filename', 'literature'), $formatnames);
        $mform->addRule('filename', get_string('required'), 'required', null, 'client');

        // Hidden
        $mform->addElement('hidden', 'listid');

        // Buttons
        $this->add_action_buttons(true, get_string('export', 'literature'));
    }

}

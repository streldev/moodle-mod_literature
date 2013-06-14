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
 * The literature list export form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_list_export_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'info', get_string('listinfo', 'literature'));

        // Listinfos
        if (!empty($this->_customdata->listinfos)) {

            $listinfos = $this->_customdata->listinfos;
        } else {

            $listinfos = array();
        }

        foreach ($listinfos as $listinfo) {

            $html = '<div class="listinfo">' .
                    '<span><b>' . get_string('name', 'literature') . '</b>' . $listinfo->name . '</span></br>' .
                    '<span><b>' . get_string('description:', 'literature') . '</b>' . $listinfo->description . '</span>' .
                    '</div>';

            $mform->addElement('html', $html);
        }

        $mform->addElement('header', 'settings', get_string('exportset', 'literature'));

        $formats = literature_converter_get_export_formats();

        $formatnames = array();
        foreach ($formats as $format) {
            $formatnames[$format->name] = $format->name;
        }

        $mform->addElement('select', 'format', get_string('format', 'literature'), $formatnames);
        $mform->addElement('checkbox', 'inonefile', get_string('inonefile', 'literature'));
        $mform->addHelpButton('inonefile', 'help:exportlists:inonefile', 'literature');

        if (!empty($this->_customdata->files)) {

            $files = $this->_customdata->files;

            $mform->addElement('header', 'files', get_string('files', 'literature'));
            $mform->addElement('html', '<ul>');

            foreach ($files as $file) {

                $url = $CFG->wwwroot . '/pluginfile.php/' . $file->get_contextid() . '/' . $file->get_component() .
                        '/' . $file->get_filearea() . '/0/' . $file->get_filename() . '?forcedownload=1';
                $html = '<li><a href="' . $url . '">' . $file->get_filename() . '</a></li>';
                $mform->addElement('html', $html);
            }

            $mform->addElement('html', '</ul>');
        }

        $this->add_action_buttons(true, get_string('export', 'literature'));
    }

}

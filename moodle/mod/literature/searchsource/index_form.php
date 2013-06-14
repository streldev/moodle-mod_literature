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
 * The searchsource overview form
 *
 * @package    mod_literature_searchsource
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_search_index_form extends moodleform {

    /**
     * @see moodleform::definition()
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'srcoverview', get_string('sources', 'literature'));

        $sources = $this->build_sources();
        $list = literature_html_build_list($sources);
        $mform->addElement('html', $list);

        $mform->addElement('header', 'srcactions', get_string('actions', 'literature'));

        $actions = array(
            'add' => get_string('add', 'literature'),
            'del' => get_string('delete', 'literature')
        );

        $actiongroup = array();
        $actiongroup[] = &$mform->createElement('select', 'selaction', null, $actions);
        $actiongroup[] = &$mform->createElement('submit', 'submitaction', get_string('ok'));
        $mform->addGroup($actiongroup, 'actiongroup');
    }

    /**
     * Build an array with the html views of the earchsources
     * @return array
     */
    private function build_sources() {
        global $CFG;

        $sources = literature_searchsource_get_available();

        if (empty($sources)) {

            $html = '<li>' . get_string('nosource', 'literature') . '</li>';
            return array($html);
        } else {

            $listitems = array();
            $counter = 0;
            foreach ($sources as $globalid => $source) {

                $source->typename = get_string('pluginname', 'searchsource_' . $source->type);

                $html = '<li style="list-style-type: none; margin:0.2em;">' .
                        '<div style="border: 1px solid #B3B2B2;">' .
                        '<input type="checkbox" name="select[' . $counter . ']" value="' . $globalid . '" style="float:left;"></input>' .
                        '<span style="margin: 0 0 0 5px;">' .
                        '<b>' . get_string('name', 'literature') . '</b>' . $source->name .
                        '&nbsp&nbsp' .
                        '<b>' . get_string('type', 'literature') . '</b>' . $source->typename .
                        '</span>' .
                        '<a href="' . $CFG->wwwroot . '/mod/literature/searchsource/' . $source->type . '/view.php?id=' . $globalid . '"
                        style="float:right; margin-right:10px; cursor: pointer; " title="Edit">' .
                        get_string('edit', 'literature') .
                        '</a>' .
                        '</div>' .
                        '</li>';

                $listitems[] = $html;
                $counter++;
            }

            return $listitems;
        }
    }

}
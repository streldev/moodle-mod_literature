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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/dbobject/listinfo.php');
require_once('locallib.php');

/**
 * Module instance settings form
 */
class mod_literature_mod_form extends moodleform_mod {

    private $is_update;

    /**
     * Overwriting the moodleform_mod constructor due to a change of the $action parameter
     *
     * @param unknown_type $current
     * @param unknown_type $section
     * @param unknown_type $cm
     * @param unknown_type $course
     */
    public function __construct($current, $section, $cm, $course) {
        global $CFG;

        $this->is_update = (!empty($current->update)) ? true : false;
        $this->current = $current;
        $this->_instance = $current->instance;
        $this->_section = $section;
        $this->_cm = $cm;
        $this->course = $course;
        if ($this->_cm) {
            $this->context = get_context_instance(CONTEXT_MODULE, $this->_cm->id);
        } else {
            $this->context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        $this->_modname = 'literature';
        $this->init_features();
        if (!$this->is_update) {
            parent::moodleform('../mod/literature/redirect.php?course=' . $course->id . '&section=' . $section . '&return=0');
        } else {
            parent::moodleform();
        }
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER, $CFG;

        $mform = $this->_form;

        if (!$this->is_update) {

            /*
             *  Add new Instance
             */

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
            $buttonarray[] = &$mform->createElement('submit', 'btn_search', get_string('search', 'literature'));
            $buttonarray[] = &$mform->createElement('submit', 'btn_extended', get_string('extendedsearch', 'literature'));
            $mform->addGroup($buttonarray);

            //-------------------------------------------------------------------------------
            // Literaturelists
            $mform->addElement('header', 'lists', get_string('lists', 'literature'));
            $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
            $lists = literature_print_listinfos($listinfos, true, $this->course->id, $this->_section);
            $mform->addElement('html', $lists);
            $mform->closeHeaderBefore('btn_post_lists');
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement('submit', 'btn_post_lists', get_string('postlists', 'literature'));
            $mform->addGroup($buttonarray);

            // Workaround
            $mform->addElement('hidden', 'update');
            $mform->addElement('hidden', 'course');
            $mform->addElement('hidden', 'section');
        } else {

            /*
             *  Update an instance
             * TODO in later versions:
             * Refactor the i18n strings (delete ": ") therefore we have to rename a lot of string ids
             */

            // Load the literature entry
            $literature = literature_dbobject_literature::load_by_id($this->current->litid);
            if (!$literature) {
                // TODO error
            }

            $mform->addElement('header', 'updateentry', get_string('general'));

            $lit_types = literature_dbobject_literature::get_types();
            $mform->addElement('select', 'type', get_string('littype', 'literature'), $lit_types);
            $mform->setDefault('type', $literature->type);

            $mform->addElement('text', 'title', get_string('title', 'literature'), array('size' => 50));
            $mform->setDefault('title', $literature->title);
            $mform->addRule('title', get_string('required'), 'required', null, 'client');

            $mform->addElement('text', 'subtitle', get_string('subtitle', 'literature'), array('size' => 50));
            $mform->setDefault('subtitle', $literature->subtitle);

            $mform->addElement('text', 'authors', get_string('authors', 'literature'), array('size' => 50));
            $mform->setDefault('authors', $literature->authors);

            $mform->addElement('text', 'publisher', get_string('publisher', 'literature'), array('size' => 50));
            $mform->setDefault('publisher', $literature->publisher);

            $mform->addElement('text', 'published', get_string('published', 'literature'), array('size' => 50));
            $mform->setDefault('published', $literature->published);

            $mform->addElement('text', 'series', get_string('series', 'literature'), array('size' => 50));
            $mform->setDefault('series', $literature->series);

            $mform->addElement('text', 'isbn10', get_string('isbn10', 'literature'), array('size' => 50));
            $mform->setDefault('isbn10', $literature->isbn10);

            $mform->addElement('text', 'isbn13', get_string('isbn13', 'literature'), array('size' => 50));
            $mform->setDefault('isbn13', $literature->isbn13);

            $mform->addElement('text', 'issn', get_string('issn', 'literature'), array('size' => 50));
            $mform->setDefault('issn', $literature->issn);

            $mform->addElement('text', 'format', get_string('format:', 'literature'), array('size' => 50));
            $mform->setDefault('format', $literature->issn);

            $mform->addElement('text', 'titlelink', get_string('titlelink', 'literature'), array('size' => 50));
            $mform->setDefault('titlelink', $literature->titlelink);

            $mform->addElement('textarea', 'description', get_string('description:', 'literature'),
                    array('cols' => 50, 'rows' => 10));
            $mform->setDefault('description', $literature->description);

            $mform->addElement('hidden', 'refs');
            $mform->setDefault('refs', $literature->refs);

            $mform->addElement('header', 'links', get_string('links', 'literature'));
            $i = 0;
            foreach ($literature->links as $link) {
                $html = '<div class="literature_link_wrapper">' .
                        '<div class="literature_link_data">' .
                        '<input name="url[' . $i . ']" type="text" size="50" value="' . format_text($link->url,
                                FORMAT_PLAIN) . '">' .
                        '<input name="linktext[' . $i . ']" type="text" size="20" value="' . format_text($link->text,
                                FORMAT_PLAIN) . '">' .
                        '<a href="#" class="literature_link_del" id="literature_link_del_' . $i . '">Delete</a>' .
                        '</div>' .
                        '</div>';
                $mform->addElement('html', $html);
                $i++;
            }

            $mform->addElement('header', 'coverpathheader', get_string('cover', 'literature'));
            $mform->addElement('filepicker', 'mod_literature_cover', get_string('file'), null,
                    array('maxbytes' => $CFG->userquota, 'accepted_types' => '*'));
            $mform->addElement('hidden', 'coverpath');
            $mform->setDefault('coverpath', $literature->coverpath);

            $this->standard_coursemodule_elements();
            $this->add_action_buttons();
        }
    }

}

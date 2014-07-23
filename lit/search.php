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
 * The main literature seach script
 *
 * This script displays and processes the different search and result forms
 * and calls the internal search api
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/listinfo.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');
require_once('results_form.php');
require_once('search_form.php');


$courseid = optional_param('course', -1, PARAM_INT);
$section = optional_param('section', -1, PARAM_INT);
$search = optional_param('search', 'true', PARAM_ALPHA);

$url = new moodle_url('/mod/literature/lit/search.php');
$url->param('course', $courseid);
$url->param('section', $section);
$url->param('search', $search);
$PAGE->set_url($url);


if ($courseid != -1) {

    $course = $DB->get_record('course', array ('id' => $courseid), '*', MUST_EXIST);

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('mod/literature:addinstance', $context);
} else {

    require_login();
    $context = context_user::instance($USER->id);
    require_capability('mod/literature:manage', $context);
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');


// Form to select the source
$mainform = new literature_search_form();

// Load new sourceform
if ($mainform->is_submitted()) {

    $data = $mainform->get_data();

    $id = $data->sourcegroup['source'];
    if (!$searchform = literature_searchsource_load_searchform($id)) {
        print_error('error:searchsource:formnotfound', 'literature');
    }
    $resultform = null;

    $maindefaults = $data;
    $searchdefaults = new stdClass();

    $searchdefaults->course = $courseid;
    $searchdefaults->section = $section;
} else if ($courseid == -1 && $search == 'false') {

    ////////////////////////////////////////////////////////////////////////////////
    // Display extended search in GLOBAL context
    ////////////////////////////////////////////////////////////////////////////////

    if (!$sources = literature_searchsource_get_available()) {
        print_error('error:searchsource:noinstalled', 'literature');
    }

    // Get first sourceid
    foreach ($sources as $id => $source) {
        $sourceid = $id;
        break;
    }

    if (!$searchform = literature_searchsource_load_searchform($sourceid)) {
        print_error('error:searchsource:formnotfound', 'literature');
    }
    $resultform = null;

    $maindefaults = new stdClass();

    $searchdefaults = new stdClass();
    $searchdefaults->section = $section;
    $searchdefaults->course = $courseid;
} else if ($search == 'false' && $courseid != -1) {

    ////////////////////////////////////////////////////////////////////////////////
    // Display extended search in COURSE context
    ////////////////////////////////////////////////////////////////////////////////

    $sourceid = required_param('source', PARAM_INT);
    $text = optional_param('text', '', PARAM_ALPHANUM);

    if (!$searchform = literature_searchsource_load_searchform($sourceid)) {
        print_error('error:searchsource:formnotfound', 'literature');
    }

    $maindefaults = new stdClass();
    $maindefaults->sourcegroup['source'] = $sourceid;
    $searchdefaults = make_form_data($text, $sourceid, $courseid, $section);
} else if ($search == 'quick') {

    ////////////////////////////////////////////////////////////////////////////////
    // Performe the quickesearch
    ////////////////////////////////////////////////////////////////////////////////

    $text = required_param('text', PARAM_NOTAGS);

    $sourceid = required_param('source', PARAM_ALPHANUM);

    $formdata = make_form_data($text, $sourceid, $courseid, $section);
    $results = literature_search($formdata, 1, 21);

    // Save results in DB
    if (!$timestamp = literature_db_insert_results($results)) {
        print_error('error:search:saveresultsfailed', 'literature');
    }
    $SESSION->literature_search_data = serialize($formdata);
    $SESSION->literature_search_timestamp = $timestamp;

    if (!$searchform = literature_searchsource_load_searchform($sourceid)) {
        print_error('error:searchsource:formnotfound', 'literature');
    }
    $searchdefaults = $formdata;

    $data = new stdClass();
    $data->incourse = true;

    // Instantiate resultform
    $resultform = new literature_results_form('search.php?course=' . $courseid . '&section=' . $section . '
            &search=redo', $data);

    $resultdefaults = new stdClass();
    $resultdefaults->source = $sourceid;

    $maindefaults = new stdClass();
    $maindefaults->sourcegroup['source'] = $sourceid;
} else if ($search == 'true') {

    ////////////////////////////////////////////////////////////////////////////////
    // Performe the extended search
    ////////////////////////////////////////////////////////////////////////////////

    $sourceid = required_param('source', PARAM_INT);

    if (!$searchform = literature_searchsource_load_searchform($sourceid)) {
        print_error('error:searchsource:formnotfound', 'literature');
    }
    $data = $searchform->get_data();

    $results = literature_search($data, 1, 21);

    // Save results in DB
    if (!$timestamp = literature_db_insert_results($results)) {
        print_error('error:search:saveresultsfailed', 'literature');
    }

    // Save search details in the session
    $SESSION->literature_search_data = serialize($data);
    $SESSION->literature_search_timestamp = $timestamp;


    $data->incourse = ($section == -1 || $courseid == -1) ? false : true;

    // Instantiate resultform
    $resultform = new literature_results_form('search.php?course=' . $courseid . '&section=' . $section . '
            &search=redo', $data);


    $maindefaults = new stdClass();
    $maindefaults->sourcegroup['source'] = $sourceid;
    $searchdefaults = $data;
    $resultdefaults = new stdClass();
    $resultdefaults->source = $sourceid;
} else if ($search == 'redo') {

    ////////////////////////////////////////////////////////////////////////////////
    // Performe the operations on the results
    ////////////////////////////////////////////////////////////////////////////////

    $data = new stdClass();
    $data->incourse = false;
    $resultform = new literature_results_form(null, $data);
    $data = $resultform->get_data();

    // Save selected items in Session
    if (isset($_POST['select'])) {
        if (!isset($SESSION->literature_search_selected)) {
            $SESSION->literature_search_selected = array ();
        }

        // Add checked and delete unchecked items
        foreach ($_POST['select'] as $tempid => $value) {

            if ($value) {
                $SESSION->literature_search_selected[$tempid] = 1;
            } else {
                $SESSION->literature_search_selected[$tempid] = 0;
            }
        }
    }

    // Post literature
    if (isset($_POST['btn_post'])) {

        if (isset($SESSION->literature_search_timestamp)) {
            $timestamp = $SESSION->literature_search_timestamp;
        } else {
            print_error('error:search:timestampnotfound', 'literature', $PAGE->url);
        }

        $litids = array ();
        $failedtoinsert = array ();
        foreach ($SESSION->literature_search_selected as $id => $isselected) {

            if ($isselected) {
                if (!$litasstdclass = literature_db_load_result_by_id($timestamp, $id)) {
                    print_error('error:db:couldnotloadresult', 'literature');
                }
                $literature = literature_cast_stdClass_literature($litasstdclass);
                if (!$litids[] = $literature->insert()) {
                    $failedtoinsert[] = $literature;
                }
            }
        }

        foreach ($failedtoinsert as $literature) {
            unset($SESSION->literature_search_selected[$literature->id]);
            // TODO notify user in next version
        }

        // Setup Session for post script
        $SESSION->literature_post_ids = $litids;

        $url = new moodle_url('/mod/literature/lit/post.php');
        $url->param('course', $courseid);
        $url->param('section', $section);
        $url->param('view', $_POST['view']);

        redirect($url);

        // Add literature to new list
    } else if (isset($data->new_list_group['btn_new_list'])) {

        $listname = trim($data->new_list_group['new_list_name']);
        $timer = new DateTime();
        $listinfo = new literature_dbobject_listinfo(null, $listname, $USER->id, $timer->getTimestamp(), null, 0);
        $list = new literature_dbobject_literaturelist($listinfo, array ());
        $listid = $list->insert();

        // Check if session is set up properly
        if (isset($SESSION->literature_search_timestamp)) {
            $timestamp = $SESSION->literature_search_timestamp;
        } else {
            print_error('error:search:timestampnotfound', 'literature');
        }

        $failedtoinsert = array ();
        foreach ($SESSION->literature_search_selected as $id => $isselected) {
            if ($isselected) {
                if (!$litasstdclass = literature_db_load_result_by_id($timestamp, $id)) {
                    print_error('error:db:couldnotloadresult', 'literature');
                }
                $literature = literature_cast_stdClass_literature($litasstdclass);
                $litid = $literature->insert();
                if ($litid) {
                    if (!literature_dbobject_literaturelist::add_literature($listid, $litid)) {
                        $literature->delete();
                        $failedtoinsert[] = $literature;
                    }
                } else {
                    $failedtoinsert[] = $literature;
                }
            }
        }

        if (count($failedtoinsert) > 0) {
            $titles = '';
            foreach ($failedtoinsert as $item) {
                $titles .= $item->title . ', ';
            }
            print_error('error:lit:insertmultiple', 'literature', $PAGE->url, $titles);
        }

        // Cleanup Session
        if (isset($SESSION->literature_search_selected)) {
            $SESSION->literature_search_selected = array ();
        }

        // Redirect to list view
        $url = new moodle_url('/mod/literature/list/view.php');
        $url->param('id', $listid);
        redirect($url);

        // Save literature in list
    } else if (isset($data->lists_group['btn_existing_list'])) {

        // check if listid is set
        if (isset($data->lists_group['select_list']) && $data->lists_group['select_list'] != 0) {
            $listid = $data->lists_group['select_list'];
        } else {
            print_error('error:novalidlist', 'literature');
        }

        // Get Results from DB
        if (isset($SESSION->literature_search_timestamp)) {
            $timestamp = $SESSION->literature_search_timestamp;
        } else {
            print_error('error:search:timestampnotfound', 'literature');
        }

        $failedtoinsert = array ();
        foreach ($SESSION->literature_search_selected as $id => $isselected) {
            if ($isselected) {
                $result = literature_db_load_result_by_id($timestamp, $id);
                $literature = literature_cast_stdClass_literature($result);
                $litid = $literature->insert();
                if ($litid) {
                    if (!literature_dbobject_literaturelist::add_literature($listid, $litid)) {
                        $literature->delete();
                        $failedtoinsert[] = $literature;
                    }
                } else {
                    $failedtoinsert[] = $literature;
                }
            }
        }

        if (count($failedtoinsert) > 0) {
            $titles = '';
            foreach ($failedtoinsert as $item) {
                $titles .= $item->title . ', ';
            }
            print_error('error:lit:insertmultiple', 'literature', $PAGE->url, $titles);
        }

        // Cleanup Session
        if (isset($SESSION->literature_search_selected)) {
            $SESSION->literature_search_selected = array ();
        }

        // Redirect to list view
        $url = new moodle_url('/mod/literature/list/view.php');
        $url->param('id', $listid);
        redirect($url);
    } else if (isset($_POST['next'])) {

        if (!isset($SESSION->literature_search_from)) {
            $SESSION->literature_search_from = 5;
        } else {
            $SESSION->literature_search_from += 5;
        }
    } else if (isset($_POST['back'])) {

        if (!isset($SESSION->literature_search_from)) {
            $SESSION->literature_search_from = 0;
        } else if ($SESSION->literature_search_from < 5) {
            $SESSION->literature_search_from = 0;
        } else {
            $SESSION->literature_search_from -= 5;
        }
    }

    if (!$searchform = literature_searchsource_load_searchform($data->source)) {
        $message = get_string('notify:failedtoloadsearchform', 'literature');
    }

    $searchdefaults = unserialize($SESSION->literature_search_data);
    $searchdefaults->course = $courseid;
    $searchdefaults->section = $section;


    $resultdata = new stdClass();
    $resultdata->incourse = $data->incourse;
    $resultform = new literature_results_form('search.php?course=' . $courseid . '&section=' . $section . '
            &search=redo', $resultdata);

    $resultdefaults = $data;

    $maindefaults = new stdClass();
    $maindefaults->sourcegroup['source'] = $searchdefaults->source;
}

if (empty($resultform)) {

    // Set form defaults
    $mainform->set_data($maindefaults);
    $searchform->set_data($searchdefaults);

    // Set page data
    $PAGE->set_title(get_string('searchlit', 'literature'));
    $PAGE->set_heading(get_string('searchlit', 'literature'));

    // Output page
    echo $OUTPUT->header();
    if (!empty($message)) {
        $OUTPUT->notification($message);
    }

    $mainform->display();
    $searchform->display();

    echo $OUTPUT->footer();
} else {

    // Set form defaults
    $mainform->set_data($maindefaults);
    $searchform->set_data($searchdefaults);
    $resultform->set_data($resultdefaults);

    // Set page data
    $PAGE->set_title(get_string('searchlit', 'literature'));
    $PAGE->set_heading(get_string('searchlit', 'literature'));

    // Output page
    echo $OUTPUT->header();
    if (!empty($message)) {
        $OUTPUT->notification($message);
    }

    $mainform->display();
    $searchform->display();
    $resultform->display();

    echo $OUTPUT->footer();
}

/**
 * Calls the form data factory of the selected searchsourcetyp
 * @param string $text
 * @param int $source
 * @param int $courseid
 * @param int $section
 */
function make_form_data($text, $source, $courseid, $section) {
    global $DB;

    $record = $DB->get_record('literature_searchsource', array ('id' => $source));
    $sourcelib = dirname(dirname(__FILE__)) . '/searchsource/' . $record->type . '/lib.php';
    if (!file_exists($sourcelib)) {
        print_error('error:searchsource:lib', 'literature');
    }
    require_once($sourcelib);
    $functionname = 'literature_searchsource_' . $record->type . '_build_formdata';
    if (!function_exists($functionname)) {
        print_error('error:function', 'literature', '', $functionname);
    }
    return $functionname($text, $source, $courseid, $section);
}

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
 * Library of interface functions and constants for module literature
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the literature specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);
////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function literature_supports($feature) {
    switch ($feature) {
        case FEATURE_IDNUMBER: return false;
        case FEATURE_GROUPS: return false;
        case FEATURE_GROUPINGS: return false;
        case FEATURE_GROUPMEMBERSONLY: return true;
        case FEATURE_MOD_INTRO: return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE: return false;
        case FEATURE_GRADE_OUTCOMES: return false;
        case FEATURE_MOD_ARCHETYPE: return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_NO_VIEW_LINK: return true;

        default: return null;
    }
}

/**
 * Saves a new instance of the literature into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $literature An object from the form in mod_form.php
 * @param mod_literature_mod_form $mform
 * @return int The id of the newly inserted literature record
 */
function literature_add_instance(stdClass $literature, mod_literature_mod_form $mform = null) {
    global $DB;

    $literature->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('literature', $literature);
}

/**
 * Updates an instance of the literature in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $object An object from the form in mod_form.php
 * @param mod_literature_mod_form $mform
 * @return boolean Success/Fail
 */
function literature_update_instance(stdClass $object, mod_literature_mod_form $mform = null) {
    global $DB, $CFG, $USER;

    // Get context
    require_login();
    $context = context_user::instance($USER->id);

    // Load old instance
    $instance = $DB->get_record('literature', array('id' => $object->instance));
    if (!$instance) {
        return false;
    }

    // Build new links
    $links = array();
    if (isset($_POST['url'])) {
        for ($i = 0; $i < count($_POST['url']); $i++) {
            $url = $_POST['url'][$i];
            $text = (!empty($_POST['linktext'][$i])) ? $_POST['linktext'][$i] : null;
            $links[] = new literature_dbobject_link($instance->litid, $object->instance, $text, $url);
        }
    }

    // Get new cover
    $file = $mform->save_stored_file('mod_literature_cover', $context->id, 'mod_literature', 'mod_literature_cover', 0,
            '/', null, true);

    if ($file) {
        $coverurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        $object->coverpath = $coverurl->out(true);
    } else {
        $data = $mform->get_data();
        $object->coverpath = $data->coverpath;
    }

    // Build updated literature entry
    $object->links = $links;
    $literature = literature_cast_stdClass_literature($object);
    $literature->timemodified = time();
    $literature->id = $instance->litid;

    // Update literature entry and get id
    $new_id = $literature->update();
    $literature->add_ref();
    $literature->save();

    if ($new_id && $new_id != $instance->litid) {
        // Set new litid and update
        $instance->litid = $new_id;
        return $DB->update_record('literature', $instance);
    }

    return true;
}

/**
 * Removes an instance of the literature from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function literature_delete_instance($id) {
    global $DB;

    if (!$literature = $DB->get_record('literature', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #
    require_once('dbobject/literature.php');
    literature_dbobject_literature::del_by_id($literature->litid);

    $DB->delete_records('literature', array('id' => $literature->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function literature_user_outline($course, $user, $mod, $literature) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $literature the module instance record
 * @return void, is supposed to echp directly
 */
function literature_user_complete($course, $user, $mod, $literature) {

}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in literature activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function literature_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link literature_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function literature_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0,
        $groupid = 0) {

}

/**
 * Prints single activity item prepared by {@see literature_get_recent_mod_activity()}
 * @return void
 */
function literature_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {

}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 * */
function literature_cron() {
    global $DB;

    $timestamp = time() - 900;

    // Clean temp. table
    $lit_table = 'literature_lit_temp';
    $link_table = 'literature_link_temp';
    $records = $DB->get_records($lit_table);
    foreach ($records as $record) {

        if ($record->timestamp <= $timestamp) {
            // Delete literature entry
            $DB->delete_records($lit_table, array('id' => $record->id));
            // Delete link entry
            $DB->delete_records($link_table, array('lit_id' => $record->id));
        }
    }

    // Clean exported file
    $file_table = 'files';
    $options = array('component' => 'mod_literature', 'filearea' => 'export');
    $files = $DB->get_records($file_table, $options);
    foreach ($files as $file) {
        if ($file->timecreated <= $timestamp) {
            $DB->delete_records($file_table, array('id' => $file->id));
        }
    }

    return true;
}

/**
 * Returns an array of users who are participanting in this literature
 *
 * Must return an array of users who are participants for a given instance
 * of literature. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $literatureid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function literature_get_participants($literatureid) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function literature_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of literature?
 *
 * This function returns if a scale is being used by one literature
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $literatureid ID of an instance of this module
 * @return bool true if the scale is used by the given literature instance
 */
function literature_scale_used($literatureid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('literature', array('id' => $literatureid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of literature.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any literature instance
 */
function literature_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('literature', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give literature instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $literature instance object with extra cmidnumber and modname property
 * @return void
 */
function literature_grade_item_update(stdClass $literature) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($literature->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax'] = $literature->grade;
    $item['grademin'] = 0;

    grade_update('mod/literature', $literature->course, 'mod', 'literature', $literature->id, 0, null, $item);
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 * 
 * This function is not realy needed. Module does not show a normal "view link"
 *
 * @global object
 * @param object $coursemodule
 * @return object|null
 */
function literature_get_coursemodule_info($coursemodule) {
    global $DB;

    $literature = $DB->get_record('literature', array('id' => $coursemodule->instance), 'id, name, intro, introformat');
    if ($literature) {
        if (empty($literature->name)) {
            // label name missing, fix it
            $literature->name = "literature{$literature->id}";
            $DB->set_field('literature', 'name', $literature->name, array('id' => $literature->id));
        }
        $info = new cached_cm_info();
        $info->name = $literature->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * Update literature grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $literature instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function literature_update_grades(stdClass $literature, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/literature', $literature->course, 'mod', 'literature', $literature->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function literature_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the literature file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function literature_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {

    $itemid = array_shift($args);
    $filename = array_shift($args);
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_literature', $filearea, $itemid, '/', $filename);
    ob_clean();
    send_stored_file($file, 120, 0, true);
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding literature nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the literature module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function literature_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {

}

/**
 * Extends the settings navigation with the literature settings
 *
 * This function is called when the context for the page is a literature module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $literaturenode {@link navigation_node}
 */
function literature_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $literaturenode = null) {

}

////////////////////////////////////////////////////////////////////////////////
// View Literature in Course                                                  //
////////////////////////////////////////////////////////////////////////////////

function literature_cm_info_view(cm_info $cminfo) {

    require_once('locallib.php');
    require_once('dbobject/literature.php');

    global $DB;

    $id = $cminfo->instance;

    $literature = $DB->get_record('literature', array('id' => $id));

    $lit = literature_dbobject_literature::load_by_id($literature->litid);

    if ($literature->litview == 1) {
        $content = literature_view_full($lit);
    } else {
        $content = literature_view_list($lit);
    }
    $cminfo->set_content($content);
}

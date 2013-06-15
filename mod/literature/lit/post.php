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
 * The script to post selected literature in a moodle course
 *
 * @package    mod_literature_lit
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/course/lib.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literature.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');


$course = required_param('course', PARAM_INT);
$section = required_param('section', PARAM_INT);
$view = required_param('view', PARAM_INT);


$url = new moodle_url('/mod/literature/lit/post.php');
$url->param('course', $course);
$url->param('section', $section);
$url->param('view', $view);

$PAGE->set_url($url);

$course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);
$module = $DB->get_record('modules', array('name' => 'literature'), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('mod/literature:addinstance', $context);

if (!course_allowed_module($course, 'literature')) {
    print_error('moduledisable');
}

if (empty($SESSION->literature->post->ids)) {
    print_error('error:nolitselected', 'literature');
}
$litids = $SESSION->literature->post->ids;
unset($SESSION->literature->post->ids);

foreach ($litids as $litid) {

    if (!$lit = literature_dbobject_literature::load_by_id($litid)) {
        print_error('error:lit:loadfailed', 'literature', '', $litid);
    }

    $cm = new stdClass();
    $cm->course = $course->id;
    $cm->section = $section;
    $cm->module = $module->id;
    $cm->modulename = 'literature';
    $cm->instance = 0;
    $cm->visible = 1;
    $cm->groupmode = $course->groupmode;
    $cm->groupingid = $course->defaultgroupingid;
    $cm->groupmembersonly = 0;

    if (!$cm->id = add_course_module($cm)) {
        print_error('error:post:litfailedcm', 'literature', '', $lit);
    }

    $cm->coursemodule = $cm->id;

    if ( $view !== 0 && $view !== 1) {
        $view = 1;
    }
   
    $literature = new stdClass();
    $literature->litid = $lit->id;
    $literature->course = $course->id;
    $literature->name = $lit->title;
    $literature->introformat = 1;
    $literature->timemodified = 0;
    $literature->litview = $view;

    $instanceid = literature_add_instance($literature);

    $DB->set_field('course_modules', 'instance', $instanceid, array('id' => $cm->id));

    course_add_cm_to_section($course, $cm->id, $section);

    set_coursemodule_visible($cm->id, true);

    // +1 to links and save
    $lit->add_ref();
    $lit->save();
}

rebuild_course_cache($course->id, false);

$url = new moodle_url('/course/view.php?');
$url->param('id', $course->id);
redirect($url);



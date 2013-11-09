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
 * Script to display and process literature list view form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/course/lib.php');
require_once('view_form.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');


$id = required_param('id', PARAM_INT);
$courseid = optional_param('course', -1, PARAM_INT);
$section = optional_param('section', -1, PARAM_INT);

if (!$list = literature_dbobject_literaturelist::load_by_id($id)) {
    $a = new stdClass();
    $a->listid = $id;
    print_error('error:list:loadfailed', 'literature', null, $a);
}

// Check if access should be denied
if ($list->info->userid != $USER->id && !$list->info->public) {
    print_error('error:list:accessdenied', 'literature');
}


if ($courseid != -1 && $section != -1) {

    ////////////////////////////////////////////////////////////////////////////////
    // In COURSE context
    ////////////////////////////////////////////////////////////////////////////////

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('mod/literature:addinstance', $context);

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('course', $courseid);
    $url->param('section', $section);
    $url->param('id', $id);

    $PAGE->set_url($url);
    $PAGE->set_context($context);



    // Process
    if (!empty($_POST)) {

        if (!empty($_POST['btn_post'])) {

            $litids = (!empty($_POST['select'])) ? $_POST['select'] : null;
            $view = (isset($_POST['view'])) ? $_POST['view'] : 1;

            if (!empty($litids)) {

                $SESSION->literature_post = new stdClass();
                $SESSION->literature_post_ids = array();
                foreach ($litids as $litid => $isselected) {
                    if ($isselected) {
                        $SESSION->literature_post_ids[] = $litid;
                    }
                }

                $url = new moodle_url('/mod/literature/lit/post.php');
                $url->param('course', $courseid);
                $url->param('section', $section);
                $url->param('view', $view);

                redirect($url);
            } else {
                $message = get_string('notify:nolitselected', 'literature');
            }
        }
    }


} else {

    ////////////////////////////////////////////////////////////////////////////////
    // In GLOBAL context
    ////////////////////////////////////////////////////////////////////////////////

    require_login();
    $context = context_user::instance($USER->id);
    require_capability('mod/literature:manage', $context);

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('id', $id);
    $PAGE->set_url($url);
    $PAGE->set_context($context);

    // Process
    if (!empty($_POST)) {

        if (!empty($_POST['btn_save'])) {

            $listname = $_POST['name'];
            $listdesc = (empty($_POST['desc'])) ? null : $_POST['desc'];
            $public = $_POST['public'];

            if (!$listinfo = literature_dbobject_listinfo::load_by_id($id)) {
                $listid = $id;
                print_error('error:list:loadfailed', 'literature', $PAGE->url, $listid);
            }
            $listinfo->name = $listname;
            $listinfo->description = $listdesc;
            $listinfo->public = $public;

            $listinfo->save();
        } else {

            $litids = (!empty($_POST['select'])) ? $_POST['select'] : null;

            switch ($_POST['act_select']) {

                case 'del' :

                    if ($litids != null) {

                        foreach ($litids as $litid => $isselected) {
                            if ($isselected) {
                                literature_dbobject_literaturelist::del_literature($id, $litid);
                            }
                        }
                    } else {
                        $message = get_string('notify:nolitselected', 'literature');
                    }
                    break;

                case 'exp' :

                    if ($litids != null) {
                        if (empty($SESSION->literature)) {
                            $SESSION->literature = new stdClass();
                        }
                        $SESSION->literature_litselected = array();
                        foreach ($litids as $litid => $isselected) {
                            if ($isselected) {
                                $SESSION->literature_litselected[] = $litid;
                            }
                        }

                        $url = new moodle_url('/mod/literature/lit/export.php');
                        $url->param('listid', $id);
                        redirect($url);
                    } else {
                        $message = get_string('notify:nolitselected', 'literature');
                    }
                    break;

                case 'imp' :

                    $url = new moodle_url('/mod/literature/lit/import.php');
                    $url->param('listid', $id);
                    redirect($url);
                    break;

                case 'add' :
                    $url = new moodle_url('/mod/literature/lit/search.php');
                    $url->param('search', 'false');
                    $url->param('listid', $id); // TODO support in later versions
                    redirect($url);
                    break;

                default :
                    $message = get_string('notify:novalidaction', 'literature');
            }
        }
    }

    $node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER);
    if ($node) {
        $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
        foreach ($listinfos as $listinfo) {
            $url = new moodle_url($CFG->wwwroot . '/mod/literature/list/view.php');
            $url->param('id', $listinfo->id);

            $listnode = $node->add(
                    $listinfo->name, $url, navigation_node::TYPE_ACTIVITY
            );
            if ($listinfo->id == $id) {
                $listnode->make_active();
            }
        }
    }
}

$list = literature_dbobject_literaturelist::load_by_id($id);

$data = new stdClass();
$data->name = $list->info->name;
$data->desc = $list->info->description;
$data->public = $list->info->public;
$data->content = $list->items;
$data->listid = $list->info->id;
$data->incourse = ($section == -1 || $courseid == -1) ? false : true;


// Set page data
$title = get_string('view_list', 'literature');
$title .= ' ' . $list->info->name;
$PAGE->set_title($title);
$PAGE->set_heading($list->info->name);
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();
if (!empty($message)) {
    echo $OUTPUT->notification($message);
}

$script = 'view.php?course=' . $courseid . '&section=' . $section . '&id=' . $id;
$mform = new literature_list_view_form($script, $data);
$mform->display();

// Finish the page
echo $OUTPUT->footer();


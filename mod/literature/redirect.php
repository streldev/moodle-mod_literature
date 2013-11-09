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
 * Process the mod_form.php and redirect
 *
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


$course = required_param('course', PARAM_INT);
$section = required_param('section', PARAM_INT);
$return = required_param('return', PARAM_INT);


require_login();
$context = context_course::instance($course);
require_capability('mod/literature:addinstance', $context);

	
//---------------------------------------------------------------------------------------
// Post lists
if (!empty($_POST['btn_post_lists'])) {
	
	$SESSION->literature_listsselected = $_POST['select'];
	$url = new moodle_url('/mod/literature/list/post.php');
	$url->param('course', $_POST['course']);
	$url->param('section', $_POST['section']);
	redirect($url);

//---------------------------------------------------------------------------------------
// Search literature
} elseif (!empty($_POST['btn_search'])) {
	$url = new moodle_url('/mod/literature/lit/search.php');
	
	$url->param('course', $_POST['course']); 
	$url->param('section', $_POST['section']);
	$url->param('source', $_POST['search_group']['source']);
	$url->param('text', $_POST['search_group']['search_field']);
	$url->param('search', 'quick');
	redirect($url);

//---------------------------------------------------------------------------------------
// Extended search
} elseif (!empty($_POST['btn_extended'])) {
	$url = new moodle_url('/mod/literature/lit/search.php');
	$url->param('course', $_POST['course']);
	$url->param('section', $_POST['section']);
	$url->param('source', $_POST['search_group']['source']);
	$url->param('text', $_POST['search_group']['search_field']);
	$url->param('search', 'false');
	redirect($url);

//---------------------------------------------------------------------------------------
// Import list
} elseif (!empty($_POST['btn_import'])) {
	$url = new moodle_url('/mod/literature/list/import.php');
	redirect($url);
	
	
//---------------------------------------------------------------------------------------
// Error no button was pushed
} else {
	$url = new moodle_url('/index.php');
	redirect($url);
}
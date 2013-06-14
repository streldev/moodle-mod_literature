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
 * English strings for literature
 *
 * @package    mod_literature_lang
 * @subpackage en
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


$string['modulename'] = 'Literature';
$string['modulenameplural'] = 'Literatures';
$string['modulename_help'] = 'The literature module allows to manage and publish literature data.';
$string['literature'] = 'literature';
$string['pluginadministration'] = 'literature administration';
$string['pluginname'] = 'literature';



$string['actions'] = 'Actions';
$string['actionsl'] = 'Select action';
$string['add'] = 'Add';
$string['addlist'] = 'Add List';
$string['addlit'] = 'Add Literature';
$string['addsource'] = 'Add Source';
$string['addtolist'] = 'Add to list';
$string['addtonewlist'] = 'Add to new list';
$string['authors'] = 'Authors: ';

// ---------------------------------------------------------------------------------------
// Search connectors
$string['and'] = 'and';
$string['or'] = 'or';
$string['andnot'] = 'and not';

$string['created:'] = 'Created: ';
$string['delete'] = 'Delete';
$string['description'] = 'Description';
$string['description:'] = 'Description: ';
$string['edit'] = 'Edit';
$string['empty'] = 'Empty';
$string['export'] = 'Export';
$string['exportset'] = 'Export settings';
$string['extendedsearch'] = 'Extended search';
$string['filename'] = 'Filename';
$string['files'] = 'Files';
$string['format'] = 'Format';
$string['format:'] = 'Format: ';
$string['import'] = 'Import';
$string['importlist'] = 'Import List';
$string['importlit'] = 'Import Literature';
$string['inonefile'] = 'Export in single file';
$string['isactiveenricher'] = 'Setting to activate/deactivate the enricher.';
$string['isbn'] = 'ISBN: ';
$string['isbn10'] = 'ISBN10: ';
$string['isbn13'] = 'ISBN13: ';
$string['ispublic'] = 'Is public';
$string['items'] = 'Items';
$string['link'] = 'Link: ';
$string['listinfo'] = 'Listinfo';
$string['lists'] = 'Lists';
$string['lit'] = 'Literature';
$string['litinfo'] = 'Literature info';
$string['load'] = 'Load';
$string['loadform'] = 'Load form';
$string['managelists'] = 'Manage Lists';
$string['managelit'] = 'Manage Literature';
$string['managesources'] = 'Manage Sources';
$string['name'] = 'Name: ';
$string['new_list'] = 'New list';
$string['nextresults'] = 'Next Results';
$string['nolist'] = 'List is empty';
$string['nolists'] = 'No lists yet';
$string['noresults'] = 'No Results';
$string['nosource'] = 'No source installed';
$string['options'] = 'Options';
$string['postas'] = 'Post as';
$string['postlists'] = 'Post selected list(s)';
$string['post'] = 'Post';
$string['prevresults'] = 'Prev. Results';
$string['published'] = 'Published: ';
$string['publisher'] = 'Publisher: ';
$string['quicksearch'] = 'QuickSearch';
$string['results'] = 'Results';
$string['save'] = 'Save';
$string['saveandsearch'] = 'Save and search';
$string['search'] = 'Search';
$string['searchlit'] = 'Search Literature';
$string['search_results'] = 'Search results';
$string['sel_source'] = 'Search source';
$string['srctype'] = 'Type';
$string['selecttype'] = 'Select type';
$string['settings'] = 'Settings';
$string['source'] = 'Source';
$string['sourceoverview'] = 'Source overview';
$string['sources'] = 'Sources';
$string['srctype'] = 'Source type';
$string['subtitle'] = 'Subtitle: ';
$string['text'] = 'Text';
$string['title'] = 'Title: ';
$string['title_add_list'] = 'New literature list';
$string['title_edit_list'] = 'Edit literature list';
$string['title:exportlists'] = 'Export Lists';
$string['title:exportlit'] = 'Export Literature';
$string['title:listoverview'] = 'List overview';
$string['type'] = 'Type: ';
$string['url'] = 'URL: ';
$string['view_list'] = 'View List';
$string['view_as_list'] = 'List';
$string['view_as_full'] = 'Full view';


// HELP

$string['help:addlist:public'] = 'Should list be public?';
$string['help:addlist:public_help'] = 'If selected, the list is marked as public. Every user can export a public list.';

$string['help:exportlists:inonefile'] = 'Export in one file?';
$string['help:exportlists:inonefile_help'] = 'If active, the selecteded lists are exported in a single file.';


// ERRORS
$string['error:noterm'] = 'Searching without a term is impossible!!!';
$string['error:nolistselected'] = 'No list selected!';
$string['error:nolitselected'] = 'No literature selected!';
$string['error:novalidlist'] = 'No valid list selected!';
$string['error:novalidaction'] = 'No valid action selected!';

$string['error:db:addlit2list'] = 'Literature with title: {$item->title} could not be added to list.';
$string['error:db:incorectlinkhandling'] = 'Congrats! You found a bug in the linkhandling for lists. Please contact the developer :-)';
$string['error:db:litinsert'] = 'Literature with title: {$item->title} could not be inserted into database.';
$string['error:db:litnotfound'] = 'Literature with id: {$id} was not found in database!';
$string['error:db:litupdate'] = 'It was impossible to update the literature with id: {$id}';

$string['error:enricher:classnotfound'] = 'This is realy mysterious. A second ago i found the correct file but now the class of the enricher is missing! Did someone delete the enricher a second ago?';
$string['error:enricher:notinstalled'] = 'Enricher {$aws} is not installed. Please install.';

$string['error:export:nolists'] = 'No lists selected for export or your SESSION is not set correct!';

$string['error:exporter:couldnotcreatefile'] = 'Could not create file for export. Pleas contact the moodle admin!';
$string['error:exporter:export'] = 'Failed to export!';
$string['error:exporter:notinstalled'] = 'The export format {$format} is not installed!';

$string['error:file:emptycontent'] = 'It seems the file {$fileinfo->filename} doesn`t have any content!';
$string['error:file:getafterupload'] = 'Can`t get your uploaded file {$fileinfo->filename}!';

$string['error:importer:extensionnotsupported'] = 'The extension {$a->yourextension} is not supported. Supported: {$a->extensions}';
$string['error:importer:import'] = 'Failed to import {$fileinfo->filename}!';
$string['error:importer:notinstalled'] = 'The import format {$format} is not installed!';

$string['error:list:accessdenied'] = 'You don`t have permission to access this list!';
$string['error:list:entriesmissing'] = 'While loading your list we found {$a} missguiding links in your list and deleted them. Pleas try again.';
$string['error:list:insert'] = 'Error while inserting list {$name} into database';
$string['error:list:loadfailed'] = 'Failed to load list with id: {$a->listid}!';

$stirng['error:lit:loadfailed'] = 'Failed to load literature with id: {$litid}!';

$string['error:post:litfailedcm'] = 'Could not post literature with id: {$lit->id}. I was not able to add course module entry!';

$string['error:search:saveresultsfailed'] = 'Failed to save the results in the database!';
$string['error:search:timestampnotfound'] = 'Failed to load results from the database. Please search again.';
$string['error:search:couldnotloadresult'] = 'The search result could not be found in the db. Please search again.';

$string['error:searchsource:dbobjectmissing'] = 'Failed to load the dbobject of the searchsource you want to delete.' .
        'Reinstall the coresponding subplugin or delete the entry by yourself.';
$string['error:searchsource:formnotfound'] = 'Form for selected searchsource not found!';
$string['error:searchsource:functionmakeformdata'] = 'Searchsource incorrect installed. Function {$functionname} is missing!';
$string['error:searchsource:lib'] = 'Failed to load searchsource lib!';
$string['error:searchsource:noinstalled'] = 'No searchsource is installed! Please contact your moodle admin.';
$string['error:searchsource:notinstalled'] = 'Searchsource {$sourcetype} is not properly installed. Searchobject not found!';
$string['error:searchsource:confignotfound'] = 'The configuration you asked for does not exist in db!';
$string['error:searchsource:configrefnotfound'] = 'The reference for the configuration you asked for does not exist in db!';
$string['error:searchsource:typemissing'] = 'Error while processing search form. Type of the searchsource is missing!';
$string['error:searchsource:addfailed'] = 'Error while adding new searchsource!';

$string['error:session:nolists'] = 'SESSION is empty. You have to select at least one list.';

// NOTIFY
$string['notify:failedtoloadsearchform'] = 'Failed to load searchform!';
$string['notify:nolistselected'] = 'No list selected!';
$string['notify:nolitselected'] = 'No lit selected!';
$string['notify:novalidaction'] = 'No valid action was selected!';

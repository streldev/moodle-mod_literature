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
 * English strings for the z3950 searchsource
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string[''] = '';

$string['pluginname'] = 'Z39.50 Source';
$string['sourcetype'] = 'Z39.50';

$string['addfield'] = 'Add field(s)';

$string['code'] = 'Code';
$string['default'] = 'Defaultindex';
$string['desc'] = 'Description';
$string['errorconnectingserver'] = 'Connecting to z server failed!';
$string['host'] = 'Host';
$string['name'] = 'Name';
$string['password'] = 'Password';
$string['profile'] = 'Profile';
$string['user'] = 'User';
$string['save'] = 'Save';
$string['yaznotinstalled'] = 'To use this plugin the php-yaz library is needed. Please contact the admin.';

$string['newsource'] = 'New source';
$string['editsource'] = 'Edit Source';

$string['help:name'] = 'Name';
$string['help:name_help'] = 'The name of the source.';

$string['help:host'] = 'Host';
$string['help:host_help'] = 'The Z39.50 server with DB. Example: z3950.gbv.de/gvk';

$string['help:profile'] = 'Z39.50 profile entries';
$string['help:profile_help'] = 'Pairs of the form "Index" => "Index name". Example: "1016" and "ALL".' .
        'The first entry is used as default.';

$string['error:profile:load'] = 'Failed to load profile! Please check the configuration of the searchsource.';
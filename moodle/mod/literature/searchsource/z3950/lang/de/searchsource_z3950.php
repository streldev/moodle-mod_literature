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
 * German strings for the z3950 searchsource
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string[''] = '';

$string['pluginname'] = 'Z39.50 Source';
$string['sourcetype'] = 'Z39.50';

$string['addfield'] = 'Feld(er) hinzufügen';

$string['code'] = 'Code';
$string['default'] = 'Standardindex';
$string['desc'] = 'Beschreibung';
$string['errorconnectingserver'] = 'Verbindung zum Z39.50 Server fehlgeschlagen!';
$string['host'] = 'Host';
$string['name'] = 'Name';
$string['password'] = 'Passwort';
$string['profile'] = 'Profile';
$string['user'] = 'User';
$string['save'] = 'Speichern';
$string['yaznotinstalled'] = 'Um das Z39.50 Subplugin zu nutzen, wird die PHP-YAZ Bibliothek benötigt.' .
        'Bitte kontaktieren sie den Administrator.';

$string['newsource'] = 'Suchquelle anlegen';
$string['editsource'] = 'Suchquelle bearbeiten';

$string['help:name'] = 'Name';
$string['help:name_help'] = 'Der Name der Suchquelle.';

$string['help:host'] = 'Host';
$string['help:host_help'] = 'Die URL zum Z39.50 Server mit Datenbank. Beispiel: z3950.gbv.de/gvk';

$string['help:profile'] = 'Ein Z39.50 Profileintrag';
$string['help:profile_help'] = 'Paare der Form "Index" => "Indexname". Beispiel: "1016" und "Volltextsuche".' .
        'Der erste Eintrag wird als Standardindex bei der Schnellsuche genutzt.';

$string['error:profile:load'] = 'Laden des Profils ist fehlgeschlagen! Überprüfen sie die Konfigurationd er Suchquelle.';
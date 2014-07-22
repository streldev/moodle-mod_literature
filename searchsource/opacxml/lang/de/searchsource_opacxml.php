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
 * German strings for opacxml searchsource
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'OCLC OPAC XML Schnittstelle';

$string['name'] = 'Name';
$string['save'] = 'Speichern';
$string['server'] = 'Server';


$string['newsource'] = 'Suchquelle anlegen';
$string['editsource'] = 'Suchquelle bearbeiten';


$string['help:server'] = 'Server';
$string['help:server_help'] = 'Der OPAC Server mit der entsprechenden Datenbank. Beispiel: http://opac.ub.uni-potsdam.de';

$string['error:no:searchparameters'] = 'Es wurden keine Suchterme angegeben. Bitte versuchen sie es erneut.';
$string['error:parser:picanotfound'] = 'Der für diese Suchqulle benötigte Pica+-Parser konnte nicht gefunden werden. Ist er installiert?';
$string['error:searchsource:config'] = 'Es scheint so als sein die Konfiguration der Suchquelle fehlerhaft. Überprüfen sie die URL des Servers.';
$string['error:searchsource:loadinfo'] = 'Der Informationseintrag der Suchquelle konnte nicht geladen werden. Wurde gerade gelöscht?';
$string['error:searchsource:loadinstance'] = 'Die Konfiguration der Suchquelle konnte nicht geladen werden. Wurde sie gelöscht?';

$string['error:config:failedtoadd'] = 'Das Hinzufügen der neuen Konfiguration zur Datenbank ist fehlgeschlagen!';
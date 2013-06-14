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
 * German strings for literature
 *
 * @package    mod_literature_lang
 * @subpackage de
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


$string['modulename'] = 'Literatur';
$string['modulenameplural'] = 'Literatures';
$string['modulename_help'] = 'Das Literatur-Modul erlaubt es Literaturdaten in verschiedenen Quellen zu suchen, zu verwalten und in Kursen zu veröffentlichen.';
$string['literature'] = 'literature';
$string['pluginadministration'] = 'literature administration';
$string['pluginname'] = 'Literature';



$string['actions'] = 'Aktionen';
$string['actionsel'] = 'Wähle Aktion';
$string['add'] = 'Hinzufügen';
$string['addlist'] = 'Liste hinzufügen';
$string['addlit'] = 'Literatur hinzufügen';
$string['addsource'] = 'Suchquele hinzufügen';
$string['addtolist'] = 'Zu Liste hinzufügen';
$string['addtonewlist'] = 'Zu neuer Liste hinzufügen';
$string['authors'] = 'Autoren: ';

// ---------------------------------------------------------------------------------------
// Search connectors
$string['and'] = 'und';
$string['or'] = 'oder';
$string['andnot'] = 'und nicht';

$string['created:'] = 'Erstellt: ';
$string['delete'] = 'Löschen';
$string['description'] = 'Beschreibung';
$string['description:'] = 'Beschreibung: ';
$string['edit'] = 'Bearbeiten';
$string['empty'] = 'Leer';
$string['export'] = 'Export';
$string['exportset'] = 'Exporteinstellungen';
$string['extendedsearch'] = 'Erweiterte Suche';
$string['filename'] = 'Dateiname';
$string['files'] = 'Dateien';
$string['format'] = 'Format';
$string['format:'] = 'Format: ';
$string['import'] = 'Import';
$string['importlist'] = 'Liste importieren';
$string['importlit'] = 'Literatur importieren';
$string['inonefile'] = 'In einzelne Datei exportieren';
$string['isactiveenricher'] = 'Gibt an, ob der Enricher aktiv ist.';
$string['isbn'] = 'ISBN: ';
$string['isbn10'] = 'ISBN10: ';
$string['isbn13'] = 'ISBN13: ';
$string['ispublic'] = 'Öffentlich';
$string['items'] = 'Einträge';
$string['link'] = 'Link: ';
$string['listinfo'] = 'Listinfo';
$string['lists'] = 'Listen';
$string['lit'] = 'Literatur';
$string['litinfo'] = 'Literaturinformation';
$string['load'] = 'Anzeigen';
$string['loadform'] = 'Formular anzeigen';
$string['managelists'] = 'Verwalte Listen';
$string['managelit'] = 'Verwalte Literatur';
$string['managesources'] = 'Verwalte Suchquellen';
$string['name'] = 'Name: ';
$string['new_list'] = 'Neue Liste';
$string['nextresults'] = 'Nächste Ergebnisse';
$string['nolist'] = 'Liste enthält keine Einträge';
$string['nolists'] = 'Keine Listen gefunden';
$string['noresults'] = 'Keine Ergebnisse';
$string['nosource'] = 'Keine Suchquelle installiert';
$string['options'] = 'Optionen';
$string['postas'] = 'Veröffentlichen als';
$string['postlists'] = 'Auswahl veröffentlichen';
$string['post'] = 'Veröffentlichen';
$string['prevresults'] = 'Vorherhige Ergebnisse';
$string['published'] = 'Veröffentlicht: ';
$string['publisher'] = 'Verlag: ';
$string['quicksearch'] = 'Schnellsuche';
$string['results'] = 'Ergebnisse';
$string['save'] = 'Speichern';
$string['saveandsearch'] = 'Speichern und suchen';
$string['search'] = 'Suchen';
$string['searchlit'] = 'Literatur suchen';
$string['search_results'] = 'Suchergebnisse';
$string['sel_source'] = 'Suchquelle';
$string['srctype'] = 'Typ';
$string['selecttype'] = 'Typ auswählen';
$string['settings'] = 'Einstellungen';
$string['source'] = 'Quelle';
$string['sourceoverview'] = 'Suchquellenübersicht';
$string['sources'] = 'Suchquellen';
$string['srctype'] = 'Suchquellentyp';
$string['subtitle'] = 'Untertitel: ';
$string['text'] = 'Text';
$string['title'] = 'Titel: ';
$string['title_add_list'] = 'Neue Literaturliste';
$string['title_edit_list'] = 'Bearbeite  Literaturliste';
$string['title:exportlists'] = 'Liste exportieren';
$string['title:exportlit'] = 'Literatur exportieren';
$string['title:listoverview'] = 'Listenübersicht';
$string['type'] = 'Typ: ';
$string['url'] = 'URL: ';
$string['view_list'] = 'Liste anzeigen';
$string['view_as_list'] = 'Liste';
$string['view_as_full'] = 'Vollansicht';


// HELP

$string['help:addlist:public'] = 'Öffentlich';
$string['help:addlist:public_help'] = 'Wenn die Liste als öffentlich gekennzeichnet wurde, können alle Benutzer die Liste exportieren.';

$string['help:exportlists:inonefile'] = 'In einzelne Datei exportieren?';
$string['help:exportlists:inonefile_help'] = 'Wenn diese Option aktiviert wurde, werden die ausgewählten Listen in eine einzelne Datei exportiert.' .
        'Ansonsten wird pro ausgewählter Liste eine neue Datei erstellt.';


// ERRORS
$string['error:noterm'] = 'Ohne Suchterm keine Suche! :-)';
$string['error:nolistselected'] = 'Keine Liste ausgewählt!';
$string['error:nolitselected'] = 'Keine Literatur ausgewählt!';
$string['error:novalidlist'] = 'Keine gültige Liste ausgewählt!';
$string['error:novalidaction'] = 'Keine gültige Aktion ausgewählt!';

$string['error:db:addlit2list'] = 'Die Literatur mit dem Titel: {$item->title} konnte nicht zur Liste hinzugefügt werden.';
$string['error:db:incorectlinkhandling'] = 'Gratulation! Sie haben einen Fehler im Linkmanagement der Listen gefunden. Bitte kontaktieren sie den Entwickler des Plugins :-)';
$string['error:db:litinsert'] = 'Die Literatur mit Titel: {$item->title} konnte nicht in die Datenbank eingefügt werden.';
$string['error:db:litnotfound'] = 'Die Literatur mit der ID: {$id} konnte nicht in der Datenbank gefunden werden.';
$string['error:db:litupdate'] = 'Das Update der Literatur mit der ID: {$id} konnte nicht durchgeführt werden.';

$string['error:enricher:classnotfound'] = 'Jetzt wird es unheimlich. Eine Sekunde zuvor wurde die gesuchte Datei noch gefunden.' .
        'Jetzt scheint die Datein und damit die Klasse des Enricher zu fehlen. Hat sie jemand gelöscht?';
$string['error:enricher:notinstalled'] = 'Der Enricher {$aws} ist nicht installiert. Um ihn zu nutzen, muss das entsprechende Subplugin installiert werden.';

$string['error:export:nolists'] = 'Keine Liste für den Export ausgewählt. Wurde die Session beendet?';

$string['error:exporter:couldnotcreatefile'] = 'Die Datei für den Export konnte nicht geschlossen werden. Kontaktieren sie den Moodel-Administrator.';
$string['error:exporter:export'] = 'Unbekannter Fehler beim Export!';
$string['error:exporter:notinstalled'] = 'Das Exportformat {$format} ist nicht installiert!';

$string['error:file:emptycontent'] = 'Es scheint, dass die Datei {$fileinfo->filename} keinen Inhalt hat!?';
$string['error:file:getafterupload'] = 'Das Laden der hochgeladenen Datei {$fileinfo->filename} ist fehlgeschlagen!';

$string['error:importer:extensionnotsupported'] = 'Das Importformate für die Dateiendung {$a->yourextension} ist nicht installiert. Unterstützt werden: {$a->extensions}';
$string['error:importer:import'] = 'Der Import der Datei {$fileinfo->filename} ist fehlgeschlagen!';
$string['error:importer:notinstalled'] = 'Das Importformat {$format} ist nicht installiert!';

$string['error:list:accessdenied'] = 'Sie haben keine Erlaubnis auf diese Liste zu zugreiffen!';
$string['error:list:entriesmissing'] = 'Während dem Laden der Liste wurden {$a} fehlweisende Referenzen in der Liste gefunden und gelöscht. Versuchen Sie es erneut.';
$string['error:list:insert'] = 'Das Einfügen der Liste {$name} in die Datenbank ist fehlgeschlagen.';
$string['error:list:loadfailed'] = 'Das Laden der Liste mit der ID: {$a->listid} ist fehlgeschlagen!';

$stirng['error:lit:loadfailed'] = 'Das Laden der Literatur mit der ID: {$litid} ist fehlgeschlagen!';

$string['error:post:litfailedcm'] = 'Die Literatur mit der ID: {$lit->id} konnte nicht veröffentlicht werden.' .
        'Grund: Der entsprechende Eintrag in die Datenbanktabelle course_module war nicht erfolgreich!';

$string['error:search:saveresultsfailed'] = 'Das Speichern der Ergebnisse in der Datenbank ist fehlgeschlagen!';
$string['error:search:timestampnotfound'] = 'Das Laden der Ergebnisse aus der Datenbank ist fehlgeschlagen! Versuchen Sie erneut zu suchen.';
$string['error:search:couldnotloadresult'] = 'Das Suchergebnis wurde nicht mehr in der Datenbank gefunden. Bitte suchen sie erneut.';

$string['error:searchsource:dbobjectmissing'] = 'Das dbobject der Suchquelle konnte nicht gefunden werden. Installieren sie das dazugehörige Subplugin erneut oder löschen' .
        ' sie den Eintrag der Suchquelle von Hand in der Datenbank.';
$string['error:searchsource:formnotfound'] = 'Das Formular für die gewählte Suchquelle konnte nicht gefunden werden!';
$string['error:searchsource:functionmakeformdata'] = 'Die gewählte Suchquelle ist fehlerhaft. Die Funktion {$functionname} ist nicht implementiert!';
$string['error:searchsource:lib'] = 'Fehler beim Laden der Bibliothek für die Suchquellen!';
$string['error:searchsource:noinstalled'] = 'Noch keine Suchquelle installiert! Bitten sie den Administrator eine Suchquelle zu installieren.';
$string['error:searchsource:notinstalled'] = 'Suchquelle {$sourcetype} ist nicht installiert. Suchobjekt nicht gefunden!';
$string['error:searchsource:confignotfound'] = 'Die angeforderte Konfiguration der Suchquelle existiert nicht!';
$string['error:searchsource:configrefnotfound'] = 'Der Verweis auf die angeforderte Konfiguration der Suchquelle existiert nicht!';
$string['error:searchsource:typemissing'] = 'Fehler beim Auswerten des Suchformulars. Der Typ der Suchqulle fehlt!';
$string['error:searchsource:addfailed'] = 'Anlegen der neuen Suchquelle fehlgeschlagen!';

$string['error:session:nolists'] = 'Keine Listen ausgewählt. Ein anderer Grund kann das Überschreiben der Session sein.';

// NOTIFY
$string['notify:failedtoloadsearchform'] = 'Das Laden des Suchformulars ist fehlgeschlagen!';
$string['notify:nolistselected'] = 'Keine Liste ausgewählt!';
$string['notify:nolitselected'] = 'Keine Literatur ausgewählt!';
$string['notify:novalidaction'] = 'Keine gültige Operation ausgewählt!';

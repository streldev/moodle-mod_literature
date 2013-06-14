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

$functions = array(
    'mod_literature_get_export_formats' => array(
        'classname' => 'mod_literature_external',
        'methodname' => 'get_export_formats',
        'classpath' => 'mod/literature/externallib.php',
        'description' => 'This function returns the available export formats.',
        'type' => 'read'
    ),
    'mod_literature_get_import_formats' => array(
        'classname' => 'mod_literature_external',
        'methodname' => 'get_import_formats',
        'classpath' => 'mod/literature/externallib.php',
        'description' => 'This function return the available import formats.',
        'type' => 'read'
    ),
    'mod_literature_import' => array(
        'classname' => 'mod_literature_external',
        'methodname' => 'import',
        'classpath' => 'mod/literature/externallib.php',
        'description' => 'This function allows to import literature as a literature list in moodle',
        'type' => 'write'
    ),
    'mod_literature_export' => array(
        'classname' => 'mod_literature_external',
        'methodname' => 'export',
        'classpath' => 'mod/literature/externallib.php',
        'description' => 'This function allows to export literature lists from moodle',
        'type' => 'read'
    ),
    'mod_literature_get_literature_lists' => array(
        'classname' => 'mod_literature_external',
        'methodname' => 'get_literature_lists',
        'classpath' => 'mod/literature/externallib.php',
        'description' => 'This function allows to get literature lists descriptions from a user',
        'type' => 'read'
    )
);


// OPTIONAL
// During the plugin installation/upgrade, Moodle installs these services as pre-build services.
// A pre-build service is not editable by administrator.
$services = array(
    'Export Service' => array(
        'functions' => array('mod_literature_get_export_formats', 'mod_literature_export', 'mod_literature_get_literature_lists'),
        'restrictedusers' => 1, // if 1, the administrator must manually select which user can use this service.
        // (Administration > Plugins > Web services > Manage services > Authorised users)
        'enabled' => 1, // if 0, then token linked to this service won't work
        'shortname' => 'mod_literature_export'
    ),
    'Import Service' => array(
        'functions' => array('mod_literature_get_import_formats', 'mod_literature_import'),
        'restrictedusers' => 1, // if 1, the administrator must manually select which user can use this service.
        // (Administration > Plugins > Web services > Manage services > Authorised users)
        'enabled' => 1, // if 0, then token linked to this service won't work
        'shortname' => 'mod_literature_import'
    )
);
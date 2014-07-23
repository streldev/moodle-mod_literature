<?php
/*class searchsource {
	public function searchsource(){
		
	}
}*/

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
 * Defines classes used for plugin info.
*
* @package    core
* @copyright  2013 Petr Skoda {@link http://skodak.org}
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
namespace mod_literature\plugininfo;


use core\plugininfo\base;


defined('MOODLE_INTERNAL') || die();




/**
 * Class for HTML editors
*/
class searchsource extends base {
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * Pre-uninstall hook.
     *
     * This is intended for disabling of plugin, some DB table purging, etc.
     *
     * NOTE: to be called from uninstall_plugin() only.
     * @private
     */
    public function uninstall_cleanup() {
        global $DB;

        // Do the opposite of db/install.php scripts - deregister the report.

        //$DB->delete_records('quiz_reports', array('name'=>$this->name));

        parent::uninstall_cleanup();
    }
}
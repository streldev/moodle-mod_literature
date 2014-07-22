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
 * Dobject of the searchsource "sru"
 *
 * Implements the db logic for the configrations of the subplugin
 *
 * @package    mod_literature_searchsource
 * @subpackage sru
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_sru_dbobject {

    /**
     * The id of the db entry
     * @var int
     */
    public $id;

    /**
     * The name of the searchsource
     * @var string
     */
    public $name;

    /**
     * The sru server of the searchsource
     * @var string
     */
    public $server;

    /**
     * The db table for the searchsources
     * @var string
     */
    private static $table = 'searchsource_sru';

    /**
     * Insert a configuration in the db
     */
    public function insert() {
        global $DB;

        $this->id = $DB->insert_record(self::$table, $this);

        return $this->id;
    }

    /**
     * Update a configuration in the db
     */
    public function update() {
        global $DB;

        return $DB->update_record(self::$table, $this);
    }

    /**
     * Delete a configuration from db
     * @param int $id The id of the configuration
     * @return boolean true
     */
    public static function delete($id) {
        global $DB;

        return $DB->delete_records(self::$table, array('id' => $id));
    }

    /**
     * Load a configuration from db
     * @param int $id The id of the configuration
     * @return boolean|fieldset false or fieldset with configuration
     */
    public static function load($id) {
        global $DB;

        return $DB->get_record(self::$table, array('id' => $id));
    }

}
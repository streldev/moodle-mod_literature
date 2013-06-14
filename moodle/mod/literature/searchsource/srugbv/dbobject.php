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
 * Dbobject of the searchsource "srugbv"
 *
 * Implements the db logic for the configrations of the subplugin
 *
 * @package    mod_literature_searchsource
 * @subpackage srugbv
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_srugbv_dbobject {

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
     * The bib code of the library
     * @var string
     */
    public $bibcode;

    /**
     * The db table the objects of the searchsource are saved
     * @var string
     */
    private static $table = 'searchsource_srugbv';

    /**
     * Insert a configuration in the db
     */
    public function insert() {
        global $DB;

        $entry = new stdClass();
        $entry->name = $this->name;
        $entry->bibcode = $this->bibcode;

        $this->id = $DB->insert_record(self::$table, $entry);

        return $this->id;
    }

    /**
     * Update a configuration in the db
     */
    public function update() {
        global $DB;

        $entry = new stdClass();
        $entry->id = $this->id;
        $entry->name = $this->name;
        $entry->bibcode = $this->bibcode;

        return $DB->update_record(self::$table, $entry);
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
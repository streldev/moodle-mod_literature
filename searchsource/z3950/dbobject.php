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
 * Dbobject of the searchsource "z3950"
 *
 * Implements the db logic for the configrations of the subplugin
 *
 * @package    mod_literature_searchsource
 * @subpackage z3950
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_z3950_dbobject {

    /**
     * The id of the db entry
     * @var int
     */
    public $id;

    /**
     * The name of the searchsource
     * @var name
     */
    public $name;

    /**
     * The Z39.50 host
     * @var string
     */
    public $host;

    /**
     * The user for the host
     * @var string
     */
    public $user = '';

    /**
     * The password for the host
     * @var string
     */
    public $password = '';

    /**
     * The profile entries
     * @var array
     */
    public $profile = array();

    /**
     * The db table for the searchsource entries
     * @var string
     */
    private static $table = 'searchsource_z3950';

    /**
     * The db table for the profile entries
     * @var string
     */
    private static $profiletable = 'searchsource_z3950_profile';

    /**
     * Insert a configuration in the db
     */
    public function insert() {
        global $DB;

        $entry = new stdClass();
        $entry->name = $this->name;
        $entry->host = $this->host;
        $entry->user = $this->user;
        $entry->password = $this->password;

        if (!$this->id = $DB->insert_record(self::$table, $entry)) {

            return false;
        }

        foreach ($this->profile as $profileentry) {

            $profileentry->profileid = $this->id;
            $DB->insert_record(self::$profiletable, $profileentry);
            // TODO in later version catch error and notify user
        }

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
        $entry->host = $this->host;
        $entry->user = $this->user;
        $entry->password = $this->password;

        if (!$DB->update_record(self::$table, $entry)) {
            return false;
        }

        // Cleanup profile
        $DB->delete_records(self::$profiletable, array('profileid' => $this->id));

        foreach ($this->profile as $profileentry) {

            $profileentry->profileid = $this->id;
            $DB->insert_record(self::$profiletable, $profileentry);
            // TODO in later version catch error and notify user
        }
    }

    /**
     * Delete a configuration from db
     * @param int $id The id of the configuration
     * @return boolean true
     */
    public static function delete($id) {
        global $DB;

        if (!$DB->delete_records(self::$table, array('id' => $id))) {
            return false;
        }

        return $DB->delete_records(self::$profiletable, array('profileid' => $id));
    }

    /**
     * Delete z39.50 profile
     * @param int $id The id of the profile
     */
    public static function deleteprofile($id) {
        global $DB;

        return $DB->delete_records(self::$profiletable, array('id' => $id));
    }

    /**
     * Load a configuration from db
     * @param int $id The id of the configuration
     * @return boolean|fieldset false or fieldset with configuration
     */
    public static function load($id) {
        global $DB;

        if (!$result = $DB->get_record(self::$table, array('id' => $id))) {
            return false;
        }

        $profiles = $DB->get_records(self::$profiletable, array('profileid' => $id));

        $source = new literature_searchsource_z3950_dbobject();
        $source->id = $result->id;
        $source->name = $result->name;
        $source->host = $result->host;
        $source->user = $result->user;
        $source->password = $result->password;
        $source->profile = $profiles;

        return $source;
    }

}
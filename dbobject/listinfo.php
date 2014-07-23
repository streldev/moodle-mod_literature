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
 * Literatur List Info
 *
 * The class implements the database logic for the informations about a
 * literature list and is a part of the plugins data model
 *
 * @package    mod_literature_dbobject
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_dbobject_listinfo {

    /**
     * The id of the db entry
     * @var int
     */
    public $id;

    /**
     * The name of the list
     * @var string
     */
    public $name;

    /**
     * The userid of the list owner
     * @var int
     */
    public $userid;

    /**
     * The timestamp of the creation
     * @var int
     */
    public $created;

    /**
     * The list description
     * @var string
     */
    public $description;

    /**
     * The timestamp of the last modification
     * @var int
     */
    public $modified;

    /**
     * Is list public?
     * @var boolean
     */
    public $public;

    /**
     * The db table for objects of this class
     * @var string
     */
    public static $table = 'literature_lists';

    public function __construct($id, $name, $userid, $created, $description = null, $modified = 0, $public = 0) {

        $this->id = $id;
        $this->name = $name;
        $this->userid = $userid;
        $this->created = $created;
        $this->description = $description;
        $this->modified = $modified;
        $this->public = $public;
    }

    /**
     * Set the modified attribute to a given timestamp
     *
     * If no timestamp is given, the current time is set.
     *
     * @param int $timestamp A linux timestamp
     */
    public function set_modified($timestamp = null) {
        if ($timestamp == null) {
            $this->modified = time();
        } else {
            $this->modified = $timestamp;
        }
    }

    /**
     * Insert listinfo in db
     *
     * @return boolean|int false or new id
     */
    public function insert() {
        global $DB, $USER;

        if ($this->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        $result = $DB->insert_record(self::$table, $this, true);
        $this->id = $result;

        return $result;
    }

    /**
     * Update record in db
     * @return boolean
     */
    public function save() {
        global $DB, $USER;

        if ($this->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        return $DB->update_record(self::$table, $this);
    }

    /**
     * Load listinfo by id
     *
     * @param int $id id of the {@link literature_dbobject_listinfo} object that should be loaded from db
     * @return boolean|literature_dbobject_listinfo false or listinfo
     */
    public static function load_by_id($id) {
        global $DB, $USER;

        if (!$listinfo = $DB->get_record(self::$table, array('id' => $id))) {
            return false;
        }

        // Check if user owns list or list is public
        if ($USER->id != $listinfo->userid && !$listinfo->public) {
            print_error('error:list:accessdenied', 'literature');
        }

        return new literature_dbobject_listinfo($listinfo->id, $listinfo->name, $listinfo->userid, $listinfo->created,
                        $listinfo->description, $listinfo->modified, $listinfo->public);
    }

    /**
     * Load all listinfos of the given user
     *
     * @param int $id The id of the user
     * @return multitype:literature_dbobject_listinfo All listinfos belongig to the user
     */
    public static function load_by_userid($id) {
        global $DB, $USER;

        if (!$listinfos = $DB->get_records(self::$table, array('userid' => $id))) {
            return array();
        }

        $results = array();
        foreach ($listinfos as $info) {

            if ($info->userid == $USER->id || $info->public) {
                $results[] = new literature_dbobject_listinfo($info->id, $info->name, $info->userid,
                                $info->created, $info->description, $info->modified);
            }
        }
        return $results;
    }

    /**
     * Delete a listinfo from db by id
     *
     * @param int $id The id of the listinfo that should be deleted
     * @return boolean true
     */
    public static function del_by_id($id) {
        global $DB, $USER;

        if (!$listinfo = self::load_by_id($id)) {
            return true;
        }

        if ($listinfo->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        return $DB->delete_records(self::$table, array('id' => $id));
    }

    /**
     * Delete the calling listinfo from db
     *
     * @return boolean true
     */
    public function delete() {
        return self::del_by_id($this->id);
    }

}
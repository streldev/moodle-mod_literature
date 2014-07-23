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

require_once('listinfo.php');
require_once('literature.php');

/**
 * Literature List Class
 *
 * The class implements the database logic for a Literature list
 * and is a part of the plugins data model
 *
 * @package    mod_literature_dbobject
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_dbobject_literaturelist {

    /**
     * The informations about the list
     *
     * Name, owner, description, ...
     * @var literature_dbobject_listinfo
     */
    public $info;

    /**
     * The list items
     *
     * An array of {@link literature_dbobject_literature} objects
     * @var array
     */
    public $items;

    public function __construct(literature_dbobject_listinfo $info, array $items) {

        $this->info = $info;
        $this->items = $items;
    }

    /**
     * Insert list in db
     * @return boolean true if successful; false otherwise
     */
    public function insert() {
        global $USER;

        if ($this->info->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        if (!$listid = $this->info->insert()) {
            return false;
        }

        foreach ($this->items as $item) {
            if (!$litid = $item->insert()) {
                print_error('error:db:litinsert', 'literature', '', $item);
            }
            if (!self::add_literature($listid, $litid)) {
                $item->delete();
                print_error('error:db:addlit2list', 'literature', '', $item);
            }
        }

        return $listid;
    }

    /**
     * Update list in db
     * @return boolean true if successful; false otherwise
     */
    public function save() {
        global $USER;

        if ($this->info->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        if (!$this->info->save()) {
            return false;
        }

        return true;
    }

    /**
     * Load a list from db
     * @param int $id The id of the list that should be loaded
     * @return boolean|literature_dbobject_literaturelist false or the list
     */
    public static function load_by_id($id) {
        global $DB;

        if (!$info = literature_dbobject_listinfo::load_by_id($id)) {
            return false;
        }

        $listitems = $DB->get_records('literature_list_lit', array('list_id' => $id));

        $items = array();
        $failedids = array();
        foreach ($listitems as $listitem) {
            $litid = $listitem->lit_id;
            if (!$items[] = literature_dbobject_literature::load_by_id($litid)) {
                $DB->delete_records('literature_list_lit', array('lit_id' => $litid));
                $failedids[] = $litid;
            }
        }

        if (count($failedids) > 0) {
            $a = count($failedids);
            print_error('error:list:entriesmissing', 'literature', '', $a);
        }

        return new literature_dbobject_literaturelist($info, $items);
    }

    /**
     * Delte entries of a list from db
     *
     * @param int $id The id of the list that should be deleted
     */
    public static function del_by_id($id) {
        global $DB, $USER;

        if (!$listinfo = literature_dbobject_listinfo::load_by_id($id)) {
            return true;
        }

        if ($listinfo->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        // Delete items
        $items = self::get_item_ids($id);
        foreach ($items as $item) {
            literature_dbobject_literature::del_by_id($item->lit_id);
        }

        // Delete entries in jointable
        $DB->delete_records('literature_list_lit', array('list_id' => $id));

        // Delete listinfo
        literature_dbobject_listinfo::del_by_id($id);

        return true;
    }

    /**
     * Delete the entrie of the calling object from db
     */
    public function delete() {

        self::del_by_id($this->id);
    }

    /**
     * Get the ids of the list items
     *
     * @param int $listid The id of the list
     * @return multitype:int The ids of the literature
     */
    private static function get_item_ids($listid) {
        global $DB;

        // Load to check for access
        if (!literature_dbobject_listinfo::load_by_id($listid)) {
            return false;
        }

        $items = $DB->get_records('literature_list_lit', array('list_id' => $listid));

        return $items;
    }

    /**
     * Add a literature entry to a list
     *
     * @param int $listid The id of the list object in the db
     * @param int $litid The id of the literature object in the db
     * @return boolean
     */
    public static function add_literature($listid, $litid) {
        global $DB, $USER;

        // Load to check for access
        if (!$listinfo = literature_dbobject_listinfo::load_by_id($listid)) {
            return false;
        }

        if ($listinfo->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        if (!$result = $DB->insert_record('literature_list_lit', array('list_id' => $listid, 'lit_id' => $litid))) {
            return false;
        }

        $lit = literature_dbobject_literature::load_by_id($litid);
        if ($lit) {
            $lit->add_ref();
            if (!$lit->save()) {
                $DB->delete_records('literature_list_lit', 'literature', array('list_id' => $listid, 'lit_id' => $litid));
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Delete a literature entry from a list
     *
     * @param int $listid The id of the list object in the db
     * @param int $litid The id of the literature object in the db
     * @return boolean
     */
    public static function del_literature($listid, $litid) {
        global $DB, $USER;

        // Load to check for access
        if (!$listinfo = literature_dbobject_listinfo::load_by_id($listid)) {
            return true;
        }

        if ($listinfo->userid != $USER->id) {
            print_error('error:list:accessdenied', 'literature');
        }

        $result = $DB->delete_records('literature_list_lit', array('list_id' => $listid, 'lit_id' => $litid));
        literature_dbobject_literature::del_by_id($litid);

        return $result;
    }

}
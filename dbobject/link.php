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
 * Link
 *
 * The class implements the database logic for the informations about a
 * link with further informations for a book, etc and is a part of the plugins data model
 *
 * @package    mod_literature_dbobject
 * @copyright  2013 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_dbobject_link {

    /**
     * The id of the db entry
     * @var int
     */
    public $id;

    /**
     * The id of the literature containing the link
     * @var int
     */
    public $lit_id;

    /**
     * The text of the link
     * @var string
     */
    public $text;

    /**
     * The link url
     * @var string
     */
    public $url;

    /**
     * The db table for objects of this class
     * @var string
     */
    public static $table = 'literature_links';

    public function __construct($id, $lit_id, $text, $url) {

        $this->id = $id;
        $this->lit_id = $lit_id;
        $this->text = $text;
        $this->url = $url;
    }

    /**
     * Insert link in db
     *
     * @return boolean|int false or new id
     */
    public function insert() {
        global $DB;

        $result = $DB->insert_record(self::$table, $this, true);
        $this->id = $result;
        return $result;
    }

    /**
     * Update record in db
     * @return boolean
     */
    public function update() {
        global $DB;
        return $DB->update_record(self::$table, $this);
    }

    /**
     * Load link by id
     *
     * @param int $id id of the {@link literature_dbobject_link} object that should be loaded from db
     * @return boolean|literature_dbobject_link false or link
     */
    public static function load_by_id($id) {
        global $DB;

        if (!$link = $DB->get_record(self::$table, array ('id' => $id))) {
            return false;
        }

        return new literature_dbobject_link($link->id, $link->lit_id, $link->text, $link->url);
    }

    /**
     * Load all links for the given literature id
     *
     * @param int $id The id of the literature
     * @return multitype:literature_dbobject_link All links belongig to the literature
     */
    public static function load_by_lit_id($id) {
        global $DB;

        if (!$links = $DB->get_records(self::$table, array ('lit_id' => $id))) {
            return array ();
        }

        $results = array ();
        foreach ($links as $link) {
            $results[] = new literature_dbobject_link($link->id, $link->lit_id, $link->text, $link->url);
        }
        return $results;
    }

    /**
     * Delete a link from db by id
     *
     * @param int $id The id of the link that should be deleted
     * @return boolean true
     */
    public static function del_by_id($id) {
        global $DB;

        if (!$link = self::load_by_id($id)) {
            return true;
        }

        return $DB->delete_records(self::$table, array ('id' => $id));
    }

    public static function del_by_lit_id($id) {
        global $DB;
        return $DB->delete_records(self::$table, array ('lit_id' => $id));
    }

    /**
     * Delete the calling link from db
     *
     * @return boolean true
     */
    public function delete() {
        return self::del_by_id($this->id);
    }

    /**
     * Delete all links of a literature
     * @param int $id Id of the literature
     */
    public function delete_by_lit_id($id) {
        $links = self::load_by_lit_id($id);
        foreach ($links as $link) {
            $link->delete();
        }
        return true;
    }

}

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

include_once 'link.php';

/**
 * Literature Class
 *
 * The class implements the database logic for a literature entry
 * and is a part of the plugins data model
 *
 * @package    mod_literature_dbobject
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_dbobject_literature {
    /**
     * Defines the constant for {@link literature_dbobject_literature} entries
     * of type BOOK
     * @var int
     */

    const BOOK = 1;

    /**
     * Defines the constant for {@link literature_dbobject_literature} entries
     * of type ELECTRONIC
     * @var int
     */
    const ELECTRONIC = 2;

    /**
     * Defines the constant for {@link literature_dbobject_literature} entries
     * of type MISC
     * @var int
     */
    const MISC = 3;

    /**
     * The id of the db entry
     * @var int
     */
    public $id;

    /**
     * The type of literature
     * @var int
     * @see literature_dbobject_literature::BOOK
     * @see literature_dbobject_literature::ELECTRONIC
     * @see literature_dbobject_literature::MISC
     */
    public $type;

    /**
     * The titel of the literature
     * @var string
     */
    public $title;

    /**
     * The subtitle of the literature
     * @var string
     */
    public $subtitle;

    /**
     * The authors of the literature
     * @var string
     */
    public $authors;

    /**
     * The publisher of the literature
     * @var string
     */
    public $publisher;

    /**
     * The date the literature was/gets published
     * @var string
     */
    public $published;

    /**
     * The series the literature is a part of
     * @todo implement in parsers
     * @var string
     */
    public $series;

    /**
     * The isbn10 of the literature
     * @var string
     */
    public $isbn10;

    /**
     * The isbn13 of the literature
     * @var string
     */
    public $isbn13;

    /**
     * The issn of the literature
     * @var string
     */
    public $issn;

    /**
     * The path to the cover of the literature
     * @var string
     */
    public $coverpath;

    /**
     * The description of the literature
     * @var string
     */
    public $description;

    /**
     * A link belonging to the literature
     * @var array of link objects
     */
    public $links;

    /**
     * The format of the literature
     *
     * Pages, Chapter, Medium
     * @var string
     */
    public $format;

    /**
     * A link to an external representation of the literature
     * @var string
     */
    public $titlelink;

    /**
     * Counts the references on the db entry
     * @var int
     */
    public $refs;

    /**
     * The db table for {@link literature_dbobject_literature} objects
     * @var string
     */
    public static $table = 'literature_lit';

    public function __construct($id, $type, $title, $subtitle, $authors, $publisher, $published, $series, $isbn10, $isbn13,
            $issn, $coverpath, $description, $links, $format, $titlelink, $refs) {

        $this->id = $id;

        // TYPE
        $this->type = $type;

        // TITLE
        $cleanedtitle = trim($title, " /:-");
        $this->title = html_entity_decode($cleanedtitle); // ugly workaround resolve in later versions
        // SUBTITLE
        $cleanedsubtitle = trim($subtitle, " /:-;");
        $this->subtitle = html_entity_decode($cleanedsubtitle); // ugly workaround resolve in alter versions
        // AUTHORS
        $this->authors = $authors;

        // PUBLISHER
        $this->publisher = $publisher;

        // PUBLISHED
        $this->published = $published;

        // SERIES
        $this->series = $series;

        // ISBN10
        $cleanedisbn10 = preg_replace("/[^0-9Xx]/", "", $isbn10);
        $this->isbn10 = $cleanedisbn10;

        // ISBN13
        $cleanedisbn13 = preg_replace("/[^0-9Xx]/", "", $isbn13);
        $this->isbn13 = $cleanedisbn13;

        // ISSN
        $this->issn = $issn;

        // COVERPATH
        $this->coverpath = $coverpath;

        // DESCRIPTION
        $this->description = $description;

        // LINKS TO READ
        $this->links = array();
        foreach ($links as $link) {
            $this->links[] = new literature_dbobject_link($link->id,$link->lit_id,$link->text, $link->url);
        }

        // FORMAT
        $this->format = $format;

        // TITLE LINK
        $this->titlelink = $titlelink;

        // REFS
        $this->refs = isset($refs) ? $refs : 0;
    }

    /**
     * Insert literature object in db
     *
     * @param boolean $enrich Should the entry get enriched?
     * @return boolean|int false or new id
     */
    public function insert($enrich=true) {
        global $DB;

        if($enrich) {
            literature_enricher_enrich($this);
        }
        $this->refs = 0;

        $result = $DB->insert_record(self::$table, $this, true);
        if ($result) {
            $this->id = $result;
            // Save all links
            foreach($this->links as $link) {
                $link->lit_id = $this->id;
                $link->insert();
            }
        }

        return $result;
    }
    
    /**
     * Duplicte this literature object in db
     *
     * After an edit to an entry this function saves the edited entry
     * in a new db row.
     * 
     * @return boolean|int false or new id
     */
    public function duplicate() {
        $this->id = null;
        $this->refs = 1;
        return $this->insert(false);    
    }

    /**
     * Update literature object in db
     * 
     * This function checks if the edited literature entry has some refs pointing on.
     * If there are some refs the edited entry gets duplicated, otherwise saved.
     * 
     * @return boolean|int false or new id
     */
    public function update() {
        
        if($this->refs > 1) {
            
            // Load the old and decrease the ref counter
            $oldEntry = literature_dbobject_literature::load_by_id($this->id);
            if($oldEntry) {
                $oldEntry->del_ref();
            }
            $oldEntry->save();
            
            // Set refs to 0 and save the modified entry
            $this->refs = 1;
            return $this->duplicate();
            
        } else {
            
            return $this->save();
            
        } 
    }
    
    public function save() {
        global $DB;
        
        // Delete old links
        literature_dbobject_link::del_by_lit_id($this->id);
        
        // Save new links
        foreach($this->links as $link) {
            $link->lit_id = $this->id;
            $link->insert();
        }
        
        if($DB->update_record(self::$table, $this)) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Load a {@link literature_dbobject_literature} object from db
     *
     * @param int $id The id of the object which should be loaded from db
     */
    public static function load_by_id($id) {
        global $DB;

        // Load literature
        if (!$item = $DB->get_record(self::$table, array('id' => $id), '*', MUST_EXIST)) {
            return false;
        }

        // Load links
        $item->links = literature_dbobject_link::load_by_lit_id($id);
        
        return new literature_dbobject_literature($item->id, $item->type, $item->title, $item->subtitle, $item->authors,
                        $item->publisher, $item->published, $item->series, $item->isbn10, $item->isbn13, $item->issn,
                        $item->coverpath, $item->description, $item->links, $item->format, $item->titlelink, $item->refs);
    }

    /**
     * Delete the entry of a {@link literature_dbobject_literature} object from db
     * @param int $id The id of the object
     * return boolean true
     */
    public static function del_by_id($id) {
        global $DB;

        if (!$literature = self::load_by_id($id)) {
            return false;
        }

        // Literature has more refs --> do not delete and reduce refs
        if ($literature->has_refs()) {
            $literature->del_ref();
            $literature->save();
            return true;
        }
        
        // Delete links
        foreach($literature->links as $link) {
            $link->delete();
        }

        // If cover delete
        if (!empty($literature->coverpath)) {

            $context = get_context_instance(CONTEXT_SYSTEM);
            $filename = basename($literature->coverpath);

            // Prepare file record object
            $fileinfo = array(
                'contextid' => $context->id, // ID of context
                'component' => 'mod_literature', // usually = table name
                'filearea' => 'enricher', // usually = table name
                'itemid' => 0, // usually = ID of row in table
                'filepath' => '/', // any path beginning and ending in /
                'filename' => $filename); // any filename

            $fs = get_file_storage();
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
            if ($file) {
                $file->delete();
            }
        }

        // Delete literature
        return $DB->delete_records(self::$table, array('id' => $id));
    }

    /**
     * Delete the entry of the calling {@link literature_dbobject_literature} object from db
     * @return true
     */
    public function delete() {
        return self::del_by_id($this->id);
    }

    /**
     * Delete one reference from the calling object
     */
    public function del_ref() {

        if ($this->refs > 0) {
            $this->refs--;
        } else {
            print_error('error:db:incorectlinkhandling', 'literature');
        }
    }

    /**
     * Add one reference to the calling object
     */
    public function add_ref() {
        $this->refs++;
    }

    /**
     * Check if the calling object has multiple references
     * @return boolean true if the object hast more then one reference, false otherwise
     */
    public function has_refs() {

        if ($this->refs > 1) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @return types
     */
    public static function getTypes() {
        
        $types = array();
        
        $book = get_string('book', 'literature');
        $electronic = get_string('electronic', 'literature');
        $misc = get_string('misc', 'literature');

        $types[self::BOOK] = $book;
        $types[self::ELECTRONIC] = $electronic;
        $types[self::MISC] = $misc;
        
        return $types;
    }

}

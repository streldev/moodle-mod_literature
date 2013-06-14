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
 * Literature Class
 *
 * The class implements the database logic for a literature entry
 * and is a part of the plugins data model
 *
 * @package    mod_literature_dbobjects
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class Literature {
	
	/**
	 * Defines the constant for {@link Literature} entries
	 * of type BOOK
	 * @var int
	 */
	const BOOK = 1;
	
	/**
	 * Defines the constant for {@link Literature} entries
	 * of type ELECTRONIC
	 * @var int
	 */
	const ELECTRONIC = 2;
	
	/**
	 * Defines the constant for {@link Literature} entries
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
	 * @see Literature::BOOK
	 * @see Literature::ELECTRONIC
	 * @see Literature::MISC
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
	 * @todo implement db table for links that one literature can
	 * have more than one link
	 * @var string
	 */
	public $linktoread;
	
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
	 * Link counter
	 * 
	 * Counts the references on the db entry
	 * @var int
	 */
	public $links;
	
	/**
	 * The db table for {@link Literature} objects
	 * @var string
	 */
	static $table = 'literature_lit';
		
	function __construct($id, $type, $title, $subtitle, $authors, $publisher, $published, $series, $isbn10, $isbn13,
				$issn, $coverpath, $description, $linktoread, $format, $titlelink, $links) {
		
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
		$cleanedisbn = preg_replace("/[^0-9Xx]/","", $isbn10);
		$this->isbn10 = $cleanedisbn;
		
		// ISBN13
		$cleanedisbn = preg_replace("/[^0-9Xx]/","", $isbn13);
		$this->isbn13 = $cleanedisbn;
		
		// ISSN
		$this->issn = $issn;
		
		// COVERPATH
		$this->coverpath = $coverpath;
		
		// DESCRIPTION
		$this->description = $description;
		
		// LINK TO READ
		$this->linktoread = $linktoread;
		
		// FORMAT
		$this->format = $format;
		
		// TITLE LINK
		$this->titlelink = $titlelink;
		
		// LINKS
		$this->links = isset($links) ? $links : 0;
		
	}
	
	/**
	 * Insert literature object in db
	 *
	 * @return boolean|int false or new id
	 */
	function insert()  {		
		global $DB;
		
		literature_enricher_enrich($this);
		
		$this->links = 0;
		
		if($result = $DB->insert_record(Literature::$table, $this, true)) {
			$this->id = $result;
		}
		
		return $result;
	}
	
	/**
	 * Update literature object in db
	 * 
	 * The object with the same id as the calling instance gets updated in db.
	 */
	function save() {
		global $DB;
		
		return $DB->update_record(Literature::$table, $this);
		
	}
	
	/**
	 * Load a {@link Literature} object from db
	 * 
	 * @param int $id The id of the object which should be loaded from db
	 */
	static function load_by_id($id) {
		global $DB, $CFG;
	
		// Load literature
		if(!$item = $DB->get_record(Literature::$table, array('id'=>$id), '*', MUST_EXIST)) {
			return false;
		}
	
		return new Literature($item->id, $item->type, $item->title, $item->subtitle, $item->authors, 
				$item->publisher, $item->published, $item->series, $item->isbn10, $item->isbn13, $item->issn,
				$item->coverpath, $item->description, $item->linktoread,$item->format, $item->titlelink, $item->links);
	}
	
	/**
	 * Delete the entry of a {@link Literature} object from db
	 * @param int $id The id of the object
	 * return boolean true
	 */
	static function del_by_id($id) {
		global $DB;
		
		if(!$literature = self::load_by_id($id)) {
			return false;
		}
		
		// Literature has more links --> do not delete and reduce links
		if($literature->has_links()) {
			$literature->del_link();
			$literature->save();
			return true;
		}
		
		// If cover delete
		if(!empty($literature->coverpath)) {
			
			$context = get_context_instance(CONTEXT_SYSTEM);
			$filename = basename($literature->coverpath);
			
			// Prepare file record object
			$fileinfo = array(
					'contextid' => $context->id, // ID of context
					'component' => 'mod_literature', // usually = table name
					'filearea' => 'enricher',  // usually = table name
					'itemid' => 0, // usually = ID of row in table
					'filepath' => '/', // any path beginning and ending in /
					'filename' => $filename); // any filename
			
			$fs = get_file_storage();
			if($file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
					$fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'])) {
				$file->delete();
			}
		}

		return $DB->delete_records(Literature::$table, array('id'=>$id));
		
	}
	
	/**
	 * Delete the entry of the calling {@link Literature} object from db
	 * @return true
	 */
	public function delete() {
		
		return Literature::del_by_id($this->id);
	}
	
	/**
	 * Delete one link from the calling object
	 */
	public  function del_link() {

		if ($this->links > 0) {
			$this->links--;
		} else {
			print_error('error:db:incorectlinkhandling', 'literature');
		}
	}
	
	/**
	 * Add one link to the calling object
	 */
	public function add_link() {
		$this->links++;
	}
	
	/**
	 * Check if the calling object has multiple links
	 * @return boolean true if the object hast more then one link; false otherwise
	 */
	public function has_links() {
		
		if($this->links > 1) {
			return true;
		} else {
			return false;
		}
	}
}
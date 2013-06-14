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


require_once(dirname(dirname(__FILE__)) . '/format_import.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/dbobject/literature.php');

/**
 * BibTex Import Class
 *
 * This class is part of the BibTex Converter Subplugin
 * and imports {@link literature_dbobject_literature} objets from a BibTex file
 *
 * @package    mod_literature_converter
 * @subpackage bibtex
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_conv_bibtex_import implements literature_conv_format_import {

    /**
     * Supported file extension
     * @var string
     */
    private $extension = '.bib';

    /**
     * Name of the supported format
     * @var string
     */
    private $formatname = 'BibTex';

    /**
     * @see literature_conv_format_import::get_formatname()
     */
    public function get_formatname() {
        return $this->formatname;
    }

    /**
     * @see literature_conv_format_import::get_extension()
     */
    public function get_extension() {
        return $this->extension;
    }

    /**
     * @see literature_conv_format_import::import()
     */
    public function import($content) {

        if (empty($content)) {
            print_error('error:import:nocontent', 'converter_bibtex');
        } else {

            // Get a string array (each line an element)
            $pattern = "/^@/m";
            $entries = preg_split($pattern, $content);
            array_shift($entries);
            $bibentries = $this->split_bib2entries($entries);

            if (count($bibentries) == 0) {
                print_error('error:import:noentries', 'converter_bibtex');
            }

            $litarray = array();
            foreach ($bibentries as $entry) {

                if (!empty($entry[0])) {
                    $litarray[] = $this->get_literature_from_bib($entry);
                }
            }
            return $litarray;
        }
    }

    /**
     * Split the bibtex entries in a more dimensional array
     *
     * Each bibtex entry gets splitted in an array where a tag value pair is one entry
     *
     * @param array $entries Array of the bibtex entries as strings
     * @return An two dimensional array
     */
    private function split_bib2entries($entries) {

        $bibentries = array();
        foreach ($entries as $entry) {

            $lines = preg_split('/,$/m', $entry);
            $last = count($lines) - 1;
            // Delete end delimiter
            $lines[$last] = preg_replace('/}$/', '', $lines[$last]);
            $bibentries[] = $lines;
        }
        return $bibentries;
    }

    /**
     * Build a {@link literature_dbobject_literature} object from an array with bibtex tags and values
     *
     * @param array $tagarray Assoc where the keys are the bibtex tags
     * @param int $type The type of the object (BOOK=1, ELECTRONIC=2, MISC=3)
     */
    private function make_literature($tagarray, $type) {

        $title = (!empty($tagarray['title'])) ? $tagarray['title'] : null;
        $subtitle = (!empty($tagarray['subtitle'])) ? $tagarray['subtitle'] : null;
        $authors = (!empty($tagarray['authors'])) ? $tagarray['authors'] : null;
        $publisher = (!empty($tagarray['publisher'])) ? $tagarray['publisher'] : null;
        $published = (!empty($tagarray['published'])) ? $tagarray['published'] : null;
        $series = (!empty($tagarray['series'])) ? $tagarray['series'] : null;
        $isbn10 = (!empty($tagarray['isbn10'])) ? $tagarray['isbn10'] : null;
        $isbn13 = (!empty($tagarray['isbn13'])) ? $tagarray['isbn13'] : null;
        $issn = (!empty($tagarray['issn'])) ? $tagarray['issn'] : null;
        $description = (!empty($tagarray['description'])) ? $tagarray['description'] : null;
        $linktoread = (!empty($tagarray['linktoread'])) ? $tagarray['linktoread'] : null;

        $literature = new literature_dbobject_literature(null, $type, $title, $subtitle, $authors, $publisher,
                        $published, $series, $isbn10, $isbn13, $issn, null, $description, $linktoread, null, null, 0);

        return $literature;
    }

    /**
     * Convert the tags to the internal equivalents
     *
     * @param array $array Bibtex array with keys are bibtex tags
     * @return array where the keys are changed to the internal quivalents of the bibtex tags
     */
    private function bibtext2stdtags($array) {

        // Minimal
        $litarray['title'] = $array['title'];
        $litarray['authors'] = (isset($array['author'])) ? $array['author'] : null;
        $litarray['published'] = (isset($array['year'])) ? $array['year'] : null;

        if (isset($array['month'])) {
            $litarray['published'] = $array['month'] . '.' . $litarray['published'];
        }

        // Detailed
        if (key_exists('publisher', $array)) {
            $litarray['publisher'] = $array['publisher'];
        }

        if (key_exists('series', $array)) {
            $litarray['series'] = $array['series'];
        }

        if (key_exists('abstract', $array)) {
            $litarray['description'] = $array['abstract'];
        }

        // 		$key = 'keywords';
        // 		if(key_exists($key, $array)) {
        // 			$stdLitArr['tags'] = $array[$key];
        // 		}

        if (key_exists('isbn', $array)) {

            $isbns = $this->get_isbns($array['isbn']);
            $litarray['isbn10'] = isset($isbns->isbn10) ? $isbns->isbn10 : null;
            $litarray['isbn13'] = isset($isbns->isbn13) ? $isbns->isbn13 : null;
        }

        if (key_exists('issn', $array)) {
            $litarray['isbn13'] = $array['issn'];
        }

        // 		$key = 'edition';
        // 		if(key_exists($key, $array)) {
        // 			$stdLitArr['edition'] = $array[$key];
        // 		}

        if (key_exists('url', $array)) {
            $litarray['linktoread'] = $array['url'];
        }

        // Delete special bibtex umlaute
        $cleanarray = array();
        foreach ($litarray as $key => $value) {

            $value = str_replace('{\"a}', 'ä', $value);
            $value = str_replace('{\"o}', 'ö', $value);
            $value = str_replace('{\"u}', 'ü', $value);

            $cleanarray[$key] = $value;
        }

        return $cleanarray;
    }

    /**
     * Get the type of a bibtex entry
     *
     * @param string $string String with type inforamtion
     * @return int 1 if entry is a book, 2 if electronic resource, 3 otherwise
     */
    private function get_type($string) {

        $bookpattern = '/book/i';
        $electronicpattern = '/electronic/i';

        if (preg_match($bookpattern, $string)) {
            return 1; // BOOK
        } else if (preg_match($electronicpattern, $string)) {
            return 2; // ELECTRONIC
        } else {
            return 3; // MISC
        }
    }

    /**
     * Get a {@link literature_dbobject_literature} object from a bibtex array
     *
     * @param array $stringarray The array of a bibtex entry
     */
    private function get_literature_from_bibtex($stringarray) {

        // Check type information
        $typestring = array_shift($stringarray);
        $type = $this->get_type($typestring);
        $tagarray = array();
        foreach ($stringarray as $string) {
            // Split string
            $delimiter = '=';
            $keyvaluearray = explode($delimiter, $string);
            if (count($keyvaluearray) < 2) {
                continue;
            }
            // Get key and value
            $key = trim($keyvaluearray[0]);
            $value = trim($keyvaluearray[1]);
            // Cleanup
            $value = trim($value, ',');
            $value = trim($value, '"');
            // Delete curly braces
            $value = preg_replace("/^\{*/", '', $value);
            $value = preg_replace("/\}*$/", '', $value);
            // Normalize string to lower
            $key = strtolower($key);
            // Set key and value in array
            $tagarray[$key] = $value;
        }
        // Standardisiere die Keys im Array
        $tagarray = $this->bibtext2stdtags($tagarray);
        // Erstelle die Buchklasse
        return $this->make_literature($tagarray, $type);
    }

    /**
     * Extract the isbn10 or isbn13 from a string
     *
     * @param string $isbnstring The string with the isbn
     * @return stdClass with the isbn10 or isbn13 as attributes if found
     */
    private function get_isbns($isbnstring) {

        $result = new stdClass();
        $result->isbn10 = null;
        $result->isbn13 = null;

        $stringarray = explode(' ', $isbnstring);

        foreach ($stringarray as $string) {

            if (strlen($string) < 10) {
                continue;
            }

            $cleanisbn = preg_replace("/[^0-9Xx]/", '', $string);

            if (strlen($cleanisbn) == 10) {
                $result->isbn10 = $cleanisbn;
            }

            if (strlen($cleanisbn) == 13) {
                $result->isbn13 = $cleanisbn;
            }
        }

        return $result;
    }

}
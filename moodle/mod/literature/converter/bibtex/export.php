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

require_once(dirname(dirname(__FILE__)) . '/format_export.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/dbobject/literature.php');

/**
 * BibTex Export Class
 *
 * This class is part of the BibTex Converter Subplugin
 * and exports {@link literature_dbobject_literature} objets in a BibTex file
 *
 * @package    mod_literature_converter
 * @subpackage bibtex
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_conv_bibtex_export implements literature_conv_format_export {

    /**
     * Supported file extension
     * @var string
     */
    private $extension = '.bib';

    /**
     * Name of supported format
     * @var string
     */
    private $formatname = 'BibTex';

    /**
     * @see literature_conv_format_export::get_formatname()
     */
    public function get_formatname() {
        return $this->formatname;
    }

    /**
     * @see literature_conv_format_export::get_extension()
     */
    public function get_extension() {
        return $this->extension;
    }

    /**
     * @see literature_conv_format_export::export()
     */
    public function export(array $literatures, $filename, $writetofile = true) {

        $converteditems = array();

        foreach ($literatures as $literature) {

            // Change the tags to BibTex tags
            $bibarray = $this->std2bibtextags($literature);
            // Format the content
            $name = explode(" ", trim($bibarray['author']));
            $max = count($name) - 1;
            // Get entry name -> user lastname of author
            // if name to short don`t use substr
            if (strlen($name[$max]) < 4) {
                $name = $name[$max];
            } else {
                $name = strtoupper(substr($name[$max], 0, 4));
            }
            // Add year to name
            if (!empty($bibarray['year'])) {
                $len = strlen($bibarray['year']);
                $pos = $len - 2;
                $name .= substr($bibarray['year'], $pos);
            }
            $converteditems[] = $this->format4bibtex($bibarray, $name);
        }

        if ($writetofile) {
            // Write the bibtex entries in a file
            $file = literature_converter_serialize_array($converteditems, $filename, $this->extension);
            if ($file) {
                return $file;
            } else {
                return false;
            }
        } else {
            // Return the bibtex entries as string
            $formated = "";
            foreach ($converteditems as $item) {

                foreach ($item as $line) {

                    $formated .= $line . "\n";
                }
                $formated .= "\n";
            }
            return $formated;
        }
    }

    /**
     * Convert the BibTex tags to the internal equivalents
     *
     * @param literature_dbobject_literature $literature
     * @return array where keys == bibtextags and the values are the corresponding values
     */
    private function std2bibtextags(literature_dbobject_literature $literature) {

        $bibarray = array();
        switch ($literature->type) {

            // BOOK
            case literature_dbobject_literature::BOOK :
                $bibarray['type'] = 'BOOK';
                break;

            // Electronic
            case literature_dbobject_literature::ELECTRONIC :
                $bibarray['type'] = 'ELECTRONIC';
                break;

            // MISC
            default:
                $bibarray['type'] = 'MISC';
        }

        // If subtitle exists concat with title
        if (empty($literature->subtitle)) {
            $title = $literature->title;
        } else {
            $title = $literature->title . ' - ' . $literature->subtitle;
        }
        $bibarray['title'] = $title;

        $bibarray['author'] = $literature->authors;

        // Split date TODO if date seperator != "." this does not work
        $date = explode('.', $literature->published);
        switch (count($date)) {
            case 0 :
                $bibarray['year'] = null;
                break;
            case 1 :
                $bibarray['year'] = $date[0];
                break;
            case 2 :
                $bibarray['year'] = $date[1];
                $bibarray['month'] = $date[0];
            default :
                $bibarray['year'] = $date[2];
                $bibarray['month'] = $date[1];
                break;
        }

        if (!empty($literature->publisher)) {
            $bibarray['publisher'] = $literature->publisher;
        }

        if (!empty($literature->series)) {
            $bibarray['series'] = $literature->series;
        }

        if (!empty($literature->description)) {
            $bibarray['abstract'] = $literature->description;
        }

        // if (isset($literature->tags)) {
        //     $bibArray['keywords'] = $literature->tags;
        // }

        if (!empty($literature->isbn13)) {
            $bibarray['isbn'] = $literature->isbn13;
        } else if (!empty($literature->isbn10)) {
            $bibarray['isbn'] = $literature->isbn10;
        }

        if (!empty($literature->issn)) {
            $bibarray['issn'] = $literature->issn;
        }

        // if (isset($literature->edition)) {
        //     $bibArray['edition'] = $bibArray[$key];
        // }

        return $bibarray;
    }

    /**
     * Formats the Bibtex content
     * @param array $bibarray Array containing a bibtex entry (tag => value)
     * @param string $name The name for the bibtex entry
     * @return Tthe formated content of a bibtex file as string
     */
    private function format4bibtex($bibarray, $name) {
        $formated = array();

        foreach ($bibarray as $key => $value) {
            if ($key == 'type') {
                continue;
            }
            $value = trim($value);
            if (!$this->has_quotes($value)) {
                $value = '"' . $value . '"';
            }
            $value .= ',';
            $formated[] = "\t" . $key . ' = ' . $value;
        }
        // Add first line
        $line = '@' . $bibarray['type'] . '{' . $name . ',';
        array_unshift($formated, $line);
        // Add closing brace
        $formated[] = '}';
        return $formated;
    }

    /**
     * Check if a string has quotes around
     * @param string $value The string to check
     * @return boolean
     */
    private function has_quotes($value) {
        $pattern = '/^".*"$/';
        return preg_match($pattern, $value);
    }

}
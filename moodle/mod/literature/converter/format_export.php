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
 * Converter Export Interface
 *
 * Interface for the Converter Export Classes
 *
 * @package    mod_literature_converter
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface literature_conv_format_export {

    /**
     * Converts an internal entry into a coresponding entry of a given format
     *
     * @param array $literatures An array of {@link literature_dbobject_literature} objects
     * @param string $filename The filename of the file in which the literature should be exported
     * @param boolean $writetofile  If true the parsed entries aren't saved in the file and returned
     * @return stored_file|boolean If succesfull a file of enties in the given format; false otherwise
     */
    public function export(array $literatures, $filename, $writetofile);

    /**
     * Get the formatname
     *
     * @return string The name of the format which the exporter supports
     */
    public function get_formatname();

    /**
     * Get the extension
     *
     * @return string The extension of the format which the exporter supports
     */
    public function get_extension();
}
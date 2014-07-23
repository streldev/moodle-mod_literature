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
 * Converter Import Interface
 *
 * Interface for the Converter Import Classes
 *
 * @package    mod_literature_converter
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface literature_conv_format_import {

    /**
     *
     * Converts a BibTex entry into the internal entry type
     *
     * @param $content Content of the uploaded file
     * @return Data_Book or Data_Journal depends on the type of the entry
     */
    public function import($path);

    /**
     * Get the formatname
     *
     * @return string The name of the format which the importer supports
     */
    public function get_formatname();

    /**
     * Get the extension
     *
     * @return string The extension of the format which the importer supports
     */
    public function get_extension();
}
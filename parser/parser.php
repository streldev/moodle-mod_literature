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
 * Parser Interface
 *
 * This interface have to be implemented by ever subplugin of type "parser"
 *
 * @package    mod_literature_parser
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface literature_parser {

    /**
     * Parse a literature entry
     *
     * @param string $string The entry
     * @param string $titlelink A link to a representation of the data
     * @return literature_dbobject_literature A object of the class literature_dbobject_literature
     */
    public function parse($string, $titlelink);
}
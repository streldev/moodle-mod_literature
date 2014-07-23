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
 * Enricher Interface
 *
 * This interface have to be implemented by each subplugin of type "enricher"
 *
 * @package    mod_literature_enricher
 * @copyright  2012 Frederik Strelczuk <frederik.strelczuk@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class literature_enricher {

    /**
     * Enrich the entries saved in the plugin
     * @param literature_dbobject_literature $literature The entry to enrich
     */
    abstract public function enrich($literature);

    /**
     * Enrich the searchresults
     * @param literature_dbobject_literature $result The searchresult to enrich
     */
    abstract public function enrich_preview($result);

    /**
     * Save a cover in the plugin
     * @param string $url The url of the file
     * @param string $filename The filename the file should get (without extension)
     * @return boolean|string false or path to file
     */
    protected function save_cover($url, $filename) {

        $context = get_context_instance(CONTEXT_SYSTEM);
        $fs = get_file_storage();
        $filecontent = file_get_contents($url);

        if (!$filecontent) {
            return false;
        }

        // Prepare file record object
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_literature',
            'filearea' => 'enricher',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => literature_enricher_unused_filename($filename)
        );

        // Create the new file and return url
        $file = $fs->create_file_from_string($fileinfo, $filecontent);
        return literature_enricher_get_file_url($file, $fileinfo['itemid']);
    }
}
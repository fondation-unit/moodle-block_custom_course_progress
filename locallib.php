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

defined('MOODLE_INTERNAL') || die();

/**
 * custom_course_progress locallib
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/config.php");
require_once("$CFG->libdir/filelib.php");

/**
 * Call the plugin renderer with the data.
 *
 * @return \block_custom_course_progress\output\main_content custom_course_progress main_content renderer
 */
function course_get_completion_generate_content($progresscourses, $idlecourses = array()) {
    return new \block_custom_course_progress\output\main_content($progresscourses, $idlecourses);
}

function get_course_image($course) {
    global $CFG;

    $url = "";
    $courseinlist = new \core_course_list_element($course);
    foreach ($courseinlist->get_course_overviewfiles() as $file) {
        $isimage = $file->is_valid_image();
        $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
    }

    return $url;
}

function cmp($a, $b) {
    return strcmp($a->fullname, $b->fullname);
}

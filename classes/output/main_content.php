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
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_custom_course_progress\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;

class main_content implements renderable, templatable {

    public $progresscourses;
    public $idlecourses;

    /**
     * Constructor.
     */
    public function __construct($progresscourses, $idlecourses) {
        $this->progresscourses = $progresscourses;
        $this->idlecourses = $idlecourses;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data = array(
            'progresscourses' => $this->progresscourses,
            'idlecourses' => $this->idlecourses,
            'pluginbaseurl' => (new moodle_url('/blocks/custom_course_progress'))->out(false),
        );

        return $data;
    }

}

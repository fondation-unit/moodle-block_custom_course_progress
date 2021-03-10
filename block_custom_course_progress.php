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
 * custom_course_progress block
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/config.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/blocks/custom_course_progress/locallib.php");

class block_custom_course_progress extends block_base {
    public function init() {
        global $COURSE;
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_custom_course_progress');
        $this->courseid = $COURSE->id;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_custom_course_progress');
            } else {
                $this->title = $this->config->title;
            }
        }
    }

    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';

        $courses = enrol_get_all_users_courses($USER->id, true);
        $usercourses = array();

        foreach ($courses as $course) {            
            if (!$course) {
                print_error('invalidcourseid');
            }

            $courseobj = $course;
            $courseobj->courseimage = get_course_image($course);
            $completion = new \completion_info($course);

            if ($completion->is_enabled()) {
                $format = \course_get_format($course->id);
                $modinfo = \get_fast_modinfo($course->id);
                $mods = $modinfo->get_cms();
                $count = count($mods);
                if (!$count) {
                    return null;
                }
                $completed = 0;
                $hascourseprogress = false;
                $progress = \core_completion\progress::get_course_progress_percentage($course);

                foreach ($modinfo->get_section_info_all() as $section) {
                    $sectionname = $format->get_section_name($section);
                    $trackedsection = $section;
                    $trackedsection->name = $sectionname;
                    $trackedsection->modules = array();
                    $trackedsection->modcount = 0;
                    $trackedsection->modcompleted = 0;
                    $trackedsection->progress = 0;
                    $trackedsection->link = new moodle_url('/course/view.php', array('id' => $course->id, 'section' => $section->section));
                    $hasprogress = false;

                    foreach ($mods as $module) {
                        if ($module->available == 1 && $module->section == $section->id) {
                            $data = $completion->get_data($module, true, $USER->id);
                            $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
                            // Count the activity with completion
                            if ($module->completion) {
                                $trackedsection->modcount = $trackedsection->modcount + 1;
                            }
                            if ($data->completionstate > 0) {
                                $hasprogress = true;
                                $hascourseprogress = true;
                                $trackedsection->modules[] = $module;
                                $trackedsection->modcompleted = $trackedsection->modcompleted + 1;
                            }
                        }
                    }
                    if ($hasprogress) {
                        $trackedsection->progress = round($trackedsection->modcompleted / $trackedsection->modcount * 100, 1);
                        $courseobj->sections[] = $trackedsection;
                        $courseobj->courseprogress = round($progress, 1);
                    }
                }

                if ($hascourseprogress) {
                    $usercourses[] = $courseobj;
                }
            }
        }

        $content = course_get_completion_generate_content($usercourses);
        $renderer = $this->page->get_renderer('block_custom_course_progress');
        $this->content->text = $renderer->render($content);

        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'all' => false,
            'my' => true
        );
    }
}
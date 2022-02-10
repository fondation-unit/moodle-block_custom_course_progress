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

use block_custom_course_progress\custom_course_progress_lib;

/**
 * custom_course_progress block
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once "$CFG->dirroot/config.php";
require_once "$CFG->dirroot/blocks/custom_course_progress/locallib.php";

class block_custom_course_progress extends block_base
{
    public function init()
    {
        global $COURSE;
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_custom_course_progress');
        $this->courseid = $COURSE->id;
    }

    public function instance_allow_multiple()
    {
        return false;
    }

    public function has_config()
    {
        return true;
    }

    public function instance_allow_config()
    {
        return true;
    }

    public function specialization()
    {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_custom_course_progress');
            } else {
                $this->title = $this->config->title;
            }
        }
    }

    public function get_content()
    {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_system::instance();
        $lib = new custom_course_progress_lib($context);
        $config = get_config('block_custom_course_progress');

        $this->content = new stdClass();
        $lib->prepare_content($USER->id);
        $content = $lib->generate_content();
        $renderer = $this->page->get_renderer('block_custom_course_progress');

        $this->content->text = $renderer->render($content);

        return $this->content;
    }

    public function applicable_formats()
    {
        return array(
            'all' => false,
            'my' => true,
            'user' => true,
        );
    }
}

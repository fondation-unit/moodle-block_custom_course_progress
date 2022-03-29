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
 * custom_course_progress locallib
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext(
    'block_custom_course_progress/report_name',
    get_string('report_name', 'block_custom_course_progress'),
    get_string('report_name_desc', 'block_custom_course_progress'), ''));

$opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
$settings->add(new admin_setting_configstoredfile(
    'block_custom_course_progress/reportlogo',
    get_string('reportlogo_name', 'block_custom_course_progress'),
    get_string('reportlogo_desc', 'block_custom_course_progress'), 'reportlogo', 0, $opts));

$settings->add(new admin_setting_configtext(
    'block_custom_course_progress/author',
    get_string('author', 'block_custom_course_progress'),
    get_string('author_desc', 'block_custom_course_progress'), ''));

$settings->add(new admin_setting_configcheckbox(
    'block_custom_course_progress/user_can_download_report',
    get_string('user_can_download_report', 'block_custom_course_progress'),
    get_string('user_can_download_report_desc', 'block_custom_course_progress'), 0));

$settings->add(new admin_setting_configcheckbox(
    'block_custom_course_progress/showidlecourses',
    get_string('showidlecourses', 'block_custom_course_progress'),
    get_string('showidlecourses_desc', 'block_custom_course_progress'), 1));
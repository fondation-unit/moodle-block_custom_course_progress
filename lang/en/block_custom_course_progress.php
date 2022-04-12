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
 * Classes to enforce the various access rules that can apply to a quiz.
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Custom Course Progress';
$string['blocktitle'] = 'Block title';
$string['custom_course_progress:addinstance'] = 'Add a new block Custom Course Progress';
$string['custom_course_progress:editadvance'] = 'Edit block Custom Course Progress';
$string['custom_course_progress:view'] = 'View block Custom Course Progress';
$string['custom_course_progress:myaddinstance'] = 'Add a new block Custom Course Progress';
$string['export_title'] = 'Progress report';
$string['summary'] = 'Summary of {$a}';

$string['settings:report_name'] = 'Report';
$string['settings:report_name_desc'] = 'The report name shown in the export.';
$string['settings:author'] = 'Author of the export';
$string['settings:author_desc'] = 'Author label of the export file';
$string['settings:reportlogo_name'] = 'Logo report';
$string['settings:reportlogo_desc'] = 'Logo on homepage report';
$string['settings:user_can_download_report'] = 'The user can download his own report';
$string['settings:user_can_download_report_desc'] = 'This settings determines if the user is allowed to download his PDF report himself.';
$string['settings:showidlecourses'] = 'Display the idle courses';
$string['settings:showidlecourses_desc'] = 'This settings determines if the block will display the idle courses.';
$string['settings:trackedmodules'] = 'Tracked modules';
$string['settings:trackedmodules_desc'] = 'This settings determines the modules tracked by the block that will be displayed under each section.';

$string['template:user_progress'] = 'Completed {$a}% of the course';
$string['template:completed_activities'] = 'Completed activities';
$string['template:detail'] = 'Details';
$string['template:other_courses'] = 'Other courses';
$string['template:course_access'] = 'Go to the course';
$string['template:in_progress'] = 'In progress';

$string['export:dateoftheday'] = 'Date of the day : ';
$string['export:worked_courses'] = 'The courses worked on';
$string['export:achieved'] = 'Has achieved';
$string['export:first_day'] = '1<sup>rst</sup> day on the platform : ';
$string['export:total_sections'] = 'Number of sections : ';
$string['export:completed_sections'] = 'Completed sections : ';
$string['export:inprogress_sections'] = 'Started sections : ';
$string['export:untouched_sections'] = 'Untouched sections : ';
$string['export:untouched_courses'] = 'Untouched courses : ';
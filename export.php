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

require_once dirname(__FILE__) . '/../../config.php';
require_once dirname(__FILE__) . '/locallib.php';

defined('MOODLE_INTERNAL') || die();

use block_custom_course_progress\custom_course_progress_lib;

require_login();

global $CFG, $PAGE, $USER;

$personalcontext = context_user::instance($USER->id);
$PAGE->set_url(new moodle_url('/blocks/custom_course_progress/export.php'));
$PAGE->set_context($personalcontext);

$context = context_system::instance();
$config = get_config('block_custom_course_progress');
$lib = new custom_course_progress_lib($context);

// The user must be allowed to download the report.
if (!$config->user_can_download_report || $config->user_can_download_report < 1) {
    redirect($CFG->wwwroot .'/my/');
}

$fs = get_file_storage();

// Prepare file record object
$fileinfo = array(
    'component' => 'block_custom_course_progress', // usually = table name
    'filearea' => 'reportlogo', // usually = table name
    'itemid' => 0, // usually = ID of row in table
    'contextid' => $context->id, // ID of context
    'filepath' => '/', // any path beginning and ending in /
    'filename' => $config->reportlogo); // any filename

// Get file
$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

// Read contents
if ($file) {
    $path = '/' . $fileinfo['contextid'] . '/' . $fileinfo['component'] . '/' . $fileinfo['filearea'] . '/' . $fileinfo['itemid'] . $fileinfo['filename'];
    $logopath = moodle_url::make_file_url('/pluginfile.php', $path);
    $reportlogo = $file->get_content();

    $lib->setReportlogo($reportlogo);

    if (strpos($fileinfo['filename'], '.') !== false) {
        $fileextension = substr($fileinfo['filename'], strrpos($fileinfo['filename'], '.') + 1);
        $lib->setReportext($fileextension);
    }
} else {
    throw new Error('Unable to find file for block_custom_course_progress');
}

$url = $lib->make_export($USER->id, 'export_' . $USER->id . '.pdf');

if (isset($url)) {
    redirect($url);
}

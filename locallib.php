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

namespace block_custom_course_progress;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use pdf;

/**
 * custom_course_progress locallib
 *
 * @package    block_custom_course_progress
 * @copyright  2021 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once "$CFG->dirroot/config.php";
require_once "$CFG->dirroot/course/lib.php";
require_once "$CFG->libdir/filelib.php";

class custom_course_progress_lib
{

    private $progresscourses;
    private $idlecourses;

    /**
     * Constructor.
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    public function prepare_content($userid)
    {
        $courses = enrol_get_all_users_courses($userid, true);
        $progresscourses = array();
        $idlecourses = array();

        foreach ($courses as $course) {
            if (!$course) {
                print_error('invalidcourseid');
            }

            $courseobj = $course;
            $courseobj->courseimage = custom_course_progress_lib::get_course_image($course);
            $courseobj->courselink = new moodle_url('/course/view.php', array('id' => $course->id));
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
                    $trackedsection->sectionlink = new moodle_url('/course/view.php', array('id' => $course->id, 'section' => $section->section));
                    $trackedsection->hasgrades = false;
                    $hasprogress = false;

                    foreach ($mods as $module) {
                        $copymodule = new \stdClass();
                        if ($module->available == 1 && $module->section == $section->id) {
                            $data = $completion->get_data($module, true, $userid);
                            $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;

                            $copymodule->id = $module->id;
                            $copymodule->name = $module->name;
                            $copymodule->modname = $module->modname;

                            if ($module->modname == 'quiz' || $module->modname == 'assign' || $module->modname == 'hvp') {
                                $gradeitems = $this->get_gradeitems($userid, $course->id, $module->instance, $module->modname);
                                $copymodule->gradeitems = $gradeitems;
                                if (isset($gradeitems->id)) {
                                    $gradeitems->finalgrade = $gradeitems->finalgrade + 0;
                                    $trackedsection->hasgrades = true;
                                }
                            }

                            // Count the activity with completion
                            if ($module->completion) {
                                $trackedsection->modcount = $trackedsection->modcount + 1;
                            }

                            if ($data->completionstate > 0) {
                                $hasprogress = true;
                                $hascourseprogress = true;
                                $trackedsection->modules[] = $copymodule;
                                $trackedsection->modcompleted = $trackedsection->modcompleted + 1;
                            }
                        }
                    }

                    if ($section->section > 0) {
                        if ($hasprogress) {
                            $trackedsection->progress = round($trackedsection->modcompleted / $trackedsection->modcount * 100, 1);
                            $courseobj->sections[] = $trackedsection;
                            $courseobj->courseprogress = round($progress, 1);
                            $courseobj->courseprogressdiff = round(100 - $courseobj->courseprogress);
                        } else {
                            $courseobj->noprogresssections[] = $section;
                        }
                    }
                }

                if ($hascourseprogress) {
                    $progresscourses[] = $courseobj;
                } else {
                    $idlecourses[] = $courseobj;
                }
            } else {
                $idlecourses[] = $courseobj;
            }
        }

        usort($progresscourses, "self::cmp");
        usort($idlecourses, "self::cmp");

        $this->progresscourses = $progresscourses;
        $this->idlecourses = $idlecourses;

        return $this;
    }

    /**
     * Call the plugin renderer with the data.
     *
     * @return \block_custom_course_progress\output\main_content custom_course_progress main_content renderer
     */
    public function generate_content()
    {
        return new \block_custom_course_progress\output\main_content($this->progresscourses, $this->idlecourses, $this->context);
    }

    public static function cmp($a, $b)
    {
        return strcmp($a->fullname, $b->fullname);
    }

    public static function get_course_image($course)
    {
        global $CFG;

        $url = "";
        $courseinlist = new \core_course_list_element($course);
        foreach ($courseinlist->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
        }

        return $url;
    }

    /**
     * Get a hash that will be unique and can be used in a path name.
     * @param int|\assign $assignment
     * @param int $userid
     * @param int $attemptnumber (-1 means latest attempt)
     */
    private static function hash($contextid, $userid)
    {
        return sha1($contextid . '_' . $userid);
    }

    /**
     * Save the completed PDF to the given file
     * @param string $filename the filename for the PDF (including the full path)
     */
    private static function save_pdf($pdf, $filename)
    {
        $olddebug = error_reporting(0);
        $pdf->Output($filename, 'F');
        error_reporting($olddebug);
    }

    public function make_export($userid, $filename)
    {
        global $CFG, $DB, $OUTPUT;

        require_once $CFG->libdir . '/pdflib.php';

        $tmpdir = \make_temp_directory('block_custom_course_progress/export/' . self::hash($this->context->id, $userid));
        $combined = $tmpdir . '/' . $filename;
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $username = fullname($user);
        $courses = enrol_get_all_users_courses($userid, true);

        $this->prepare_content($userid);
        $content = new \block_custom_course_progress\output\export_content($username, $this->progresscourses, $this->idlecourses);

        if (isset($content)) {
            $pdf = new pdf();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Fondation UNIT');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->setHeaderMargin(10);
            $pdf->setHeaderFont(array('helvetica', '', 11));
            $pdf->setHeaderData('blocks/custom_course_progress/pix/logo-upr.jpg', 40, get_string('export_title', 'block_custom_course_progress'), $username);

            $pdf->SetTitle(get_string('export_title', 'block_custom_course_progress'));
            $pdf->SetSubject(get_string('export_title', 'block_custom_course_progress'));
            $pdf->SetKeywords('');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFooterFont(array('helvetica', '', 10));
            $pdf->SetFillColor(255, 255, 176);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->setCellHeightRatio(0.8);
            // Set margins.
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            // Get the CSS.
            $styles = '<style>' . file_get_contents($CFG->dirroot . '/blocks/custom_course_progress/styles.css') . '</style>';

            $pdf->AddPage();
            $pdf->setJPEGQuality(100);

            $logos = array(
                $OUTPUT->image_url('logo-disp', 'block_custom_course_progress'),
                $OUTPUT->image_url('logo-unit', 'block_custom_course_progress'),
                $OUTPUT->image_url('logo-sonate', 'block_custom_course_progress'),
                $OUTPUT->image_url('logo-uga', 'block_custom_course_progress'),
                $OUTPUT->image_url('logo-upr', 'block_custom_course_progress'),
            );

            $x = 15;
            $y = 20;
            $w = 80;
            $h = 40;
            $pdf->Rect($x, $y, $w, $h, 'F', array(), array(255, 255, 255));
            $imgdata = file_get_contents($logos[0]);
            $pdf->Image('@' . $imgdata, $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);

            $w = 60;
            $x = 135;
            $pdf->Rect($x, $y, $w, $h, 'F', array(), array(255, 255, 255));
            $imgdata = file_get_contents($logos[1]);
            $pdf->Image('@' . $imgdata, $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);

            $pdf->SetXY(15, 145);
            $pdf->writeHTMLCell(0, 0, 15, 100, '<h1>Rapport</h1><h1>' . $username . '</h1>', 0, 0, false, true, 'C', true);
            $x = 75;
            $y = 130;
            $imgdata = file_get_contents($logos[2]);
            $pdf->Image('@' . $imgdata, $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'C', false, false);

            $x = 15;
            $y = 235;
            $w = 45;
            $pdf->Rect($x, $y, $w, $h, 'F', array(), array(255, 255, 255));
            $imgdata = file_get_contents($logos[3]);
            $pdf->Image('@' . $imgdata, $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);

            $x = 125;
            $w = 70;
            $pdf->Rect($x, $y, $w, $h, 'F', array(), array(255, 255, 255));
            $imgdata = file_get_contents($logos[4]);
            $pdf->Image('@' . $imgdata, $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);

            $pdf->setPrintHeader(true);
            $pdf->AddPage();
            $pdf->setPrintFooter(true);

            // Résumé cours.
            $firstusedate = $this->get_first_use_date($userid, $courses);
            if (!$firstusedate->timecreated) {
                $firstusedate = $user->firstaccess;
            }

            $datenow = new \DateTime('now', new \DateTimeZone(\core_date::normalise_timezone($CFG->timezone)));
            $html = "<h3>Synthèse SONATE de $username</h3>";
            $html .= "<p><br><br></p>";
            $html .= "<em><h4>$user->city</h4></em>";
            $html .= "<p><br><br><br></p>";
            $html .= "<p>Date du jour : " . $datenow->format('d/m/Y') . "</p>";
            $html .= "<p>1<sup>er</sup> jour d'utilisation de la plateforme : " . date('d/m/Y', $firstusedate->timecreated) . "</p>";
            $html .= "<p><br><br><br></p>";
            $html .= "<p><br><br><br></p>";
            $html .= "<h3>Les modules travaillés :</h3>";
            $html .= "<p><br><br><br></p>";
            foreach ($this->progresscourses as $course) {
                if (isset($course) && $course->courseprogress > 0) {
                    $html .= "<h4>$course->fullname</h4>";
                    $html .= "<p>A réalisé " . $course->courseprogress . "%</p>";
                    $validated = 0;
                    $inprogress = 0;
                    foreach ($course->sections as $section) {
                        if ($section->progress > 99) {
                            $validated++;
                        } else if ($section->progress >= 0) {
                            $inprogress++;
                        }
                    }
                    $noprogress = count($course->noprogresssections);
                    $total = count($course->sections) + $noprogress;
                    $html .= "<p>Total de jalons : " . $total . "</p>";
                    $html .= "<p>Jalons complétés : $validated</p>";
                    $html .= "<p>Jalons commencés (mais encore incomplets) : $inprogress</p>";
                    $html .= "<p>Jalons non travaillés : $noprogress</p>";
                    $html .= "<p><br><br><br></p>";
                }
            }
            $html .= "<p><br><br><br><br><br><br><br></p>";
            $html .= "<h3>Les modules non travaillés :</h3>";
            $html .= "<p><br><br><br></p>";
            foreach ($this->idlecourses as $course) {
                $html .= "<h4>$course->fullname</h4><br><br>";
            }
            $pdf->writeHTML($html);

            // Détail cours.
            $pdf->AddPage();
            $pdf->writeHTML($styles . $OUTPUT->render_from_template('block_custom_course_progress/export', $content));
            self::save_pdf($pdf, $combined);
            $pdf->Close();

            $fs = get_file_storage();

            $fileinfo = array(
                'contextid' => $this->context->id, // ID of context
                'component' => 'block_custom_course_progress', // usually = table name
                'filearea' => 'content', // usually = table name
                'itemid' => 0, // usually = ID of row in table
                'filepath' => '/', // any path beginning and ending in /
                'filename' => $filename); // any filename

            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            if ($file) {
                // Delete the old file first
                $file->delete();
            }

            $file = $fs->create_file_from_pathname($fileinfo, $tmpdir . '/' . $filename);

            $path = '/' . $this->context->id . '/block_custom_course_progress/content/' . $file->get_itemid() . $file->get_filepath() . $filename;
            return moodle_url::make_file_url('/pluginfile.php', $path);
        }
    }

    private function get_gradeitems($userid, $courseid, $moduleinstance, $modname)
    {
        global $DB;

        $sql = "SELECT {grade_items}.id AS id, {course}.id as course_id,
                {grade_items}.itemname, {grade_items}.itemtype, {grade_items}.itemmodule,
                ROUND({grade_grades}.finalgrade, 2) AS finalgrade,
                ROUND({grade_grades}.rawgrademax, 0) AS grademax,
                FROM_UNIXTIME({grade_grades}.timecreated) AS date_created,
                FROM_UNIXTIME({grade_grades}.timemodified) AS date_modified,
                SUBSTRING({grade_grades}.feedback, 1, 15)
                FROM {grade_grades}
                JOIN {user} ON {grade_grades}.userid = {user}.id
                JOIN {grade_items} ON {grade_grades}.itemid = {grade_items}.id
                JOIN {course} ON {grade_items}.courseid = {course}.id
                WHERE {user}.id = ?
                AND {course}.id = ?
                AND {grade_items}.iteminstance = ?
                AND {grade_items}.itemmodule = ?
                AND {grade_items}.itemtype = 'mod'
                AND {grade_grades}.finalgrade > 0
                ORDER BY {course}.id, {grade_items}.id, {grade_grades}.timemodified LIMIT 1;";

        return $DB->get_record_sql($sql, array($userid, $courseid, $moduleinstance, $modname));
    }

    private function get_first_use_date($userid, $courses)
    {
        global $DB;

        $sql = "SELECT {logstore_standard_log}.timecreated
                FROM {logstore_standard_log}
                WHERE {logstore_standard_log}.userid = " . $userid . "
                AND {logstore_standard_log}.courseid IN (" . implode(',', array_column($courses, 'id')) . ")
                ORDER BY {logstore_standard_log}.timecreated ASC LIMIT 1;";

        return $DB->get_record_sql($sql);
    }
}

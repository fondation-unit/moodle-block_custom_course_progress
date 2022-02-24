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

require_once($CFG->libdir .'/pdflib.php');

use pdf;

class custompdf extends pdf {

    private $htmlHeader;

    public function setHtmlHeader($htmlHeader) {
        $this->htmlHeader = $htmlHeader;
    }

    public function Header() {
        $this->writeHTMLCell(
            $w = 0,
            $h = 0,
            $x = '',
            $y = '',
            $this->htmlHeader, 
            $border = 0,
            $ln = 1,
            $fill = 0,
            $reseth = true,
            $align = 'top',
            $autopadding = true
        );
    }
}

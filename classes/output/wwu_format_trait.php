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
 * Provide renderer component for WWU-CD section headings.
 *
 * @package theme_wwu2019
 * @copyright 2020 Justus Dieckmann WWU, Jan C. Dageförde WWU
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_wwu2019\output;

use html_writer;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Provide renderer component for WWU-CD section headings.
 *
 * @package theme_wwu2019
 * @copyright 2020 Justus Dieckmann WWU, Jan C. Dageförde WWU
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait wwu_format_trait {

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @return string HTML to output.
     */
    public function wwu_section_header($section, $course, $onsectionpage) {
        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o .= html_writer::start_tag('li', array('id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle, 'role' => 'region',
            'aria-label' => get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $o .= html_writer::start_div('header');

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o .= $this->output->heading($sectionname, 3, 'sectionname');

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));

        $o .= html_writer::end_div();

        $o .= html_writer::start_tag('div', array('class' => 'content'));

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Generate a summary of a section for display on the 'course index page'
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array $mods (argument not used)
     * @return string HTML to output.
     */
    protected function wwu_section_summary($section, $course, $mods) {
        $classattr = 'section main section-summary clearfix';
        $linkclasses = '';

        // If section is hidden then display grey section link
        if (!$section->visible) {
            $classattr .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if (course_get_format($course)->is_section_current($section)) {
            $classattr .= ' current';
        }

        $title = get_section_name($course, $section);
        $o = '';
        $o .= html_writer::start_tag('li', array('id' => 'section-' . $section->section,
                'class' => $classattr, 'role' => 'region', 'aria-label' => $title));

        if ($section->uservisible) {
            $title = html_writer::tag('a', "» $title",
                    array('href' => course_get_url($course, $section->section), 'class' => $linkclasses));
        }
        $o .= html_writer::start_div('header');
        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $o .= $this->output->heading($title, 3, 'section-title sectionname');
        $o .= html_writer::tag('div', '', array('class' => 'right side'));
        $o .= html_writer::end_div();

        $o .= html_writer::start_div('content');
        $o .= $this->section_availability($section);
        $o .= html_writer::start_tag('div', array('class' => 'summarytext'));

        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
        $o .= html_writer::end_tag('div');
        $o .= $this->section_activity_summary($section, $course, null);
        $o .= html_writer::link(course_get_url($course, $section->section), get_string('viewfullsection', 'theme_wwu2019'),
                array('class' => 'mdl-right pr-2 summary-view-full'));
        $o .= html_writer::end_div();
        $o .= html_writer::end_tag('li');

        return $o;
    }
}
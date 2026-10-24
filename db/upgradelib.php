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
 * format_board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @author     Michael Meneses <michael@middag.io>
 * @copyright  2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Hide sections above the legacy "numsections" option and remove the option.
 *
 * Up to build 2017120700 sections above numsections were not displayed to
 * students. Since the numsections option is gone, those sections (and their
 * activities) are hidden, the same way hiding a section does it in the UI.
 * No section is deleted.
 */
function format_board_upgrade_hide_orphaned_sections() {
    global $DB;

    $defaultnumsections = (int)get_config('moodlecourse', 'numsections');
    $sql = "SELECT c.id, cfo.value AS numsections
              FROM {course} c
         LEFT JOIN {course_format_options} cfo
                ON cfo.courseid = c.id AND cfo.format = c.format
                   AND cfo.sectionid = 0 AND cfo.name = :name
             WHERE c.format = :format";
    $courses = $DB->get_recordset_sql($sql, ['name' => 'numsections', 'format' => 'board']);
    foreach ($courses as $course) {
        $numsections = ($course->numsections === null) ? $defaultnumsections : (int)$course->numsections;
        format_board_upgrade_hide_sections_above($course->id, $numsections);
    }
    $courses->close();

    $DB->delete_records('course_format_options', ['format' => 'board', 'name' => 'numsections']);
}

/**
 * Hide the visible sections of a course above a section number, and their activities.
 *
 * @param int $courseid
 * @param int $numsections last section number that stays untouched
 */
function format_board_upgrade_hide_sections_above($courseid, $numsections) {
    global $DB;

    $sectionids = $DB->get_fieldset_select('course_sections', 'id',
        'course = :courseid AND section > :numsections AND visible = 1',
        ['courseid' => $courseid, 'numsections' => $numsections]);
    if (empty($sectionids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($sectionids, SQL_PARAMS_NAMED);
    $DB->execute("UPDATE {course_modules} SET visibleold = visible, visible = 0 WHERE section $insql", $params);
    $DB->execute("UPDATE {course_sections} SET visible = 0 WHERE id $insql", $params);
    rebuild_course_cache($courseid, true);
}

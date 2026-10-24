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

namespace format_board\output\courseformat;

use format_board;
use renderer_base;

/**
 * Course content grouped in board columns.
 *
 * Columns are only built when the editor is off and the whole course is shown:
 * the reactive course editor (drag and drop, add section) expects a single
 * section list, so editing mode falls back to the standard topics list.
 */
class content extends \format_topics\output\courseformat\content {

    /** Layout value for the "Blocks" section layout. */
    const LAYOUT_BLOCKS = 1;

    /** Column width used when the stored one is invalid. */
    const DEFAULT_WIDTH = 100;

    /**
     * Use the board template instead of core_courseformat/local/content.
     *
     * @param renderer_base $renderer
     * @return string
     */
    public function get_template_name(renderer_base $renderer): string {
        return 'format_board/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $format = $this->format;
        $course = $format->get_course();

        $data->boardlayout = ((int)($course->sectionlayout ?? 0) === self::LAYOUT_BLOCKS) ? 'blocks' : '';
        $data->boardcolor = ($format instanceof format_board) ? $format->get_board_color() : null;

        if ($format->show_editor() || $format->get_sectionid()) {
            return $data;
        }

        $data->hascolumns = true;
        $data->columns = $this->export_columns($data->sections, $course);
        if (empty($course->showdefaultsectionname)) {
            $data->hiddenheaders = $this->get_unnamed_section_numbers($data->sections);
            $data->hashiddenheaders = !empty($data->hiddenheaders);
        }
        return $data;
    }

    /**
     * Split the exported sections into columns ("groups").
     *
     * Section 0 always gets its own full width column. Group N holds
     * numsectionscolN sections (0 = unlimited) with widthcolN width.
     *
     * @param array $sections exported sections
     * @param \stdClass $course course with format options
     * @return array
     */
    protected function export_columns(array $sections, $course): array {
        $max = format_board::get_max_groups();
        $groups = [];
        $initial = [];
        $colnum = 1;
        $count = 0;
        foreach ($sections as $section) {
            if ((int)$section->num === 0) {
                $initial[] = $section;
                continue;
            }
            $groups[$colnum][] = $section;
            $count++;
            $limit = (int)($course->{'numsectionscol'.$colnum} ?? 0);
            if ($limit > 0 && $count >= $limit && $colnum < $max) {
                $colnum++;
                $count = 0;
            }
        }

        $columns = [];
        if (!empty($initial)) {
            $columns[] = ['num' => 0, 'width' => self::DEFAULT_WIDTH, 'sections' => $initial];
        }
        foreach ($groups as $num => $groupsections) {
            $columns[] = [
                'num' => $num,
                'width' => $this->get_column_width($course, $num),
                'sections' => $groupsections,
            ];
        }
        return $columns;
    }

    /**
     * Validated width (percent) of a column.
     *
     * @param \stdClass $course
     * @param int $num column number
     * @return int
     */
    protected function get_column_width($course, int $num): int {
        $width = (int)($course->{'widthcol'.$num} ?? self::DEFAULT_WIDTH);
        return in_array($width, format_board::GROUP_WIDTHS, true) ? $width : self::DEFAULT_WIDTH;
    }

    /**
     * Section numbers without a custom name (their header is hidden).
     *
     * @param array $sections exported sections
     * @return int[]
     */
    protected function get_unnamed_section_numbers(array $sections): array {
        $modinfo = $this->format->get_modinfo();
        $numbers = [];
        foreach ($sections as $section) {
            $sectioninfo = $modinfo->get_section_info((int)$section->num);
            if ($sectioninfo && trim((string)$sectioninfo->name) === '') {
                $numbers[] = (int)$section->num;
            }
        }
        return $numbers;
    }

}

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

require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * format_board
 *
 * Moodle 4.x: inherits section handling, numsections on course creation, highlighting,
 * hiddensections and coursedisplay from format_topics. The legacy "numsections" option
 * was dropped because, together with update_course_format_options(), it deleted sections
 * created through the 4.x "Add section" UI.
 */
class format_board extends format_topics {

    /** Default number of groups when moodlecourse/maxsections is not set. */
    const DEFAULT_MAX_GROUPS = 52;

    /** Allowed group widths (percent). */
    const GROUP_WIDTHS = [100, 75, 66, 50, 33, 25];

    /**
     * Maximum number of section groups (columns).
     *
     * @return int
     */
    public static function get_max_groups() {
        $max = get_config('moodlecourse', 'maxsections');
        if (empty($max) || !is_numeric($max)) {
            return self::DEFAULT_MAX_GROUPS;
        }
        return (int)$max;
    }

    /**
     * course_format_options
     *
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = null;
        $max = self::get_max_groups();
        if ($courseformatoptions === null) {
            $courseformatoptions = parent::course_format_options();
            $courseformatoptions['showdefaultsectionname'] = array(
                'default' => get_config('format_board', 'showdefaultsectionname'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['sectionlayout'] = array(
                'default' => get_config('format_board', 'sectionlayout'),
                'type' => PARAM_INT,
            );
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptions['widthcol'.$i] = array(
                    'default' => get_config('format_board', 'widthcol'.$i),
                    'type' => PARAM_INT,
                );
                $courseformatoptions['numsectionscol'.$i] = array(
                    'default' => get_config('format_board', 'numsectionscol'.$i),
                    'type' => PARAM_INT,
                );
            }
            $courseformatoptions['color'] = array(
                'default' => get_config('format_board', 'color'),
                'type' => PARAM_TEXT,
            );
        }
        if ($foreditform && !isset($courseformatoptions['showdefaultsectionname']['label'])) {
            $courseformatoptionsedit = parent::course_format_options(true);
            $sectionmenu = array(0 => get_string('unlimited', 'format_board'));
            for ($i = 1; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit['showdefaultsectionname'] = array(
                'label' => get_string('showdefaultsectionname', 'format_board'),
                'help' => 'showdefaultsectionname',
                'help_component' => 'format_board',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_board'),
                        0 => get_string('no', 'format_board'),
                    ),
                ),
            );
            $courseformatoptionsedit['sectionlayout'] = array(
                'label' => get_string('sectionlayout', 'format_board'),
                'help' => 'sectionlayout',
                'help_component' => 'format_board',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('none', 'format_board'),
                        1 => get_string('blocks', 'format_board'),
                    ),
                ),
            );
            $widthmenu = array();
            foreach (self::GROUP_WIDTHS as $width) {
                $widthmenu[$width] = $width.'%';
            }
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptionsedit['widthcol'.$i] = array(
                    'label' => get_string('widthcol', 'format_board').' '.$i,
                    'help' => 'widthcol',
                    'help_component' => 'format_board',
                    'element_type' => 'select',
                    'element_attributes' => array($widthmenu),
                );
                $courseformatoptionsedit['numsectionscol'.$i] = array(
                    'label' => get_string('numsectionscol', 'format_board').' '.$i,
                    'help' => 'numsectionscol',
                    'help_component' => 'format_board',
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                );
            }
            $courseformatoptionsedit['color'] = array(
                'label' => get_string('color', 'format_board'),
                'help' => 'color',
                'help_component' => 'format_board',
                'element_type' => 'text',
            );
            $courseformatoptions = array_replace_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Course color as a validated "#rrggbb" / "#rgb" string, or null.
     *
     * @return string|null
     */
    public function get_board_color() {
        $color = $this->get_course()->color ?? '';
        $color = ltrim(trim((string)$color), '#');
        if (!preg_match('/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
            return null;
        }
        return '#'.$color;
    }

}

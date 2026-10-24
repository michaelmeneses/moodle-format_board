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

$plugin->component = 'format_board';
$plugin->version = 2026102400;
$plugin->release = '4.5.0 (Build: 2026102400) by Michael Meneses';
$plugin->requires = 2024100700; /* moodle 4.5.0 */
$plugin->supported = [405, 405];
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = ['format_topics' => ANY_VERSION];

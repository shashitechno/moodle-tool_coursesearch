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
 * Defines the renderer for the Advance Course Search helper plugin.
 *
 * @package    tool_coursesearch
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
/**
 * Renderer for the Advance Course Search
 *
 * @package    tool_coursesearch
 * @copyright  2013 Shashikant Vaishnav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_coursesearch_renderer extends plugin_renderer_base
{
    /**
     * Render the index page.
     * @param string $detected information about what sort of site was detected.
     * @param array $actions list of actions to show on this page.
     * @return string html to output.
     */
    public function test() {
        $this->page->requires->js_init_call('M.tool_coursesearch.init');
    }
    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    public function moodleform(moodleform $mform) {
        $this->page->requires->js_init_call('M.tool_coursesearch.init');
        $this->page->requires->js_init_call('M.tool_coursesearch.loadcontent');
        $this->page->requires->js_init_call('M.tool_coursesearch.deleteAll');
        $this->page->requires->js_init_call('M.tool_coursesearch.optimize');
        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }
}

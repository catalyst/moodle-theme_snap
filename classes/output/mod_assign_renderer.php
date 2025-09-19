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
 * Snap assign renderer.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_snap\output;

class mod_assign_renderer extends \mod_assign\output\renderer {

    /**
     * Renders the submission action menu.
     *
     * @param \mod_assign\output\actionmenu $actionmenu The actionmenu
     * @return string Rendered action menu.
     */
    public function submission_actionmenu(\mod_assign\output\actionmenu $actionmenu): string {
        // Export the original template context.
        $context = $actionmenu->export_for_template($this);

        // Add the view all submissions link that was moved to secondary navigation in MDL-82195 back.
        if (isset($this->page->cm->id)) {
            $submissionlink = new \moodle_url('/mod/assign/view.php', ['id' => $this->page->cm->id, 'action' => 'grading']);
            $context['submissionlink'] = $submissionlink->out(false);
        }

        return $this->render_from_template('mod_assign/submission_actionmenu', $context);
    }
}

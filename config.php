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
 * Theme config
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use theme_snap\local;
use theme_snap\snap_page_requirements_manager;

global $SESSION, $COURSE, $USER, $PAGE;

$theme = local::resolve_theme();
$themeissnap = $theme === 'snap';
$notajaxscript = !defined('AJAX_SCRIPT') || AJAX_SCRIPT == false;
// The code inside this conditional block is to be executed prior to page rendering when the theme is set to snap and
// when the current request is not an ajax request.
// There doesn't appear to be an official hook we can use for doing things prior to page rendering, so this is a
// workaround.
if ($themeissnap && $notajaxscript) {

    // Setup debugging html.
    // This allows javascript to target debug messages and move them to footer.
    if (!empty($CFG->snapwrapdebug) && !function_exists('xdebug_break')) {
        ini_set('error_prepend_string', '<div class="php-debug">');
        ini_set('error_append_string', '</div>');
    }

    // SL - dec 2015 - Make sure editing sessions are not carried over between courses.
    if (empty($SESSION->theme_snap_last_course) || $SESSION->theme_snap_last_course != $COURSE->id) {
        $USER->editing = 0;
        $SESSION->theme_snap_last_course = $COURSE->id;
    }

    // Reset the edition sessions before and after entering to the grader report page.
    if ($PAGE !== null) {
        if (!empty($SESSION->theme_snap_last_page)) {
            $previousgraderreport =
                $SESSION->theme_snap_last_page === "grade-report-grader-index" &&
                $PAGE->pagetype !== "grade-report-grader-index";
            $previousnotgraderreport =
                $SESSION->theme_snap_last_page !== "grade-report-grader-index" &&
                $PAGE->pagetype === "grade-report-grader-index";
            $previousdifferenttocurrent = $SESSION->theme_snap_last_page !== $PAGE->pagetype;

            if ($previousgraderreport) {
                // Previous page was grader report and current page is not grader report.
                $USER->editing = 0;
                $SESSION->theme_snap_last_page = $PAGE->pagetype;
            } else if ($previousnotgraderreport && $USER->editing === 1) {
                // Previous page was not grader report and current page is grader report.
                $USER->editing = 0;
                $SESSION->theme_snap_last_page = $PAGE->pagetype;
            } else if ($previousdifferenttocurrent) {
                // Update theme_snap_last_page with current page.
                $SESSION->theme_snap_last_page = $PAGE->pagetype;
            }
        } else {
            // Initialize theme_snap_last_page with current page.
            $SESSION->theme_snap_last_page = $PAGE->pagetype;
        }
    }

    // Keep Dashboard enabled for Snap.
    $disablesnapmycourses = isset($CFG->theme_snap_disable_my_courses) ? $CFG->theme_snap_disable_my_courses : true;
    if ($disablesnapmycourses) {
        $CFG->enabledashboard = 1;
    }

    if (isset($SESSION->wantsurl)) {
        // We are taking a backup of this because it can get unset later by core.
        $SESSION->snapwantsurl = $SESSION->wantsurl;
    }
}

$THEME->doctype = 'html5';
$THEME->yuicssmodules = array('cssgrids'); // This is required for joule grader.
$THEME->name = 'snap';
$THEME->parents = array('boost');

$THEME->enable_dock = false;
$THEME->prescsscallback = 'theme_snap_get_pre_scss';
$THEME->scss = function($theme) {
    return theme_snap_get_main_scss_content($theme);
};
$THEME->csspostprocess = 'theme_snap_process_css';
$THEME->supportscssoptimisation = false;

$THEME->editor_sheets = array('editor');

$THEME->rendererfactory = 'theme_overridden_renderer_factory';

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default.
    'base' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    // Standard layout with blocks, this is recommended for most pages with general information.
    'standard' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'message' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    // Main course page.
    'course' => array(
        'file' => 'course.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'coursecategory' => array(
        'file' => 'course-index-category.php',
        'regions' => array(),
    ),
    // Part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true),
    ),
    // Server administration pages.
    'admin' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    // My public page.
    'mypublic' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'login' => array(
        'file' => 'login.php',
        'regions' => array(),
        'options' => array('langmenu' => true, 'nonavbar' => true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nocoursefooter' => true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible.
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array()
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, links, or API calls that would lead to database or cache interaction.
    // Please be extremely careful if you are modifying this layout.
    'maintenance' => array(
        'file' => 'maintenance.php',
        'regions' => array(),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
    ),
    // The pagelayout used for reports.
    'report' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // The pagelayout used for safebrowser and securewindow.
    'secure' => array(
        'file' => 'secure.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
);

$THEME->javascripts = array();
$THEME->javascripts_footer = array();

$THEME->hidefromselector = false;

// For use with Flexpage layouts.
$THEME->blockrtlmanipulations = array(
    'side-pre' => 'side-post',
    'side-post' => 'side-pre'
);

if ($themeissnap && $notajaxscript) {
    if (empty($CFG->snappageinit) && !empty($PAGE)) {
        $CFG->snappageinit = true;
        $PAGE->initialise_theme_and_output();

        // Modify $PAGE to use snap requirements manager.
        $snappman = new snap_page_requirements_manager();
        $snappman->copy_page_requirements();
    }

}

$runningbehattest = defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING;
$requiredblocks = array('settings');
if ($runningbehattest) {
    array_push($requiredblocks, 'navigation');
}

$THEME->requiredblocks = $requiredblocks;
$THEME->haseditswitch = false;
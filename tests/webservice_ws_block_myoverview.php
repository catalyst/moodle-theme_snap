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
namespace theme_snap;
use theme_snap\webservice\ws_block_myoverview;
use core_external\external_function_parameters;
use core_external\external_single_structure;

/**
 * Test Course Overview block web service for Snap
 * @author    Daniel Cifuentes
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class webservice_ws_block_myoverview extends \advanced_testcase {

    public function test_service_parameters() {
        $params = ws_block_myoverview::service_parameters();
        $this->assertTrue($params instanceof external_function_parameters);
    }

    public function test_service_returns() {
        $returns = ws_block_myoverview::service_returns();
        $this->assertTrue($returns instanceof external_single_structure);
    }

    public function test_service() {
        global $DB;

        $this->resetAfterTest();

        $startdate = gmmktime('0', '0', '0', 10, 24, 2023);
        $enddate = gmmktime('0', '0', '0', 10, 24, 2024);
        $course = $this->getDataGenerator()->create_course(['startdate' => $startdate, 'enddate' => $enddate]);
        $user = $this->getDataGenerator()->create_user();
        $categorycreated = $this->getDataGenerator()->create_category(['name' => 'test']);
        $course2 = $this->getDataGenerator()->create_course([
                'startdate' => $startdate,
                'enddate' => $enddate,
                'category' => $categorycreated->id]);
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id,
            $course->id,
            $studentrole->id);
        $this->getDataGenerator()->enrol_user($user->id,
            $course2->id,
            $studentrole->id);
        // User not enrol in this course.
        $this->getDataGenerator()->create_course([
                'startdate' => $startdate,
                'enddate' => $enddate,
                'category' => $categorycreated->id]);
        $this->setUser($user);
        $usercourses = enrol_get_my_courses();
        $classification = 'all';
        $limit = 0;
        $offset = 0;
        $sort = 'fullname';
        $customfieldname = null;
        $customfieldvalue = null;
        $searchvalue = null;
        $yeardata = '2022';
        $progress = null;
        $category = null;

        $result = ws_block_myoverview::service(
            $classification,
            $limit,
            $offset,
            $sort,
            $customfieldname,
            $customfieldvalue,
            $searchvalue,
            $yeardata,
            $progress,
            $category
        );

        $this->assertEmpty($result["courses"]);

        $classification = 'all';
        $limit = 0;
        $offset = 0;
        $sort = 'fullname';
        $customfieldname = null;
        $customfieldvalue = null;
        $searchvalue = null;
        $yeardata = '2024';
        $progress = null;
        $category = 'all';

        $result = ws_block_myoverview::service(
            $classification,
            $limit,
            $offset,
            $sort,
            $customfieldname,
            $customfieldvalue,
            $searchvalue,
            $yeardata,
            $progress,
            $category
        );
        // All category filter selected, all courses should be displayed.
        $this->assertEquals(count($usercourses),  count($result["courses"]));
        // Testing the category filter with an extra category.
        $classification = 'all';
        $limit = 0;
        $offset = 0;
        $sort = 'fullname';
        $customfieldname = null;
        $customfieldvalue = null;
        $searchvalue = null;
        $yeardata = '2024';
        $progress = null;
        $category = $categorycreated->id;

        $result = ws_block_myoverview::service(
                $classification,
                $limit,
                $offset,
                $sort,
                $customfieldname,
                $customfieldvalue,
                $searchvalue,
                $yeardata,
                $progress,
                $category
        );
        // Only one category with the filter.
        $this->assertEquals(1,  count($result["courses"]));
    }
}

<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
 ini_set("display_errors", 1);

require_once("imslib/imslib.php");
$default_course = "";


if (!isset($_GET['course'])) {
	$course = $default_course;
} else {
	$course = $_GET['course'];
}

if ($course == "") {
	action_listcourses();
} else {

	action_viewcourse($course);
}




	function action_viewcourse($course) {

		$reader = new ims_file_reader();
		$course = $reader->read_folder($course);

		echo "<pre>";
		print_r($course);
		echo "</pre>";
	}

	function action_listcourses() {
		$courses = ims_file_reader::list_courses();
		//print_r($courses);
		foreach ($courses as $course) {
			echo "<a href=index.php?course=$course>$course</a><br>\n";
		}

	}
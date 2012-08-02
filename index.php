<?php

if (!isset($_GET['course'])) {

}


$course = $_GET['course'];

$course = ims_file_reader::read_folder($course);


print_r($course);



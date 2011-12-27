<?php

if (!isset($_GET['course'])) {

}

$xmlstr = file_get_contents($_GET['course']."/imsmanifest.xml");


$course = new SimpleXMLElement($xmlstr);

$organizations = $course->organizations;
$resources = $course->resources;

echo $course->organizations->organization->title."<br>";

foreach ($course->organizations->organization->item as $organization) {
$attrib = $organization->attributes();
echo "<li>".$organization->title."</li>";
$res =  find_resource_by_id($_GET['course'], $attrib['identifierref']);
print_r($organization->attributes());

}


find_resource_by_id($course, $ref) {

}

print_r($course);
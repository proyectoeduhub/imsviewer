<?php


/**
 *
 * @package    imslib
 * @copyright  2012 Ramon Antonio Parada <ramon@bigpress.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_file_reader {
	public $course;

	/**
	 * @param string $course_path path of the course. Course name will be derived from it
	 * @return ims_course the course obtained
	 * @throws Exception 
	 */
	public function read_folder($course_path) {
		$course_name = $course_path;


		$xmlstr = file_get_contents($course_path."/imsmanifest.xml");
		
$this->parse_manifestfile($xmlstr);
		return $this->course;

	}

	function parse_manifestfile2($xmlstr) {
		$course = new SimpleXMLElement($xmlstr);

		$organizations = $course->organizations;
		$resources = $course->resources;

		echo $course->organizations->organization->title."<br>";

		foreach ($course->organizations->organization->item as $organization) {
			$attrib = $organization->attributes();
			echo "<li>".$organization->title."</li>";
			$res =  $course->find_resource_by_id($attrib['identifierref']);
			print_r($organization->attributes());

		}


		return $course;
	}


	/**
	 * @param string $filename name of the zip file to read
	 * @return ims_course the course obtained
	 * @throws Exception 
	 */
	public function read_zip($filename) {

	}
	public static function list_courses($path = "./") {

		return array_filter(scandir($path), function ($f) use($path) {
    $blacklist = array('.', '..', '.git', "imslib", "img");
			if (!in_array($f, $blacklist)) {
			return is_dir($path . DIRECTORY_SEPARATOR . $f);
			}
		});
	}
	


	/**
	 * Parse the contents of a IMS package's manifest file
	 * @param string $manifestfilecontents the contents of the manifest file
	 * @return array
	 */
	private function parse_manifestfile($manifestfilecontents) {
		$doc = new DOMDocument();
		if (!$doc->loadXML($manifestfilecontents, LIBXML_NONET)) {
			return null;
		}

	

		// we put this fake URL as base in order to detect path changes caused by xml:base attributes
		$doc->documentURI = 'http://grrr/';
		$this->course = new ims_course();
		$this->parse_organizations($doc);
		$this->parse_resources($doc);
		$this->parse_items($doc);
	}


	private function parse_organizations($doc) {
		$xmlorganizations = $doc->getElementsByTagName('organizations');
		if (empty($xmlorganizations->length)) {
			return null;
		}
		$default = null;
		if ($xmlorganizations->item(0)->attributes->getNamedItem('default')) {
			$default = $xmlorganizations->item(0)->attributes->getNamedItem('default')->nodeValue;
		}
		$xmlorganization = $doc->getElementsByTagName('organization');
		if (empty($xmlorganization->length)) {
			return null;
		}
		$organization = null;
		foreach ($xmlorganization as $org) {
			$theorganization = new ims_organization();
			if (is_null($organization)) {
				// use first if default nor found
				$organization = $org;
				$this->course->organizations[] = $theorganization;
				$this->course->default_organization = $theorganization;
				$this->organization = $organization;
			}
			$identifier = $org->attributes->getNamedItem('identifier');
			if (!$identifier) {
				continue;
			}
/*

    <organization identifier="MOODLE-2-1" structure="hierarchical">
      <title>libro1</title>
        <item identifier="ITEM-2-1-1" isvisible="true" identifierref="RES-2-1-1">
          <title>capitulo1</title>
        </item>
        <item identifier="ITEM-2-1-2" isvisible="true" identifierref="RES-2-1-2">
          <title>capitulo2</title>
        </item>
    </organization>

*/
$theorganization->identifier = $org->attributes->getNamedItem('identifier')->nodeValue;
$theorganization->structure = $org->attributes->getNamedItem('structure')->nodeValue;
$titlenodes = $org->getElementsByTagName('title');
$theorganization->title = $titlenodes->item(0)->textContent;
			if ($default === $theorganization->identifier) {
				// found default - use it
				$organization = $org;
				$this->course->organizations[] = $theorganization;
				$this->course->default_organization = $theorganization;
				$this->organization = $organization;
				break;
			}
		}

	}

	private function parse_resources($doc) {
		// load all resources
		$this->course->resources = array();

		$xmlresources = $doc->getElementsByTagName('resource');
		foreach ($xmlresources as $res) {
			if (!$identifier = $res->attributes->getNamedItem('identifier')) {
				continue;
			}

//<resource identifier="RES-2-1-1" type="webcontent" xml:base="1/" href="index.html">
			$resource = new ims_resource();
			$resource->identifier = $identifier->nodeValue;
			if ($xmlbase = $res->baseURI) {
				// undo the fake URL, we are interested in relative links only
				$xmlbase = str_replace('http://grrr/', '/', $xmlbase);
				$resource->xmlbase = rtrim($xmlbase, '/').'/';
			} else {
				$resource->xmlbase = '';
			}
			if (!$href = $res->attributes->getNamedItem('href')) {
				continue;
			}
			$href = $href->nodeValue;
			if (strpos($href, 'http://') !== 0) {
				$href = $xmlbase.$href;
			}
			// href cleanup - Some packages are poorly done and use \ in urls
			$resource->href = ltrim(strtr($href, "\\", '/'), '/');
			$this->course->resources[$resource->identifier] = $resource;
		}

	}

	private function parse_items($doc) {
		//only for default organization
		$organization = $this->organization;
		$org = $this->course->default_organization;

		$items = array();
		foreach ($organization->childNodes as $child) {
			if ($child->nodeName === 'item') {
				if (!$item = $this->parse_recursive_item($child, 0, $resources)) {
					continue;
				}
				$items[] = $item;
			}
		}
/*
        <item identifier="ITEM-2-1-1" isvisible="true" identifierref="RES-2-1-1">
          <title>capitulo1</title>
        </item>
*/
		$org->items = $items;
	}

	private function parse_recursive_item($xmlitem, $level, $resources) {
		$titem = new ims_item();
		$titem->identifierref = '';
		if ($identifierref = $xmlitem->attributes->getNamedItem('identifierref')) {
			$titem->identifierref = $identifierref->nodeValue;
		}

		$title = '?';
		$subitems = array();
/*

        <item identifier="ITEM-2-1-2" isvisible="true" identifierref="RES-2-1-2">
          <title>capitulo2</title>
        </item>

*/
		foreach ($xmlitem->childNodes as $child) {
			if ($child->nodeName === 'title') {
				$title = $child->textContent;

			} else if ($child->nodeName === 'item') {
				if ($subitem = $this->parse_recursive_item($child, $level+1, $resources)) {
					$subitems[] = $subitem;
				}
			}
		}
		$titem->identifier = $xmlitem->attributes->getNamedItem('identifier')->nodeValue;
		$titem->isvisible = $xmlitem->attributes->getNamedItem('isvisible')->nodeValue;

		$titem->href = isset($resources[$identifierref]) ? $resources[$identifierref] : '';
		$titem->title    = $title;
		$titem->level    = $level;
		$titem->subitems = $subitems;
		return $titem;

	}
}


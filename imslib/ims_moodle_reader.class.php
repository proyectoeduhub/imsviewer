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
 * Private imscp module utility functions
 *
 * @package mod
 * @subpackage imscp
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/mod/imscp/lib.php");
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");

function imscp_print_content($imscp, $cm, $course) {
	global $PAGE, $CFG;

	$items = unserialize($imscp->structure);
	$first = reset($items);
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
	$urlbase = "$CFG->wwwroot/pluginfile.php";
	$path = '/'.$context->id.'/mod_imscp/content/'.$imscp->revision.'/'.$first['href'];
	$firsturl = file_encode_url($urlbase, $path, false);

	echo '<div id="imscp_layout">';
	echo '<div id="imscp_toc">';
	echo '<div id="imscp_tree"><ul>';
	foreach ($items as $item) {
		echo imscp_htmllize_item($item, $imscp, $cm);
	}
	echo '</ul></div>';
	echo '<div id="imscp_nav" style="display:none"><button id="nav_skipprev">&lt;&lt;</button><button id="nav_prev">&lt;</button><button id="nav_up">^</button><button id="nav_next">&gt;</button><button id="nav_skipnext">&gt;&gt;</button></div>';
	echo '</div>';
	echo '</div>';

	$PAGE->requires->js_init_call('M.mod_imscp.init');
	return;
}

/**
 * Internal function - creates htmls structure suitable for YUI tree.
 */
function imscp_htmllize_item($item, $imscp, $cm) {
	global $CFG;

	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
	$urlbase = "$CFG->wwwroot/pluginfile.php";
	$path = '/'.$context->id.'/mod_imscp/content/'.$imscp->revision.'/'.$item['href'];
	$url = file_encode_url($urlbase, $path, false);
	$result = "<li><a href=\"$url\">".$item['title'].'</a>';
	if ($item['subitems']) {
		$result .= '<ul>';
		foreach ($item['subitems'] as $subitem) {
			$result .= imscp_htmllize_item($subitem, $imscp, $cm);
		}
		$result .= '</ul>';
	}
	$result .= '</li>';

	return $result;
}

/**
 * Parse an IMS content package's manifest file to determine its structure
 * @param object $imscp
 * @param object $context
 * @return array
 */
function imscp_parse_structure($imscp, $context) {
	$fs = get_file_storage();

	if (!$manifestfile = $fs->get_file($context->id, 'mod_imscp', 'content', $imscp->revision, '/', 'imsmanifest.xml')) {
		return null;
	}

	return imscp_parse_manifestfile($manifestfile->get_content());
}


/**
 * File browsing support class
 */
class imscp_file_info extends file_info {
	protected $course;
	protected $cm;
	protected $areas;
	protected $filearea;

	public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
		parent::__construct($browser, $context);
		$this->course = $course;
		$this->cm = $cm;
		$this->areas = $areas;
		$this->filearea = $filearea;
	}

	/**
	 * Returns list of standard virtual file/directory identification.
	 * The difference from stored_file parameters is that null values
	 * are allowed in all fields
	 * @return array with keys contextid, filearea, itemid, filepath and filename
	 */
	public function get_params() {
		return array('contextid'=>$this->context->id,
					 'component'=>'mod_imscp',
					 'filearea' =>$this->filearea,
					 'itemid'   =>null,
					 'filepath' =>null,
					 'filename' =>null);
	}

	/**
	 * Returns localised visible name.
	 * @return string
	 */
	public function get_visible_name() {
		return $this->areas[$this->filearea];
	}

	/**
	 * Can I add new files or directories?
	 * @return bool
	 */
	public function is_writable() {
		return false;
	}

	/**
	 * Is directory?
	 * @return bool
	 */
	public function is_directory() {
		return true;
	}

	/**
	 * Returns list of children.
	 * @return array of file_info instances
	 */
	public function get_children() {
		global $DB;

		$children = array();
		$itemids = $DB->get_records('files', array('contextid'=>$this->context->id, 'component'=>'mod_imscp', 'filearea'=>$this->filearea), 'itemid', "DISTINCT itemid");
		foreach ($itemids as $itemid=>$unused) {
			if ($child = $this->browser->get_file_info($this->context, 'mod_imscp', $this->filearea, $itemid)) {
				$children[] = $child;
			}
		}
		return $children;
	}

	/**
	 * Returns parent file_info instance
	 * @return file_info or null for root
	 */
	public function get_parent() {
		return $this->browser->get_file_info($this->context);
	}
}


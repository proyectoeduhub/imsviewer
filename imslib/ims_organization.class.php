<?php


/**
 *
 * @package    imslib
 * @copyright  2012 Ramon Antonio Parada <ramon@bigpress.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_organization {

	public $identifier;
	public $structure = "hierarchical";
    public $title;

	public $items;

	
	function __construct() {
		$this->items = array();
	}

	function from_dom($node) {
		foreach ($node->attributes as $attrName => $attrNode) {
			echo "$attrName -> $attrNode <br>";
		}

	}
}


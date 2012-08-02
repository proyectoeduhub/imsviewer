<?php



/**
 *
 * @package    imslib
 * @copyright  2012 Ramon Antonio Parada <ramon@bigpress.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_resource {

	/** code type RES-2-1-2 */
	public $identifier;
	public $type="webcontent";
	/** base path */
	public $base;
	/** filename */
	public $href;


	public $dependencies;//array o uno?
	public $files; //array o uno?

	function from_dom($node) {
		foreach ($node->attributes as $attrName => $attrNode) {
			echo "$attrName -> $attrNode <br>";
		}

	}


}

<?php

/**
 *
 * @package    imslib
 * @copyright  2012 Ramon Antonio Parada <ramon@bigpress.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_course {

	public $default_organization;
	public $organizations;
	public $resources;
  


	function __construct() {
		$this->organizations = array();
		$this->resources = array();
	}

	/**
	 * Retrieves a resource by it's resource
	 * @param string resource reference
	 * @return ims_resource the resource
	 */
	function find_resource_by_ref($ref) {

	}

}
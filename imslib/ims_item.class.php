<?php

/**
 *
 * @package    imslib
 * @copyright  2012 Ramon Antonio Parada <ramon@bigpress.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_item {
	/** like ITEM-2-1-1 */
	public $identifier; 
	public $isvisible = "true";
	/** linked resurce, like RES-2-1-1 */
	public $identifierref;
	public $title;
	/** class ims_item */
	public $subitems;
}
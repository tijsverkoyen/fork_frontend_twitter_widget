<?php

/**
 * This is the configuration-object
 *
 * @package		frontend
 * @subpackage	twitter
 *
 * @author		Tijs Verkoyen <tijs@sumocoders.com>
 * @since		2.6
 */
final class FrontendTwitterConfig extends FrontendBaseConfig
{
	/**
	 * The default action
	 *
	 * @var	string
	 */
	protected $defaultAction = 'index';


	/**
	 * The disabled actions
	 *
	 * @var	array
	 */
	protected $disabledActions = array();
}

?>
<?php
/**
* @version	$Id$
* @package	Joomla
* @subpackage	NoKWebDAV
* @copyright	Copyright (c) 2022 Norbert Kümin. All rights reserved.
* @license	http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE
* @author	Norbert Kuemin
* @authorEmail	momo_102@bluemail.ch
*/

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

class PlgWebservicesNoKWebDAV extends CMSPlugin {
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_content's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router) {
		$defaults    = [
			'component' => 'com_nokwebdav',
			'type_alias' => 'com_nokwebdav.container',
			'type_id' => 1,
			'public' => false
		];

		$routes = [
			new Route(['GET'], 'v1/nokwebdav/connector/:name/:reference', 'nokwebdav.connector', ['name' => '([A-Za-z0-9-_]+)', 'reference' => '(.*)'], $defaults),
		];

		$router->addRoutes($routes);
	}
}
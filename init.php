<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Init for the Instagram plugin
 *
 * @package SwiftRiver
 * @author Ushahidi Team
 * @category Plugins
 * @copyright (c) 2008-2011 Ushahidi Inc <htto://www.ushahidi.com>
 */

class Instagram_Init {

	public function __construct() 
	{
		// Register a crawler
		Swiftriver_Crawlers::register('instagram', array(new Swiftriver_Crawler_Instagram(), 'crawl'));

		// Register an installer
		Swiftriver_Plugins::register('instagram', array(new Instagram_Install(), 'create'));

		// Hook into the river settings page
		Swiftriver_Event::add('swiftriver.river.settings.nav', array($this, 'settings_nav'));
	}

	/**
	 * Render the Instagram Settings Menu
	 */
	public function settings_nav()
	{
		// Get the active menu
		$active = Swiftriver_Event::$data;

		// Kind of a dirty way to get the base_url without a global variable
		$river_base_url = URL::site().Request::current()->param('account').'/river/'.Request::current()->param('name');

		echo View::factory('instagram/menu')
			->bind('active', $active)
			->bind('river_base_url', $river_base_url);
	}

}
new Instagram_Init;
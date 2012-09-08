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
	}
}
new Instagram_Init;
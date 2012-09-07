<?php defined('SYSPATH') OR die('No direct access allowed.');

class Instagram_Install {

	public function create()
	{
		$query = DB::query(NULL, "
			CREATE TABLE IF NOT EXISTS `river_instagrams` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `river_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `token` varchar(255) DEFAULT NULL,
			  `client_id` varchar(255) DEFAULT NULL,
			  `client_secret` varchar(255) DEFAULT NULL,
			  `instagram_date_add` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
			  `instagram_date_edit` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$result = $query->execute();
	}
}
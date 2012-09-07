<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Model_River_Instagram
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @subpackage Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Model_River_Instagram extends ORM {
	
	protected $_belongs_to = array(
		'river' => array(),
		'user' => array()
	);

	/**
	 * Auto-update columns for creation
	 * @var string
	 */
	protected $_created_column = array('column' => 'instagram_date_add', 'format' => 'Y-m-d H:i:s');

	/**
	 * Auto-update columns for updates
	 * @var string
	 */
	protected $_updated_column = array('column' => 'instagram_date_edit', 'format' => 'Y-m-d H:i:s');

	/**
	 * Rules for the river_instagram model
	 * @return array Rules
	 */
	public function rules()
	{
		return array(
			'client_id' => array(
				array('max_length', array(':value', 200)),
			),
			'client_secret' => array(
				array('max_length', array(':value', 200)),
			)
		);
	}
}
<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Instagram crawler
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Swiftriver - https://github.com/ushahidi/Swiftriver_v2
 * @category   Libraries
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Swiftriver_Crawler_Instagram {

	/**
	 * Fetch instagram feeds attached to a river id
	 *
	 * @param   int   $river_id	 
	 * @return  bool
	 */
	public function crawl($river_id)
	{
		// If the river ID is NULL or non-existent, exit
		if (empty($river_id) OR ! ORM::factory('river', $river_id)->loaded())
		{
			Kohana::$log->add(Log::ERROR, 'Invalid database river id: :river_id', 
				array(':river_id' => $river_id));
			
			return FALSE;
		}

		// If Instagram vendor not found, exit
		$path = Kohana::find_file( 'vendor', 'instagram/Instagram' );
		if( FALSE === $path )
		{
			Kohana::$log->add(Log::ERROR, 'Instagram vendor not found');
			
			return FALSE;
		}
		require_once( $path );

		// Get the keywords and users to search form the db
		$filter_options = Model_Channel_Filter::get_channel_filter_options('instagram', $river_id);

		if ( ! empty($filter_options))
		{
			// Get this Rivers Access Info
			$settings = ORM::factory('river_instagram')
				->where('river_id', '=', $river_id)
				->find();

			if ( ! $settings->loaded() )
			{
				Kohana::$log->add(Log::ERROR, 'River id: :river_id has not been set up for Instagram',
					array(':river_id' => $river_id));
			
				return FALSE;
			}

			if ( ! $settings->client_id OR 
				! $settings->client_secret OR
				! $settings->token)
			{
				Kohana::$log->add(Log::ERROR, 'River id: :river_id has not been authorized as a client on Instagram',
					array(':river_id' => $river_id));
			
				return FALSE;
			}

			$config = array(
				'client_id' => $settings->client_id,
				'client_secret' => $settings->client_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => ''
			);

			// Instantiate the API handler object
			$instagram = new Instagram($config);

			try
			{
				$instagram->setAccessToken($settings->token);
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Log::ERROR, 'Invalid Instagram token for river id: :river_id',
					array(':river_id' => $river_id));
			
				return FALSE;
			}

			foreach ($filter_options as $option)
			{				
				$value = $option['data']['value'];

				$instagrams = array();

				switch($option['key'])
				{
					case 'keyword':						
						try
						{
							$response = $instagram->getRecentTags($value);
							$instagrams = json_decode($response, TRUE);
							$this->_process_instagrams($river_id, $instagrams);
						}
						catch (Exception $e)
						{
							Kohana::$log->add(Log::ERROR, 'Error retrieving data for river id: :river_id from Instagram for keyword :keyword',
								array(':river_id' => $river_id, ':keyword' => $value));

							Kohana::$log->add(Log::ERROR, $e);
			
							return FALSE;
						}
					break;
					
					case 'user':
						try
						{
							// first get the user
							$response = $instagram->searchUser($value);
							$users = json_decode($response, TRUE);

							if ($user_id = $this->_process_user($value, $users))
							{
								// then get the users feed
								$response = $instagram->getUserRecent($user_id);
								$instagrams = json_decode($response, TRUE);
								$this->_process_instagrams($river_id, $instagrams);	
							}
						}
						catch (Exception $e)
						{
							Kohana::$log->add(Log::ERROR, 'Error retrieving data for river id: :river_id from Instagram for user :user',
								array(':river_id' => $river_id, ':user' => $value));

							Kohana::$log->add(Log::ERROR, $e);
			
							return FALSE;
						}
						
					break;				
				}							
			}
		}
		
	}


	/**
	 * Process response from Instagrams API
	 *
	 * @param int $river_id
	 * @param array $instagrams - Result from Instagram API
	 */
	private function _process_instagrams($river_id, $instagrams)
	{
		if ( isset($instagrams['data']) )
		{
			foreach ($instagrams['data'] as $instagram)
			{
				// Get the droplet template
				$droplet = Swiftriver_Dropletqueue::get_droplet_template();

				// Populate the droplet
				$droplet['channel'] = 'instagram';
				$droplet['river_id'] = array($river_id);
				$droplet['identity_orig_id'] = $instagram['user']['id'];
				$droplet['identity_username'] = $instagram['user']['username'];
				$droplet['identity_name'] = $instagram['user']['full_name'];
				$droplet['identity_avatar'] = $instagram['user']['profile_picture'];
				$droplet['droplet_orig_id'] = $instagram['id'];
				$droplet['droplet_type'] = 'original';
				$droplet['droplet_title'] = $instagram['caption']['text'];
				$droplet['droplet_raw'] = $droplet['droplet_content'] = $instagram['caption']['text'];
				$droplet['droplet_date_pub'] = gmdate("Y-m-d H:i:s", $instagram['created_time']);
				$droplet['links'] = array(
										array(
											'url' => $instagram['link'],
											'original_url' => TRUE
										)
									);

				if ( isset($instagram['images']['low_resolution']) )
				{
					$droplet['media'] = array(
											array(
												'url' => $instagram['images']['low_resolution']['url'],
												'droplet_image' => $instagram['images']['low_resolution']['url'],
												'type' => 'image'
											),
										);
				}

				Swiftriver_Dropletqueue::add($droplet);				
			}
		}
	}

	/**
	 * Process response from Instagrams API for a User Search
	 *
	 * @param array $users - Result from Instagram API
	 */
	private function _process_user($keyword, $users)
	{
		if ( isset($users['data']) )
		{
			if (count($users['data']) > 1)
			{
				Kohana::$log->add(Log::ERROR, 'Error retrieving Instagram user search for user: :user. Please be more specific about the username',
						array(':user' => $keyword));
				return FALSE;
			}
			else
			{
				return $users['data'][0]['id'];
			}			
		}
		else
		{
			return FALSE;
		}
	}
}
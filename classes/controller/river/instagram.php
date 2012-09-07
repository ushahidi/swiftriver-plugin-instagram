<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * River Channel Instagram Settings Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @subpackage Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Controller_River_Instagram extends Controller_River_Settings {
	
	/**
	 * @return	void
	 */
	public function action_index()
	{
		// Load Instagram
		$path = Kohana::find_file( 'vendor', 'instagram/Instagram' );
		if( FALSE === $path )
		{
			throw new Kohana_Cache_Exception('Instagram vendor code not found');
		}
		require_once( $path );


		$this->template->header->title = $this->river->river_name.' ~ '.__('Instagram Settings');
		
		$this->active = 'instagram';
		$this->settings_content = View::factory('instagram/settings')
			->bind('callback_url', $callback_url)
			->bind('client_id', $client_id)
			->bind('client_secret', $client_secret)
			->bind('errors', $errors)
			->bind('success', $success);

		$callback_url = URL::base('http', TRUE).$this->river->account->account_path.'/river/'.$this->river->river_name_url.'/settings/instagram';

		$settings = ORM::factory('river_instagram')
			->where('river_id', '=', $this->river->id)
			->find();

		// Retrieving token
		if ($this->request->post())
		{
			echo 'test';
			$post = Validation::factory($this->request->post())
				->rule('client_id', 'not_empty')
				->rule('client_id', 'max_length', array(':value', 200))
				->rule('client_secret', 'not_empty')
				->rule('client_secret', 'max_length', array(':value', 200));

			if ( $post->check())
			{
				// First Save id and secret
				$settings->river_id = $this->river->id;
				$settings->user_id = $this->user->id;
				$settings->client_id = $this->request->post('client_id');
				$settings->client_secret = $this->request->post('client_secret');
				$settings->save();

				$config = array(
					'client_id' => $this->request->post('client_id'),
					'client_secret' => $this->request->post('client_secret'),
					'grant_type' => 'authorization_code',
					'redirect_uri' => $callback_url,
				);

				// Instantiate the API handler object
				$instagram = new Instagram($config);
				$instagram->openAuthorizationUrl();
			}
			else
			{
				// Display the errors
				$errors = $post->errors("validation");
			}
		}

		// This is a callback with code to retrieve token
		elseif ( isset($_GET['code']) )
		{
			if ( $settings->loaded() )
			{
				$config = array(
					'client_id' => $settings->client_id,
					'client_secret' => $settings->client_secret,
					'grant_type' => 'authorization_code',
					'redirect_uri' => $callback_url,
				);

				// Instantiate the API handler object to
				// get the Access Token using Code
				$instagram = new Instagram($config);

				try
				{
					$accessToken = $instagram->getAccessToken();

					if ($accessToken)
					{
						$settings->token = $accessToken;
						$settings->save();

						$this->request->redirect($callback_url.'?success');
					}
					else
					{
						$errors = 'Invalid Access Token Returned';
					}
				}
				catch (Exception $e)
				{
					$errors = 'Invalid Access Token Returned';
				}
			}
		}
		else
		{
			if ( isset($_GET['success']) )
			{
				$success = TRUE;
			}
			$client_id = $settings->client_id;
			$client_secret = $settings->client_secret;
		}
	}
}
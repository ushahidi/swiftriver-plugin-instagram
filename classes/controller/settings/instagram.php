<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Instagram Settings Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @category   Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Controller_Settings_Instagram extends Controller_Settings_Main {

	/**
	 * @return	void
	 */
	public function before()
	{
		// Execute parent::before first
		parent::before();
		
		// Add an Admin User Menu Link
		Swiftriver_Event::add('swiftriver.settings.nav', array($this, 'instagram_menu'));
	}

	/**
	 * Render the Instagram Menu
	 */
	public function instagram_menu()
	{
		echo View::factory('instagram/menu');
	}

	/**
	 * List all the available settings for Instagram
	 *
	 * @return  void
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

		$this->template->header->title = __('Instagram Settings');
		$this->settings_content = View::factory('pages/settings/instagram')
		    ->bind('callback_url', $callback_url)
		    ->bind('settings', $settings)
		    ->bind('success', $success)
		    ->bind('errors', $errors);

		$this->active = 'instagram';
		
		// Setting items
		$settings = array(
			'instagram_client_id' => '',
			'instagram_client_secret' => '',
			'instagram_token' => ''
		);

		if ( Model_Setting::get_settings(array_keys($settings)) )
		{
			$settings = Model_Setting::get_settings(array_keys($settings));
		}

		$callback_url = URL::base('http', TRUE).'settings/instagram';

		if ($this->request->post())
		{
			echo 'post!';
			// Setup validation for the application settings
			$validation = Validation::factory($this->request->post())
				->rule('form_auth_token', array('CSRF', 'valid'))
				->rule('instagram_client_id', 'not_empty')
				->rule('instagram_client_id', 'max_length', array(':value', 200))
				->rule('instagram_client_secret', 'not_empty')
				->rule('instagram_client_secret', 'max_length', array(':value', 200));
			
			if ($validation->check())
			{
				// Set the setting key values
				$settings = array(
					'instagram_client_id' => $this->request->post('instagram_client_id'),
					'instagram_client_secret' => $this->request->post('instagram_client_secret')
				);

				// Update the settings
				Model_Setting::update_settings($settings);

				$config = array(
					'client_id' => $this->request->post('instagram_client_id'),
					'client_secret' => $this->request->post('instagram_client_secret'),
					'grant_type' => 'authorization_code',
					'redirect_uri' => $callback_url,
				);
				
				//$this->settings_content->set('messages', 
				//	array(__('Instagram settings have been updated.')));
				// Instantiate the API handler object
				$instagram = new Instagram($config);
				$instagram->openAuthorizationUrl();
			}
			else
			{
				$errors = $validation->errors('instagram');
			}
		}

		// This is a callback with code to retrieve token
		elseif ( isset($_GET['code']) )
		{
			$config = array(
				'client_id' => $settings['instagram_client_id'],
				'client_secret' => $settings['instagram_client_secret'],
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
					$settings = array(
						'instagram_token' => $accessToken,
					);
					// Update the settings
					Model_Setting::update_settings($settings);

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

		// This is a callback with code to retrieve token
		elseif ( isset($_GET['success']) )
		{
			$success = TRUE;
		}
	}

}
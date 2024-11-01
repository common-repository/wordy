<?php
/*
Plugin Name: Wordy
Plugin URI: http://www.wordy.com
Description: Wordy is the fastest, most reliable way of adding professional copy-editing to your WordPress blogs. 
Version: 1.3.2
Author: Wordy
Author URI: http://www.wordy.com
*/


define('WORDY_PHP_VERSION', '5.0');

function wordy_system_check()
{
	if (!version_compare(WORDY_PHP_VERSION, PHP_VERSION, '<')) 
	{
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die("<p><center><strong>" . __('Warning from WORDY<br/><br/>', 'wordy') . "</strong>" . sprintf(__('Your server is running PHP version %1$s. Wordy requires PHP version %2$s.<br/>Please contact your web host and request PHP version %2$s.<br /><br />The Wordy plugin has been deactivated.<br/><br/>"Refresh" this page to return to your WordPress Dashboard', 'wordy'), PHP_VERSION, WORDY_PHP_VERSION) . "</center></p>");
	}
}
add_action('admin_init', 'wordy_system_check', 0);




$wordy = new Wordy();

class Wordy
{
	
	// Wordy Defaults
	protected $wordy = array(
		'url' => 'http://api.wordy.com' // 'http://www.wordy.com'
		,'signin_url' => 'http://www.wordy.com' // 'http://www.wordy.com'
		,'version' => '1.3.2'
		,'interval' => 300
	);
	
	// Default Options
	protected $default_options = array(
		'email' => ''
		,'password' => ''
		,'name' => ''
		,'company_name' => ''
		,'publishing_settings' => 'approve'
		,'language_code' => 'GB'
		,'country_code' => 'GB'
	);
	
	protected $allow_language_codes = array(
		'GB'
		,'US'
	);
	
	// Options pulled from WordPress
	protected $options;

	// API Debuger
	public $debug = false;
	public $debug_limit = 10;


	// 
	// Init
	// 

	public function __construct()
	{
		global $pagenow;
		
		$this->set_session();
		$this->set_i18n();

		$this->options = unserialize(get_option('wordy'));
		if ($this->options === false)
		{
			$this->set_options();
		}
		
		$this->view_post();
		
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_menu', array(&$this, 'status'));
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_print_styles', array(&$this, 'options_admin_styles'));
		add_action('admin_print_scripts', array(&$this, 'options_admin_scripts'));
		
		if ($pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'page-new.php' || $pagenow == 'page.php')
		{
			add_action('admin_footer', array(&$this, 'word_count'));
			add_action('admin_footer', array(&$this, 'payment'));
			
			if ($this->debug)
			{
				add_action('admin_footer', array(&$this, 'api_debug'));
			}
		}
		
		if ($_GET['page'] != 'wordy.php')
		{
			add_action('admin_menu', array(&$this, 'admin_warnings')); 
		}

		if (isset($_SESSION['wordy_admin_notices']) && !empty($_SESSION['wordy_admin_notices']))
		{
			add_action('admin_notices', array(&$this, $_SESSION['wordy_admin_notices']));
			unset($_SESSION['wordy_admin_notices']);
		}
		
		add_action('post_submitbox_start', array(&$this, 'add_button'));
		add_action('save_post', array(&$this, 'if_wordy'), 10);

		add_filter('post_row_actions', array(&$this, 'row_actions'), 10, 2);
		add_filter('page_row_actions', array(&$this, 'row_actions'), 10, 2);

		add_filter('plugin_row_meta', array(&$this, 'plugin_meta'), 10, 2 );
		add_filter('plugin_action_links', array(&$this, 'plugin_links'), 10, 2);
	
		add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widget'));
	}
	
	

	// 
	// Workflow
	// 
	
	public function if_wordy($post_ID)
	{
		$actual = wp_is_post_revision($post_ID);
		$sent = get_post_meta($actual, '_sent_to_wordy', true);

		if ($sent == 1 && !isset($_REQUEST['error'])) 
		{
			wp_redirect($_SERVER['REQUEST_URI'] . (strstr($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . 'error=true');
			exit;
		}
		else 
		{
			if (isset($_REQUEST['wordy']['publish_via_wordy']) && !empty($actual)) 
			{
				if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) 
				{
					$part = 1;
					$arguments = array();

					if (isset($_REQUEST['post_title']) && !empty($_REQUEST['post_title'])) 
					{
						$arguments['title' . $part] = 'Post title';
						$arguments['type' . $part]  = 'shorttext';
						$arguments['value' . $part] = $_REQUEST['post_title'];
						$part++;
					}

					if (isset($_REQUEST['excerpt']) && !empty($_REQUEST['excerpt'])) 
					{
						$arguments['title' . $part] = 'Post excerpt';
						$arguments['type' . $part]  = 'longtext';
						$arguments['value' . $part] = $_REQUEST['excerpt'];
						$part++;
					}

					if (!empty($_REQUEST['content'])) 
					{
						$content = wptexturize($_REQUEST['content']); 
	     			$content = wpautop($content); 

	     			if (function_exists('shortcode_unautop')) 
						{
	     				$content = shortcode_unautop($content);
	     			}

	     			$content = do_shortcode($content); 
	     			$content = $this->set_absolute_urls($content);

						$arguments['title' . $part] = 'Post content';
						$arguments['type' . $part]  = 'html';
						$arguments['value' . $part] = $content;
						$part++;
					}

					if (!empty($arguments)) 
					{
						foreach($arguments as $key => $value) 
						{
							$arguments[$key] = stripslashes($value);
						}
					}
					
					$response = $this->api('CREATE_ORDER', $arguments);

					if (isset($response->order->documents[0])) 
					{
						$response->order->documents[0]->last_check = time();

						update_post_meta($actual, '_wordy_order_info', serialize($response->order));
						update_post_meta($actual, '_wordy_document_info', serialize($response->order->documents[0]));
						update_post_meta($actual, '_sent_to_wordy', '1');

						$_SESSION['wordy_order_id'] = $response->order->id;
					}
				}
				else 
				{
					$_SESSION['wordy_admin_notices'] = 'publish_cancel';
				}
			}
		}
	}
	
	public function add_button()
	{
		global $post;

		if ($post)
		{
			$this->render('button', array(
				'post' => $post
				,'options' => $this->options
			));
		}
	}
	
	public function row_actions($actions, $post)
	{
		// TODO UI: Replace Content Status' (other than anyting in process) with icons instead of text?
		
		$sent = get_post_meta($post->ID, '_sent_to_wordy', true);
		
		$details = unserialize(get_post_meta($post->ID, '_wordy_document_info', true));

		if ($post->post_status == 'publish') 
		{
			if ((int) $sent === 2) 
			{
				echo '<em>' . __('Published via Wordy', 'wordy') . '</em>';
			}
		}
		else
		{
			if (isset($details->status_code) && $details->status_code != 'document_new' && $details->status_code != 'document_open' && $details->status_code != 'document_completed') 
			{
				echo '<em>' . __('Your post is being edited by a Wordy editor. Approximate delivery time: ', 'wordy') . '</em><strong>' . (isset($details->approximate_time) ? $details->approximate_time : '') . '</strong>';
			}
			elseif (isset($details->status_code) && $details->status_code === 'document_open') 
			{
				echo '<em>' . __('Waiting for a Wordy editor to take your order', 'wordy') . '</em>';
			}
			else 
			{
				switch($sent) 
				{
					case 1:
					
						echo '<em>' . __('Sent to Wordy', 'wordy') . '</em>';
						
						// TODO Workflow: Launch Payment screen from here?
						$actions['wordy'] = '<a href="post.php?action=edit&post=' . intVal($post->ID) . '&cancel_wordy=true">' . __('Cancel Wordy', 'wordy') . '</a>';
					
					break;
				
					case 2:
						
						echo '<em>' . __('Edited by Wordy', 'wordy') . '</em>';
					
					break;
				
					case 0:
					default:
						
						$actions['wordy'] = '<a href="post.php?action=edit&post=' . $post->ID . '&pub_via_wordy=true">' . __('Publish via Wordy', 'wordy') . '</a>';

					break;
				}
			}
		}
		
		return $actions;
	}
	
	public function status()
	{
		global $wpdb;

		$result = $wpdb->get_results("SELECT A.* FROM $wpdb->postmeta A, $wpdb->posts B WHERE A.meta_key = '_wordy_document_info' AND A.post_id = B.ID AND B.post_parent = 0");

		if (!empty($result)) 
		{
			foreach($result as $key => $value) 
			{
				$details = unserialize(unserialize($value->meta_value));

				if ((!isset($details->last_check) || $details->last_check < time() + $this->wordy['interval']) && (!isset($details->status_code) || ($details->status_code != 'document_closed' && $details->status_code != 'document_canceled'))) 
				{
					$response = $this->api('CHECK_DOCUMENT_INFO', array(
						'document_id' => $details->id
					));
				
					$response->document->last_check = time();
					$downloaded = get_post_meta($value->post_id, '_sent_to_wordy', true);
					$post = get_post($value->post_id);

					if (!empty($response->document->completed_at) && $downloaded != 2) 
					{
						$response_download = $this->api('DOWNLOAD_DOCUMENT', array(
							'document_id' => $details->id
						));
						
						if (isset($response_download->success) && $response_download->success === true) 
						{
							$arguments['ID'] = $post->ID;
							foreach($response_download->fields as $field) 
							{
								if (strtolower($field->title) === 'post title') 
								{
									$arguments['post_title'] = $field->value;
								}
								elseif (strtolower($field->title) == 'post content') 
								{
									$arguments['post_content'] = $field->value;
								}
								elseif (strtolower($field->title) == 'post excerpt') 
								{
									$arguments['post_excerpt'] = $field->value;
								}
							}

							if ($this->options['publishing_settings'] == 'publish' || $this->options['publishing_settings'] == 'only_wordy')
							{
								$arguments['post_status'] = 'publish';
							}
							
							wp_update_post($arguments);
							update_post_meta($post->ID, '_sent_to_wordy', '2');

							$response = $this->api('CHECK_DOCUMENT_INFO', array(
								'document_id' => $details->id
							));
							
							update_post_meta($post->ID, '_wordy_document_info', serialize($response->document));
						}
					}

					if (isset($response->document))
					{
						update_post_meta($post->ID, '_wordy_document_info', serialize($response->document));
						if($response->document->status_code === 'document_reclaimed' || $response->document->status_code === 'document_pending') 
						{
							update_post_meta($post->ID, '_sent_to_wordy', 1);
						}
					}
				}
			}
		}
	}
	
	public function word_count()
	{
		$this->render('word_count', array(
			'configuration' => $this->api('WORDY_CONFIGURATION')
		));
	}
	
	public function payment()
	{
		if (isset($_SESSION['wordy_order_id']) && !empty($_SESSION['wordy_order_id']))
		{
			$this->render('payment', array(
				'wordy' => $this->wordy
				,'options' => $this->options
				,'wordy_plugin_url' => WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__))
			));
			unset($_SESSION['wordy_order_id']);
		}
	}
	
	public function view_post()
	{
		if (isset($_GET['post']) && !empty($_GET['post'])) 
		{
			$post_ID = (int) $_GET['post'];
			$post = get_post($post_ID);

			if (isset($_GET['cancel_wordy'])) 
			{
				$postmeta = unserialize(get_post_meta($post_ID, '_wordy_document_info', true));
				if (isset($postmeta->id)) 
				{
					$response = $this->api('CANCEL_WORDY', array(
						'document_id' => $postmeta->id
					));

					if ($response->success == true || $postmeta->status_code == 'document_new') 
					{
						update_post_meta($post_ID, '_sent_to_wordy', '0');
						update_post_meta($post_ID, '_wordy_document_info', '');
						update_post_meta($post_ID, '_wordy_order_info', '');

						add_action('admin_notices', array(&$this, 'post_canceled_notice'));
					} 
					else 
					{
						add_action('admin_notices', array(&$this, 'post_cannot_cancel_notice'));
					}
				}
			}
			
			if (isset($_POST['submit_reclaim']) && isset($_GET['post'])) 
			{
				$details = unserialize(get_post_meta($post_ID, '_wordy_document_info', true));
				$response = $this->api('RECLAIM_WORDY', array(
					'id' => $details->id
					,'message' => $_REQUEST['message']
				));

				if (isset($response->success) && $response->success === true) 
				{
					update_post_meta($post_ID, '_sent_to_wordy', '1');
				} 
				else if (isset($response->message)) 
				{
					add_action('admin_notices', array(&$this, 'post_cannot_reclaim_notice'));
				}
			}

			$sent = get_post_meta($post_ID, '_sent_to_wordy', true);

			if ((int) $sent == 1) 
			{
				add_action('admin_notices', array(&$this, 'post_sent_notice'));
				add_action('wp_print_scripts', array(&$this, 'disable_auto_save'));
				add_action('edit_form_advanced', array(&$this, 'remove_submit'));
			}
			elseif ((int) $sent == 2) 
			{
				add_action('admin_notices', array(&$this, 'post_edited_notice'));
			}
		}

		if ($this->options['publishing_settings'] == 'only_wordy') 
		{
			add_action('edit_form_advanced', array(&$this, 'remove_publish_button'));
		}

		if (isset($_GET['pub_via_wordy'])) 
		{
			add_action('admin_head', array(&$this, 'publish_from_list'));
		}		
	}
	
	
	
	// 
	// Javascript Helpers
	// 
	
	public function remove_publish_button()
	{
		$this->render('remove_publish_button');
	}
	
	public function publish_from_list()
	{
		$this->render('publish_from_list');
	}
	
	public function remove_submit()
	{
		$this->render('remove_submit');
	}
	
	public function disable_auto_save()
	{
		wp_deregister_script('autosave');
	}
	
	
	
	// 
	// Administration
	// 
	
	public function admin_init()
	{
		wp_register_style('wordy', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/css/wordy-admin.css');
		wp_register_script('wordy', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/js/wordy.js', array('jquery'), $this->wordy['version'], true);	
	}

	public function admin_menu()
	{
		add_submenu_page('options-general.php', 'Wordy settings', 'Wordy', 'manage_options', basename(__FILE__), array(&$this, 'options_page'));
	}
	
	public function options_admin_styles()
	{
		wp_enqueue_style('wordy');
	}
	
	public function options_admin_scripts()
	{
		wp_enqueue_script('wordy');
	}

	public function options_page()
	{
		global $current_user;

		get_currentuserinfo(); 

		if (isset($_GET['logout']) && empty($_POST)) 
		{
			$this->set_options(null);
		}

		if (!empty($_POST) && current_user_can('manage_options')) 
		{
			$_POST['wordy'] = $this->clean_post_variables($_POST['wordy']);
			
			if (isset($_POST['wordy']['settings'])) 
			{
				$this->options['publishing_settings'] = $_POST['wordy']['settings'] ;

				$message = __('Options Updated.');
				$this->set_options($this->options);
			}
			
			if (isset($_POST['wordy']['language_code'])) 
			{
				$this->options['language_code'] = $_POST['wordy']['language_code'];

				$message = __('Options Updated.');
				$this->set_options($this->options);
			}

			if (isset($_POST['wordy']['status'])) 
			{
				if ($_POST['wordy']['status'] == 'enabled') 
				{
					activate_plugins('wordy/wordy.php');
				}
				else 
				{
					deactivate_plugins('wordy/wordy.php');
				}

				$message = __('Options Updated.');
			}

			if (isset($_POST['wordy']['signin']) && isset($_POST['wordy']['email']) && !empty($_POST['wordy']['email']) && isset($_POST['wordy']['password']) && !empty($_POST['wordy']['password']) && empty($_POST['wordy']['re_password'])) 
			{
				$response = $this->api('CUSTOMER_INFO', array(
					'email' => $_POST['wordy']['email']
					,'password' => $_POST['wordy']['password']
				));

				if (is_object($response) && $response->success === true) 
				{
					$this->options['password'] = isset($_POST['wordy']['password']) ? $_POST['wordy']['password'] : '';
					$this->options['email'] = $response->customer->email;
	  			$this->options['name'] = $response->customer->first_name;
	  			$this->options['last_name'] = $response->customer->last_name;
	  			$this->options['company_name'] = $response->customer->company_name;

					$this->set_options($this->options);
					
					$message = __('You are now signed in.');
				}
				else 
				{
					$message = $response->message;
				}
			}
			else if (isset($_POST['wordy']['signin']))
			{
				$message = __('Please fill in all required fields.');
			}
			
			if (isset($_POST['wordy']['register']) && isset($_POST['wordy']['country_code']) && !empty($_POST['wordy']['country_code']) && isset($_POST['wordy']['email']) && !empty($_POST['wordy']['email']) && isset($_POST['wordy']['password']) && !empty($_POST['wordy']['password']) && isset($_POST['wordy']['re_password']) && !empty($_POST['wordy']['re_password'])) 
			{
				$response = $this->api('CREATE_CUSTOMER', array(
					'email' => $_POST['wordy']['email']
					,'password' => $_POST['wordy']['password']
					,'confirm' => $_POST['wordy']['re_password']
					,'first_name' => isset($_POST['wordy']['first_name']) ? $_POST['wordy']['first_name'] : ''
					,'last_name' => isset($_POST['wordy']['last_name']) ? $_POST['wordy']['last_name'] : ''
					,'company_name' => isset($_POST['wordy']['company_name']) ? $_POST['wordy']['company_name'] : ''
					,'country_code' => isset($_POST['wordy']['country_code']) ? $_POST['wordy']['country_code'] : $this->default_options['language_code']
					,'vat_number' => isset($_POST['wordy']['vat_number']) ? $_POST['wordy']['vat_number'] : ''
				));

				if (is_object($response) && $response->success === true) 
				{
					$response = $_POST['wordy'];
					$response['language_code'] = in_array($_POST['wordy']['country_code'], $this->allow_language_codes) ? $_POST['wordy']['country_code'] : $response['language_code'];
					$response['name'] = $_POST['wordy']['first_name'] . ' ' . $_POST['wordy']['last_name'];
					
					$this->set_options($response);

					$message = __('Your account has been created.');
				}
				else 
				{
					$message = $response->message;
				}
			}
			else if (isset($_POST['wordy']['register']))
			{
				$message = __('Please fill in all required fields.');
			}
		}

		$this->render('options', array(
			'options' => $this->options
			,'wordy' => $this->wordy
			,'current_user' => $current_user
			,'active' => is_plugin_active('wordy/wordy.php')
			,'message' => isset($message) ? $message : false
			,'POST' => $_POST['wordy']
		));
	}
	
	public function plugin_links($links, $file) 
	{
		static $this_plugin;
		
		if (!$this_plugin)
		{
			$this_plugin = plugin_basename(__FILE__);
		}
		
		if ($file == $this_plugin )
		{
			$settings_link = '<a href="admin.php?page=wordy.php">' . __('Settings', 'wordy') . '</a>';
			array_unshift($links, $settings_link);
		}
		
		return $links;
	}

	public function plugin_meta($links, $file) 
	{
		$plugin = plugin_basename(__FILE__);
	
		if ($file == $plugin) 
		{
			return array_merge($links, array(
				'<a href="admin.php?page=wordy.php">' . __('Plugin Settings', 'wordy') . '</a>'
				,'<a href="http://www.wordy.com/settings" target="_blank">' . __('Additional Settings at Wordy.com', 'wordy') . '</a>'
				,'<a href="http://support.wordy.com/wordy" target="_blank">' . __('Support', 'wordy') . '</a>'
			));
		}
		
		return $links;
	}	
	
	public function add_dashboard_widget()
	{
		wp_add_dashboard_widget('wordy_dashboard_widget', 'Wordy Status', array(&$this, 'dashboard_widget'));
	}
	
	public function dashboard_widget()
	{
		global $wpdb;

		$result = $wpdb->get_results("SELECT A.* FROM $wpdb->postmeta A, $wpdb->posts B WHERE A.meta_key = '_wordy_document_info' AND A.post_id = B.ID AND B.post_parent = 0");
		
		$status = array();
		
		if (!empty($result)) 
		{
			foreach($result as $key => $value) 
			{
				array_push($status, array(
					'post' => get_post($value->post_id)
					,'sent' =>get_post_meta($value->post_id, '_sent_to_wordy', true)
				));
			}
		}
		
		$this->render('dashboard_widget', array(
			'status' => $status
		));
	}
	
	
	// 
	// Messages, Notices & Warnings
	// 
	
	public function server_unavailable() 
	{
		$this->render('message', array(
			'message' => __('Wordy plugin server parameters are not properly configured or the server is temporarily unavailable . ', 'wordy')
		));
	}
	
	public function publish_cancel()
	{
		$this->render('message', array(
			'message' => __('Post could not be posted to wordy, because it is empty.', 'wordy')
		));
	}
	
	public function admin_warnings()
	{
		if (empty($this->options['email']) || empty($this->options['password'])) 
		{
			add_action('admin_notices', array(&$this, 'warning'));
		}
	}

	public function warning()
	{
		$this->render('warning');
	}

	public function post_canceled_notice() 
	{
		$this->render('message', array(
			'message' => __('Publish via Wordy has been cancelled.', 'wordy')
		));
	}

	public function post_cannot_cancel_notice() 
	{
		$this->render('message', array(
			'message' => __('Wordy could not be canceled. It is likely an editor has accepted taken the job.', 'wordy')
		));
	}

	public function post_cannot_reclaim_notice()
	{
		$this->render('message', array(
			'message' => __('You cannot claim re-edit now.', 'wordy')
		));
	}

	public function post_edited_notice()
	{
		$post_ID = (int) $_GET['post'];
		$post = get_post($post_ID);
		$document = unserialize(get_post_meta($post_ID, '_wordy_document_info', true));

		$this->render('post_edited_notice', array(
			'post' => $post
			,'document' => $document
		));
	}
	
	public function post_sent_notice()
	{
		$post = get_post($_GET['post']);

		if (isset($post->ID) && !empty($post->ID)) 
		{
			$message = '';
			$show_cancel = true;
			
			$details = unserialize(get_post_meta($post->ID, '_wordy_document_info', true));
			
			$time = isset($details->approximate_time) && !empty($details->approximate_time) ? $details->approximate_time : 'unknown';

			if ($details->status_code != 'document_new' && $details->status_code != 'document_open') 
			{
				$message = __('Your post is being edited by a Wordy editor. Approximate delivery time: ', 'wordy') . $time . '. <a href="#" onclick="location.href=location.href;return false;">' . __('Update Wordy status', 'wordy') . '</a>';
				$show_cancel = false;
			} 
			elseif ($details->status_code === 'document_open') 
			{
				$message = __('Waiting for a Wordy editor to take your order. ', 'wordy') . '<a href="#" onclick="location.href=location.href;return false;">' . __('Update Wordy status', 'wordy') . '</a>';
				$show_cancel = false;
			} 
			else 
			{
				$message = __('Wordy is verifying your payment.', 'wordy') . ' ';
			}

			$this->render('post_sent_notice', array(
				'message' => $message
				,'show_cancel' => $show_cancel
				,'post' => $post
			));
		}
	}


	
	// 
	// Helper Functions
	// 
	
	protected function api($call, $arguments = array())
	{
		$email = isset($arguments['email']) ? $arguments['email'] : $this->options['email'];
		$password = isset($arguments['password']) ? $arguments['password'] : $this->options['password'];

		switch($call) 
		{
			case 'CREATE_ORDER':
				$remote_url = '/version/1/format/json/order/create/email/' . $email . '/';
				$arguments['language_code'] = in_array($this->options['language_code'], $this->allow_language_codes) ? $this->options['language_code'] : $this->default_options['language_code'];
			break;
		
			case 'CHECK_DOCUMENT_INFO':
				if (!isset($arguments['document_id']) || empty($arguments['document_id'])) 
				{
					return false;
				}
				$remote_url = '/document/info/email/' . $email . '/id/' . $arguments['document_id'];
			break;
			
			case 'DOWNLOAD_DOCUMENT':
				$remote_url = '/document/download/email/' . $email . '/id/' . $arguments['document_id'];
			break;
			
			case 'CREATE_CUSTOMER':
				$remote_url = '/customer/create/';
			break;
			
			case 'CANCEL_WORDY':
				$remote_url = '/version/1/format/json/document/cancel/email/' . $email . '/id/' . $arguments['document_id'] . '/';
			break;
			
			case 'CUSTOMER_INFO':
				$remote_url = '/version/1/format/json/customer/info/email/' . $email . '/';
			break;
			
			case 'RECLAIM_WORDY':
				$remote_url = '/version/1/format/json/document/reedit/email/' . $email . '/id/' . $arguments['id'] . '/';
			break;
			
			case 'WORDY_CONFIGURATION':
			default:
				$remote_url = '/config/info/email/' . $email . '/';
			break;			
		}

		$arguments['password'] = $password;
		
		$response = wp_remote_post($this->wordy['url'] . $remote_url, array('body' => $arguments));
		
		if ($this->debug)
		{
			$_SESSION['wordy_api_url'][] = $this->wordy['url'] . $remote_url;
			$_SESSION['wordy_api_arguments'][] = $arguments;
			$_SESSION['wordy_api_response'][] = $response;
		}
		
		if (is_array($response) && isset($response['body']) && $response['body'] === false) 
		{
			return $response['response']['message'];
		}
		else 
		{
			if (is_array($response) && isset($response['body'])) 
			{
				$data = json_decode($response['body']);
				if ($data == null)
				{
					$data = $response['body'];
				}
				return $data;
			}
			else 
			{
				add_action('admin_notices', array(&$this, 'server_unavailable'));
			}
		}
	}
	
	public function api_debug()
	{
		if (isset($_SESSION['wordy_api_url']))
		{
			echo "<pre>";
				print_r($_SESSION['wordy_api_url']);
				print_r($_SESSION['wordy_api_arguments']);
				print_r($_SESSION['wordy_api_response']);
			echo "</pre>";
			
			if (count($_SESSION['wordy_api_url']) > $this->debug_limit)
			{
				unset($_SESSION['wordy_api_url']);
				unset($_SESSION['wordy_api_arguments']);
				unset($_SESSION['wordy_api_response']);
			}
		}
	}
	
	protected function set_session()
	{
		if(!session_id() || !session_name()) 
		{
			session_start();
		} 
		elseif(!session_id()) 
		{
			session_id();
		}
	}
	
	protected function set_options($options = null)
	{
		if ($options) 
		{
			$new_options = array();
			foreach ($this->default_options as $key => $value)
			{
				if (isset($options[$key]))
				{
					$new_options[$key] = $options[$key];
				}
				else
				{
					$new_options[$key] = $this->default_options[$key];
				}
			}
		}
		else
		{
			$new_options = $this->default_options;
		}

		update_option('wordy', serialize($new_options));

		$this->options = $new_options;
	}
	
	protected function set_i18n()
	{
		load_plugin_textdomain('wordy', WP_PLUGIN_DIR . '/i18n/' . plugin_basename(dirname(__FILE__)));
	}
	
	protected function render($view, $arguments = array(), $ajax = false) 
	{
		foreach ($arguments as $key => $value) 
		{
			$$key = $value;
		}

		if ($ajax)
		{
			ob_start();
		}
		
			$file = rtrim(dirname( __FILE__ ), '/') . '/views/' . $view . '.php';
			if (file_exists($file))
			{
				include $file;
			}
			else
			{
				echo '<pre>' . $file . ' not found.</pre>';
			}

		if ($ajax)
		{
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
	}

	protected function clean_post_variables($post)
	{
		foreach($post as $variable)
		{
      $variable = mysql_real_escape_string(trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($variable) : addslashes($variable))));
		}

		return $post;
	}


	// 
	// URL Handlers for Content before its sent.
	// 
	
	protected function set_absolute_urls($content)
	{
		$base = $this->base_url();
		
		$content = stripslashes($content);
    $content = preg_replace_callback("/<(a[^>]* href)=['\"]([\\/\\.].*?)['\"]/", array(&$this, 'replace_url'), $content);
    $content = preg_replace_callback("/<(img[^>]* src)=['\"]([\\/\\.].*?)['\"]/", array(&$this, 'replace_url'), $content);

		return $content;
	}

	protected function replace_url($match) 
	{
		$url = $this->url_join($this->base_url(), parse_url($match[2]));
		return "<" . $match[1] . "=\"" . $url . "\"";
	}

	protected function base_url() 
	{
		return parse_url(rtrim(get_option('siteurl'), '/') . '/');
	}
	
	protected function url_join($base, $info) 
	{
		if(!($info['path'] || $info['query'] || $info['fragment'])) 
		{
			return $this->url_deparse($base);
		}

		$base['query'] = $info['query'];
		$base['fragment'] = $info['fragment'];
		
		if(substr($info['path'], 0, 1) == '/') 
		{
			$base['path'] = $info['path'];
			return $this->url_deparse($base);
		}

		$segments = explode('/', $base['path']);
		
		array_pop($segments);
		
		$segments = array_merge($segments, explode('/', $info['path']));

	  if($segments[count($segments) - 1] == '.')
		{
			$segments[count($segments) - 1] = '';
		}
		$segments = array_filter($segments, array(&$this, 'segment_helper'));

		while (true) 
		{
			$x = 1;
			$number = count($segments) - 1;
			while ($x < $number) 
			{
				if ($segments[$x] == '..' && $segments[$x - 1] != '' && $segments[$x - 1] != '..') 
				{
					unset($segments[$x]);
					unset($segments[$x - 1]);
					break;
				}
				$x++;
			}
			
			if ($x == $number)
			{
				break;
			}

			$segment_count = count($segments);
			if ($segment_count == 2 && $segments[0] == '' && $segments[1] == '..')
			{
				$segments[1] = '';
			}
			elseif ($segment_count >= 2 && $segments[$segment_count - 1] == '..') 
			{
				unset($segments[$segment_count - 1]);
				$segments[$segment_count - 2] = '';
			}
			
			$base['path'] = implode('/', $segments);
		}
		
		return $this->url_deparse($base);
	}

	protected function segment_helper($value) 
	{
		return $value != '.';
	}

	protected function url_deparse($info) 
	{
		$result = $info['scheme'] . '://';
		
		if ($info['user'] || $info['pass'])
		{
			$result .= $info['user'] . ':' . $info['pass'] . '@';
		}
		
		$result .= $info['host'] . $info['path'];
		
		if ($info['query'])
		{
			$result .= '?' . $info['query'];
		}
		
		if ($info['fragment'])
		{
			$result .= '?' . $info['fragment'];
		}
		
		return $result;
	}
}

?>
<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

use Pusher\Pusher;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-live-support
 * @since      1.0.0
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/public
 * @author     SRKP Team <keshavsharma262001@gmail.com>
 */

class Srkp_Live_Support_Public
{
	private $pusher;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{


		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/srkp-live-support-public.css', array(), filemtime(plugin_dir_path(__FILE__) . 'css/srkp-live-support-public.css'), 'all');
		wp_enqueue_style($this->plugin_name.'-font', plugin_dir_url(dirname(__FILE__)) . 'fontawesome/css/fontawesome.min.css', [], filemtime(plugin_dir_path(dirname(__FILE__)) . 'fontawesome/css/fontawesome.min.css'));
		wp_enqueue_style($this->plugin_name . '-fa-solid',plugin_dir_url(dirname(__FILE__)) . 'fontawesome/css/solid.min.css',[],filemtime(plugin_dir_path(dirname(__FILE__)) . 'fontawesome/css/solid.min.css'));
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		$srkp_pusher_key     = get_option('srkp_pusher_key', '');
		$srkp_pusher_cluster = get_option('srkp_pusher_cluster', 'ap2');
		wp_enqueue_script('pusher-js', plugin_dir_url(__FILE__) . 'js/pusher.min.js', [], filemtime(plugin_dir_path(__FILE__) . 'js/pusher.min.js'), true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/srkp-live-support-public.js', ['jquery', 'pusher-js'], filemtime(plugin_dir_path(__FILE__) . 'js/srkp-live-support-public.js'), true);
		$user_id = get_current_user_id();
		if (!$user_id && session_id()) {
			$user_id = isset($_SESSION['srkp_guest_id']) ? sanitize_text_field($_SESSION['srkp_guest_id']) : '';
		}
		if ($user_id && session_id() && !empty($_SESSION['srkp_guest_id'])) {
			$user_id = isset($_SESSION['srkp_guest_id']) ? sanitize_text_field($_SESSION['srkp_guest_id']) : '';
		}
		// Localize data
		wp_localize_script($this->plugin_name, 'srkp_chat_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('srkp_chat_nonce'),
			'nonce_get_chat' => wp_create_nonce('srkp_chat_nonce_get_chat'),
			'pusher_key' => $srkp_pusher_key,
			'pusher_cluster' => $srkp_pusher_cluster,
			'user_id' => $user_id
		]);
	}

	function srkp_chat_get()
	{
		check_ajax_referer('srkp_chat_nonce_get_chat', 'nonce');
		global $wpdb;
		$table = esc_sql($wpdb->prefix . 'srkp_live_chat');
		$messages_table = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');

		if (is_user_logged_in()) {
			$email = wp_get_current_user()->user_email;

			$cache_key = 'srkp_chat_' . md5('email_' . $email);
			$cached = wp_cache_get($cache_key);
			if ($cached !== false) {
				wp_send_json_success($cached);
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$row = $wpdb->get_row($wpdb->prepare("SELECT u.id ,m.messages FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m on u.id = m.user_id WHERE email = %s", $email),);
			$messages = $row ? json_decode($row->messages, true) : [];
			wp_cache_set($cache_key, $messages, '', 30);
		} else {
			session_start();
			if (!$user_id && session_id()) {
				$user_id = isset($_SESSION['srkp_guest_id']) ? sanitize_text_field($_SESSION['srkp_guest_id']) : '';
				if ($user_id) {
					$cache_key = 'srkp_chat_' . md5('guest_' . $user_id);
					$cached = wp_cache_get($cache_key);
					if ($cached !== false) {
						wp_send_json_success($cached);
					}
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$row = $wpdb->get_row($wpdb->prepare("SELECT u.id, m.messages, m.id AS message_id FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id WHERE u.user_id = %s", $user_id));
					$messages = $row ? json_decode($row->messages, true) : [];
					wp_cache_set($cache_key, $messages, '', 30);
				}
			}
		}
		wp_send_json_success($messages);
	}

	public function  srkp_live_chat_support()
	{
		$email_nonce = wp_create_nonce('srkp_guest_email_nonce');
		$otp_nonce = wp_create_nonce('srkp_otp_nonce');
		require_once plugin_dir_path(__FILE__) . 'partials/srkp-live-support-public-display.php';
	}

	public function wp_ajax_srkp_chat_send_data()
	{
		check_ajax_referer('srkp_chat_nonce', 'nonce');
		global $wpdb;
		$table = esc_sql($wpdb->prefix . 'srkp_live_chat');
		$messages_table = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');
		$saved_tz = get_option('srkp_timezone','Asia/Kolkata');
		$dt = new DateTime('now', new DateTimeZone($saved_tz));
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$name  = $current_user->display_name;
			$email = $current_user->user_email;
			$type  = 'user';
			$cache_key = 'srkp_live_chat_user_' . $user_id;
			$row = wp_cache_get( $cache_key, 'srkp_live_chat' );

			if ( $row === false ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$row = $wpdb->get_row($wpdb->prepare("SELECT u.id AS srkp_table_id, u.user_id , m.id, m.messages , m.unread_count FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m on u.id = m.user_id WHERE email = %s", $email),);
				wp_cache_set( $cache_key, $row, 'srkp_live_chat', 60 );
			}
			if (! empty($row)) {
				$user_id = $row->user_id;
				$new_message = [
					'sender'  => 'user',
					'message' => isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '',
					'time'    => $dt->format('Y-m-d H:i:s'),
					'user_id' => $row->user_id
				];
				$messages = (!empty($row) && !empty($row->messages)) ? json_decode($row->messages, true) : [];
				if (!is_array($messages)) {
					$messages = [];
				}
				$messages[] = $new_message;
				$unread_count = (!empty($row) && isset($row->unread_count)) ? intval($row->unread_count) : 0;
				if (!empty($messages)) {
					$unread_count = $unread_count + 1;
				}

				$this->update_user($table, $row->srkp_table_id, $type);
				$this->update_message($messages_table, $row->id, $messages, $unread_count);
				if ($row && empty($row->id)) {
					$this->insert_message($messages_table, $row->srkp_table_id, $messages);
				}
			} else {
				$guest_user_id = 'user_' . time() . '_' . wp_generate_uuid4();
				$is_first_message = true;
				$new_message = [
					'sender'  => 'user',
					'message' => isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '',
					'time'    => $dt->format('Y-m-d H:i:s'),
					'user_id' => $guest_user_id
				];

				$messages[] = $new_message;
				$data = [
					'user_id' => $guest_user_id,
					'email' => $email,
					'name' => $name,
					'type' => $type,
					'status' => 'active',
					'email_verified' => 1,
				];
				$this->insert_user($table, $data);

				$main_id = $wpdb->insert_id;

				$this->insert_message($messages_table, $main_id, $messages);
			}
			if (!session_id()) {
				session_start();
			}
			$_SESSION['srkp_guest_id'] = $user_id ? $user_id : $guest_user_id;
			$_SESSION['srkp_session_start'] = time();
			$_SESSION['srkp_guest_email'] = $current_user->user_email;

			$this->chat_room($user_id, $new_message);

			if ($is_first_message) {
				wp_send_json_success([
					'register' => true,
					'message'  => $new_message,
					'user_id'  => $user_id ? $user_id : $guest_user_id,
				]);
			} else {
				wp_send_json_success([
					'register' => false,
					'message'  => $new_message,
				]);
			}
		} else {
			session_start();
			if (!$user_id && session_id()) {
				$user_id = isset($_SESSION['srkp_guest_id']) ? sanitize_text_field($_SESSION['srkp_guest_id']) : '';

				$new_message = [
					'sender' => 'user',
					'message' => isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '',
					'time' => $dt->format('Y-m-d H:i:s'),
					'user_id' => $user_id
				];
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$row = $wpdb->get_row($wpdb->prepare("SELECT u.id AS user_id, m.messages ,m.id AS message_id,m.unread_count FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id WHERE u.user_id = %s", $user_id),);
				$messages = (!empty($row->messages)) ? json_decode($row->messages, true) : [];
				if (!is_array($messages)) {
					$messages = [];
				}
				$unread_count = (!empty($row) && isset($row->unread_count)) ? intval($row->unread_count) : 0;
				if (!empty($messages)) {
					$unread_count = $unread_count + 1;
				}
				$messages[] = $new_message;

				if ($row && !empty($row->message_id)) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$this->update_user($table, $row->user_id, 'active');
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$this->update_message($messages_table, $row->message_id, $messages, $unread_count);
				}
				if ($row && empty($row->message_id)) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$this->update_user($table, $row->user_id, 'active');
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$this->insert_message($messages_table, $row->user_id, $messages);
				}
			}
			wp_cache_delete( $cache_key, 'srkp_live_chat' );
			$this->chat_room($user_id, $new_message);
			wp_send_json_success($new_message);
		}
	}

	public function insert_user($table, $data)
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->insert(
			$table,
			[
				'user_id'        => $data['user_id'],
				'email'          => $data['email'],
				'name'           => $data['name'],
				'type'           => $data['type'],
				'status'         => $data['status'],
				'email_verified' => $data['email_verified'],
			],
			['%s', '%s', '%s', '%s', '%s', '%d']
		);
	}
	public function update_user($table, $user_id, $type)
	{
		global $wpdb;
		$cache_key = 'srkp_live_chat_user_' . $user_id;
		wp_cache_delete( $cache_key, 'srkp_live_chat' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->update(
			$table,
			[
				'type' => $type,
				'status' => 'active',
				'email_verified'=>1
			],
			['id' => $user_id],
			['%s', '%s','%d'],
			['%d']
		);
	

	}
	public function insert_message($messages_table, $user_id, $messages)
	{
		global $wpdb;
		$cache_key = 'srkp_live_chat_user_' . $user_id;
		wp_cache_delete( $cache_key, 'srkp_live_chat' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->insert(
			$messages_table,
			[
				'user_id' => $user_id,
				'messages' => wp_json_encode($messages),
				'unread_count' => 1,
			],
			['%d', '%s', '%d'],
		);
		
	}

	public function update_message($messages_table, $user_id, $messages, $unread_count)
	{
	
		global $wpdb;
		$cache_key = 'srkp_live_chat_user_' . $user_id;
		wp_cache_delete( $cache_key, 'srkp_live_chat' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->update(
			$messages_table,
			[
				'messages' => wp_json_encode($messages),
				'unread_count' => $unread_count ? $unread_count : 1,
			],
			['id' => $user_id],
			['%s', '%d'],
			['%d']
		);
		
	}

	public function chat_room($user_id, $new_message)
	{
		$srkp_pusher_app_id  = get_option('srkp_pusher_app_id', '');
		$srkp_pusher_key     = get_option('srkp_pusher_key', '');
		$srkp_pusher_secret  = get_option('srkp_pusher_secret', '');
		$srkp_pusher_cluster = get_option('srkp_pusher_cluster', 'ap2');

		$pusher = new Pusher(
			$srkp_pusher_key,
			$srkp_pusher_secret,
			$srkp_pusher_app_id,
			['cluster' => $srkp_pusher_cluster, 'useTLS' => true]
		);
		$pusher->trigger('live-chat-' . $user_id, 'new-message', $new_message);
	}

	function srkp_save_guest_email()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'srkp_live_chat';
		$messages_table = $wpdb->prefix . 'srkp_live_chat_messages';

		// Handle guest email submission
		if (
			!empty($_POST['srkp_guest_email_nonce']) &&
			wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['srkp_guest_email_nonce'])), 'srkp_guest_email_nonce')
		) {

			$guest_email = isset($_POST['srkp_guest_email']) ? sanitize_email( wp_unslash($_POST['srkp_guest_email']) ) : '';
			if (!is_email($guest_email)) {
				wp_send_json_error(['message' => 'Invalid email']);
			}

			$cache_key = "srkp_guest_user_" . md5($guest_email);
			$existing_user = wp_cache_get($cache_key);

			if ($existing_user === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$existing_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}srkp_live_chat WHERE email = %s", $guest_email));
				wp_cache_set($cache_key, $existing_user, '', MINUTE_IN_SECONDS * 10);
			}

			$user_name = sanitize_text_field(explode('@', $guest_email)[0]);
			$guest_user_id = 'user_' . time() . '_' . wp_generate_uuid4();
			$otp = wp_rand(100000, 999999);
			$hashed_otp = password_hash($otp, PASSWORD_DEFAULT);

			if ($existing_user) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update(
					$table_name,
					[
						'verification_token' => $hashed_otp,
						'email_verified' => 0,
						'updated_at' => current_time('mysql')
					],
					['email' => $guest_email],
					['%s', '%d', '%s'],
					['%s']
				);
				$user = $existing_user;
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$table_name,
					[
						'user_id' => $guest_user_id,
						'name' => $user_name,
						'email' => $guest_email,
						'type' => 'guest',
						'status' => 'active',
						'email_verified' => 0,
						'verification_token' => $hashed_otp,
						'created_at' => current_time('mysql'),
						'updated_at' => current_time('mysql')
					],
					['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
				);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}srkp_live_chat WHERE email = %s", $guest_email));
			}

			// Update cache
			wp_cache_set($cache_key, $user, '', MINUTE_IN_SECONDS * 10);

			// Send OTP email
			$from_email = get_option('srkp_smtp_from_email', get_option('admin_email'));
			$from_name  = get_option('srkp_smtp_from_name', 'SRKP Live Chat');
			$headers = [
				'Content-Type: text/plain; charset=UTF-8',
				'From: ' . $from_name . ' <' . $from_email . '>'
			];
			wp_mail(
				$guest_email,
				"Your OTP for Live Chat",
				"Hello $guest_email,\n\nYour OTP is: $otp\nValid for 10 minutes.\nDo not share it with anyone.",
				$headers
			);


			wp_send_json_success(['message' => 'OTP sent']);
		}

		// Handle OTP verification
		if (!empty($_POST['srkp_otp_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['srkp_otp_nonce'])), 'srkp_otp_nonce')) {

			$guest_email = sanitize_email(wp_unslash($_POST['srkp_guest_email'] ?? ''));
			$entered_otp = sanitize_text_field(wp_unslash($_POST['srkp_otp_input'] ?? ''));
			$cache_key = "srkp_guest_user_" . md5($guest_email);

			$user_data = wp_cache_get($cache_key);
			if ($user_data === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}srkp_live_chat WHERE email = %s", $guest_email));
				wp_cache_set($cache_key, $user_data, '', MINUTE_IN_SECONDS * 10);
			}

			if (!$user_data) {
				wp_send_json_error(['message' => 'User not found.']);
			}

			if (password_verify($entered_otp, $user_data->verification_token)) {
				// Mark email verified
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update(
					$table_name,
					['email_verified' => 1, 'status' => 'active'],
					['email' => $guest_email],
					['%d', '%s'],
					['%s']
				);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$user_row = $wpdb->get_row($wpdb->prepare(
					"SELECT u.id AS srkp_user_id, u.user_id, m.id AS message_id, m.messages 
					 FROM {$wpdb->prefix}srkp_live_chat AS u 
					 LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id 
					 WHERE u.email = %s",
					$guest_email
				));
				wp_cache_set("srkp_chat_user_" . md5($guest_email), $user_row, '', MINUTE_IN_SECONDS * 30);

				if (!session_id()) session_start();
				$_SESSION['srkp_guest_id'] = $user_row->user_id;
				$_SESSION['srkp_session_start'] = time();
				$_SESSION['srkp_guest_email'] = $guest_email;

				wp_send_json_success([
					'message' => 'OTP verified successfully.',
					'srkp_guest_id' => $user_row->user_id
				]);
			} else {
				wp_send_json_error(['message' => 'Incorrect OTP']);
			}
		}
	}

	function srkp_start_session()
	{
		global $wpdb;
		$table = $wpdb->prefix . 'srkp_live_chat';
		$saved_tz = get_option('srkp_timezone', 'Asia/Kolkata');
		$dt = new DateTime('now', new DateTimeZone($saved_tz));

		if (!session_id()) {
			session_start();
		}
		if (!is_user_logged_in()) {
			$session_lifetime = 3600;
			if (isset($_SESSION['srkp_session_start']) && isset($_SESSION['srkp_guest_id'])) {
				if (time() - $_SESSION['srkp_session_start'] > $session_lifetime) {
					$guest_id = isset($_SESSION['srkp_guest_id']) ? sanitize_text_field($_SESSION['srkp_guest_id']) : '';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$wpdb->update(
						$table,
						[
							'status'     => 'inactive',
							'updated_at' => $dt->format('Y-m-d H:i:s')
						],
						['user_id' => $guest_id]
					);
					$cache_key = 'srkp_live_chat_user_' . $guest_id;
					wp_cache_delete( $cache_key, 'srkp_live_chat' );

					unset($_SESSION['srkp_guest_id']);
					unset($_SESSION['srkp_session_start']);
				}
			}
		}
	}
}

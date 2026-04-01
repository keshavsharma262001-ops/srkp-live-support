<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

use Pusher\Pusher;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-live-support
 * @since      1.0.0
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/admin
 * @author     SRKP Team <keshavsharma262001@gmail.com>
 */
class Srkp_Live_Support_Admin
{

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Srkp_Live_Support_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Srkp_Live_Support_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/srkp-live-support-admin.css', [], filemtime(plugin_dir_path(__FILE__) . 'css/srkp-live-support-admin.css'));
		wp_enqueue_style($this->plugin_name.'-font', plugin_dir_url(dirname(__FILE__)) . 'fontawesome/css/fontawesome.min.css', [], filemtime(plugin_dir_path(dirname(__FILE__)) . 'fontawesome/css/fontawesome.min.css'));
		wp_enqueue_style($this->plugin_name . '-fa-solid',plugin_dir_url(dirname(__FILE__)) . 'fontawesome/css/solid.min.css',[],filemtime(plugin_dir_path(dirname(__FILE__)) . 'fontawesome/css/solid.min.css')
);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook)
	{
		$srkp_pusher_app_id  = get_option('srkp_pusher_app_id', '');
		$srkp_pusher_key     = get_option('srkp_pusher_key', '');
		$srkp_pusher_secret  = get_option('srkp_pusher_secret', '');
		$srkp_pusher_cluster = get_option('srkp_pusher_cluster', 'ap2');
		$message_count=$this->srkp_notify_new_message();
		$count='';
		if($message_count){
			$count= '-'.$message_count;
		}
		if ($hook !== 'toplevel_page_srkp-live-chat' && $hook !== 'srkp-live-chat'.$count.'_page_srkp-live-chat-settings') {
			return;
		}
		// wp_enqueue_script('pusher-js', 'https://js.pusher.com/8.0/pusher.min.js', [], null, true);
		wp_enqueue_script('pusher-js', plugin_dir_url(__FILE__) . 'js/pusher.min.js', [], filemtime(plugin_dir_path(__FILE__) . 'js/pusher.min.js'), true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/srkp-live-support-admin.js', ['jquery', 'pusher-js'], filemtime(plugin_dir_path(__FILE__) . 'js/srkp-live-support-admin.js'), true);
		wp_localize_script($this->plugin_name, 'srkp_chat_admin', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('srkp_chat_nonce'), 'pusher_key' => $srkp_pusher_key, 'pusher_cluster' => $srkp_pusher_cluster]);
	}




	public function srkp_notify_new_message()
	{
		global $wpdb;
		 $table_users   = $wpdb->prefix . 'srkp_live_chat';
			$table_messages = $wpdb->prefix . 'srkp_live_chat_messages';
			$cache_key_menu = 'srkp_live_chat_unread_count';
			$users_with_unread = wp_cache_get($cache_key_menu, 'srkp_live_chat');

			if ($users_with_unread === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$users_with_unread = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT u.id)
						FROM {$wpdb->prefix}srkp_live_chat AS u
						LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id
						WHERE u.email_verified = %d
						AND m.unread_count > 0",
						1
					)
				);
				wp_cache_set($cache_key_menu, $users_with_unread, 'srkp_live_chat', 60);
			}

			return $users_with_unread;
			
	}
	public function admin_menu()
	{
		$message_count= $this->srkp_notify_new_message();
			$label = 'SRKP Live Chat';
			if ($message_count > 0) {
				$label .= ' <span class="srkp_notification">' . intval($message_count) . '</span>';
			}
		add_menu_page(
		'SRKP Live Chat', 
		$label,
		'manage_options', 
		'srkp-live-chat', 
		
		function () {
			global $wpdb;
			$table = esc_sql($wpdb->prefix . 'srkp_live_chat');
			$messages_table = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');
			$cache_key = 'srkp_live_chat_admin_users';
			$users = wp_cache_get($cache_key, 'srkp_live_chat');
			if ($users === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$users = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT u.*, m.unread_count ,  m.created_at AS message_created , m.updated_at AS message_update
					 FROM {$wpdb->prefix}srkp_live_chat AS u
					 LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m 
						 ON u.id = m.user_id
					 WHERE u.email_verified = %d
					 ORDER BY m.updated_at DESC",
						1
					)
				);
				wp_cache_set($cache_key, $users, 'srkp_live_chat', 60);
			}
			require_once plugin_dir_path(__FILE__) . 'partials/srkp-live-support-admin-display.php';
		}, 'dashicons-format-chat', 26);
		add_submenu_page(
			'srkp-live-chat',
			'Chat Settings',
			'Settings',
			'manage_options',
			'srkp-live-chat-settings',
			[$this, 'settings_page_callback']
		);
	}
	public function update_user_status(	$table , $user_id){
		global $wpdb;
		$cache_key = 'srkp_live_chat_admin_users';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$udpate = $wpdb->update(
			$table,
			['status' => 'inactive'],
			['id' => $user_id],
			['%s'],
			['%d']
		);
		wp_cache_delete($cache_key, 'srkp_live_chat');
		return $udpate;
	}

	function settings_page_callback()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/srkp-live-chat-settings.php';
	}

	public function srkp_register_chat_settings()
	{
		register_setting('srkp_live_chat_settings_group', 'srkp_enable_chat', ['sanitize_callback' => 'absint']);
		// register_setting('srkp_live_chat_settings_group', 'srkp_support_icon', ['sanitize_callback' => [$this, 'srkp_sanitize_support_icon']]);
		
		register_setting('srkp_live_chat_settings_group', 'srkp_widget_text', ['sanitize_callback' => 'sanitize_text_field']);
		$color_fields = [
			'srkp_btn_bg_color',
			'srkp_btn_txt_color',
			'srkp_chatbox_bg_color',
			'srkp_chatbox_txt_color',
			'srkp_chatbox_header_bg_color',
			'srkp_chatbox_header_color'
		];
		foreach ($color_fields as $field) {
			register_setting('srkp_live_chat_settings_group', $field, ['sanitize_callback' => 'sanitize_hex_color']);
		}
		register_setting('srkp_live_chat_settings_group', 'srkp_chatbox_header_only', ['sanitize_callback' => 'absint']);
		$creds = [
			'srkp_timezone',
			'srkp_pusher_app_id',
			'srkp_pusher_key',
			'srkp_pusher_secret',
			'srkp_pusher_cluster'
		];
		foreach ($creds as $cred) {
			register_setting('srkp_live_chat_settings_group', $cred, ['sanitize_callback' => 'sanitize_text_field']);
		}
		$smtp_fields = [
				'srkp_smtp_host'       => 'sanitize_text_field',
				'srkp_smtp_port'       => 'absint',
				'srkp_smtp_username'   => 'sanitize_text_field',
				'srkp_smtp_password'   => 'sanitize_text_field',
				'srkp_smtp_encryption' => 'sanitize_text_field',
				'srkp_smtp_from_email' => 'sanitize_email',
				'srkp_smtp_from_name'  => 'sanitize_text_field'
			];
		foreach ($smtp_fields as $field => $callback) {
			register_setting('srkp_live_chat_settings_group', $field, ['sanitize_callback' => $callback]);
		}
	}
	public function srkp_sanitize_support_icon() {
		if (!isset($_POST['srkp_support_icon_nonce'])) {
    		return; 
		}
		$nonce = isset($_POST['srkp_support_icon_nonce']) ? sanitize_text_field(wp_unslash($_POST['srkp_support_icon_nonce'])) : '';
		if (!wp_verify_nonce($nonce, 'srkp_support_icon_action')) {
			return; 
		}
        if (!isset($_POST['option_page']) || $_POST['option_page'] !== 'srkp_live_chat_settings_group') {
            return ;
        }

        if (!empty($_FILES['srkp_support_icon_file']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('srkp_support_icon_file', 0);
            if (!is_wp_error($attachment_id)) {
                $url = wp_get_attachment_url($attachment_id);
				update_option('srkp_support_icon', $url);
            }
        }
		if(isset($_POST) && isset($_POST['srkp_support_icon']) && $_POST['srkp_support_icon']==='' && empty($_FILES['srkp_support_icon_file']['name'])){
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			update_option('srkp_support_icon','');
		}
        return ;
    }
		/**
		 * Apply SMTP credentials stored in plugin settings
		 */
		public function srkp_custom_smtp_config( $phpmailer ) {

			// Get saved SMTP settings

			$host       = get_option('srkp_smtp_host') ?? '';
			$port       = get_option('srkp_smtp_port') ?? '';
			$username   = get_option('srkp_smtp_username') ?? '';
			$password   = get_option('srkp_smtp_password') ?? '';
			$encryption = get_option('srkp_smtp_encryption') ?? '';
			$from_email = get_option('srkp_smtp_from_email') ?? '';
			$from_name  = get_option('srkp_smtp_from_name') ?? '';

			// Configure PHPMailer
			$phpmailer->isSMTP();
			$phpmailer->Host       = $host;
			$phpmailer->SMTPAuth   = true;
			$phpmailer->Port       = $port;
			$phpmailer->Username   = $username;
			$phpmailer->Password   = $password;
			$phpmailer->SMTPSecure = $encryption;

			// Force From address
			$phpmailer->From       = $from_email;
			$phpmailer->FromName   = $from_name;
		}
		public function srkp_smtp_from_email( $email ) {
			$from = get_option('srkp_smtp_from_email');
		return !empty($from) ? $from : $email;
		}

		public function srkp_smtp_from_name( $name ) {
			$from_name = get_option('srkp_smtp_from_name');
		return !empty($from_name) ? $from_name : $name;
		}


	public function wp_ajax_srkp_chat_get_user()
	{
		check_ajax_referer('srkp_chat_nonce', 'nonce');
		if (!current_user_can('manage_options')) wp_send_json_error('Not allowed');

		if (isset($_POST['user_id'])) {
			$user_id = sanitize_text_field(wp_unslash($_POST['user_id']));
		} else {
			$user_id = null;
		}

		global $wpdb;
		$table = esc_sql($wpdb->prefix . 'srkp_live_chat');
		$messages_table = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');

		$cache_key = 'srkp_live_chat_user_' . $user_id;
		$row = wp_cache_get($cache_key, 'srkp_live_chat');
		if ($row === false) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT m.id AS message_id, m.messages ,m.unread_count
				 FROM {$wpdb->prefix}srkp_live_chat AS u 
				 LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m 
				 ON u.id = m.user_id 
				 WHERE u.user_id = %s",
					$user_id
				)
			);
			wp_cache_set($cache_key, $row, 'srkp_live_chat', 60);
		}
		$messages = $row ? json_decode($row->messages, true) : [];
		if (! empty($row->message_id)) {
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update($messages_table, ['unread_count' => 0], ['id' => $row->message_id], ['%d'], ['%d']);
			wp_cache_delete($cache_key, 'srkp_live_chat');
			wp_cache_delete('srkp_live_chat_admin_users', 'srkp_live_chat');
		}
		wp_send_json_success($messages);
	}
	public function wp_ajax_srkp_chat_send_admin_user()
	{
		check_ajax_referer('srkp_chat_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Not allowed');
		}

		global $wpdb;

		$user_id = isset($_POST['user_id']) ? sanitize_text_field(wp_unslash($_POST['user_id'])) : '';
		$message_text = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';

		$saved_tz = get_option('srkp_timezone', 'Asia/Kolkata');
		$dt = new DateTime('now', new DateTimeZone($saved_tz));

		$new_message = [
			'sender'  => 'admin',
			'message' => $message_text,
			'time'    => $dt->format('Y-m-d H:i:s'),
			'user_id' => $user_id
		];

		$table = esc_sql($wpdb->prefix . 'srkp_live_chat');
		$messages_table = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');

		$cache_key = 'srkp_live_chat_user_' . $user_id;
		$row = wp_cache_get($cache_key, 'srkp_live_chat');
		if ($row === false) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT u.id AS chat_id, m.id AS message_id, m.messages 
             FROM {$wpdb->prefix}srkp_live_chat AS u
             LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id
             WHERE u.user_id = %s",
					$user_id
				)
			);
			wp_cache_set($cache_key, $row, 'srkp_live_chat', 60);
		}

		$messages = !empty($row->messages) ? json_decode($row->messages, true) : [];
		if (!is_array($messages)) {
			$messages = [];
		}

		$messages[] = $new_message;

		if ($row && $row->chat_id) {
			// Update existing message record
			if (!empty($row->message_id)) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update(
					$messages_table,
					['messages' => wp_json_encode($messages)],
					['id' => $row->message_id],
					['%s'],
					['%d']
				);
			} else {
				// Insert if messages record does not exist
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$messages_table,
					[
						'user_id'  => $row->chat_id,
						'messages' => wp_json_encode($messages)
					],
					['%d', '%s']
				);
			}
			wp_cache_delete($cache_key, 'srkp_live_chat');
			wp_cache_delete('srkp_live_chat_admin_users', 'srkp_live_chat');
		}

		// Push message via Pusher
		$pusher = new Pusher(
			get_option('srkp_pusher_key', ''),
			get_option('srkp_pusher_secret', ''),
			get_option('srkp_pusher_app_id', ''),
			[
				'cluster' => get_option('srkp_pusher_cluster', 'ap2'),
				'useTLS' => true
			]
		);
		$pusher->trigger('live-chat-' . $user_id, 'new-message', $new_message);

		wp_send_json_success($new_message);
	}


	function srkp_clear_chat()
	{
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (! wp_verify_nonce($nonce, 'srkp_clear_chat')) {
			wp_send_json_error('Invalid nonce!');
		}
		$user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? sanitize_text_field(wp_unslash($_POST['user_id'])) : '';
		if ($user_id) {

			global $wpdb;
			$table 		      = esc_sql($wpdb->prefix . 'srkp_live_chat');
			$messages_table   = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');

			$cache_key = 'srkp_live_chat_message_' . $user_id;
			$row = wp_cache_get($cache_key, 'srkp_live_chat');

			if ($row === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$message_id = $wpdb->get_var($wpdb->prepare("SELECT m.id AS message_id FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id WHERE u.user_id = %s", $user_id));
				wp_cache_set($cache_key, $row, 'srkp_live_chat', 60);
			}
			if ($message_id) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->delete($messages_table, ['id' => $message_id], ['%d']);
				wp_cache_delete($cache_key, 'srkp_live_chat');
			}
			wp_send_json_success('Chat cleared successfully!');
		}
		wp_send_json_success('Chat not cleared !');
	}

	function srkp_delete_user()
	{
		global $wpdb;
		$table 		      = esc_sql($wpdb->prefix . 'srkp_live_chat');
		$messages_table   = esc_sql($wpdb->prefix . 'srkp_live_chat_messages');
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (!wp_verify_nonce($nonce, 'srkp_delete_user')) {
			wp_send_json_error('Invalid nonce!');
		}
		$cache_key = 'srkp_live_chat_user_delete_' . $user_id;
		$row = wp_cache_get($cache_key, 'srkp_live_chat');

		$user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? sanitize_text_field(wp_unslash($_POST['user_id'])) : '';
		if ($user_id) {
			if ($row === false) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$row = $wpdb->get_row($wpdb->prepare("SELECT u.id , m.id AS message_id FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id WHERE u.user_id = %s", $user_id));
				wp_cache_set($cache_key, $row, 'srkp_live_chat', 60);
			}
			if ($row->id) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->delete($table, ['id' => $row->id], ['%d']);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->delete($messages_table, ['id' => $row->message_id], ['%d']);
				wp_cache_delete($cache_key, 'srkp_live_chat');
				wp_cache_delete('srkp_live_chat_admin_users', 'srkp_live_chat');
			}
			wp_send_json_success('User Delete!');
		}
		wp_send_json_success('User Not Delete');
	}
	// bulk user action handler

function srkp_bulk_user_action_handler() {
		global $wpdb;
		if (isset($_POST['user_ids']) && is_array($_POST['user_ids'])) {
			$user_ids = array_map('sanitize_text_field', wp_unslash($_POST['user_ids']));
		} else {
			$user_ids = [];
		}

		$bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bulk_action'])) : '';
		$nonce       = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		$cache_key = 'srkp_live_chat_bulk_action';
		$row = wp_cache_get($cache_key, 'srkp_live_chat');
			$message_id = [];
			$srkp_live_chat_id= [];
				foreach ($user_ids as $user_id) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$row = $wpdb->get_row($wpdb->prepare("SELECT u.id , m.id AS message_id FROM {$wpdb->prefix}srkp_live_chat AS u LEFT JOIN {$wpdb->prefix}srkp_live_chat_messages AS m ON u.id = m.user_id WHERE u.user_id = %s", $user_id));
					if ($row->id) {
						$srkp_live_chat_id[]=$row->id;
					}
					if ($row->message_id) {
						$message_id[]=$row->message_id;	
					}
					wp_cache_set($cache_key, $row, 'srkp_live_chat', 60);

					}
		switch ($bulk_action) {
			case 'clear':
				if (!wp_verify_nonce($nonce, 'srkp_bulk_clear_chat')) {
					wp_send_json_error(['message' => 'Invalid clear nonce']);
				}
				if (!empty($message_id)) {
					
					$this->bulk_delete('srkp_live_chat_messages', $message_id);
				}	
				wp_send_json_success([
				'message' => 'Chat messages cleared successfully'
				]);
			break;

			case 'delete':
				if (!wp_verify_nonce($nonce, 'srkp_bulk_delete_user')) {
					wp_send_json_error(['message' => 'Invalid delete nonce']);
				}
				if (!empty($srkp_live_chat_id)) {
					$this->bulk_delete('srkp_live_chat', $srkp_live_chat_id);
				}
				if (!empty($message_id)) {
					$this->bulk_delete('srkp_live_chat_messages', $message_id);
				}
				wp_send_json_success([
					'message' => 'Users deleted successfully'
				]);

			break;
			case 'read':
				if (!wp_verify_nonce($nonce, 'srkp_bulk_read_all_user')) {
					wp_send_json_error(['message' => 'Invalid read nonce']);
				}
				if (!empty($message_id)) {				
					foreach ($message_id as $msg_id) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->update($wpdb->prefix . 'srkp_live_chat_messages', ['unread_count' => 0], ['id' => $msg_id], ['%d'], ['%d']);
					}
					wp_send_json_success([
						'message' => 'Message seen successfully'
					]);
				}
			break;

			default:
				wp_send_json_error(['message' => 'Invalid action']);
		}
}
function bulk_delete($tablename, $ids){
		global $wpdb;
		$cache_key = 'srkp_live_chat_bulk_action';
		$row = wp_cache_get($cache_key, 'srkp_live_chat');

		foreach ($ids as $id) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->delete($wpdb->prefix . $tablename , ['id' => $id], ['%d']);
			wp_cache_delete($cache_key, 'srkp_live_chat');
		}
}




public function srkp_test_smtp() {
    add_action('wp_mail_failed', function($error){
        $error_message = $error->get_error_message();
        wp_send_json_error("<span style='color:red;'>SMTP Error: $error_message</span>");
    });
	$get_smtp_from_email = get_option('srkp_smtp_from_email', get_option('admin_email'));
    $to      = $get_smtp_from_email;
    $subject = "SRKP Chat - SMTP Test";
    $message = "This is a test email from SRKP Live Chat plugin.";
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    if ( wp_mail($to, $subject, $message, $headers) ) {
        wp_send_json_success("<span style='color:green;'>SMTP working! Test email sent to: $to</span>");
    } else {
        wp_send_json_error("<span style='color:red;'>SMTP failed! No detailed error returned.</span>");
    }
}





}

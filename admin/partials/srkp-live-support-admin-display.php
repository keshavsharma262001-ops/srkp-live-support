<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-live-support
 * @since      1.0.0
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/admin/partials
 */
$saved_tz = get_option('srkp_timezone', 'Asia/Kolkata');
$dt = new DateTime('now', new DateTimeZone($saved_tz));
$current = $dt->format('Y-m-d H:i:s');
?>
<div class="srkp-livechat-page-outer">
<h1>SRKP Live Chat</h1>
<div class="wrap srkp-chat-wrapper">

    <!-- USERS LIST -->
    <div id="srkp-user-list">
        <div class="header-with-checkbox">
            <h2>Users</h2>
            <div class="srkp-bulk-select-all-checkbox">
                <?php if ($users) { ?>
               <input type="checkbox"class="update_bulk_checkbox" id="srkp-bulk-select-all-users"/>
               <?php } ?>
               <span class="srkp-bulk-menu-toggle">⋮</span>
            </div>
                <ul class="srkp-bulk-actions-menu">
                    <li class="srkp-bulk-clear-chat"  data-nonce="<?php echo esc_attr(wp_create_nonce('srkp_bulk_clear_chat')); ?>" data-action="clear">Clear Chat</li>
                    <li class="srkp-bulk-delete-user" data-nonce="<?php echo esc_attr(wp_create_nonce('srkp_bulk_delete_user')); ?>" data-action="delete">Delete User</li>
                     <li class="srkp-bulk-read-all-user" data-nonce="<?php echo esc_attr(wp_create_nonce('srkp_bulk_read_all_user')); ?>" data-action="read">Mark as Read</li>
                </ul>
        </div>

        <?php
        if ($users) {
            foreach ($users as $row) {
                if ($row->message_update) {
                    $updated = strtotime($row->message_update);
                    $currenttime = strtotime($current);
                    $diff = $currenttime - $updated;
                    if ($diff > 300) {
                       $this->update_user_status($table ,$row->id);
                    }
                }
                $status = 'Offline';
                if (!empty($row->status)) {
                    if ($row->status === 'active') {
                        $status = 'Online';
                        $status_class = 'srkp-status-online';
                    } else {
                        $status = 'Offline';
                        $status_class = 'srkp-status-offline';
                    }
                }
                $active = '';
                if (isset($_GET['user'], $_GET['_wpnonce'])) {

                    $user_id = intval($_GET['user']); // sanitize user ID
                    $nonce   = sanitize_text_field(wp_unslash($_GET['_wpnonce'])); // sanitize nonce

                    if (wp_verify_nonce($nonce, 'select_user_' . $user_id)) {
                        $active = ($user_id === intval($row->user_id)) ? ' active' : '';
                    }
                }
                $new_message = $row->unread_count ? $row->unread_count : 0;
                $status_class = "srkp-status-offline";
                if ($status === "Online") $status_class = "srkp-status-online";
                elseif (strpos($status, "Last seen") !== false) $status_class = "srkp-status-lastseen";

                $nonce = wp_create_nonce('select_user_' . $row->user_id);
                $user_url = add_query_arg(
                    [
                        'page'    => 'srkp-live-chat',
                        'user'    => $row->user_id,
                        '_wpnonce' => $nonce
                    ],
                    admin_url('admin.php')
                );
        ?>
                <div class="srkp-user-item-outer srkp-user-item<?php echo esc_attr($active); ?>" data-userid="<?php echo esc_html($row->user_id); ?>" data-username="<?php echo esc_html($row->name); ?>">
                    <a href="<?php echo esc_url($user_url); ?>" class="user_detail">
                        <div class="name_status_outer">
                             <input type="checkbox" class="srkp-bulk-user-checkbox" data-userid="<?php echo esc_attr($row->user_id); ?>" />
                            <span class="srkp-user-status <?php echo esc_attr($status_class); ?>"></span><div class="srkp-user-name">
                                <?php echo esc_html($row->name); ?>
                            </div>
                            
                            <?php if ($new_message):
                                $show_bell = empty($active) ? 'block' : 'none';
                            ?>
                                <span class="srkp-unread-bell">
                                    <!-- <i class="fa fa-bell"></i> -->
                                </span>
                                <span class="srkp-unread-count"><?php echo esc_html($new_message); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="srkp-user-actions-dropdown">
                        <span class="srkp-menu-toggle">⋮</span>
                        <ul class="srkp-actions-menu">
                            <li class="srkp-clear-chat" data-userid="<?php echo esc_attr($row->user_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('srkp_clear_chat')); ?>">Clear Chat</li>
                            <li class="srkp-delete-user" data-userid="<?php echo esc_attr($row->user_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('srkp_delete_user')); ?>">Delete User</li>
                        </ul>
                    </div>
                </div>
        <?php
            }
        }else{
            ?>
            <div class="user_not_fount_outer">
           <i class="fa-solid fa-users"></i>
             <h3>No User Found</h3>
            </div>
            <?php  }
        ?>
    </div>

    <!-- CHAT AREA -->
    <div class="srkp-chat-container">
        <div id="srkp-chat-header">Chat</div>
        <div id="srkp-admin-chat">
            <?php
            $user_selected = false;
            if (isset($_GET['user'], $_GET['_wpnonce'])) {

                $user_id = intval($_GET['user']); // sanitize user ID
                $nonce   = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

                if (wp_verify_nonce($nonce, 'select_user_' . $user_id)) {
                    $user_selected = true;
                }
            }
            if (!$user_selected):  ?>
                <div class="srkp-no-chat-selected">
                    <i class="fa fa-comments"></i>
                     <p>No chats are available at the moment.</p>
                </div>
            <?php else: ?>
                <div class="srkp-admin-chat-message"></div>
            <?php endif; ?>
        </div>

        <div class="srkp-chat-input-area">
            <input type="text" id="srkp-admin-input" placeholder="Type reply..." />
            <button id="srkp-admin-send">Send <div id="srkp-loader" class="srkp-loader loader" style="display:none;"></div></button>
        </div>

    </div>
</div>
</div>
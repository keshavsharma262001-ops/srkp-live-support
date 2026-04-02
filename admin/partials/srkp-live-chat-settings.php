<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="srkp_live_wrap">
    <h1>SRKP Live Chat Settings</h1>
    
    <div class="nav-tab-wrapper srkp-tab-wrapper">
          <a href="#tab-settings" class="nav-tab nav-tab-active">Settings</a>
            <a href="#tab-smtp" class="nav-tab ">SMTP</a>
         <a href="#tab-advance" class="nav-tab ">Advance</a>
    </div>

    <form method="post" action="options.php" enctype="multipart/form-data">
        <?php
        wp_nonce_field('srkp_support_icon_action', 'srkp_support_icon_nonce');
        settings_fields('srkp_live_chat_settings_group');
        do_settings_sections('srkp-live-chat-settings');
        ?>
        <div class="form_outer">
        <div id="tab-settings" class="srkp-tab-content active">
            <table class="form-table">
                    <tr valign="middle">
                        <th scope="row">Enable Chat Support</th>
                        <td>
                            <label class="srkp-switch">
                                <input type="checkbox" name="srkp_enable_chat" value="1" <?php $srkp_enable_chat = get_option('srkp_enable_chat', 0);
                                 checked(1, $srkp_enable_chat, true); ?> />
                                <span class="srkp-slider round"></span>
                            </label>
                        </td>
                        
                    </tr>
                    <tr>
                    <td colspan="2" class="show_message_warning"><?php if ( ! get_option('srkp_enable_chat') ) : ?>
                        <div class="notice notice-warning is-dismissible">
                        <p><strong>Chat Support is disabled.</strong> Please enable it to access all live chat features.</p>
                        </div>
                        <?php endif; ?>
                        <div class="notice notice-warning is-dismissible onload"style="display:none">
                        <p><strong>Chat Support is disabled.</strong> Please enable it to access all live chat features.</p>
                        </div>
                    </td>
                    </tr>
                    <!-- Pusher App ID -->
                    <?php $srkp_enable_chat = get_option('srkp_enable_chat', 0); ?>
                    <tr class="srkp_enable_chat_credentials" style="display: <?php echo $srkp_enable_chat ? 'table-row' : 'none'; ?>;">
                    <th scope="row">Timezone</th>
                        <td>
                            <?php  $srkp_saved_tz = get_option('srkp_timezone','Asia/Kolkata'); ?>
                            <select class="srkp_timezone" name="srkp_timezone">
                                <?php foreach (timezone_identifiers_list() as $srkp_tz): ?>
                                    <option value="<?php echo esc_attr($srkp_tz); ?>"
                                        <?php selected($srkp_saved_tz, $srkp_tz); ?>>
                                        <?php echo esc_html($srkp_tz); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="srkp_enable_chat_credentials" style="display: <?php echo $srkp_enable_chat ? 'table-row' : 'none'; ?>;">
                        <th scope="row">Pusher App ID</th>
                        <td>
                            <input type="text" name="srkp_pusher_app_id" value="<?php echo esc_attr(get_option('srkp_pusher_app_id', '')); ?>" style="width:60%;" required>
                        </td>
                    </tr>

                    <tr class="srkp_enable_chat_credentials" style="display: <?php echo $srkp_enable_chat ? 'table-row' : 'none'; ?>;">
                        <th scope="row">Pusher Key</th>
                        <td>
                            <input type="text" name="srkp_pusher_key" value="<?php echo esc_attr(get_option('srkp_pusher_key', '')); ?>" style="width:60%;" required>
                        </td>
                    </tr>

                    <tr class="srkp_enable_chat_credentials" style="display: <?php echo $srkp_enable_chat ? 'table-row' : 'none'; ?>;">
                        <th scope="row">Pusher Secret</th>
                        <td>
                            <input type="text" name="srkp_pusher_secret" value="<?php echo esc_attr(get_option('srkp_pusher_secret', '')); ?>" style="width:60%;" required>
                            <p class="description">Keep this private. Do not share publicly.</p>
                        </td>
                    </tr>

                    <tr class="srkp_enable_chat_credentials" style="display: <?php echo $srkp_enable_chat ? 'table-row' : 'none'; ?>;">
                        <th scope="row">Pusher Cluster</th>
                        <td>
                            <input type="text" name="srkp_pusher_cluster" value="<?php echo esc_attr(get_option('srkp_pusher_cluster', '')); ?>" style="width:60%;" required><br>
                            <a href="https://dashboard.pusher.com/" target="_blank">https://dashboard.pusher.com/</a>
                        </td>
                    </tr>
                </table>
            </div>
                         <!-- SMTP Settings Section -->
                <div id="tab-smtp" class="srkp-tab-content">
                    <table class="form-table">

                   <tr class="genrate_app_password">
                        <th><label for="google_app_password_link">Generate App Password:</label></br>
                        <a id="google_app_password_link" href="https://myaccount.google.com/apppasswords" target="_blank">
                                https://myaccount.google.com/apppasswords
                            </a>
                    </th>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials smtphost">
                        <th scope="row">SMTP Host</th>
                        <td>
                            <input type="text" name="srkp_smtp_host" value="<?php echo esc_attr(get_option('srkp_smtp_host', '')); ?>" style="width:60%;" placeholder="smtp.example.com">
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">SMTP Port</th>
                        <td>
                            <input type="number" name="srkp_smtp_port" value="<?php echo esc_attr(get_option('srkp_smtp_port', 587)); ?>" style="width:60%;" placeholder="587">
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">SMTP Username</th>
                        <td>
                            <input type="text" name="srkp_smtp_username" value="<?php echo esc_attr(get_option('srkp_smtp_username', '')); ?>" style="width:60%;" placeholder="username@example.com">
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">SMTP Password</th>
                        <td>
                            <input type="password" name="srkp_smtp_password" value="<?php echo esc_attr(get_option('srkp_smtp_password', '')); ?>" style="width:60%;" placeholder="password">
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">SMTP Encryption</th>
                        <td>
                            <select name="srkp_smtp_encryption" style="width:60%;">
                                <option value="none" <?php selected(get_option('srkp_smtp_encryption'), 'none'); ?>>None</option>
                                <option value="ssl" <?php selected(get_option('srkp_smtp_encryption'), 'ssl'); ?>>SSL</option>
                                <option value="tls" <?php selected(get_option('srkp_smtp_encryption'), 'tls'); ?>>TLS</option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">From Email</th>
                        <td>
                            <input type="email" name="srkp_smtp_from_email" value="<?php echo esc_attr(get_option('srkp_smtp_from_email', get_bloginfo('admin_email'))); ?>" style="width:60%;" placeholder="from@example.com">
                        </td>
                    </tr>

                    <tr valign="middle" class="srkp_enable_smtp_credentials">
                        <th scope="row">From Name</th>
                        <td>
                            <input type="text" name="srkp_smtp_from_name" value="<?php echo esc_attr(get_option('srkp_smtp_from_name', get_bloginfo('name'))); ?>" style="width:60%;" placeholder="Your Site Name">
                                </br>
                        </td>
                    </tr>
                        <?php if ( ! empty( get_option( 'srkp_smtp_from_email' ) ) ) : ?>
                        <tr valign="middle" class="srkp_enable_smtp_credentials">
                            <th scope="row"><?php esc_html_e( 'Test Email', 'srkp-live-support' ); ?></th>
                            <td>
                                <button type="button" class="button button-primary" id="srkp_smtp_test_btn">
                                    <?php esc_html_e( 'Send Test Email', 'srkp-live-support' ); ?>
                                </button>
                                <p id="srkp_smtp_test_result"></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
                <div id="tab-advance" class="srkp-tab-content">
                <table class="form-table">
                    <!-- Upload Support Icon -->
                    <tr valign="middle">
                        <th scope="row">Support Icon</th>
                     <td class="srkp_support_icon_upload">
                        <!-- Upload Wrapper -->
                          <?php $srkp_icon = get_option('srkp_support_icon'); 
                          $srkp_imagename = explode('/', $srkp_icon);
                            $srkp_imagename = end($srkp_imagename); 
                          ?>
                        <div class="srkp-upload-wrapper">
                            <label class="srkp-upload-box">
                               <div class=" srkp-upload-box-content">
                                <h2>Drop files to upload</h2>
                                <p>or</p>
                                </div>
                                <span class="srkp-upload-btn">Select Files</span>
                                <input type="file" name="srkp_support_icon_file" id="srkp_upload_input" accept="image/jpg, image/png, image/jpeg, image/webp, image/gif ">
                                <p>Maximum upload file size: 10 MB.</p>
                            </label>
                        </div>
                        <!-- Hidden field storing image URL -->
                       
                       <input type="hidden" id="srkp_support_icon" name="srkp_support_icon" value="<?php echo esc_attr($srkp_icon); ?>">
                        <!-- Preview Wrapper -->

                        <div class="srkp_support_icon_preview_wrapper">
                            <?php if(!empty($srkp_icon)):?>
                            <img id="srkp_support_icon_preview"
                                src="<?php echo $srkp_icon ? esc_url($srkp_icon) : "" ?>"
                                data-default="" alt="Icon Preview">
                                <i id="srkp_support_icon_font"class="fa-regular fa-message" style="display:none"></i>  
                            <?php else: ?>
                            <img id="srkp_support_icon_preview"src=""alt="Icon Preview"style="display:none;">
                            <i id="srkp_support_icon_font"class="fa-regular fa-message"></i>                                
                            <?php endif; ?>
                            <span class="srkp-remove-icon"
                                style="display: <?php echo $srkp_icon ? 'inline-block' : 'none'; ?>;">&times;</span>
                        </div>
                        <br><br>
                    </td>
                    </tr>

                    <!-- Chat Widget Text -->
                    <tr valign="middle">
                        <th scope="row">Widget Text</th>
                        <td>
                            <input type="text" name="srkp_widget_text" value="<?php echo esc_attr(get_option('srkp_widget_text', 'SRKP Live Support')); ?>" class="input_style">
                        </td>
                    </tr>

                    <tr class="srkp_color_settings_section">
                        <th>
                            <div class="srkp-switch-label-outer">
                                <span>Enable only for header</span> <label class="srkp-switch">
                                    <input type="checkbox"
                                        name="srkp_chatbox_header_only"
                                        value="1"
                                        <?php checked(1, get_option('srkp_chatbox_header_only', '#000000')); ?>>
                                    <span class="srkp-slider"></span>
                                </label>
                            </div>
                        </th>
                    </tr>
                    <tr valign="middle" class="srkp_color_settings_section_wrapper">
                        <th scope="row"> 
                            Chat Header/Footer Background Color</th>
                            <td class="chat_box_header_footer_background">
                            <input type="color"name="srkp_chatbox_header_bg_color"value="<?php echo esc_attr(get_option('srkp_chatbox_header_bg_color', '#ffffff')); ?>">
                            <input type="text" id="srkp_chatbox_header_bg_color_text" name="srkp_chatbox_header_bg_color" value="<?php echo esc_attr(get_option('srkp_chatbox_header_bg_color', '#ffffff')); ?>"class="input_color_style">
                        </td>
                    </tr>


                    <tr valign="middle">
                        <th scope="row">Chat Header/Footer Text Color</th>
                        <td class="color_picker_wrapper">
                            <input type="color" name="srkp_chatbox_header_color" value="<?php echo esc_attr(get_option('srkp_chatbox_header_color', '#000000')); ?>">
                            <input type="text" id="srkp_chatbox_header_color_text" name="srkp_chatbox_header_color" value="<?php echo esc_attr(get_option('srkp_chatbox_header_color', '#000000')); ?>"class="input_color_style">
                            
                        </td>
                    </tr>


                    <!-- Button Background Color -->
                    <tr valign="middle">
                        <th scope="row">Button Background Color</th>
                        <td class="color_picker_wrapper">
                            <input type="color" id= "srkp_btn_bg_color" name="srkp_btn_bg_color" value="<?php echo esc_attr(get_option('srkp_btn_bg_color', '#ff0000')); ?>">
                            <input type="text" id="srkp_btn_bg_color_text" name="srkp_btn_bg_color" value="<?php echo esc_attr(get_option('srkp_btn_bg_color', '#ff0000')); ?>"class="input_color_style">
                        </td>
                    </tr>

                    <!-- Button Text Color -->
                    <tr valign="middle">
                        <th scope="row">Button Text Color</th>
                        <td class="color_picker_wrapper">
                            <input type="color" name="srkp_btn_txt_color" value="<?php echo esc_attr(get_option('srkp_btn_txt_color', '#ffffff')); ?>">
                             <input type="text" id="srkp_btn_txt_color_text" name="srkp_btn_txt_color" value="<?php echo esc_attr(get_option('srkp_btn_txt_color', '#ffffff')); ?>"class="input_color_style">
                        </td>
                    </tr>

                    <!-- Chatbox Background Color -->
                    <tr valign="middle">
                        <th scope="row">Chatbox Background Color</th>
                        <td class="color_picker_wrapper">
                            <input type="color" name="srkp_chatbox_bg_color" value="<?php echo esc_attr(get_option('srkp_chatbox_bg_color', '#ffffff')); ?>">
                             <input type="text" id="srkp_chatbox_bg_color_text" name="srkp_chatbox_bg_color" value="<?php echo esc_attr(get_option('srkp_chatbox_bg_color', '#ffffff')); ?>"class="input_color_style">
                        </td>
                    </tr>

                    <!-- Chatbox Text Color -->
                    <tr valign="middle">
                        <th scope="row">Chatbox Text Color</th>
                        <td class="color_picker_wrapper">
                            <input type="color" name="srkp_chatbox_txt_color" value="<?php echo esc_attr(get_option('srkp_chatbox_txt_color', '#000000')); ?>">
                                <input type="text" id="srkp_chatbox_txt_color_text" name="srkp_chatbox_txt_color" value="<?php echo esc_attr(get_option('srkp_chatbox_txt_color', '#000000')); ?>"class="input_color_style">
                        </td>
                    </tr>
                </div>
            </table>
        </div>
        <?php submit_button(); ?>
    </form>
    
</div>

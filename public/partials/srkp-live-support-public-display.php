<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-live-support
 * @since      1.0.0
 *
 * @package    Srkp_Live_Support
 * @subpackage Srkp_Live_Support/public/partials
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Get chat settings
$srkp_chat_icon               = esc_url( get_option( 'srkp_support_icon', '' ) );
$srkp_chat_widget_txt         = esc_html( get_option( 'srkp_widget_text', 'SRKP Live Support' ) );
$srkp_btn_bg_color            = esc_attr( get_option( 'srkp_btn_bg_color', '#ff0000' ) );
$srkp_btn_txt_color           = esc_attr( get_option( 'srkp_btn_txt_color', '#fff' ) );
$srkp_chatbox_bg              = esc_attr( get_option( 'srkp_chatbox_bg_color', '#f5f5f5' ) );
$srkp_chatbox_txt             = esc_attr( get_option( 'srkp_chatbox_txt_color', '#000' ) );
$srkp_header_bg_color         = esc_attr( get_option( 'srkp_chatbox_header_bg_color', '#fffff' ) );
$srkp_header_color            = esc_attr( get_option( 'srkp_chatbox_header_color', '#000' ) );
$srkp_enable_chat             = get_option( 'srkp_enable_chat', 0 );
$srkp_chatbox_header_only     = get_option( 'srkp_chatbox_header_only', 0 );
$srkp_pusher_app_id           = get_option( 'srkp_pusher_app_id', '' );
$srkp_guest_context           = $this->get_guest_context();
$srkp_session_exists          = ! empty( $srkp_guest_context['guest_id'] );
?>
<?php if ( $srkp_enable_chat && $srkp_pusher_app_id ) : ?>
    <div id="chatPage" class="chat_page">
        <div class="chat_button" style="background-color: <?php echo esc_attr( $srkp_btn_bg_color ); ?>; color: <?php echo esc_attr( $srkp_btn_txt_color ); ?>;">
            <?php if ( $srkp_chat_icon ) : ?>
                <img src="<?php echo esc_url( $srkp_chat_icon ); ?>" alt="Site Logo" class="chat_logo" />
            <?php else: ?>
                <i id="srkp_support_icon_font"class="fa-regular fa-message chat_logo"></i>  
            <?php endif; ?>
            <span class="chat_close_icon" style="display:none;">X</span>
        </div>

        <div id="srkp-live-chat">
            <div id="srkp-chat-header" style="background-color: <?php echo esc_attr( $srkp_header_bg_color ); ?>; color: <?php echo esc_attr( $srkp_header_color ); ?>;">
                <!-- New clean SVG arrows -->
                <?php if ( ! is_user_logged_in() && ! $srkp_session_exists ) : ?>
                    <a href="javascript:void(0);" class="srkp-arrow-svg backToEmail">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M15 6l-6 6 6 6" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </a>
                <?php endif; ?>
                <?php echo esc_html( $srkp_chat_widget_txt ); ?>
                <?php if ( ! is_user_logged_in() && ! $srkp_session_exists ) : ?>
                    <a href="javascript:void(0);" class="srkp-arrow-svg forwardToOtp">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M9 6l6 6-6 6" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <?php if (! is_user_logged_in()): ?>
                <div class="guest_user_login_form" style="display:none;">
                    <form id="srkp-guest-email-form" style="padding:15px;">
                        <div id="srkp-email-box" style="padding:15px;">
                            <div class="guest_login_field_outer">
                                <h2 class="guest_form_heading">Enter your email to start chat</h2>
                                <div class="input_outer">
                                    <input type="email" name="srkp_guest_email" id="srkp-guest-email" placeholder="Enter your email" style="width: 100%;padding: 11px 0;text-align: center;border-radius: 6px;" />
                                    <button id="srkp-save-email" type="submit">Continue
                                        <div id="srkp-loader" style="display:none;">
                                            <span class="spinner"></span>
                                        </div>
                                    </button>
                                </div>
                                <p id="srkp-email-error" style="color:red; display:none; margin-top:5px;">
                                    Please enter a valid email.
                                </p>
                                <input type="hidden" name="srkp_guest_email_nonce" value="<?php echo esc_attr($email_nonce); ?>">
                            </div>
                        </div>
                    </form>
                    <form id="srkp-guest-otp-form" style="display:none; padding:15px;">
                        <div id="srkp-email-box" style="padding:15px;">
                            <div class="guest_login_field_outer">
                                <h2 class="guest_form_heading">Enter OTP</h2>
                                <div class="input_outer">
                                    <input type="number" id="srkp-otp-input" name="srkp-otp-input" maxlength="6" placeholder="Enter OTP" style="width: 100%;padding: 11px 0;text-align: center;border-radius: 6px;" />
                                    <button id="srkp-verify-otp" type="submit">Verify
                                        <div id="srkp-otp-loader" style="display:none;">
                                            <span class="spinner"></span>
                                        </div>
                                    </button>
                                    <span class="resendOtpBtn outer"> Didn’t get the code? <a href="javascript:void(0);" id="resendOtpBtn">Resend</a></span>
                                    <span id="countdown" style="margin-left: 10px; font-weight: bold;"></span>
                                </div>
                                <input type="hidden" name="srkp_otp_nonce" value="<?php echo esc_attr($otp_nonce); ?>">
                            </div>
                            <p id="srkp-otp-error" style="color:red; display:none; margin-top:5px;">
                                Please enter a valid OTP.
                            </p>
                        </div>
                    </form>
                </div>
            <?php endif; 
            ?>
            <div id="srkp-chat-messages" style="background-color: <?php echo esc_attr( $srkp_chatbox_bg ); ?>; color: <?php echo esc_attr( $srkp_chatbox_txt ); ?>;"></div>
            <div class="srkp-chat-send_input" style="background-color: <?php echo $srkp_chatbox_header_only === '0' ? esc_attr( $srkp_header_bg_color ) : ''; ?>; color: <?php echo $srkp_chatbox_header_only ? esc_attr( $srkp_header_color ) : ''; ?>;">
                <input type="text" id="srkp-chat-input" placeholder="Type your message..." />
                <button id="srkp-chat-send" style="background-color: <?php echo esc_attr( $srkp_btn_bg_color ); ?>; color: <?php echo esc_attr( $srkp_btn_txt_color ); ?>;">Send
                    <div id="srkp-loader-send-button" style="display:none;">
                        <span class="spinner"></span>
                    </div>
                </button>
            </div>
        </div>
    </div>

<?php endif; ?>

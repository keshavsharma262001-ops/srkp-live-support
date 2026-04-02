(function( $ ) {
	'use strict';
	function initializeChat(userid) {
		var pusher = new Pusher(srkp_chat_ajax.pusher_key, {
			cluster: srkp_chat_ajax.pusher_cluster,
			forceTLS: true
		});
		var channel = pusher.subscribe('live-chat-' + userid);
		channel.bind('new-message', function(data){
			appendMessage(data);
		});
	}
	var $messages = $('#srkp-chat-messages');
	var  input = $('#srkp-chat-input');
	var userid= srkp_chat_ajax.user_id; 
	var isGuest = (!userid || userid == 0);
	var lastMessageDate = '';

	function appendMessage(msg) {
		var cls = msg.sender === 'user' ? 'srkp-msg-user' : 'srkp-msg-admin';
		var timeText = '';
		var dateSeparator = '';
		if (msg.time) {
			var date = new Date(msg.time);
			// ---------- FORMAT TIME ----------
			var hours = date.getHours();
			var minutes = date.getMinutes();
			var ampm = hours >= 12 ? 'PM' : 'AM';
			hours = hours % 12;
			hours = hours ? hours : 12;
			minutes = minutes < 10 ? '0' + minutes : minutes;
			timeText = hours + ':' + minutes + ' ' + ampm;

			// ---------- FORMAT DATE ----------
			var options = { day: '2-digit', month: 'short', year: 'numeric' };
			var formattedDate = date.toLocaleDateString('en-US', options);

			// ---------- CHECK TODAY / YESTERDAY ----------
			var today = new Date();
			var yesterday = new Date();
			yesterday.setDate(today.getDate() - 1);

			var todayStr = today.toLocaleDateString('en-US', options);
			var yesterdayStr = yesterday.toLocaleDateString('en-US', options);

			var displayDate = formattedDate;

			if (formattedDate === todayStr) {
				displayDate = "Today";
			} else if (formattedDate === yesterdayStr) {
				displayDate = "Yesterday";
			}

			// ---------- INSERT DATE SEPARATOR ----------
			if (lastMessageDate !== formattedDate) {
				dateSeparator = `<div class="srkp-date-separator">${displayDate}</div>`;
				lastMessageDate = formattedDate;
			}
		}
			// Append date separator + message
			$('#srkp-chat-messages').append(
				dateSeparator +
				`<div class="${cls}">
					${msg.message}
					<div class="srkp-msg-time">${timeText}</div>
				</div>`
			);
		
			// Scroll to bottom
			$('#srkp-chat-messages').scrollTop($('#srkp-chat-messages')[0].scrollHeight);
		}
		
		// Load previous messages
		function loadMessages(){
			$.post(srkp_chat_ajax.ajax_url, {action: 'srkp_chat_get', nonce: srkp_chat_ajax.nonce_get_chat}, function(res){
				if(res.success){
					$messages.html('');
					res.data.forEach(function(msg){
						appendMessage(msg);
					});
				}
			});
		}
		// loadMessages(); 
		
		// Send message
		function sendMessage() {
			var message =  input.val().trim();
			if (message === '') return;
			 input.val('');
			// $('#srkp-loader-send-button').show();
			$.post(
				srkp_chat_ajax.ajax_url,
				{
					action: 'srkp_chat_send',
					message: message,
					nonce: srkp_chat_ajax.nonce
				},
				function(res) {
					if (res.success) {
						// $('#srkp-loader-send-button').hide();
						var user_id = res.data.user_id;
						if (res.data.register === true) {
							initializeChat(user_id);
						}
						loadMessages();
					}
				}
			)
			// .fail(function () {
			// 	// $('#srkp-loader-send-button').hide();
			// 	 input.val(message);
			// 	alert('Failed to send message');
			// });
		}
		
	
		$('#srkp-chat-send').click(sendMessage);
	
		 input.keypress(function(e){
			if(e.which === 13){ sendMessage(); return false; }
		});
		$(document).on('click', function () {
			$('#srkp-live-chat').hide();
			$('.chat_logo').show();
			$('.chat_close_icon').hide();
		});
		$('#chatPage').on('click', function (e) {
			e.stopPropagation();
		});
		function toggleChatBox() {
			if(userid){
				initializeChat(userid)
				loadMessages()
			}
			$('#srkp-live-chat').toggle(); 
			$('.chat_logo').toggle();     
			$('.chat_close_icon').toggle()
		}
		$('.chat_page .chat_button').click(toggleChatBox)
		if (isGuest) {
			$('#srkp-chat-messages').hide();
			$('.srkp-chat-send_input').hide();
			$('.guest_user_login_form').show();
		}

		function toggleForms(showEmailForm) {
			if (showEmailForm) {
				$('#srkp-guest-email-form').show();
				$('#srkp-guest-otp-form').hide();
				$('.forwardToOtp').addClass('active');
				$('.backToEmail').removeClass('active');
			} else {
				$('#srkp-guest-email-form').hide();
				$('#srkp-guest-otp-form').show();
				$('.forwardToOtp').removeClass('active');
				$('.backToEmail').addClass('active');
			}
		}
		function get_otp_byemail() {
			var email = $('#srkp-guest-email').val();
			if (!email) {
				$('#srkp-email-error').show();
				return;
			}
			var nonce = $('input[name="srkp_guest_email_nonce"]').val();
			$('#srkp-loader').show();
			startCountdown(60);
			$('#resendOtpBtn').off('click').on('click', get_otp_byemail);
			$('.backToEmail').addClass('active');
			$.post(srkp_chat_ajax.ajax_url, {
				action: 'srkp_save_guest_email',
				srkp_guest_email: email,
				srkp_guest_email_nonce: nonce
			}, function (res) {
				$('#srkp-loader').hide();
				if (res.success) {
					toggleForms(false); 
				} else {
					$('#srkp-email-error').text(res.data.message).show();
				}
			});
		}
		
		// Toggle Buttons — only bind once
		$('.backToEmail').on('click', function () {
			if ($(this).hasClass('active')) {
				toggleForms(true);
			}
		});
		$('.forwardToOtp').on('click', function () {
			if ($(this).hasClass('active')) {
				toggleForms(false);
			}
		});
		
		// Form Submit
		$('#srkp-guest-email-form').on('submit', function (e) {
			e.preventDefault();
			get_otp_byemail();
		});
		
		$('#srkp-guest-otp-form').on('submit', function(e){
			e.preventDefault();	
			var email = $('#srkp-guest-email').val();
			var nonce = $('input[name="srkp_otp_nonce"]').val();
			var otp = $('input[name="srkp-otp-input"]').val();
			$('#srkp-otp-loader').show();
			$.post(srkp_chat_ajax.ajax_url, {
				action: 'srkp_save_guest_email',
				srkp_guest_email: email,
				srkp_otp_input:otp,
				srkp_otp_nonce: nonce
				
			}, function(res){
				$('#srkp-otp-loader').hide();
				if(res.success){
					var user_id=res.data.srkp_guest_id
					initializeChat(user_id);
					loadMessages();
					$('#srkp-chat-messages').show();
					$('.srkp-chat-send_input').show();
					$('.guest_user_login_form').hide();
					$('.guest_user_login_form').remove();
					$('.backToEmail , .forwardToOtp').remove();
				} else {
					$('#srkp-otp-error').html(res.data.message).show();
				}
			});
		});		
		// count down show when user 
		function startCountdown(seconds = 60) {
			const btn = document.getElementById('resendOtpBtn');
			const timer = document.getElementById('countdown');
		
			btn.style.pointerEvents = 'none';
			btn.style.opacity = '0.5';
		
			let timeLeft = seconds;
			timer.textContent = `(${timeLeft}s)`;
		
			const interval = setInterval(() => {
				timeLeft--;
				timer.textContent = `(${timeLeft}s)`;
		
				if (timeLeft <= 0) {
					clearInterval(interval);
					timer.textContent = "";
					btn.style.pointerEvents = 'auto';
					btn.style.opacity = '1';
				}
			}, 1000);
		}
})( jQuery );

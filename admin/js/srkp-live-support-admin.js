(function( $ ) {
	'use strict';

		var selectedUser = 0;
		var currentChannel = null;
		var pusher = new Pusher(srkp_chat_admin.pusher_key, {
			cluster: srkp_chat_admin.pusher_cluster,
			forceTLS: true
		});
		function subscribeToChannel(user_id) {
			if (currentChannel) {
				pusher.unsubscribe(currentChannel);
			}
			currentChannel = "live-chat-" + user_id;
			var channel = pusher.subscribe(currentChannel);
			channel.bind("new-message", function (data) {
				if (selectedUser == data.user_id) {
					appendMessage(data);
				} else if (data.sender === "user") {
					let item = $('.srkp-user-item[data-userid="' + data.user_id + '"]');
					item.addClass("incoming-highlight");
					setTimeout(() => item.removeClass("incoming-highlight"), 2000);
				}
			});
		}

		var lastMessageDate = ""; 
		function appendMessage(msg) {
			var cls = (msg.sender == 'user') ? 'srkp-msg-user' : 'srkp-msg-admin';
			var timeText = '';
			var dateSeparator = '';
		
			if (msg.time) {
				var d = new Date(msg.time);
				var hrs = d.getHours();
				var mins = d.getMinutes();
				var ampm = hrs >= 12 ? 'PM' : 'AM';
				hrs = hrs % 12 || 12;
				mins = mins < 10 ? '0' + mins : mins;
				timeText = hrs + ':' + mins + ' ' + ampm;
				var today = new Date();
				var yesterday = new Date();
				yesterday.setDate(today.getDate() - 1);
				var msgDateString = d.toDateString();
				var todayString = today.toDateString();
				var yesterdayString = yesterday.toDateString();
				let finalDateLabel = "";
				if (msgDateString === todayString) {
					finalDateLabel = "Today";
				} else if (msgDateString === yesterdayString) {
					finalDateLabel = "Yesterday";
				} else {
					var options = { day: '2-digit', month: 'short', year: 'numeric' };
					finalDateLabel = d.toLocaleDateString('en-US', options);
				}
				if (lastMessageDate !== msgDateString) {
					dateSeparator = `<div class="srkp-date-separator">${finalDateLabel}</div>`;
					lastMessageDate = msgDateString;
				}
			}
		
			$("#srkp-admin-chat").append(
				dateSeparator +
				`<div class="${cls}">
					${msg.message}
					<div class="srkp-msg-time">${timeText}</div>
				</div>`
			);
		
			$("#srkp-admin-chat").scrollTop($("#srkp-admin-chat")[0].scrollHeight);
		}
		
		//   SEND ADMIN MESSAGE
		// -----------------------------
		function sendAdminMessage() {
			var message = $('#srkp-admin-input').val().trim();
			if (!message || selectedUser == 0) return;
			$('#srkp-admin-input').val('');
			// $('#srkp-loader').show();
			$.post(srkp_chat_admin.ajax_url, {
				action: 'srkp_chat_send_admin_user',
				user_id: selectedUser,
				message: message,
				nonce: srkp_chat_admin.nonce
			}, function (res) {
				// $('#srkp-loader').hide(); 
				if (res.success) {
					$('#srkp-admin-input').val('');
				}
			});
		}
	
		$('#srkp-admin-send').click(sendAdminMessage);
		$('#srkp-admin-input').keypress(function (e) {
			if (e.which == 13) {
				sendAdminMessage();
			}
		});
		// ------------------------------------
		//   AUTO-LOAD USER FROM URL
		// ------------------------------------
		let urlParams = new URLSearchParams(window.location.search);
		if (urlParams.has("user")) {
			let uid = urlParams.get("user");
			let userItem = $('.srkp-user-item[data-userid="' + uid + '"]');
			if (userItem.length) {
				$('.srkp-user-item').removeClass('active');
				$(userItem).addClass('active');
				$('.srkp-user-item.active .srkp-unread-count').hide();
				selectedUser = $(userItem).data('userid');
				$('#srkp-chat-header').text($(userItem).find('.srkp-user-name').text().trim())
				$('#srkp-admin-chat').html('');
				$('.srkp-chat-input-area').css('display', 'flex');
				$.post(srkp_chat_admin.ajax_url, {
					action: 'srkp_chat_get_user',
					user_id: selectedUser,
					nonce: srkp_chat_admin.nonce
				}, function (res) {
					if (res.success) {
						$.each(res.data, function (i, msg) {
							appendMessage(msg);
						});
					}
				});
				subscribeToChannel(selectedUser);
			}
		}else{
			let firstUser = $('.srkp-user-item').first();
			if (firstUser.length) {
				let userId = firstUser.data('userid');
				let userUrl = firstUser.find("a.user_detail").attr("href");
				window.location.href = userUrl;
			}			
		}

	// jquery for srkp live support setting page 
		jQuery(document).ready(function($){
			$('input[name="srkp_enable_chat"]').on('change', function(){
				if ($(this).is(':checked')) {
					$('.srkp_enable_chat_credentials').fadeIn();
					$('.srkp_enable_chat_credentials td input').prop('required', true);
					$('.notice.notice-warning.is-dismissible').hide()
				} else {
					$('.srkp_enable_chat_credentials').fadeOut();
					 $('.srkp_enable_chat_credentials td input').prop('required', false);
					 $('.notice.notice-warning.is-dismissible.onload').show()
				}
			});
			$(document).on('click', '.srkp-menu-toggle', function(e){
				e.stopPropagation();
				$(this).siblings('.srkp-actions-menu').toggle();
			});
		
			// Close dropdown when clicking outside
			$(document).click(function(){
				$('.srkp-actions-menu').hide();
			});
		
			// Handle actions
			$(document).on('click', '.srkp-clear-chat', function(){
				if(confirm('Are you sure you want to Delete chat?')){
						var userId = $(this).data('userid');
						var nonce = $(this).data('nonce');
						$.post(srkp_chat_admin.ajax_url, {
							action: 'srkp_clear_chat',
							user_id: userId,
							nonce: nonce
						}, function(response){
							location.reload();
						});
				}
			});
		
			$(document).on('click', '.srkp-delete-user', function(){
				var userId = $(this).data('userid');
				if(confirm('Do you really want to remove this user?')){
					var userId = $(this).data('userid');
					var nonce = $(this).data('nonce');
					$.post(srkp_chat_admin.ajax_url, {
						action: 'srkp_delete_user',
						user_id: userId,
						nonce: nonce
					}, function(response){
						if(response.success){
							location.reload();
							let firstUser = $('.srkp-user-item').first();
							if (firstUser.length) {
								let userId = firstUser.data('userid');
								let userUrl = firstUser.find("a.user_detail").attr("href");
								window.location.href = userUrl;
							}		
						}
					});

				}
			});

			$('.srkp-remove-icon').on('click', function() {
				var $wrapper = $(this).closest('.srkp_support_icon_preview_wrapper');
				var $img = $wrapper.find('#srkp_support_icon_preview');
				var defaultSrc = $img.data('default');
				$img.attr('src', defaultSrc);
				$('#srkp_support_icon').val('');
				$('.srkp-remove-icon').hide();
			});
		});

		$("#srkp_smtp_test_btn").click(function () {
			$("#srkp_smtp_test_result").text("Testing SMTP...");
			$.post(ajaxurl, {
				action: "srkp_test_smtp"
			}, function (response) {
				$("#srkp_smtp_test_result").html(response.data);
			});
		});

		jQuery(document).ready(function($){
			$('input[type="color"]').each(function () {
				let colorPicker = $(this);
				let name = colorPicker.attr('name'); 
				let textField = $('#' + name + '_text'); 
				colorPicker.on('input', function () {
					textField.val($(this).val());
				});
				textField.on('input', function () {
					let val = $(this).val();
					let isHex = /^#([0-9A-F]{3}){1,2}$/i.test(val);
					if (isHex) {
						colorPicker.val(val);
					}
				});
			});
		});

jQuery(document).ready(function ($) {

    const fileInput = $("#srkp_upload_input");
    const previewImg = $("#srkp_support_icon_preview");
    const hiddenInput = $("#srkp_support_icon"); 
    const removeBtn = $(".srkp-remove-icon");
    const defaultImg = previewImg.data("default");
    const dropArea = $(".srkp-upload-box");
	const uploadText = $(".srkp-upload-text");
	const maxSize = 10 * 1024 * 1024; // 10 MB
    // Drag & drop events
    dropArea.on("dragover", function (e) {
        e.preventDefault();
        dropArea.addClass("dragover-border");
    });
    dropArea.on("dragleave", function (e) {
        dropArea.removeClass("dragover-border");
    });
    dropArea.on("drop", function (e) {
        e.preventDefault();
        dropArea.removeClass("dragover-border");
        let files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
			if (files[0].size > maxSize) {
                alert("File too large! Max size allowed is 10 MB.");
                fileInput.val("");
                uploadText.text("No file chosen");
                return;	
            }
            fileInput[0].files = files;  
            handleFile(files[0]);
        }
    });

    // File input change
    fileInput.on("change", function () {
        if (this.files.length > 0) {
			if (this.files[0].size > maxSize) {
                alert("File too large! Max size allowed is 10 MB.");
                fileInput.val("");
                uploadText.text("No file chosen");
                return;	
            }
            handleFile(this.files[0]);
        }
    });

    // Remove button click
    removeBtn.on("click", function () {
        previewImg.hide();
        $("#srkp_support_icon_font").show();
        hiddenInput.val("");
        fileInput.val(""); 
        $(this).hide();
		uploadText.text("No file chosen");
    });

    function handleFile(file) {
        let reader = new FileReader();
        reader.onload = function (e) {
            previewImg.attr("src", e.target.result).show();
            $("#srkp_support_icon_font").hide();
            hiddenInput.val(""); 
            removeBtn.show();
			uploadText.text(file.name)
        };
        reader.readAsDataURL(file);
    }
});
jQuery(document).ready(function () {
    $(".srkp-tab-wrapper .nav-tab").on("click", function (e) {
        e.preventDefault();
        $(".nav-tab").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        $(".srkp-tab-content").removeClass("active");
        let target = $(this).attr("href");
        $(target).addClass("active");
    });
	$(".srkp-bulk-menu-toggle").on("click", function (e) {
        e.stopPropagation(); 
        $("#srkp-user-list .srkp-bulk-actions-menu").toggle();
    });

	
	function toggleBulkClearMode(activeitem, nonce, messsage){ 
		$(".srkp-bulk-actions-menu").hide();
		$(".checkbox_update_option_outer").show();
		var allUserIds = [];
		$(".srkp-bulk-user-checkbox:checked").each(function() {
			allUserIds.push($(this).data("userid"));
		});
		if(activeitem){
			$.post(srkp_chat_admin.ajax_url, {
				action: 'srkp_bulk_user_action',
				user_ids: allUserIds,
				bulk_action: activeitem,
				nonce: nonce
			}, function (res) {
				if (res.success) {
					location.reload();
				} else {
					alert("An error occurred while performing the bulk action.");
				}
			});
		}
	}

// Click handler
$("#srkp-user-list .srkp-bulk-actions-menu li").on("click", function(e) {
    e.stopPropagation();
    var $this = $(this);
    $this.siblings().removeClass("active");
    $this.addClass("active");
	var action = $this.data("action");
	if ($(".srkp-bulk-user-checkbox:checked").length > 0) {
		if (confirm("Are you sure you want to " + $this.text() + "?")) {
            toggleBulkClearMode(action , $this.data("nonce"), $this.text());
        }
	} else {
		alert("Please select at least one user.");
	}
});

});
$(document).on("click", function (e) {
	if (!$(e.target).closest(".srkp-bulk-actions-menu").length &&
		!$(e.target).closest(".srkp-bulk-menu-toggle").length) {
		$(".srkp-bulk-actions-menu").hide();
	}
});

$("#srkp-bulk-select-all-users").change(function() {
	var isChecked = $(this).is(":checked");
	if(isChecked){
	$("#srkp-user-list .srkp-bulk-user-checkbox").prop("checked", isChecked).show();
	}else{
	$("#srkp-user-list .srkp-bulk-user-checkbox").prop("checked", isChecked).hide();
	}
});


})( jQuery );

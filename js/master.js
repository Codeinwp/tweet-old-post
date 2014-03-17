jQuery(document).ready(function(){

	jQuery("#cwp_top_currenturl").val(document.location.href);

	jQuery(".cwp_top_wrapper").append("<div class='ajaxAnimation'></div>")

	// Update Options Event
	jQuery(".cwp_top_wrapper a.update-options").click(function(e){
		e.preventDefault();
		cwpTopUpdateForm();
		return false;
	});

	// Reset Options Event
	jQuery(".cwp_top_wrapper a.reset-settings").click(function(e) {
		e.preventDefault();
		startAjaxIntro();

		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: 'reset_options'
			},
			success: function(response) {
				console.log("Success: " + response);
				jQuery("#cwp_top_form").cwpTopResetForm();
				endAjaxIntro();
			},
			error: function(response) {
				console.log("Error: "+ response);
			}
		});

		endAjaxIntro();
		return false;
	});

	function cwpTopUpdateForm() {
		startAjaxIntro();
		var data = jQuery("#cwp_top_form").serialize();

		var formData = {
			action:'updateAllOptions',
			dataSent:data
		}

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "update_response",
				dataSent: formData
			},
			success: function(response) {
				console.log(response);
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		});

		endAjaxIntro();
		return false; 
	}


	// Add New Twitter Account
	jQuery("#cwp_top_form button#twitter-login").click(function(e){
		e.preventDefault();
		startAjaxIntro();
		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "add_new_twitter_account",
				currentURL: jQuery("#cwp_top_currenturl").val()
			},
			success: function(response) {
				window.location.href = response;
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: " + errorThrown);
			}
		});
		return false;
	});

	// Log Out Twitter User
	jQuery("#cwp_top_form .logout_user").click(function(e){
		e.preventDefault();
		startAjaxIntro();

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "log_out_twitter_user"
			},
			success: function(response) {
				console.log(response);
				window.location.href = jQuery("#cwp_top_currenturl").val();
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		});

		endAjaxIntro();

	});

	// Tweet Now
	jQuery("#cwp_top_form a.tweet-now").click(function(e){
		e.preventDefault();
		startAjaxIntro();
		cwpTopUpdateForm();

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "tweet_old_post_action"
			},
			success: function(response) {
				if(response !== '') {
					jQuery('.cwp_top_wrapper').append(response);
				}
				location.reload();	
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		});

		endAjaxIntro();
	});

	// Stop Tweet Old Post
	jQuery("#cwp_top_form a.stop-tweet-old-post").click(function(e){
		e.preventDefault();
		startAjaxIntro();
		cwpTopUpdateForm();

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "stop_tweet_old_post"
			},
			success: function(response) {
				if(response !== '') {
					jQuery('.cwp_top_wrapper').append(response);
				} 
				location.reload();	
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		});
		endAjaxIntro();
		//location.reload();
	});

	var nextTweetDate = jQuery(".cwp_top_container .nextTweet").html();
	jQuery(".cwp_top_container .nextTweet").html('');
	jQuery(".cwp_top_container .nextTweet").countdown({
		date: nextTweetDate
	});

	function startAjaxIntro() {
		jQuery(".cwp_top_wrapper .ajaxAnimation").fadeIn();
	}

	function endAjaxIntro() {
		jQuery(".cwp_top_wrapper .ajaxAnimation").fadeOut();
	}

	jQuery.fn.cwpTopResetForm = function() {
		jQuery(this).find("input[type=text], textarea").val("");
		jQuery(this).find("input[type=radio], input[type=checkbox]").checked = false;
	}

	jQuery("button.select-all").click(function(e){
		e.preventDefault();
		if(jQuery(this).hasClass('active')) {
			jQuery(this).removeClass('active').text('Select All');
			jQuery(this).parent().parent().find('.right input[type=checkbox]').attr('checked', false);
		} else { 
			jQuery(this).addClass('active').text('Deselect All');
			jQuery(this).parent().parent().find('.right input[type=checkbox]').attr('checked', true);
		}		
	});

	
});	
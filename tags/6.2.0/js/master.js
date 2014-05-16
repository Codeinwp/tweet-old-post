jQuery(document).ready(function(){

	hideSpecifiedFieldsets();

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
		//cwpTopUpdateForm();

		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: 'reset_options'
			},
			success: function(response) {
				console.log("Success: " + response);
				//jQuery("#cwp_top_form").cwpTopUpdateForm();
				location.reload();
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
		//console.log(data);

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

		function cwpTopUpdateFormWithoIntro() {
		//startAjaxIntro();
		var data = jQuery("#cwp_top_form").serialize();
		//console.log(data);

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

		//endAjaxIntro();
		return false; 
	}




	// Add New Twitter Account
	jQuery("#cwp_top_form button#twitter-login").click(function(e){
		e.preventDefault();
		
		if (jQuery(this).text()!=="+") {
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
		}
		else
			jQuery.ajax({
				type: "POST", 
				url: cwp_top_ajaxload.ajaxurl,
				data: {
					action: "add_new_twitter_account_pro",
					currentURL: jQuery("#cwp_top_currenturl").val()
				},
				success: function(response) {
					if (response.search("api.twitter.com")==-1)
						jQuery(".cwp_top_status .inactive").html(response);
					else
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

		var userID = jQuery(this).attr('id');

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "log_out_twitter_user",
				user_id: userID
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

	// Start Tweet
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

	setInterval(function(){ jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "getNotice_action"
			},
			success: function(response) {
				if(response !== '') {
					if (response.substring(0,5)=="Error") {
						jQuery(".cwp_top_status p:nth-child(2)").css( "color", "red" );
						jQuery(".cwp_top_status p:nth-child(2)").text(response);	
					} else {

						//jQuery(".cwp_top_status p:nth-child(2)").addClass("active").removeClass("inactive");
						jQuery(".cwp_top_status p:nth-child(2)").text(response);
						jQuery(".cwp_top_status p:nth-child(2)").css( "color", "#218618" );

					}
					
					//jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet").html(response);
				}
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		})},3000);

	jQuery("#cwp_top_form a.see-sample-tweet").click(function(e){
		e.preventDefault();
		startAjaxIntro();
		cwpTopUpdateFormWithoIntro();

		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "view_sample_tweet_action"
			},
			success: function(response) {
				if(response !== '') {
					jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview").fadeIn().addClass("active");
					
					jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet").html(response);
				}
				endAjaxIntro();
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
				endAjaxIntro();
			}
		});

		
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

 	jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner button").click(function(e){
 		jQuery(this).parent().parent().fadeOut().removeClass("active");
 	});	

	// Transform the date into a countdown.
	var nextTweetDate = jQuery(".cwp_top_container .nextTweet").html();
	jQuery(".cwp_top_container .nextTweet").html('');
	jQuery(".cwp_top_container .nextTweet").countdown({
		date: nextTweetDate
	});

	// Starting the AJAX intro animation
	function startAjaxIntro() {
		jQuery(".cwp_top_wrapper .ajaxAnimation").fadeIn();
	}

	// Ending the AJAX intro animation
	function endAjaxIntro() {
		jQuery(".cwp_top_wrapper .ajaxAnimation").fadeOut();
	}

	// Reset all checkboxes and clear textareas
	jQuery.fn.cwpTopResetForm = function() {
		//jQuery(this).find("input[type=text], textarea").val("");
		//jQuery(this).find("input[type=radio], input[type=checkbox]").checked = false;
	}

	// Select all function
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


	function hideSpecifiedFieldsets()
	{
		jQuery("#top_opt_tweet_type_custom_field").parent().parent().hide();
		jQuery("#top_opt_custom_url_option").parent().parent().hide();
		jQuery("#top_opt_custom_url_field").parent().parent().hide();
		jQuery("#top_opt_url_shortner").parent().parent().hide();
		jQuery("#top_opt_use_url_shortner").parent().parent().hide();
		jQuery("#top_opt_hashtags").parent().parent().hide();
		jQuery("#top_opt_hashtag_length").parent().parent().hide();
		jQuery("#top_opt_custom_hashtag_field").parent().parent().hide();
		jQuery("#top_opt_post_type_value").parent().parent().hide();
		jQuery("#top_opt_bitly_user").parent().parent().hide();
		jQuery("#top_opt_bitly_key").parent().parent().hide();

	}

	function cwpManipulateHashtags()
	{
		if(jQuery("#top_opt_custom_hashtag_option").val() == "nohashtag") {
			jQuery("#top_opt_hashtags").parent().parent().slideUp("fast");
			jQuery("#top_opt_hashtag_length").parent().parent().slideUp("fast");
			jQuery("#top_opt_custom_hashtag_field").parent().parent().slideUp("fast");
		} else if(jQuery("#top_opt_custom_hashtag_option").val() == "common") { 
			jQuery("#top_opt_hashtags").parent().parent().slideDown("fast");
			jQuery("#top_opt_hashtag_length").parent().parent().slideDown("fast");
		} else if(jQuery("#top_opt_custom_hashtag_option").val() == "categories" || jQuery("#top_opt_custom_hashtag_option").val() == "tags") {
			jQuery("#top_opt_hashtags").parent().parent().slideUp("fast");
			jQuery("#top_opt_hashtag_length").parent().parent().slideDown("fast");
			jQuery("#top_opt_custom_hashtag_field").parent().parent().slideUp("fast");
		} else if(jQuery("#top_opt_custom_hashtag_option").val() == "custom") {
			jQuery("#top_opt_hashtags").parent().parent().slideUp("fast");
			jQuery("#top_opt_hashtag_length").parent().parent().slideDown("fast");
			jQuery("#top_opt_custom_hashtag_field").parent().parent().slideDown("fast");
		} 
	}


	// Functions to show / hide specific inputs based on user selection.
	cwpManipulateHashtags();

	if(jQuery("#top_opt_use_url_shortner").is(":checked")) {
		jQuery("#top_opt_url_shortner").parent().parent().show();
		if(jQuery("#top_opt_url_shortner").val() == "bit.ly") {
			jQuery("#top_opt_bitly_user").parent().parent().show();
			jQuery("#top_opt_bitly_key").parent().parent().show();
				
		}
	} else {
		jQuery("#top_opt_url_shortner").parent().parent().hide();
		jQuery("#top_opt_bitly_user").parent().parent().hide();
		jQuery("#top_opt_bitly_key").parent().parent().hide();
	}

	jQuery( "#top_opt_url_shortner" ).change(function(){
		if(jQuery("#top_opt_url_shortner").val() == "bit.ly" && jQuery("#top_opt_use_url_shortner").is(":checked")) {
			jQuery("#top_opt_bitly_user").parent().parent().show();
			jQuery("#top_opt_bitly_key").parent().parent().show();
				
		}
		else {
			jQuery("#top_opt_bitly_user").parent().parent().hide();
			jQuery("#top_opt_bitly_key").parent().parent().hide();
		}
	})

	if(jQuery("select#top_opt_include_link").val() == "true") {
		jQuery("#top_opt_custom_url_option").parent().parent().show();
		jQuery("#top_opt_use_url_shortner").parent().parent().show();
		//jQuery("#top_opt_url_shortner").parent().parent().show();
		if (jQuery("#top_opt_custom_url_option").is(":checked"))
			jQuery("#top_opt_custom_url_field").parent().parent().show();
	} else { 
		jQuery("#top_opt_use_url_shortner").parent().parent().hide();
		jQuery("#top_opt_custom_url_option").parent().parent().hide();
		jQuery("#top_opt_url_shortner").parent().parent().hide();
		jQuery("#top_opt_bitly_user").parent().parent().hide();
		jQuery("#top_opt_bitly_key").parent().parent().hide();
	}

	if(jQuery("#top_opt_post_type").val() == "custom-post-type") {
		jQuery("#top_opt_post_type_value").parent().parent().slideDown("fast");
	} else { 
		jQuery("#top_opt_post_type_value").parent().parent().slideUp("fast");
	}

	jQuery("select#top_opt_tweet_type").change(function(){
		if(jQuery(this).val() == "custom-field") { 
			jQuery("#top_opt_tweet_type_custom_field").parent().parent().slideDown("fast");
		} else { 
			jQuery("#top_opt_tweet_type_custom_field").parent().parent().slideUp("fast");		
		}
	});


	jQuery("select#top_opt_include_link").change(function(){
		if(jQuery(this).val() == "true") {
			jQuery("#top_opt_custom_url_option").parent().parent().slideDown("fast");
			jQuery("#top_opt_use_url_shortner").parent().parent().slideDown("fast");
			jQuery("#top_opt_url_shortner").parent().parent().slideDown("fast");
		} else { 
			jQuery("#top_opt_use_url_shortner").parent().parent().slideUp("fast");
			jQuery("#top_opt_custom_url_option").parent().parent().slideUp("fast");
			jQuery("#top_opt_url_shortner").parent().parent().slideUp("fast");
		}
	});

	jQuery("#top_opt_custom_url_option").change(function(){ 
		if(jQuery(this).is(":checked")) {
			jQuery("#top_opt_custom_url_field").parent().parent().slideDown("fast");
		} else { 
			jQuery("#top_opt_custom_url_field").parent().parent().slideUp("fast");
		}
	});

	jQuery("#top_opt_use_url_shortner").change(function(){
		if(jQuery(this).is(":checked")) {
			jQuery("#top_opt_url_shortner").parent().parent().slideDown("fast");
			if(jQuery("#top_opt_url_shortner").val() == "bit.ly") {
			jQuery("#top_opt_bitly_user").parent().parent().slideDown("fast");
			jQuery("#top_opt_bitly_key").parent().parent().slideDown("fast");
				
		}
		} else {
			jQuery("#top_opt_url_shortner").parent().parent().slideUp("fast");
			jQuery("#top_opt_bitly_user").parent().parent().slideUp("fast");
			jQuery("#top_opt_bitly_key").parent().parent().slideUp("fast");

		}
	});

	jQuery("#top_opt_custom_hashtag_option").change(function(){
		cwpManipulateHashtags();
	});

	jQuery("#top_opt_post_type").change(function(){
		if(jQuery(this).val() == "custom-post-type") {
			jQuery("#top_opt_post_type_value").parent().parent().slideDown("fast");
		} else { 
			jQuery("#top_opt_post_type_value").parent().parent().slideUp("fast");
		}
	});



	
});	
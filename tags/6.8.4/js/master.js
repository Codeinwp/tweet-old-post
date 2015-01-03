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



	jQuery("#cwp_top_form button.top_authorize").click(function(e){
		e.preventDefault();
		startAjaxIntro();
		if (jQuery(this).attr("service")=='facebook') {
			app_id = jQuery("#top_opt_app_id").val();
			app_secret = jQuery("#top_opt_app_secret").val();
		}
		else {
			app_id = jQuery("#top_opt_app_id_lk").val();
			app_secret = jQuery("#top_opt_app_secret_lk").val();
		}
		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "add_new_account",
				currentURL: jQuery("#cwp_top_currenturl").val(),
				social_network: jQuery(this).attr("service"),
				app_id: app_id,
				app_secret: app_secret
			},
			success: function(response) {

				window.location.href = response;
			}
		})
		return false;
	});


		
	function addFacebook(){
		var service = "facebook";
		
		startAjaxIntro();
		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "display_pages",
				currentURL: jQuery("#cwp_top_currenturl").val(),
				social_network: service
			},
			success: function(response) {
				switch (service) {
					
					case 'facebook':
						var elem = jQuery(".cwp_top_wrapper .cwp_user_pages");
						elem.fadeIn().addClass("active");
						
	   					var scrollhere = elem.offset().top+(jQuery(window).height()+elem.height())/2;
						jQuery('html, body').scrollTop(scrollhere);
					    response = JSON.parse(response);
						html='';
						data = response.data;

						for (i = 0; i < data.length; i++) {
						//	if (jQuery(".remove_user a[service=facebook").attr("id")!==data[i].id) {
								html+="<a href='#' class='cwp_preview_page' service='"+service+"' pagetoken='"+data[i].access_token+"' pageid='"+data[i].id+"'>";
								profile_image = 'https://graph.facebook.com/'+data[i].id+'/picture';
								name = data[i].name;
								category = data[i].category.substr(0,9);
								html+="<div class='page_avatar'><img src='"+profile_image+"'/></div><div class='page_name'>"+name+"</div><div class='page_category'>"+category+"</div></a>";
							//}
							}
							//html+='<button class="top_close_popup">Close preview</button>';
							//data.length = 3;
						fheight = (Math.ceil(data.length / 4) )*95;
						//if (fheight<=0) fheight = 175; 
						jQuery(".cwp_top_wrapper .cwp_user_pages .cwp_user_pages_inner ").html(html);
						jQuery(".cwp_top_wrapper .cwp_user_pages .cwp_user_pages_inner ").height(fheight);	
						jQuery(".cwp_top_wrapper .cwp_user_pages .cwp_sample_tweet_preview_inner ").height(fheight+120);	

					    endAjaxIntro();
					    break;

					case 'linkedin':
						var elem = jQuery(".cwp_top_wrapper .cwp_user_pages");
						elem.fadeIn().addClass("active");
						
	   					var scrollhere = elem.offset().top+(jQuery(window).height()+elem.height())/2;
						jQuery('html, body').scrollTop(scrollhere);
					    response = JSON.parse(response);
						html='';
						data = response.data;
						for (i = 0; i < data.length; i++) {
							html+="<a href='#' class='cwp_preview_page' service='"+service+"' pagetoken='"+data[i].access_token+"' pageid='"+data[i].id+"'>";
							profile_image = 'https://graph.facebook.com/'+data[i].id+'/picture';
							name = data[i].name;
							category = data[i].category.substr(0,9);
							html+="<div class='page_avatar'><img src='"+profile_image+"'/></div><div class='page_name'>"+name+"</div><div class='page_category'>"+category+"</div></a>";
						
							}
						jQuery(".cwp_top_wrapper .cwp_user_pages .cwp_user_pages_inner ").html(html);	
					    endAjaxIntro();
					    break;
				}
				
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: " + errorThrown);
			}
		});
	


	return false;
	}

	// Add New Account
	if (location.hash=="#_=_"|| location.hash=="#fbadd") {
		addFacebook();
		
	};

	// Add New Twitter Account
	jQuery("#cwp_top_form button.login").click(function(e){
		e.preventDefault();
		var service = jQuery(this).attr('service');
		var action = "add_new_account";
		var another = 0;
		if (jQuery(this).text()=="+") {
			action = "add_new_account_pro";
			another = 1;
		}
		if (jQuery(this).text()==" Add Account ") {
			another = 1;
		}
			startAjaxIntro();
			jQuery.ajax({
				type: "POST", 
				url: cwp_top_ajaxload.ajaxurl,
				data: {
					action: action,
					currentURL: jQuery("#cwp_top_currenturl").val(),
					social_network: service,
					another:another
				},
				success: function(response) {

					if (response.indexOf("upgrade to the PRO")===-1) {
						switch (service) {
							case 'twitter': 
								window.location.href = response;
								break;
							case 'facebook':
								if (another===0) {

								    var elem = jQuery(".cwp_top_wrapper .cwp_fbapp_preview")
									elem.fadeIn().addClass("active");
									
				   					var scrollhere = elem.offset().top+(jQuery(window).height()+elem.height())/2;
									jQuery('html, body').scrollTop(scrollhere);
								} else {
									addFacebook();
								}
							    endAjaxIntro();

							    break;
							case 'linkedin':
								var elem = jQuery(".cwp_top_wrapper .cwp_lkapp_preview")
								elem.fadeIn().addClass("active");
									
				   				var scrollhere = elem.offset().top+(jQuery(window).height()+elem.height())/2;
								jQuery('html, body').scrollTop(scrollhere);
							   // html = "<input type='text' placeholder='App key'/>";
							    //jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet").html(html);
							    endAjaxIntro();
							    break;
						}
				}else {
					jQuery(".cwp_top_status .inactive").html(response);
					  endAjaxIntro();
				}

					
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
				action: "log_out_user",
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
						jQuery(".cwp_top_status p:nth-child(2)").html(response);	
					} else {

						//jQuery(".cwp_top_status p:nth-child(2)").addClass("active").removeClass("inactive");
						jQuery(".cwp_top_status p:nth-child(2)").html(response);
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

					//jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview").css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
    				//jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview").css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
   					var elem = jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview");
   					var scrollhere = elem.offset().top+(jQuery(window).height()+elem.height())/2;
					jQuery('html, body').scrollTop(scrollhere);
				  
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

 	jQuery(".cwp_sample_tweet_preview_inner button.top_close_popup").on("click",function(e){
 		jQuery(this).parent().parent().fadeOut().removeClass("active");
 	});	

 	jQuery(".cwp_user_pages_inner button.top_close_popup").on("click",function(e){
 		e.preventDefault();
 		jQuery(this).parent().parent().parent().fadeOut().removeClass("active");
 		return false;
 	});	

 	jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner button.tweetitnow").click(function(e){
 		e.preventDefault();
		startAjaxIntro();
		
		jQuery.ajax({
			type: "POST", 
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "tweet_now_action"
			},
			success: function(response) {
				endAjaxIntro();
				jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview").fadeOut().removeClass("active");
				jQuery('html, body').animate({
				        scrollTop: jQuery(".cwp_top_wrapper .cwp_top_status").offset().top
				    }, 1000);
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
				endAjaxIntro();
			}
		});
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

		jQuery("body").on('click',function(e){
		
		
		if (jQuery(e.target).parent().hasClass("cwp_preview_page")) {
			e.preventDefault();
			//console.log(e);
			
			startAjaxIntro();
			var service = jQuery(e.target).parent().attr('service');
			var access_token = jQuery(e.target).parent().attr('pagetoken');
			var page_id = jQuery(e.target).parent().attr('pageid');
			
			jQuery.ajax({
				type: "POST", 
				url: cwp_top_ajaxload.ajaxurl,
				data: {
					action: "add_pages",
					currentURL: jQuery("#cwp_top_currenturl").val(),
					social_network: service,
					page_token:access_token,
					page_id:page_id,
					picture_url: jQuery(e.target).parent().children().children('img').attr('src'),
					page_name: jQuery(e.target).parent().children('.page_name').text()
				},
				success: function(response) {
					switch (service) {
						
						case 'facebook':						
						    endAjaxIntro();
						    jQuery(".cwp_top_wrapper .cwp_user_pages").fadeOut().removeClass("active");
						    window.location.href = response;
						    break;

						case 'linkedin':						
						    endAjaxIntro();
						    jQuery(".cwp_top_wrapper .cwp_user_pages").fadeOut().removeClass("active");
						    window.location.href = response;
						    break;
					}
					
				},
				error: function(MLHttpRequest, textStatus, errorThrown) {
					console.log("There was an error: " + errorThrown);
				}
			});
			return false;
		}
			
		});

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
		jQuery("#top_opt_post_type_custom_field").parent().parent().hide();
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
			jQuery("#top_opt_post_type_custom_field").parent().parent().slideDown("fast");
		} else { 
			jQuery("#top_opt_post_type_custom_field").parent().parent().slideUp("fast");		
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
jQuery(document).ready(function(){


	jQuery(".rop-not-version  ").on("click" ,function(){
		console.log('not version')
		jQuery(".cwp_not_version_preview").show();
		return false;
	});
	setInterval(function(){
		var clock = jQuery(".rop-twitter-clock");
		var time = parseInt(clock.attr('data-current'));
	//	console.log(time);
		clock.attr('data-current',time+1);
		clock.find("b").html(new Date(time * 1000).toUTCString());
	},1000);
	jQuery(".cwp_top_wrapper").append("<div class='ajaxAnimation'></div>");
	jQuery("#update-options").click(function(e){
		e.preventDefault();
		cwpTopUpdateForm();
		return false;
	});
	jQuery("#reset-settings").click(function(e) {
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
	jQuery("#linkedin-login").on("click",function(){
		if(jQuery(this).hasClass("pro-only")) return false;
		if(jQuery(this).hasClass("rop-not-version")) return false;
		jQuery(".cwp_lkapp_preview").show();
		return false;
	});
	jQuery("#xing-login").on("click",function(){
		if(jQuery(this).hasClass("pro-only")) return false;
		if(jQuery(this).hasClass("rop-not-version")) return false;
		jQuery(".cwp_xingapp_preview").show();
		return false;
	});
	jQuery("#tumblr-login").on("click",function(){
		if(jQuery(this).hasClass("pro-only")) return false;
		if(jQuery(this).hasClass("rop-not-version")) return false;
		jQuery(".cwp_tumblrapp_preview").show();
		return false;
	});
	jQuery("#facebook-login").on("click",function(){
		if(jQuery(this).hasClass("pro-only")) return false;
		if(jQuery(this).hasClass("rop-not-version")) return false;
		if(jQuery(this).hasClass("another-account")){
			addFacebook()
		}else{
			jQuery(".cwp_fbapp_preview").show();
		}
		return false;
	});
	jQuery("#facebook-login").on("click",function(){
		if(jQuery(this).hasClass("pro-only")) return false;
		if(jQuery(this).hasClass("rop-not-version")) return false;
		if(jQuery(this).hasClass("another-account")){
			addFacebook()
		}else{
			jQuery(".cwp_fbapp_preview").show();
		}
		return false;
	});
	jQuery("#cwp_remote_check").on("click",function(){

		var state = "";
		var th  = jQuery(this);
		if(th.hasClass("on")){
			state = "off";
			th.addClass("off").removeClass("on");
		}else{
			state = "on";
			th.addClass("on").removeClass("off");
		}
		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: 'remote_trigger',
				state:state
			},
			success: function(response) {
				console.log(response);
			},
			error: function(response) {
				console.log("Error: "+ response);
			}
		});
		return false;
	})
	jQuery("#rop-beta-button").on("click",function(){

		var state = "";
		var th  = jQuery(this);
		if(th.hasClass("on")){
			state = "off";
			th.addClass("off").removeClass("on");
		}else{
			state = "on";
			th.addClass("on").removeClass("off");
		}
		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: 'beta_user_trigger',
				state:state
			},
			success: function(response) {
				console.log(response);
			},
			error: function(response) {
				console.log("Error: "+ response);
			}
		});
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
			async:false,
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
	if ((location.hash=="#_=_"|| location.hash=="#fbadd") && jQuery("#cwp_top_currenturl").attr("data-cnetwork")!= 'tumblr') {
		addFacebook();
	};

	// Add New Twitter Account
	jQuery("#twitter-login,.top_authorize").click(function(e){

		if(jQuery(this).hasClass("rop-not-version")) return false;
		var service = jQuery(this).attr('service');
		var action = "add_new_account";
		var extra = {};
		if(service != 'twitter'){
			e.preventDefault();
			startAjaxIntro();
			if (service=='facebook') {
				extra.app_id = jQuery("#top_opt_app_id").val();
				extra.app_secret = jQuery("#top_opt_app_secret").val();
			}

			if(service == 'linkedin') {
				extra.app_id = jQuery("#top_opt_app_id_lk").val();
				extra.app_secret = jQuery("#top_opt_app_secret_lk").val();
			}
			if(service == 'xing') {
				extra.app_id = jQuery("#top_opt_app_id_xing").val();
				extra.app_secret = jQuery("#top_opt_app_secret_xing").val();
			}
			if(service == 'tumblr') {
				extra.app_id = jQuery("#top_opt_app_id_tumblr").val();
				extra.app_secret = jQuery("#top_opt_app_secret_tumblr").val();
				extra.app_url = jQuery("#top_opt_app_url_tumblr").val();
			}


		}
		startAjaxIntro();
			jQuery.ajax({
				type: "POST",
				url: cwp_top_ajaxload.ajaxurl,
				data: {
					action: action,
					currentURL: jQuery("#cwp_top_currenturl").val(),
					social_network: service,
					extra:extra
				},
				dataType:"json",
				success: function(response) {

					if(response.url){
					 	window.location.href = response.url;
					}else{
						jQuery(".cwp_fbapp_preview").hide();
						jQuery(".cwp_lkapp_preview").hide();
						endAjaxIntro();

					}
					/*if (response.indexOf("upgrade to the PRO")===-1) {
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
				}*/


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
				window.location.href = jQuery("#cwp_top_currenturl").val();
			},
			error: function(MLHttpRequest, textStatus, errorThrown) {
				console.log("There was an error: "+errorThrown);
			}
		});

		endAjaxIntro();
	});
	jQuery("#rop-clear-log").on("click",function(){
		clearNotices();
		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "rop_clear_log"
			}
		});
		return false;
	})
	// Start Tweet
	jQuery("#tweet-now").click(function(e){

		startAjaxIntro();
		cwpTopUpdateForm();

		jQuery.ajax({
			type: "POST",
			url: cwp_top_ajaxload.ajaxurl,
			data: {
				action: "tweet_old_post_action"
			},
			success: function(response) {

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
			dataType:"json",
			success: function(response) {

					if(response.length > 0 ){
						jQuery(".inactive-rop-error-label").hide();
						jQuery(".active-rop-error-label").show();
						jQuery(".rop-error-log span").html(response.length).removeClass('no-error');
						jQuery(".active-rop-error-label").html( " You have <b>" + response.length + " </b>new  messages ! Go to Log tab to see them");
						jQuery("#rop-log-list").html('');
						jQuery.each(response,function(k,v){

							jQuery("#rop-log-list").append('<li class="rop-log-item rop-'+ v.type +'"> <span class="rop-log-date">' + v.time + '</span> <span class="rop-log-text">'+ v.message+ '</span> </li>');


						})

					}else{
						clearNotices();
						jQuery("#rop-log-list").html('<li class="rop-log-item rop-notice">  <span class="rop-log-text">You have no messages ! </span> </li>');

					}

				}
			} )
		},3000);

	jQuery("#see-sample-tweet").click(function(e){
		if(!cwpTopCheckAccounts()){
			jQuery("#tabs_menu li:first").trigger("click");
			showCWPROPError("You need to add an account in order to start posting.");
			return false;
		}
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

					jQuery(window).scrollTop(0);

                    $json = JSON.parse(response);

					if($json .twitter)
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_twitter").html($json .twitter);
					else
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_twitter").hide().prev().hide();
					if($json .facebook)
                   		 jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_facebook").html( $json .facebook) ;
					else
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_facebook").hide().prev().hide();
					if($json .linkedin)
                    jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_linkedin").html( $json .linkedin);
					else
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_linkedin").hide().prev().hide();
					if($json .xing)
                    jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_xing").html( $json .xing);
					else
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_xing").hide().prev().hide();
					if($json .tumblr)
                    jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_tumblr").html( $json .tumblr);
					else
						jQuery(".cwp_top_wrapper .cwp_sample_tweet_preview .cwp_sample_tweet_preview_inner .sample_tweet.sample_tweet_tumblr").hide().prev().hide();

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
	jQuery("#stop-tweet-old-post").click(function(e){
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
	var nextTweetDate =  jQuery(".cwp_top_container .nextTweet").html();
	jQuery(".cwp_top_container .nextTweet").html('');
	if(nextTweetDate){
		jQuery(".cwp_top_container .nextTweet").countdown({
			date: nextTweetDate
		});
	}
	jQuery(".rop-network-countdown").each(function() {
		var span = jQuery(this).find('.rop-network-timestamp');
		var timestamp = parseInt(span.attr('data-timestamp'));

		if (!isNaN(timestamp)) {
			span.countdownPlugin(timestamp * 1000).on('update.countdown',  function(event) {
				var format = '%H hr %M m %S s';
				if(event.offset.days > 0) {
						format = '%-d day%!d ' + format;
				}
				if(event.offset.weeks > 0) {
						format = '%-w week%!w ' + format;
				}
				jQuery(this).html(event.strftime(format));
			}).on('finish.countdown', function(event) {
				var th = jQuery(this).parent();
				th.html("Please wait ....");
				setTimeout(function(){
					th.html("You can refresh the page to see the next schedule !");

				},1000)

			});
			span.parent().show();
		}else{
			span.parent().hide();
		}
	})

	jQuery(".cwp-cpt-checkbox").click(function(){
		var ck = false;
		var th = jQuery(this);
		var val = th.val();
		if(th.is(":checked")){

			ck = true;
		}else{

			ck = false;
		}
		if(ck){

			jQuery(".cwp-tax-"+val).show();

		}else{

			jQuery(".cwp-tax-"+val + " input").removeAttr("checked");
			jQuery(".cwp-tax-"+val).hide();

		}

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
    jQuery(".option[data-dependent] select, .option[data-dependent] input[type='checkbox']").change(function(){
        cwpDependeField(jQuery(this).closest('.option'));
    });

	jQuery("#cwp_top_tabs ul#tabs_menu li ").not(".not-tab").click(function() {
        jQuery("#cwp_top_tabs ul#tabs_menu li").removeClass("active");
        jQuery("#cwp_top_tabs div.tab").removeClass("active");
        var th = jQuery(this);
        var index = th.index();
        th.addClass("active");


        jQuery("#cwp_top_tabs div.tab").eq(index).addClass("active");

        return false
    });
    jQuery(".cwp-schedule-days li").click(function(){
        var th = jQuery(this);
        if(th.hasClass("active")){
            th.removeClass('active');
        }else{

            th.addClass('active');
        }

    })
    jQuery(".cwp-schedule-days li").click(function(){
        var container = jQuery(this).parent().parent();
        cwpTopRefreshPostingDays(container);

    })
    jQuery(".cwp-populate-hidden-radio").click(function(){
        var th = jQuery(this);
        var id = th.attr('data-hidden');
        var value = th.attr('data-value');
        jQuery("#"+id).val(value);

    })
    jQuery(".cwp-populate-hidden-checkbox").click(function(){
        var th = jQuery(this);
        var id = th.parent().attr('data-hidden');
        var values = [];
        th.parent().find('.active').each(function(){
                var ith = jQuery(this);
                values.push(ith.attr('data-value'));
        });
        jQuery("#"+id).val(values.join(','));

    })
    jQuery(".cwp-according-item .cwp-according-header").click(function(){

        var th = jQuery(this).parent();
        if(th.hasClass('active')) return false;
        var active = th.parent().children(".active");


        active.find(".cwp-according-body").slideUp(100,function(){
            active.removeClass('active');
        });
        th.find(".cwp-according-body").slideDown(100,function(){
            th.addClass('active');
        });
        return false;
    });
	jQuery(".cwp_top_tabs_vertical .cwp_top_tabs_btns li ").not('.not-tab').click(function() {

        var th = jQuery(this);
        if(th.parent().parent().find(".tab-vertical").is(":animated")) return false;
        var index = th.index();
        th.parent().parent().find(".tab-vertical.active").fadeOut(200,function(){
            jQuery(this).removeClass("active");
            th.parent().find("li").removeClass("active");
            th.parent().parent().find(".tab-vertical").eq(index).fadeIn(200,function(){
                jQuery(this).addClass("active");
                th.parent().find("li").eq(index).addClass("active");
            } );
        } );


        return false
    });

    jQuery("#cwp_top_tabs").on("click",".cwp-top-times-close",function(){
        var li  =  jQuery(this).parent();
        li.remove();
        cwpTopRefreshPostingDays( );
    })
    jQuery(".cwp-add-posting-time").click(function(){

        var container = jQuery(this).parent().parent();
		var network = jQuery(this).closest('.tab-vertical').attr('data-network');
        container.find(".cwp-posting-times").append(getCwpTopTimeHTML(network));

        cwpTopRefreshPostingDays(container);
        return false;
    })
    cwpBindCheckedHidden();
    cwpLoadPostFormaFields();

	cwpTopBindTimes();
    jQuery(".cwp-custom-schedule-days").each(function(){
        var container = jQuery(this);
        cwpTopRefreshPostingDays(container);
    });

	jQuery(".cwp-cpt-checkbox").each(function(){
		var th = jQuery(this);
		if(th.is(":checked")){

			jQuery(".cwp-tax-"+th.val()).show();

		}

	});

	jQuery(".login.pro-only").click(function(e){
		if(!ropProAvailable){
			window.open(
				'https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=imagepro&utm_medium=link&utm_campaign=top&upgrade=true',
				'_blank'
			);
			return false;
		}
	});
});

function cwpDependeField(field,second){

    var dvalues = field.attr("data-dependent");
    if(dvalues === undefined) return false;
    var value;

    if(field.find("select").length != 0 ){

        value = field.find("select").val();

    }
    if(field.find("input[type='checkbox']").length != 0 ){
        if(field.find("input[type='checkbox']").is(":checked")){

            value = 'true';

        }else{

            value = 'false';
        }

    }
    var json = JSON.parse(dvalues);
    var item;

    var tmpvalues;
    jQuery.each(json,function(k,v){
        item = field.parent().find(".twp"+k);
		v = v.split(',');
        if(jQuery.inArray(value,v) > -1 && !second){
            item.slideDown('fast');
            cwpDependeField(item);
        }else{

            item.slideUp('fast');
            cwpDependeField(item,true);
        }

    });

}
function getCwpTopTimeHTML(network){
	var cwp_top_time =  '<li class="clearfix cwp-top-times-choice">\
            <select class="cwp-top-times-hours" name="'+network+'_time_choice_hour[]">\
        <option value="00">00</option>\
        <option value="01">01</option>\
        <option value="02">02</option>\
        <option value="03">03</option>\
        <option value="04">04</option>\
        <option value="05">05</option>\
        <option value="06">06</option>\
        <option value="07">07</option>\
        <option value="08">08</option>\
        <option value="09">09</option>\
        <option value="10">10</option>\
        <option value="11">11</option>\
        <option value="12">12</option>\
        <option value="13">13</option>\
        <option value="14">14</option>\
        <option value="15">15</option>\
        <option value="16">16</option>\
        <option value="17">17</option>\
        <option value="18">18</option>\
        <option value="19">19</option>\
        <option value="20">20</option>\
        <option value="21">21</option>\
        <option value="22">22</option>\
        <option value="23">23</option>\
        </select> : \
            <select class="cwp-top-times-hours" name="'+network+'_time_choice_min[]">\
                <option value="00">00</option>\
                <option value="01">01</option>\
                <option value="02">02</option>\
                <option value="03">03</option>\
                <option value="04">04</option>\
                <option value="05">05</option>\
                <option value="06">06</option>\
                <option value="07">07</option>\
                <option value="08">08</option>\
                <option value="09">09</option>\
                <option value="10">10</option>\
                <option value="11">11</option>\
                <option value="12">12</option>\
                <option value="13">13</option>\
                <option value="14">14</option>\
                <option value="15">15</option>\
                <option value="16">16</option>\
                <option value="17">17</option>\
                <option value="18">18</option>\
                <option value="19">19</option>\
                <option value="20">20</option>\
                <option value="21">21</option>\
                <option value="22">22</option>\
                <option value="23">23</option>\
                <option value="24">24</option>\
                <option value="25">25</option>\
                <option value="26">26</option>\
                <option value="27">27</option>\
                <option value="28">28</option>\
                <option value="29">29</option>\
                <option value="30">30</option>\
                <option value="31">31</option>\
                <option value="32">32</option>\
                <option value="33">33</option>\
                <option value="34">34</option>\
                <option value="35">35</option>\
                <option value="36">36</option>\
                <option value="37">37</option>\
                <option value="38">38</option>\
                <option value="39">39</option>\
                <option value="40">40</option>\
                <option value="41">41</option>\
                <option value="42">42</option>\
                <option value="43">43</option>\
                <option value="44">44</option>\
                <option value="45">45</option>\
                <option value="46">46</option>\
                <option value="47">47</option>\
                <option value="48">48</option>\
                <option value="49">49</option>\
                <option value="50">50</option>\
                <option value="51">51</option>\
                <option value="52">52</option>\
                <option value="53">53</option>\
                <option value="54">54</option>\
                <option value="55">55</option>\
                <option value="56">56</option>\
                <option value="57">57</option>\
                <option value="58">58</option>\
                <option value="59">59</option>\
            </select><span class="cwp-top-times-close">x</span>\
        </li>';
		return  cwp_top_time;
}
function cwpBindCheckedHidden(){
        jQuery(".cwp-populate-hidden-checkbox-group").each(function(){
            var th = jQuery(this);
            var field = th.attr('data-hidden');
            var values = jQuery("#"+field).val();
            values = values.split(',');
            jQuery.each(values,function(k,v){
                th.find(".cwp-populate-hidden-checkbox[data-value='"+v+"']").addClass('active');
            });

        })

}

function cwpTopBindTimes(){

    jQuery(".cwp-posting-times").each(function(){
		var network = jQuery(this).closest('.tab-vertical').attr('data-network');
        var values = jQuery(this).attr('data-times');
        var th = jQuery(this);
        values = jQuery.parseJSON(values);
        jQuery.each(values,function(k,v){
            th.append(getCwpTopTimeHTML(network) );
            th.find("li:last select:first").val(v.hour);
            th.find("li:last select:last").val(v.minute);
        });


    })

}
function cwpTopRefreshPostingDays(container){
    if(container === undefined){
        jQuery(".cwp-custom-schedule-days").each(function(){
            var container = jQuery(this);
            cwpTopRefreshPostingDays(container);
        })
        return false;
    }
    var times = container.find(".cwp-top-times-choice").length;
    var days  = [];
    var day = "";
    container.find('.cwp-populate-hidden-checkbox.active').each(function(){
        day = jQuery(this).text().substr(0,3);
        days.push(day);
    });
    container.find(".cwp-posts-time-info-days").text(days.join(","));
    container.find(".cwp-posts-time-info-times").text(times);
}
function cwpLoadPostFormaFields(){
    jQuery(".option[data-dependent], .option[data-dependent] ").each(function(){
        cwpDependeField(jQuery(this));

    })

}
function showCWPROPError(string){
	jQuery(".cwp_top_status p.cwp-error-label").css( "color", "red" );
	jQuery(".cwp_top_status p.cwp-error-label").html(string);

}
function cwpTopCheckAccounts(){
		var users = jQuery(".user_details").length;
		return (users > 0);

}
function clearNotices(){
	jQuery("#rop-log-list").html('');
	jQuery(".rop-error-log span").html('').addClass("no-error");
	jQuery(".active-rop-error-label").hide();
	jQuery(".inactive-rop-error-label").show();


}

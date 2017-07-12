jQuery(document).ready(function(){
    if (String(typeof(rf_activation_panel_js)) == "undefined"){
        rf_activation_panel_js = true;
        /* functions */
        function sign_in_email(){
            if(!validate_email(jQuery(".rf_email_box").val())){
                window.rf_post_panel.show_message("We need an email address to sign you in.");
                jQuery(".rf_email_box").focus();
                return false;
            }
            if(jQuery(".rf_pass_box").val().length <8){
                window.rf_post_panel.show_message("Passwords must be at least 8 characters please");
                jQuery(".rf_pass_box").focus();

                return false;
            }
            var data = {
                email:jQuery(".rf_email_box").val(),
                password:jQuery(".rf_pass_box").val(),
                referrer: window.location.href
            };
            var url = rf_call_home+"sign_in_remote"; //not the best way to do it
            if (jQuery(".sign_label").html() == 'Sign up')
                url = rf_call_home+"sign_up_remote";
            jQuery(".rf_activation_panel_active").hide();//hide sign up form
            jQuery(".rf_activation_panel_loading").show();//show loading
            jQuery.post(url,data,function(server_response){
                if (server_response.result){
                    rf_add_key(server_response.key);
                    window.rf_api_key = server_response.key;
                }
                else{
                    jQuery(".rf_activation_panel_active").show();//hide sign up form
                    jQuery(".rf_activation_panel_loading").hide();//show loading
                    jQuery(".message_holder_area").html(server_response.message).show();
                }
            })
        }
        function toggle_sign_up(caller){
            jQuery(".sign_in").removeClass("active");
            caller.addClass("active");
            var text= caller.data("text");
            jQuery(".sign_label").html(text);
            jQuery(".forgot_pass_area, .sign_up_link_holder").toggle();
            if (text == "Sign up"){
                jQuery(".sign_up_link_holder").show();
                jQuery(".forgot_pass_area").hide();
            }
            else{
                jQuery(".forgot_pass_area").show();
                jQuery(".sign_up_link_holder").hide();
            }
        }
        function forgot_pass(){
            if(!validate_email(jQuery(".rf_email_box").val())){
                window.rf_post_panel.show_message("We need an email address to send your password.");
                jQuery(".rf_email_box").focus();
            }
            else{
                var data = {
                    email_address:jQuery(".rf_email_box").val()
                };
                var url = rf_call_home+"remote_forgot_pass";//todo
                jQuery.post(url,data,function(server_response){
                    window.rf_post_panel.show_message(server_response.message);
                })
            }
        }
        function rf_add_key(rf_api_key) {
            var data = {
                'action': 'rf_ajax_handler',
                'rewards_fuel_add_api_key': rf_api_key
            };
            jQuery.post(ajaxurl, data, function(data) {
                window.rf_post_panel.update_panel(); // on post page
            });
        }
        function validate_email(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }
        /* event listners */
        jQuery(document).on("click",".sign_in",function(e){
            e.preventDefault();
            toggle_sign_up(jQuery(this));
        });
        jQuery(document).on("click",".show_why",function(e){
            e.preventDefault();
            jQuery(".why_sign_up_in").toggle();

        });
        jQuery(document).on("click",".forgot_pass",function(e){
            e.preventDefault();
            forgot_pass();
        });
        jQuery(document).on("keypress", ".email_holder", function(event) {
            if (event.keyCode == 13) { //stops the form from being submitted
                sign_in_email();
                return false;
            }
        });
        jQuery(document).on("click",".activate_button",function(e){
            e.preventDefault();
            sign_in_email();
        });

        window.rf_init_active_panel = function(){
            console.log("rf_init_active_panel called", window.rf_api_key);
            rf_populate_contests(); //gets all contestants contests.
            rf_get_comment_entry_id(); //gets current situation with post if there is comment entry being used
            rf_get_status(); //adds ability to communicate back to WordPress user (updates available etc)
            rf_embedded_contest_id = rf_check_for_short_code();
        };

        var watching_for_new_key = false;
        jQuery(document).on("click",".connect_button",function(e){
           
            window.onblur = function() {
                if (window.rf_api_key == false) {
                    watching_for_new_key = true;
                }
                else{
                    watching_for_new_key = false;
                }
            };
        });
        window.onfocus = function() {
            if (watching_for_new_key){
                window.rf_post_panel.rf_get_key();
                if (window.rf_api_key != false){
                    watching_for_new_key = false;
                }
            }
        };
    } 
});
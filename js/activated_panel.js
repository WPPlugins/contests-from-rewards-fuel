jQuery(document).ready(function(){
    if (String(typeof(rf_post_activated_panel_js)) == "undefined"){
        rf_post_activated_panel_js = true;
        var current_rf_a = "";
        var current_rf_e = "";
        var rf_embedded_contest_id = false;
        function rf_delete_key() {
            var data = {
                'action': 'rf_ajax_handler',
                'remove_rewards_fuel_api_key': 'true'
            };
            jQuery.post(ajaxurl, data, function(data) {
                if(typeof rf_post_id !== 'undefined'){
                    window.rf_api_key = false;
                    window.rf_post_panel.update_panel(); // on post page
                }
                else{
                    window.location.reload();
                }
            });
        }
        window.rf_init_active_panel = function(){
            console.log("rf_init_active_panel called", window.rf_api_key);
            rf_populate_contests(); //gets all contestants contests.
            rf_get_comment_entry_id(); //gets current situation with post if there is comment entry being used
            rf_get_status(); //adds ability to communicate back to WordPress user (updates available etc)
            rf_embedded_contest_id = rf_check_for_short_code();
        };


        function rf_populate_contests() {
            console.log("populating contests",window.rf_api_key);
            if (String(typeof(window.rf_api_key)) !="undefined" && window.rf_api_key != false) {
                rf_clear_contest_box();
                url_to_load = rf_call_home + "get_api_key_contests/?key=" + window.rf_api_key;
                var request = jQuery.ajax({
                    url: url_to_load,
                    cache: false,
                    dataType: 'json'

                });
                request.done(function (data) {
                    //console.log("contests data", data);
                    for (i = 0; i < data.length; i++) {
                        rf_add_to_contest_box(data[i].id, data[i].name, data[i].status);
                    }
                });
                
            }
        }

        function rf_clear_contest_box() {
            jQuery("#rf_contests").find('option').remove().end();
            jQuery("#rf_contests").append('<option value="0">Select your contest</option>')
        }
        function rf_add_to_contest_box(contest_id, contest_name, contest_status) {
            jQuery("#rf_contests").append('<option value="' + contest_id + '" class="rf_contest_class_'+contest_status+'">' + contest_id + ' - ' + contest_name + '</option>');
        }

        function rf_get_status() {
            if (String(typeof(rf_api_key)) !="undefined") {
                var url = rf_call_home + "get_status/?&a=" + rf_api_key;
                var request = jQuery.ajax({
                    url: url,
                    cache: false,
                    dataType: 'html'
                });
                request.done(function (data) {
                    jQuery("#rf_news").html(data);
                });
            }
        }

        function rf_get_comment_entry_id() {
            if (typeof rf_post_id != 'undefined') {
                var data = {
                    'action': 'rf_ajax_handler',
                    'get_comment_entry_id': rf_post_id
                };
                jQuery.post(ajaxurl, data, function(data) {
                    rf_post_entry_method = data.entry_id;
                    rf_post_entry_method_key = data.entry_method_key;
                    contest_id = jQuery("#rf_contests").val();
                    if (rf_post_entry_method > 0)
                        rf_wp_entry_method_exists_on_post = true;
                    else
                        rf_wp_entry_method_exists_on_post = false;
                    current_rf_a = rf_post_entry_method_key;
                    current_rf_e = rf_post_entry_method;
                    rf_get_contest_id_of_current_entry_method();
                    return true;
                });


            }
        }
        function rf_get_contest_id_of_current_entry_method(){
            var url = rf_call_home + "get_entry_method_details/?api_key=" + current_rf_a + "&entry_method_id=" + current_rf_e;
            var request = jQuery.ajax({
                url: url,
                cache: false,
                dataType: 'json'
            });
            request.done(function (data) {
                rf_choose_selector(data.contest_id);
            });
        }
        function contest_id_in_select_box(test_value) {
            var select_contest_ids = jQuery('#rf_contests option');
            try{
                for(i = 0;i<select_contest_ids.length;i++){
                    if (String(jQuery(select_contest_ids[i]).val()) == String(test_value)){
                        return true;
                    }
                }
            }
            catch(e){};
            return false;
        }

        function rf_choose_selector(entry_method_contest_id){
            //case 1 same contest and entry methods contest id - best case
            var embedded_contest_id = rf_check_for_short_code();
            if(contest_id_in_select_box(embedded_contest_id)) {
                var selector = jQuery("#rf_contests");
                if (parseInt(entry_method_contest_id) == parseInt(embedded_contest_id)) {
                    selector.val(entry_method_contest_id).change();
                    return
                }
                //case 2 contest is embedded but is different than entry method contest
                if (parseInt(embedded_contest_id) > 0) {
                    selector.val(embedded_contest_id).change();
                    return
                }
                //case 3 no contest embedded but entry method is active
                if (parseInt(embedded_contest_id) > 0) {
                    selector.val(embedded_contest_id).change();
                    return
                }
                if (parseInt(entry_method_contest_id) > 0) {
                    selector.val(entry_method_contest_id).change();
                }
            }
        }
        function rf_check_for_short_code(){
            try {
                var editor = jQuery("#content");
                var contest_id = 0;
                var content = editor.val();
                var upper_case_index = content.indexOf("[RF_CONTEST");
                var lower_case_index = content.indexOf("[rf_contest");
                if (upper_case_index > -1 || lower_case_index > -1) {
                    //there is a contest embedded in the editor
                    var contest_in_editor = true;
                    var starting_point = Math.max(upper_case_index, upper_case_index);
                    var end_point = content.indexOf("']", starting_point);
                    var content_data = content.substring(starting_point, end_point);
                    var content_data_split = content_data.split("=", 2);
                    contest_id = content_data_split[1];
                    contest_id = contest_id.replace(/\D/g, '');
                    contest_id = parseInt(contest_id);
                }
                return contest_id
            }
            catch(e){
                console.log("error trying to find short code ",e);
            }
        }

        function rf_check_wp_comment_in_contest(contest_id, rf_api_key) {
            var url_to_load = rf_call_home + "check_contest_for_wp_entry?contest_id=" + contest_id + "&a=" + rf_api_key;
            var request = jQuery.ajax({
                url: url_to_load,
                cache: false,
                dataType: 'json'

            });
            request.done(function (data) {
                //console.log("comment entry data", data);
                wp_comment_entry_exists = data.result;
                if (wp_comment_entry_exists) {

                    contest_selected_rf_a = data[0].api_key;
                    contest_selected_rf_e = data[0].entry_method_id;
                    //console.log("selected rf_a", contest_selected_rf_a);
                    //console.log("selected rf_e", contest_selected_rf_e);
                    window["rf_wp_entry_exists_in_selected"] = true;
                }
                else {
                    contest_selected_rf_a = 0;
                    contest_selected_rf_e = 0;
                    window["rf_wp_entry_exists_in_selected"] = false;
                }

                rf_check_comment_entry_status(window["rf_wp_entry_exists_in_selected"], contest_selected_rf_e);
                return true;
            });
            request.fail(function (data) {
                console.log("Failure, " + data);
                return false;
            });
            return true;
        }

        function rf_generate_rf_short_code(contest_id) {

            return "[rf_contest contest='" + contest_id + "']";
        }

        function rf_check_comment_entry_status(rf_wp_entry_exists_in_selected, selected_entry_method_id) {
            //console.log("wp entry exists in selected box", rf_wp_entry_exists_in_selected);
            rf_hide_widget_messages(); // hide all messages initially
            if (rf_wp_entry_exists_in_selected) { //if comment entry exists in selected contest
                if (current_rf_e > 0) {
                    if (parseInt(selected_entry_method_id) == parseInt(current_rf_e)) {
                        //wp comment entry method is being used here
                        jQuery("#rf_comment_entry_being_used").show();
                    }
                    else {
                        //wp entry method is being used here, just not this contests,
                        jQuery("#rf_comment_entry_being_used_dif_contest").show();
                    }
                }
                else {
                    //there is a wp comment entry method, its just not being used here - caution on refreshing the page
                    jQuery("#rf_comment_entry_not_used").show();
                }
                //get information about currently used entry method
            }
            else {
                //there is no wp entry method for this contest
                jQuery("#rf_no_comment_entry").show();
            }
        }
        function rf_hide_widget_messages() {
            jQuery("#rf_no_comment_entry").hide();
            jQuery("#rf_comment_entry_not_used").hide();
            jQuery("#rf_comment_entry_being_used").hide();
            jQuery("#rf_comment_entry_being_used_dif_contest").hide();

        }

        function rf_modify_wp_comment_entry_to_post(action) {

            var main_action = "";
            if (action == "add") {
                main_action = "add_comment_entry";
            }
            if (action == "edit") {
                main_action = "edit_comment_entry";
            }
            if (action == "remove") {
                main_action = "remove_comment_entry";
            }


            var data = {
                'action': 'rf_ajax_handler',
                'rf_a': contest_selected_rf_a,
                'rf_e':contest_selected_rf_e,
                'post_id': rf_post_id
            };
            data[main_action] =true;
            console.log("ajaxing rf_modify_wp_comment_entry_to_post posting url", ajaxurl, data);
            jQuery.post(ajaxurl, data, function(data) {
                if (data.result == true) {
                    if (action != "remove") {
                        rf_wp_entry_method_exists_on_post = true;
                        window["rf_wp_entry_exists_in_selected"] = true;
                        current_rf_a = contest_selected_rf_a;
                        current_rf_e = contest_selected_rf_e;
                    }
                    else {
                        var selected_contest = jQuery("#rf_contests").val();
                        rf_check_wp_comment_in_contest(selected_contest, rf_api_key);
                        rf_wp_entry_method_exists_on_post = false;
                        current_rf_a = 0;
                        current_rf_e = 0;
                    }

                    rf_get_comment_entry_id();
                    rf_check_comment_entry_status(window["rf_wp_entry_exists_in_selected"], contest_selected_rf_e)
                }
                return true;
            });

        }
        function rf_get_comment_entry_id() {
            if (typeof rf_post_id != 'undefined') {
                var data = {
                    'action': 'rf_ajax_handler',
                    'get_comment_entry_id': rf_post_id
                };

                jQuery.post(ajaxurl, data, function(data) {
                    rf_post_entry_method = data.entry_id;
                    rf_post_entry_method_key = data.entry_method_key;
                    contest_id = jQuery("#rf_contests").val();
                    if (rf_post_entry_method > 0)
                        rf_wp_entry_method_exists_on_post = true;
                    else
                        rf_wp_entry_method_exists_on_post = false;
                    current_rf_a = rf_post_entry_method_key;
                    current_rf_e = rf_post_entry_method;
                    //console.log("currently used entry method", current_rf_e);
                    //console.log("currently used key", current_rf_a);
                    //get information about currently used entry method to update the selector
                    rf_get_contest_id_of_current_entry_method();
                    return true;
                });


            }
        }

        function rf_add_comment_entry(contest_id) {
            if (String(typeof(rf_api_key)) !="undefined") {
                var URL = rf_call_home + "add_comment_entry_to_contest/?a=" + rf_api_key + "&c=" + contest_id;
                var win_name = "add_entry";
                var specs = "height=500,width=500,location=no,titlebar=no,toolbar=no";
                var win = window.open(URL, win_name, specs);
                if (win !== null) {
                    win.focus();
                    rf_win_timer = setInterval(function () {
                        if (win.closed) {
                            rf_check_wp_comment_in_contest(contest_id, rf_api_key);
                            window.clearInterval(rf_win_timer);
                        }
                        //console.log("checked for tw window.");
                    }, 1000);
                    return false;
                } else {
                    jQuery(this).attr("href", URL).attr("target", "_blank");
                    return true;
                }
            }
        }
        function rf_set_cookie(name,value,days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }

        function rf_get_cookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }
        function get_current_plan(){
            var cookie_name = 'rf_current_plan';
            var current_plan = rf_get_cookie(cookie_name);
            if(current_plan != null){
                window["rf_current_plan"] =current_plan;
                handle_current_plan(current_plan);
                return current_plan;
            }
            var url = rf_call_home+'get_current_plan/?k='+rf_api_key;
            jQuery.get(url,function(server_response){
                rf_set_cookie(cookie_name,server_response,1);
                window["rf_current_plan"] = server_response;
                handle_current_plan(current_plan);
            })
        }
        function handle_current_plan(plan){
            if(parseInt(plan) ==1){
                jQuery(".rf_free_account_area").show();
            }else{
                jQuery(".rf_free_account_area").hide();
            }
        }
        get_current_plan();
        var rf_creator_popup = "";
        var rf_timer = "";
        function rf_create_contest(rf_api_key) {
            var URL =  window.rf_call_home+"create_contest_via_wp_key/?a=" + encodeURI(rf_api_key);
            win_name = "RF_creator";
            specs = "height=700,width=756,scrollbars=1,toolbar=no,resizable=yes";
            rf_creator_popup = window.open(URL, win_name, specs);
            rf_creator_popup.focus();
            rf_timer = setInterval(rf_check_for_contest_competed, 1500);

        }

        function rf_check_for_contest_competed() {

            if (rf_creator_popup.closed) {
                //console.log("popup window closed", rf_creator_popup);
                rf_populate_contests();
                clearInterval(rf_timer);
                rf_creator_popup = "";
            }
        }
        /******listners*****/
        jQuery(document).on('click', '#rf_remove_api_key', function (e) {
            e.preventDefault();
            rf_delete_key();
            return false;
        });

        jQuery(document).on('change', '#rf_contests', function (e) {
            //update short code
            var selected_contest = jQuery("#rf_contests").val();
            rf_check_wp_comment_in_contest(selected_contest, rf_api_key);
            //console.log("selected contest", selected_contest);
            var short_code_container = jQuery("#rf_short_code_container");
            if (String(selected_contest) != ("0" || "null"))
                short_code = rf_generate_rf_short_code(selected_contest);
            else
                short_code ="Please select a contest.";
            short_code_container.val(short_code);

        });

        jQuery(document).on('click', '.add_wp_comment_entry', function (e) {
            e.preventDefault();
            var contest_id = jQuery('#rf_contests').val();
            rf_add_comment_entry(contest_id);
            return false;
        });
        jQuery(document).on('click', '.use_wp_comment_entry', function (e) {
            e.preventDefault();
            var contest_id = jQuery('#rf_contests').val();
            rf_modify_wp_comment_entry_to_post("add");
            return false;
        });
        jQuery(document).on('click', '.edit_wp_comment_entry', function (e) {
            e.preventDefault();
            var contest_id = jQuery('#rf_contests').val();
            rf_modify_wp_comment_entry_to_post("edit");
            return false;
        });
        jQuery(document).on('click', '.remove_wp_comment_entry', function (e) {
            e.preventDefault();
            var contest_id = jQuery('#rf_contests').val();
            rf_modify_wp_comment_entry_to_post("remove");
            return false;
        });
        jQuery(document).on('click', '#add_rf_contest', function (e) {
            e.preventDefault();
            console.log("api key", rf_api_key);
            rf_create_contest(window.rf_api_key);
            return false;
        });
    }
    if (window.rf_api_key != false)
        window.rf_init_active_panel();
    jQuery("#rf_short_code_container").click(function () {
        jQuery(this).select();
    });
});
jQuery(document).ready(function(){
    if (String(typeof(rf_post_panel_js)) == "undefined"){
        rf_post_panel_js = true;
        rf_post_panel = {};
        /* functions */
        window.rf_post_panel.update_panel = function() {
            var data = {
                'action': 'rf_ajax_handler',
                'update_display': window.rf_post_id
            };
            jQuery.post(ajaxurl, data, function(data) {
                jQuery("#rf_post_panel_container").html(data);
                if(window.rf_api_key != false){
                    window.location.reload();
                }
            });
        };

        window.rf_post_panel.show_message = function(message_html,duration){
            jQuery(".message_holder_area").html(message_html);
            jQuery(".message_holder_area").show();
            //todo add hide on duration
            setTimeout(function(){
                jQuery(".message_holder_area").html("");
                jQuery(".message_holder_area").hide(1500);
            },5000);
        };
        window.rf_post_panel.rf_get_key = function(){
            var data = {
                'action': 'rf_ajax_handler',
                'rewards_fuel_get_api_key': true
            };
            jQuery.post(ajaxurl, data, function(data) {
                if (data != "" || data != false || data != null){
                    window.rf_api_key = data;
                    window.rf_post_panel.update_panel();
                }
            });
        };
        /* event listners */
        
    } 
});
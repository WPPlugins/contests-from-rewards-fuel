<div id="rf_widget_container">
    <script>
        rf_api_key = "<?php echo($api_key); ?>";
    </script>

<div id="rf_widget">
    <p class="your_contests">Your contests:</p>

    <div><select id="rf_contests" class="form-control">
            <option value="0">Select your contest</option>
        </select></div>
    <div>
        <button id="add_rf_contest" class="btn button-primary">Create new contest</button>
    </div>
    <div id="current status" style="clear: both; margin-top: 10px;">
        <div id="rf_no_comment_entry" hidden><i class="fa fa-exclamation-triangle"></i> This contest has no WordPress
            Comment entry method, <a href="#" class="add_wp_comment_entry">click here to add one.</a></div>
        <div id="rf_comment_entry_not_used" hidden><i class="fa fa-star"></i> This contest has WordPress Comment entry
            method, but isn't active on this post, <a href="#" class="use_wp_comment_entry">click here to activate
                now.</a></div>
        <div id="rf_comment_entry_being_used" hidden><i class="fa fa-thumbs-up"></i> This post is using Comment entry.
            <a href="#" class="remove_wp_comment_entry">Click here if you want to remove it.</a></div>
        <div id="rf_comment_entry_being_used_dif_contest" hidden><i class="fa fa-question"></i> This post is using
            Comment entry, but not for the selected contest. <a href="#" class="edit_wp_comment_entry">Click here if you
                would like to change this post to use the selected contest's comment entry.</a></div>
    </div>
    <div class="rf_widget_breaker"></div>
    <div id="rf_short_code">
        <p>Your embed code for this contest is:</p>
        <input class="form-control" id="rf_short_code_container" type="text"> &nbsp; <em>( place
            this where you would like to see your contest )</em>
    </div>
    <div class="rf_widget_breaker"></div>
    <div>
        <button id="rf_remove_api_key" class="btn button-primary">Disconnect your account</button>
    </div>
    <div id="rf_news"></div>

</div>

</div>
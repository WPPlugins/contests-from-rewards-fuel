<div id="rf_widget_container">
    <div id="rf_widget" class="rf_widget">
        <div class="rf-activation-panel">
            <a href="http://app.rewardsfuel.com/api/wp/sign_in/?ud=<?php echo($ud); ?>" data-ud="<?php echo($ud); ?>" class="rf-sign-in-button" target="_blank">Activate your Contests by Rewards Fuel. <i class="fa fa-caret-right"></i></a>
            <span class="rf-activation-message">Activate your Rewards Fuel Account &amp; create a contest in minutes.</span>
            <span class="rf-close-area"></span>
        </div>
        <h3>Or enter your Rewards Fuel API key</h3>

        <div class="api_key_title">API Key:</div>
        <input class="form-control" name="rewards_fuel_add_api_key" id="rf_api_key">
        <button class="rf_add_api_key button button-primary button-large">Add your API Key</button>
    <div class="rf_widget_breaker"></div>
        <div><a href="http://RewardsFuel.com" class="btn btn-default get-api-key" role="button">Get your API key</a></div>
        <div id="rf_help"><a href="http://rewardsfuel.com/wordpress/?ref=wp_plugin" target="_blank">For instruction on how to use this better click here.</a> </div>
        <div id="rf_news"></div>
</div>

    <script>
        var rf_call_home = "<?php echo($rf_api_home); ?>";
        var rf_version_id = "<?php echo($rf_version); ?>";
        var rf_post_id = <?php echo($post->ID); ?>;
        var rf_api_key = "no_key";
    </script>

</div>
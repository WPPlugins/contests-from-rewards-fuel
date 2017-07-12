<div id="rf_widget_container">
    <div id="rf_widget" class="rf_widget">
        <h3>Enter your Rewards Fuel API key</h3>

        <div class="api_key_title">API Key:</div>
        <input class="form-control" name="rewards_fuel_add_api_key" id="rf_api_key">
        <button class="rf_add_api_key button button-primary button-large">Add your API Key</button>
    <div class="rf_widget_breaker"></div>
        <div><a href="http://RewardsFuel.com" class="btn btn-default get-api-key" role="button">Get your API key</a></div>
        <div id="rf_news"></div>
</div>

    <script>
        var rf_call_home = "http://app.rewardsfuel.com/api/wp/";
        var rf_version_id = "<?php echo($rf_version); ?>";
        var rf_api_key = "no_key";
    </script>

</div>
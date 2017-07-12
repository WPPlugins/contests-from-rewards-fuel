<div>
    <h2>Create a contest</h2>
    <button id="add_rf_contest" class="btn button-primary">Create new contest</button>
</div>
<div id="rf_widget_container">
    <h2>You have a valid API Key</h2>
    <script>
        var rf_call_home = "http://app.rewardsfuel.com/api/wp/";
        var rf_api_key = "<?php echo($api_key); ?>";
        var rf_version_id = "<?php echo($rf_version); ?>";

    </script>

<div id="rf_widget">

    <div>
        <p>Your currently access Rewards fuel via your api key:</p>
        <input id="rf_api_key" value="<?php echo($api_key); ?>" type="text">
        <button id="rf_remove_api_key" class="btn button-primary">Remove key</button>
        <button id="rf_edit_api_key" class="btn button-primary">Edit key</button>
    </div>
    <div id="rf_help"><a href="http://rewardsfuel.com/wordpress/?ref=wp_plugin" target="_blank">For instruction on how to use this better click here.</a> </div>
    <div id="rf_news"></div>
</div>

</div>

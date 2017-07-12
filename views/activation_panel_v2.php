<div class="rf_activation_panel">
    <div class="rf_activation_panel_active">
        <div class="sign_in_tabs">
            <span class="title"><img src="//cdn.rewardsfuel.com/assets/images/small-wp-logo.png" width="24" height="24" class="activation_logo">Activate Contests by Rewards Fuel</span>
            <a class="sign_in" href="#" data-text="Sign in">Sign in</a>
            <a class="sign_in active" href="#"  data-text="Sign up">Sign up</a>
            <a href="#" class="show_why"><i class="fa fa-question" aria-hidden="true"></i></a>
            <div class="why_sign_up_in" hidden>Rewards Fuel requires you to sign in so that we can help you run contests.  The contests which you create and place on your site run on our servers and require us to connect to other services like Facebook, Instagram and Twitter.  To use this plugin you will need to sign up for a free Rewards Fuel account, no credit card or trial period needed. You will get instant access to our contest creation software as well as several free features, plus the option to purchase additional paid features if required or needed.</div>
        </div>
        <div class="sign_in_screen">
            <div class="message_holder_area" hidden></div>
            <div class="email_holder">
                <form class="rf_sign_in_form" id="rf_sign_in_form">
                <input type="email" class="rf_email_box" placeholder="Email" name="email">
                <input type="password"  class="rf_pass_box" placeholder="Password" name="password">
                <button class="activate_button" type="button"><span class="sign_label">Sign up</span></button>
                </form>
                <div class="clear_fix"></div>
                <div class="sign_up_link_holder"></div>
                <div class="forgot_pass_area" hidden><a href="#" class="forgot_pass" >Forgot your password?</a></div>
                <div class="show_or">Or <span class="sign_label">Sign up</span> with </div>
            </div>
            <div class="fb_holder"><a href="<?php echo($rf_api_home."sign_in_social/?t=f&au=".urlencode(admin_url( "options-general.php?page=rewards_fuel_contests"))); ?>" class="connect_button" target="_blank"><span class="sign_label">Sign up</span> with Facebook <i class="fa fa-facebook" aria-hidden="true"></i></a> </div>
            <div class="twitter_holder"><a href="<?php echo($rf_api_home."sign_in_social/?t=t&au=".urlencode(admin_url( "options-general.php?page=rewards_fuel_contests"))); ?>" class="connect_button" target="_blank"><span class="sign_label">Sign up</span> with Twitter <i class="fa fa-twitter" aria-hidden="true"></i></a> </div>
        </div>
        <div class="clear_fix"></div>
    </div>
    <div class="rf_activation_panel_loading" hidden><i class="fa fa-circle-o-notch fa-spin fa-fw"></i> Loading..</div>

    <div class="privacy_link"><a href="http://rewardsfuel.com" target="_blank">Visit RewardsFuel.com</a> | <a href="http://rewardsfuel.com/about.php" target="_blank">About</a> | <a href="http://rewardsfuel.com/privacy" target="_blank">Privacy policy</a> </div>
</div>
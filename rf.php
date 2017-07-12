<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @package Contests by Rewards_Fuel
 * @version 1
 */
/*
Plugin Name: Contests by Rewards Fuel
Plugin URI: https://RewardsFuel.com
Description: This plugin helps with contests and promotions run on RewardsFuel.com, specifically this version helps with contest embedding and utilizing the entry method of counting WordPress comments as entries into a giveaway.
Author: Rewards Fuel
Version: 1.3.7
*/
/*
error_reporting(E_ALL);
ini_set('display_errors', true);
*/
///////////////REGISTER WP HOOKS AND ACTIONS///////////////////////////////////////////////////////

//hook for approved comments
add_action('transition_comment_status', 'rf_approve_comment_callback', 10, 3);
//hook for new comments
add_action('comment_post', 'rf_new_comment_callback', 10, 1);
// hook for javascript for embedding contests

//hook for changing short code to div
add_shortcode('rf_contest', 'rf_embed_func');
add_shortcode('RF_CONTEST', 'rf_embed_func'); //old name - will remove shortly..
add_shortcode('RF_CONTEST_ITC', 'rf_itc_embed_func');
//adds widget to post page
add_action('add_meta_boxes', 'rf_add_widget');

//add ajax handler
add_action( 'wp_ajax_rf_ajax_handler', 'rf_ajax_handler' );


function rf_ajax_handler(){
    $capability = "edit_posts";
    if (current_user_can($capability)) {
        $rf = new Rewards_Fuel();
        ///api key handlers
        if (isset($_REQUEST["rewards_fuel_add_api_key"])) {
            $rf->register_api_key($_REQUEST["rewards_fuel_add_api_key"]);
            wp_die();
        }
        if (isset($_REQUEST["rewards_fuel_get_api_key"])) {
            header("content-type: application/json");
            echo(json_encode($rf->get_api_key()));
            wp_die();
        }
        if (isset($_REQUEST["remove_rewards_fuel_api_key"])) {
            $rf->remove_api_key();
            wp_die();
        }
        if (isset($_REQUEST["update_rewards_fuel_api_key"])) {
            $rf->update_api_key($_REQUEST["update_rewards_fuel_api_key"]);
            wp_die();
        }
        if (isset($_REQUEST["add_comment_entry"])) {
            $result = $rf->add_comment_entry_to_post($_REQUEST["rf_a"], $_REQUEST["rf_e"], $_REQUEST["post_id"]);
            header("content-type: application/json");
            echo(json_encode(array("result" => $result)));
            wp_die();
        }
        if (isset($_REQUEST["edit_comment_entry"])) {
            $result = $rf->edit_comment_entry_to_post($_REQUEST["rf_a"], $_REQUEST["rf_e"], $_REQUEST["post_id"]);
            header("content-type: application/json");
            echo(json_encode(array("result" => $result)));
            wp_die();
        }
        if (isset($_REQUEST["remove_comment_entry"])) {
            $result = $rf->remove_comment_entry_to_post($_REQUEST["post_id"]);
            header("content-type: application/json");
            echo(json_encode(array("result" => $result)));
            wp_die();
        }
        if (isset($_REQUEST["get_comment_entry_id"])) {
            $entry_method_id = $rf->get_post_entry_method_id($_REQUEST["get_comment_entry_id"]);
            $entry_method_key = $rf->get_post_entry_method_key($_REQUEST["get_comment_entry_id"]);
            header("content-type: application/json");
            echo(json_encode(array("entry_id" => $entry_method_id, 'entry_method_key' => $entry_method_key)));
            wp_die();
        }
        if (isset($_REQUEST["update_display"])) {
           $rf->update_widget_area($_REQUEST["update_display"]);
        }
    }
    wp_die();
}
///////////////REGISTER WP HOOKS AND ACTIONS///////////////////////////////////////////////////////

$rf_version = 1;
//api key stuff
$rewards_fuel = new Rewards_Fuel();
add_action( 'admin_notices', array( $rewards_fuel, 'rf_admin_notice' )  );
add_action( 'admin_menu', 'rewards_fuel_plugin_menu' );

///////////Menu functions////////////////////////////////////////////////////
/** Step 1. */
function rewards_fuel_plugin_menu() {
    add_options_page( 'Rewards Fuel Contests Plugin Options', 'Contests by Rewards Fuel', 'manage_options', 'rewards_fuel_contests', 'rewards_fuel_plugin_options' );
}
function rewards_fuel_plugin_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $rf = new Rewards_Fuel();
    $current_key = $rf->get_api_key();
    if (isset($_GET["add_key"]) && $current_key == false){
        //set the key
        $rf->register_api_key($_GET["add_key"]);
        echo("<style>.rf-activation-panel{display: none;}</style>");
    }
    wp_enqueue_script( $handle = "rf_st",  "https://ws.sharethis.com/button/buttons.js", false, $ver = '1', $in_footer = false);
    $rf->rewards_fuel_print_settings_widget();
}
function rewards_fuel_plugin_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=rewards_fuel_contests">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$rewards_fuel_plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$rewards_fuel_plugin", 'rewards_fuel_plugin_settings_link' );
///////////Comment entry/////////////////////////////////////////////////////

function rf_approve_comment_callback($new_status, $old_status, $comment)
{
    $rf = new Rewards_Fuel();
    $post_id = $comment->comment_post_ID;
    $entry_method_id = $rf->get_post_entry_method_id($post_id);
    $api_key = $rf->get_post_entry_method_key($post_id);
    if ($entry_method_id > 0) {
        $comment_data = array(
            'entry_method_id' => $entry_method_id,
            'api_key' => $api_key,
            'comment_id' => $comment->comment_ID,
            'comment_post_id' => $post_id,
            'comment_status' => $new_status,
            'comment_author' => $comment->comment_author,
            'comment_author_email' => $comment->comment_author_email,
            'comment_author_url' => $comment->comment_author_url,
            'comment_author_IP' => $comment->comment_author_IP,
            'comment_date_gmt' => $comment->comment_date_gmt,
            'comment_karma' => $comment->comment_karma,
            'comment_agent' => $comment->comment_agent,
            'comment_content' => $comment->comment_content
        );

        if ($new_status == 'approved') {
            $rf->add_comment_entry($comment_data);
        }

        if ($new_status == 'unapproved') {
            $rf->remove_comment_entry($comment_data);
        }

    }
}


//function for new comments - had to create two function as both hooks have different variables they supply
function rf_new_comment_callback($comment_id)
{
    $rf = new Rewards_Fuel();
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    $entry_method_id = $rf->get_post_entry_method_id($post_id);
    $api_key = $rf->get_post_entry_method_key($post_id);
    if ($entry_method_id > 0) {
        if ((bool)$comment->comment_approved) {
            $new_status = 'approved';
        }
        else {
            $new_status = 'unapproved';
        }
        $comment_data = array(
            'entry_method_id' => $entry_method_id,
            'api_key' => $api_key,
            'comment_id' => $comment->comment_ID,
            'comment_post_id' => $post_id,
            'comment_status' => $new_status,
            'comment_author' => $comment->comment_author,
            'comment_author_email' => $comment->comment_author_email,
            'comment_author_url' => $comment->comment_author_url,
            'comment_author_IP' => $comment->comment_author_IP,
            'comment_date_gmt' => $comment->comment_date_gmt,
            'comment_karma' => $comment->comment_karma,
            'comment_agent' => $comment->comment_agent,
            'comment_content' => $comment->comment_content
        );

        if ($new_status == 'approved') {
            $rf->add_comment_entry($comment_data);
        }

        if ($new_status == 'unapproved') {
            $rf->remove_comment_entry($comment_data);
        }

    }
}


///////////CRUD for comment entry/////////////////////////////////////////////////////

///////////SHORT CODE EMBEDDING/////////////////////////////////////////////////////

//function to create short code for embedding contest
function rf_embed_func($atts)
{
    $rf = New Rewards_Fuel();
    $contest_id = $atts['contest'];
    $embed = $rf->embed_js($contest_id);
    return ("$embed<a href='https://rewardsfuel.com' class='rewardsfuel-contest' data-contest-key='$contest_id'>".rewards_fuel_get_random_link()."</a>");
}
function rf_itc_embed_func($atts)
{
    $rf = New Rewards_Fuel();
    $contest_id = $atts['contest'];
    $embed = $rf->embed_itc_js($contest_id);
    return ("$embed<a href='https://rewardsfuel.com' class='itc-rewardsfuel-contest' data-contest-key='$contest_id'>".rewards_fuel_get_random_link()."</a>");
}

//its free so we want a little link love when you embed our contests.
function rewards_fuel_get_random_link(){
    $tmp_array = array(
        0 =>"Contests by Rewards Fuel",
        1=>"Social media contests by Rewards Fuel",
        2=>"Instagram contests by Rewards Fuel",
        3=>"Facebook contests by Rewards Fuel",
        4=>"WordPress Contests by Rewards Fuel",
        5=>"Contest powered by Rewards Fuel",
        6=>"Rewards Fuel social media contests",
        7=>"Free contest software by Rewards Fuel",
        8=>"Twitter contests by Rewards Fuel"
    );
    $lucky_number = rand(0,8);
    return $tmp_array[$lucky_number];
}


///////////SHORT CODE EMBEDDING/////////////////////////////////////////////////////

//////////Widget functions///////////////////////////////////////////

function rf_add_widget()
{
    $id = "RF_Widget";
    $title = "Add a contest / giveaway to your blog via Rewards Fuel.";
    $callback = "rf_print_widget";
    $post_type = "post";
    $context = "normal";
    $priority = "default";
    $callback_args = "";
    add_meta_box($id, $title, $callback, $post_type, $context, $priority, $callback_args);
}

function rf_print_widget($post)
{
    $deps = false; //used for dependencies given as an array
    $src = plugins_url("contests-from-rewards-fuel/js/post_panel.js");
    wp_enqueue_script( $handle = "post_panel",  $src, $deps, $ver = '1', $in_footer = false);
    $src = plugins_url("contests-from-rewards-fuel/js/activated_panel.js");
    wp_enqueue_script( $handle = "rf_activated_panel",  $src, $deps, $ver = '1', $in_footer = false);
    $src = plugins_url("contests-from-rewards-fuel/js/activation_panel.js");
    wp_enqueue_script( $handle = "rf_activation_panel",  $src, $deps, $ver = '1', $in_footer = false);

    $src = plugins_url("contests-from-rewards-fuel/css/rf_style.css");
    wp_enqueue_style( $handle = "rf_style", $src, $deps, $ver  = '1', $media ="all");
    wp_enqueue_style( $handle = "font_awesome", $src = "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css", $deps, $ver  = '1', $media ="all");
    wp_enqueue_style( $handle = "rf_goog_roboto", $src="https://fonts.googleapis.com/css?family=Roboto:400,700,300,900italic,400italic", $deps,$ver='1', $media="all");
    wp_enqueue_style( $handle = "rf_animate", $src="//cdn.rewardsfuel.com/assets/css/animate.css", $deps,$ver='1', $media="all");
    $rf = New Rewards_Fuel();
    $rf_api_home = $rf->rf_api_home;
    //$base_url = plugins_url() . "/Rewards-Fuel";
    $api_key = $rf->get_api_key();
    if ($api_key != false){$api_key = '\''.$api_key.'\'';}else{$api_key = 'false';}
    //$rf_version = $GLOBALS["rf_version"];
    echo('<script>var rf_call_home = "https://app.rewardsfuel.com/api/wp/"; var rf_post_id = "'.$post->ID.'"; var rf_api_key='.$api_key.';</script>');
    echo ("<div id='rf_post_panel_container'>");
    if ($api_key != 'false') {//if key exists show scree
        include "views/activated_panel.php";
    } else {//else show add key form
        include "views/activation_panel_v2.php";
    }
    echo ("</div >");

}

//////////Widget functions///////////////////////////////////////////



class Rewards_Fuel
{

    public $rf_api_home;
    public function __construct()
    {
        $this->rf_api_home = "https://app.rewardsfuel.com/api/wp/";
        //error_reporting(E_ALL);
        //ini_set('display_errors', true);
    }

    private $api_key_option_name = "rewards_fuel_api_key";

    public function register_api_key($api_key)
    {
        if (update_option($this->api_key_option_name, $api_key)) {
            //echo("success");
        }
        else {
            //echo("failure");
        }
    }


    //gets api key or false if it doesn't exist
    public function get_api_key()
    {
        return get_option($this->api_key_option_name, false);
    }

    public function remove_api_key()
    {
        return delete_option($this->api_key_option_name);
    }

    public function update_api_key($new_api_key)
    {
        return update_option($this->api_key_option_name, $new_api_key);
    }

    public function add_comment_entry_to_post($entry_method_key, $entry_method_id, $post_id)
    {
        $results = add_post_meta($post_id, "rf_a", $entry_method_key, true);
        $results2 = add_post_meta($post_id, "rf_e", $entry_method_id, true);
        if (!($results && $results2)){ // if already set try updating them.
            $results = update_post_meta($post_id, "rf_a", $entry_method_key);
            $results2 = update_post_meta($post_id, "rf_e", $entry_method_id);
        }
        return ($results && $results2 );
    }

    public function edit_comment_entry_to_post($entry_method_key, $entry_method_id, $post_id)
    {
        $results = update_post_meta($post_id, "rf_a", $entry_method_key);
        $results2 = update_post_meta($post_id, "rf_e", $entry_method_id);
        return ($results && $results2);
    }

    public function remove_comment_entry_to_post($post_id)
    {
        $results = delete_post_meta($post_id, "rf_a");
        $results2 = delete_post_meta($post_id, "rf_e");
        return ($results && $results2);
    }


    //function to get the post's entry method id
    function get_post_entry_method_id($post_id)
    {
        $key = 'rf_e';
        $themeta = get_post_meta($post_id, $key, TRUE);
        if ($themeta != '') {
            return $themeta;
        } else {
            return 0;
        }
    }

    //function for getting the api key for this entry method
    function get_post_entry_method_key($post_id)
    {
        $key = 'rf_a';
        $themeta = get_post_meta($post_id, $key, TRUE);
        if ($themeta != '') {
            return $themeta;
        } else {
            return 0;
        }
    }

    //function for adding needed javascript
    function embed_js($contest_id)
    {
        $js_embed = "<script type='text/javascript'>(function() { var se = document.createElement('script'); se.type = 'text/javascript'; se.async = true; se.src = '//cdn.rewardsfuel.com/embed.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(se, s); })(); </script>";
        $js_embed .= "<noscript><a href='//win.rewardsfuel.com/$contest_id'>Sorry you need JavaScript enabled to embed this contest.  Click here to visit the contest page.</a></noscript>";
        return $js_embed;
    }
    function embed_itc_js($contest_id)
    {
        $js_embed = "<script type=\"text/javascript\">(function() {var se = document.createElement(\"script\"); se.type = \"text/javascript\"; se.async = true;se.src = \"https://cdn.rewardsfuel.com/assets/embed_itc.js\";var s = document.getElementsByTagName(\"script\")[0]; s.parentNode.insertBefore(se, s)})();</script>";
        $js_embed .= "<noscript><a href='https://itc.rewardsfuel.com/i/$contest_id'>Sorry you need JavaScript enabled to embed this contest.  Click here to visit the contest page.</a></noscript>";
        return $js_embed;
    }


    //function to send api request to rewards fuel for potentially new comment
    function add_comment_entry($arg_array)
    {
        $url = $this->rf_api_home."wp_comment_entry/";
        $qs = http_build_query($arg_array);
        $url .= "?" . $qs;
        $handle = curl_init($url);
        curl_exec($handle);
        curl_close($handle);
    }

//function to send api request to rewards fuel for potentially removed comment
    function remove_comment_entry($arg_array)
    {
        $url = $this->rf_api_home."wp_remove_comment_entry/";
        $qs = http_build_query($arg_array);
        $url .= "?" . $qs;
        $handle = curl_init($url);
        curl_exec($handle);
        curl_close($handle);
    }

    function rewards_fuel_print_settings_widget(){
        if (isset($_GET["add_key"])){
            //add key to WP
        }
        $api_key = $this->get_api_key();
        $rf_version = $GLOBALS["rf_version"];
        $src = plugins_url("contests-from-rewards-fuel/js/rf.js");
        wp_enqueue_script( $handle = "rf_script",  $src, $deps=false, $ver = '2', $in_footer = false);
        $rf_api_home = $this->rf_api_home;
        $src = plugins_url("contests-from-rewards-fuel/css/rf_style_settings_page.css");
        wp_enqueue_style( $handle = "rf_style", $src, $deps= false, $ver  = '1', $media ="all");
        if ($api_key != false) {//if key exists show screen
            include "views/activated_settings_page.php";
        } else {//else show add key form
            $sign_up_data = $this->hash_user_data();
            $src = plugins_url("contests-from-rewards-fuel/js/activation_panel.js");
            wp_enqueue_script( $handle = "rf_activation_script",  $src, $deps, $ver = '1', $in_footer = false);
            $src = plugins_url("contests-from-rewards-fuel/js/post_panel.js");
            wp_enqueue_script( $handle = "rf_activation_script_post_panel",  $src, $deps, $ver = '1', $in_footer = false);
            include "views/not_activated_settings_page.php";
        }

        wp_enqueue_style( $handle = "font_awesome", $src = "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css", $deps, $ver  = '1', $media ="all");

    }

    public function rf_admin_notice() {
        $api_key = $this->get_api_key();
        if ((String)$api_key == "" && !(isset($_COOKIE["rf_activation_dismissed"])) ){//include cookied dismissable notice
            //if no API key nag for activation

            $deps = false; //used for dependencies given as an array
            $src = plugins_url("contests-from-rewards-fuel/css/rf_style.css");
            wp_enqueue_style( $handle = "rf_style", $src, $deps, $ver  = '1', $media ="all");
            $src = plugins_url("contests-from-rewards-fuel/js/rf.js");
            wp_enqueue_script( $handle = "rf_script",  $src, $deps, $ver = '1', $in_footer = true);
            wp_enqueue_style( $handle = "font_awesome", $src = "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css", $deps, $ver  = '1', $media ="all");
            $ud = $this->hash_user_data();
            include "views/activation_panel.php";
        }
    }

    public function hash_user_data(){
        $current_user = wp_get_current_user();
        $data = "admin_url=".urlencode(admin_url( "options-general.php?page=rewards_fuel_contests"))."&first_name=".$current_user->user_firstname."&last_name=".$current_user->user_lastname."&email_address=".$current_user->user_email;
        //$crypt = $this->simple_encrypt($data);
        return urlencode($data);
    }


    public function update_widget_area(){
        $key = $this->get_api_key();
        $rf_api_home = $this->rf_api_home;
        $base_url = plugins_url() . "/Rewards-Fuel";
        if ($key != false){//case 1 we have and api key
            include "views/activated_panel.php";
        }
        else{//case 2 we do not have an api key
            include "views/activation_panel_v2.php";
        }
    }



}


?>
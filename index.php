<?php
/*
Plugin Name: Hide My Site
Description: Choose a single password to protect your entire wordpress site. Only visitors who know the password will be able to access your wordpress site. This is a great tool for someone setting up a development version of a wordpress site or anyone else looking to hide their site from the public, search engines, etc...Set your site-wide password by going to <strong>Settings > Hide My Site > Set Your Password</strong>. If you want to disable password protection uncheck the box at <strong>Settings > Hide My Site > Enable Password Protection</strong>.
Version: 1.0
Author: Justin Saad
Author URI: http://www.clevelandwebdeveloper.com
License: GPL2
*/

$plugin_label = "Hide My Site";
$plugin_slug = "hide_my_site";

class hide_my_site{
	
	//define variables
	var $plugin_label = "Hide My Site";
	var $plugin_slug = "hide-my-site";
	
    public function __construct(){
    	
		global $plugin_label, $plugin_slug;
		$this->plugin_slug = $plugin_slug;
		$this->plugin_label = $plugin_label;
		global $pagenow; 	
		if( (!is_admin()) AND ($pagenow!='wp-login.php') AND (get_option($this->plugin_slug.'_enabled', 1) == 1) AND (get_option($this->plugin_slug.'_password')) ) { //public site and plugin enabled with password set
			add_action('plugins_loaded', array($this, 'verify_login')); //hooks into plugins_loaded. one of the earliest functions in wordpress
		}
		
        if(is_admin()){
		    add_action('admin_menu', array($this, 'add_plugin_page'));
		    add_action('admin_init', array($this, 'page_init'));
			//add admin notices
			add_action( 'admin_notices', array($this, 'admin_notices') );
			//add Settings link to plugin page
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'add_plugin_action_links') );
			add_filter( 'plugin_row_meta', array($this,'plugin_row_links'), 10, 2 );
		}
		
    }
	public function get_cookie_name(){
		$name = $this->plugin_slug . "-access";
		return $name ;
	}
	public function get_cookie_duration(){
		return get_option($this->plugin_slug.'_duration', 1)*(86400);
	}
    public function verify_login(){

		//set access cookie if password is correct
	 	if ((isset($_POST['hwsp_motech']) AND ($_POST['hwsp_motech'] != "")) AND ($_POST['hwsp_motech'] == get_option($this->plugin_slug.'_password'))) {
    		setcookie($this->get_cookie_name(), 1, time()+$this->get_cookie_duration());
			$cookie_just_set = 1;
		}
		if( ($_COOKIE[$this->get_cookie_name()] != 1) AND ($cookie_just_set != 1) ) {
				// This is the login page for the public
				echo "
					<style>
						html, body {

							text-align: center;
							height: 100%;
						}
						body { background: url(".plugins_url( 'images/bg_dot.png' , __FILE__ ).") rgba(111, 122, 151, 0.28); font-family:Arial;}
						#form_wrap { background: url(".plugins_url( 'images/login_bg.png' , __FILE__ ).") no-repeat;display: block;margin: 0px auto;height: 450px;width: 403px; position: relative;top: 50%;margin-top: -225; }
						#form_wrap input[type=text], .enter_password {background: url(".plugins_url( 'images/input_back.png' , __FILE__ ).") no-repeat; position: absolute;top: 159px;left: 50px;
							border: 0px;
							width: 313px;
							padding-left: 50px;
							font-size: 15px;
							line-height: 15px;
							padding-top: 9px;
							height:62px;
							color:rgb(85, 86, 90);
							opacity:.8;
						}

						#form_wrap input:active, #form_wrap input:focus {outline:0;opacity:1;}
						#form_wrap button {background: url(".plugins_url( 'images/login_button.png' , __FILE__ ).") no-repeat top; width: 316px;
							border: 0px;
							height: 85px;
							position: absolute;
							top: 257px;
							left: 43px;
							cursor:pointer; opacity:.7;
						}
						#form_wrap button:hover {opacity:.8}
						#form_wrap button:focus, #form_wrap button:active { opacity:1;}
					</style>
						<!--[if IE]>
						<style>
						#form_wrap input[type=text], .enter_password {
						  line-height:50px;    /* adjust value */
						}
						</style>
						<![endif]-->
					<body>
						<div id='form_wrap'>
							<form method=post>
								<input type=text  name='hwsp_motech' placeholder='Password' class='enter_password'>
								<button type=submit></button>
							</form>
						</div>
					</body>
				";
				exit;
		}
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
		add_options_page('Settings Admin', $this->plugin_label, 'manage_options', $this->plugin_slug.'-setting-admin', array($this, 'create_admin_page'));
    }
	
    public function print_section_info(){ //section summary info goes here
		//print 'This is the where you set the password for your site.';
    }

    public function get_donate_button(){ ?>
	<style type="text/css">
	.motechdonate{border: 1px solid #DADADA; background:white; font-family: tahoma,arial,helvetica,sans-serif;font-size: 12px;overflow: hidden;padding: 5px;position: absolute;right: 0;text-align: center;top: 0;width: 160px; box-shadow:0px 0px 8px rgba(153, 153, 153, 0.81);}
	.motechdonate form{display:block;}
	</style>
    <div class="motechdonate">
        <div style="overflow: hidden; width: 161px; text-align: center;">
        <div style="overflow: hidden; width: 161px; text-align: center; float: left;"><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input name="cmd" value="_s-xclick" type="hidden"><input name="hosted_button_id" value="9TL57UDBAB7LU" type="hidden"><input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image"> <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" border="0" height="1" width="1"></form></div>
        If you enjoy or find any of my plugins useful, please donate a few dollars to my company The Motech Network to help with future development and updates. Thanks in advance.			</div>
	</div>    
    
    <?php

    }

    public function create_admin_page(){
        ?>
		<div class="wrap" style="position:relative">
        	<?php $this->get_donate_button() ?>
		    <?php screen_icon(); ?>
		    <h2><?php echo $this->plugin_label ?></h2>			
		    <form method="post" action="options.php">
		        <?php
	            // This prints out all hidden setting fields
			    settings_fields($this->plugin_slug.'_option_group');	
			    do_settings_sections($this->plugin_slug.'-setting-admin');
			?>
		        <?php submit_button(); ?>
		    </form>
		</div>
	<?php
    }
	
    public function page_init(){
		
        add_settings_section(
	    $this->plugin_slug.'_setting_section',
	    'Configuration',
	    array($this, 'print_section_info'),
	    $this->plugin_slug.'-setting-admin'
		);	
		
		//add checkbox field
		$field_slug = "enabled";
		$field_label = "Enable Password Protection";
		$field_id = $this->plugin_slug.'_'.$field_slug;
		register_setting($this->plugin_slug.'_option_group', $field_id);
		add_settings_field(
		    $field_id,
		    $field_label, 
		    array($this, 'create_a_checkbox'), //callback function for checkbox
		    $this->plugin_slug.'-setting-admin',
		    $this->plugin_slug.'_setting_section',
		    array(								// The array of arguments to pass to the callback.
				"id" => $field_id, //sends field id to callback
				"desc" => 'Check this box to enable site-wide password protection.', //description of the field (optional)
				"default" => '1' //sets the default field value (optional), when grabbing this option value later on remember to use get_option(option_name, default_value) so it will return default value if no value exists yet
				
			)			
		);
	
		//add text input field
		$field_slug = "password";
		$field_label = "Set Your Password";
		$field_id = $this->plugin_slug.'_'.$field_slug;
		register_setting($this->plugin_slug.'_option_group', $field_id);
		add_settings_field(
		    $field_id,
		    $field_label, 
		    array($this, 'create_a_text_input'), //callback function for text input
		    $this->plugin_slug.'-setting-admin',
		    $this->plugin_slug.'_setting_section',
		    array(								// The array of arguments to pass to the callback.
				"id" => $field_id, //sends field id to callback
				"desc" => 'Choose a password for your site. Only visitors who know this password will be able to access your site.', //description of the field (optional)
			)			
		);
		
		//add text input field
		$field_slug = "duration";
		$field_label = "Duration (in days):";
		$field_id = $this->plugin_slug.'_'.$field_slug;
		register_setting($this->plugin_slug.'_option_group', $field_id);
		add_settings_field(
		    $field_id,
		    $field_label, 
		    array($this, 'create_a_text_input'), //callback function for text input
		    $this->plugin_slug.'-setting-admin',
		    $this->plugin_slug.'_setting_section',
		    array(								// The array of arguments to pass to the callback.
				"id" => $field_id, //sends field id to callback
				"desc" => 'For how many days do you want the user to stay logged in?', //description of the field (optional)
				"default" => '1' //sets the default field value (optional), when grabbing this option value later on remember to use get_option(option_name, default_value) so it will return default value if no value exists yet
			)			
		);
	
	//add radio option
	//$option_id = "status";
	//add_settings_field($option_id, 'Status', array($this, 'create_radio_field'), 'wordpresshidesite-setting-admin', 'setting_section_id', array("option_id" => $option_id));
			
    }

	/**
	 * This following set of functions handle all input field creation
	 * 
	 */
	function create_a_checkbox($args) {
		$html = '<input type="checkbox" id="'  . $args[id] . '" name="'  . $args[id] . '" value="1" ' . checked(1, get_option($args[id], $args["default"]), false) . '/>'; 
		
		// Here, we will take the desc argument of the array and add it to a label next to the checkbox
		$html .= '<label for="'  . $args[id] . '"> '  . $args[desc] . '</label>'; 
		
		echo $html;
		
	} // end create_a_checkbox
	
	function create_a_text_input($args) {
		//grab placeholder if there is one
		if($args[placeholder]) {
			$placeholder_html = "placeholder=\"".$args[placeholder]."\"";
		}		
		// Render the output
		echo '<input type="text" '  . $placeholder_html . ' id="'  . $args[id] . '" name="'  . $args[id] . '" value="' . get_option($args[id], $args["default"]) . '" />';
		if($args[desc]) {
			echo "<p class='description'>".$args[desc]."</p>";
		}
		
	} // end create_a_text_input
	
	function create_a_textarea_input($args) {
		//grab placeholder if there is one
		if($args[placeholder]) {
			$placeholder_html = "placeholder=\"".$args[placeholder]."\"";
		}	
		// Render the output
		echo '<textarea '  . $placeholder_html . ' id="'  . $args[id] . '"  name="'  . $args[id] . '" rows="5" cols="50">' . get_option($args[id], $args["default"]) . '</textarea>';
		if($args[desc]) {
			echo "<p class='description'>".$args[desc]."</p>";
		}		
	}
	
	function create_a_radio_input($args) {
	
		$radio_options = $args[radio_options];
		$html = "";
		if($args[desc]) {
			$html .= $args[desc] . "<br>";
		}
		foreach($radio_options as $radio_option) {
			$html .= '<input type="radio" id="'  . $args[id] . '_' . $radio_option[value] . '" name="'  . $args[id] . '" value="'.$radio_option[value].'" ' . checked($radio_option[value], get_option($args[id], $args["default"]), false) . '/>';
			$html .= '<label for="'  . $args[id] . '_' . $radio_option[value] . '"> '.$radio_option[label].'</label><br>';
		}
		
		echo $html;
	
	} // end create_a_radio_input callback

	function create_a_select_input($args) {
	
		$select_options = $args[select_options];
		$html = "";
		if($args[desc]) {
			$html .= $args[desc] . "<br>";
		}
		$html .= '<select id="'  . $args[id] . '" name="'  . $args[id] . '">';
			foreach($select_options as $select_option) {
				$html .= '<option value="'.$select_option[value].'" ' . selected( $select_option[value], get_option($args[id], $args["default"]), false) . '>'.$select_option[label].'</option>';
			}
		$html .= '</select>';
		
		echo $html;
	
	} // end create_a_select_input callback
	

	/**
	 * Add admin notices logic
	 */
	
	public function admin_notices() {
		global $current_user;
		$userid = $current_user->ID;
		global $pagenow;
		
		// This notice will only be shown if no data entered for required input
		//check input field based on field slug
		$field_slug = "password";
		//check if plugin is enabled
		if((!(get_option($this->plugin_slug.'_'.$field_slug)) AND (get_option($this->plugin_slug.'_enabled', 1, false) == 1) )) {
			echo '
				<div class="updated">
					<p><strong>'.$this->plugin_label.' is almost ready.</strong> You must <a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page='.$this->plugin_slug.'-setting-admin">set your password</a> for it to work.</p>
				</div>';
		}
		
	
	}
	
	//add plugin action links logic
	function add_plugin_action_links( $links ) {
	 
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page='.$this->plugin_slug.'-setting-admin">Settings</a>'
			),
			$links
		);
	 
	}
	
	public function plugin_row_links($links, $file) {
		$plugin = plugin_basename(__FILE__); 
		if ($file == $plugin) // only for this plugin
				return array_merge( $links,
			array( '<a target="_blank" href="http://www.linkedin.com/in/ClevelandWebDeveloper/">' . __('Find me on LinkedIn' ) . '</a>' ),
			array( '<a target="_blank" href="http://twitter.com/ClevelandWebDev">' . __('Follow me on Twitter') . '</a>' )
		);
		return $links;
	}
	
	
		
} //end plugin class

$custom_plugin = new $plugin_slug();	
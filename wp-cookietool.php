<?php
/*
Plugin Name: WP Cookie Tool
Plugin URI: http://cmorales.es/descargas/wordpress-cookie-tool/
Description: Easy way to follow the EU Cookie directive, especially for Spain.
Version: 1.1
Author: Carlos Morales & Emilio Cobos
Author URI: http://cmorales.es/
*/

/**
 * Copyright (c) 2013 Carlos Morales & Emilio Cobos. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */




/*
Javascript work based on the work by Emilio Cobos: https://github.com/ecoal95/CookieTool
*/

define( 'WP_COOKIE_TOOL_PATH', plugin_dir_path(__FILE__) ); //Includes trailing slash!!!

class wp_cookie_tool {
	protected $textdomain = "wp-cookie-tool";
	protected $prefix = "wp_ct";
	protected $longname = "wp_cookie_tool";

    /**
     * Returns the options array
     *
     * @since WP Cookie Tool 1.0
     */
    function get_options() {
    	$saved = (array) get_option($this->longname . '_options');
    	$defaults = array(
    	                  'load_css' => 'on',
    	                  'link' => 'http://example.com/cookies',
    	                  'link_name' => 'Cookie Policy',
    	                  'panel_class' => 'cookietool-message-top',
    	                  'message' => __('The message you are going to show', $this->textdomain),
    	                  'agreetext' => __('I agree', $this->textdomain),
    	                  'declinetext' => __('I disagree', $this->textdomain),
                          'google_analytics' => false
                          );

    	$defaults = apply_filters($this->prefix. '_default_options', $defaults);

    	$options = wp_parse_args($saved, $defaults);
    	$options = array_intersect_key($options, $defaults);
    	return $options;
    }
}


class wp_cookie_tool_options extends wp_cookie_tool {
	function __construct() {
		add_action('admin_init', array(&$this, 'init'));
		add_action('admin_menu', array(&$this, 'add_menu_page'));
	}

	function add_menu_page() {
		$options_page = add_options_page(
                __('WP Cookie Tool', $this->textdomain), // Name of page (html title)
                __('WP Cookie Tool', $this->textdomain), // Label in menu
                'manage_options', // Capability required
                $this->longname . '_options_page', // Menu slug, used to uniquely identify the page
                array(&$this, 'render_page')
                );
	}

	function init() {
		register_setting(
                $this->longname . '_options', //Options name
                $this->longname . '_options', //DB entry
                array($this, 'validate') //Validate callback
                );

    // Register our settings field group
		add_settings_section(
                $this->longname . '_general', // Unique identifier for the settings section
                __('General', $this->textdomain), // Section title
                '__return_false', // Section callback (we don't want anything)
                $this->longname . '_options_page' // Menu slug, used to uniquely identify the page; see add_menu_page()
                );
        add_settings_section(
                $this->longname . '_scripts', // Unique identifier for the settings section
                __('Scripts', $this->textdomain), // Section title
                array(&$this, 'render_scripts_section'), // Section callback
                $this->longname . '_options_page' // Menu slug, used to uniquely identify the page; see add_menu_page()
                );


        //General fields
        add_settings_field(
                           $this->longname.'_load_css',
                           __('Load css?', $this->textdomain),
                           array($this, 'render_load_css_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_link',
                           __('Cookie policy page link', $this->textdomain),
                           array($this, 'render_link_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_link_name',
                           __('Cookie policy page title', $this->textdomain),
                           array($this, 'render_link_name_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_panel_class',
                           __('Panel class', $this->textdomain),
                           array($this, 'render_panel_class_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_message',
                           __('Message', $this->textdomain),
                           array($this, 'render_message_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_agreetext',
                           __('Agree text', $this->textdomain),
                           array($this, 'render_agreetext_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );
        add_settings_field(
                           $this->longname.'_declinetext',
                           __('Decline text', $this->textdomain),
                           array($this, 'render_declinetext_field'),
                           $this->longname . '_options_page',
                           $this->longname . '_general'
                           );

        /*** Scripts section ***/
        add_settings_field(
                           $this->longname.'_analytics_code',
                           __('Google Analytics Code', $this->textdomain),
                           array($this, 'render_google_analytics_code'),
                           $this->longname . '_options_page',
                           $this->longname . '_scripts'
                           );
    }

    /**
     * Renders the options page.
     *
     * @since WP Cookie Tool 1.0
     */
    function render_scripts_section() {
        ?>
        <p><?php _e('Some code, such as Google Analytics, needs to be modified to prevent it to store cookies before the user consent.', $this->textdomain) ?> <?php _e('The plugin can insert a modified version for you, or you can choose to do it for yourself.', $this->textdomain) ?></p>
        <p><?php _e('Currently, Google Analytics and AdSense are supported.', $this->textdomain) ?></p>
        <p><?php _e('For Google Analytics, you have to avoid loading the script, leaving just this:', $this->textdomain) ?></p>
        <pre>
             &lt;script type=&quot;text/javascript&quot;&gt;
            var _gaq = _gaq || [];
            _gaq.push([&#39;_setAccount&#39;, &#39;UA-XXXXX-Y&#39;]);
            _gaq.push([&#39;_trackPageview&#39;]);
            &lt;/script&gt;
        </pre>
        <p><?php _e('You can do it by yourself, or insert your code below and the plugin will do it for you.', $this->textdomain) ?></p>
        <p><?php _e('Regarding Google AdSense, you need to use the asynchronous version.', $this->textdomain) ?></p>
        <?php
    }

    /**
     * Renders the options page.
     *
     * @since WP Cookie Tool 1.0
     */
    function render_page() {
    	?>
    	<div class="wrap">

    		<h2><?php _e('WP Cookie Tool Settings', $this->textdomain) ?></h2>
    		<form method="post" action="options.php">
    			<?php
    			settings_fields($this->longname . '_options');
    			do_settings_sections($this->longname . '_options_page');
    			submit_button();
    			?>
    		</form>
    	</div>
    	<?php
    }

    /**
     * Renders the load css checkbox
     *
     * @since WP Cookie Tool 1.0
     */
    function render_load_css_field() {
    	$options = $this->get_options();
    	?>
    	<label for="load_css" class="description">
    		<input type="checkbox" name="<?php echo $this->longname?>_options[load_css]" id="load_css" <?php checked('on', $options['load_css']); ?> />
    		<?php _e('Uncheck if you don\'t want to include the default styles.', $this->textdomain); ?>
    	</label>
    	<?php
    }

    /**
     * Renders the input field for the link
     *
     * @since WP Cookie Tool 1.0
     */
    function render_link_field() {
    	$options = $this->get_options();
    	?>
    	<input type="url" name="<?php echo $this->longname?>_options[link]" id="link" value="<?php echo esc_attr($options['link']); ?>" />
        <p class="description"><?php _e('Place here your privacy/cookie policy page URL.', $this->textdomain); ?></p>
        <p class="description"><?php _e('You will need to place the [wp_ct-cookie-div] shortcode in that page.', $this->textdomain); ?></p>
        <?php
    }

    /**
     * Renders the input field for the link name
     *
     * @since WP Cookie Tool 1.0
     */
    function render_link_name_field() {
    	$options = $this->get_options();
    	?>
    	<input type="text" name="<?php echo $this->longname?>_options[link_name]" id="link" value="<?php echo esc_attr($options['link_name']); ?>" />
    	<p class="description"><?php _e('Place here your privacy/cookie policy title', $this->textdomain); ?></p>
    	<?php
    }
    /**
     * Renders the input field for the panel class
     *
     * @since WP Cookie Tool 1.0
     */
    function render_panel_class_field() {
    	$options = $this->get_options();
    	?>
    	<input type="text" name="<?php echo $this->longname?>_options[panel_class]" id="panel_class" value="<?php echo esc_attr($options['panel_class']); ?>" />
    	<p class="description"><?php _e('Panel\'s CSS class name.', $this->textdomain); ?></p>
    	<?php
    }
    /**
     * Renders the input field for the message
     *
     * @since WP Cookie Tool 1.0
     */
    function render_message_field() {
    	$options = $this->get_options();
    	?>
    	<input type="text" name="<?php echo $this->longname?>_options[message]" id="message" value="<?php echo esc_attr($options['message']); ?>" />
    	<p class="description"><?php _e('This is the text you show to your visitors.', $this->textdomain); ?></p>
    	<?php
    }
    /**
     * Renders the input field for the agreetext
     *
     * @since WP Cookie Tool 1.0
     */
    function render_agreetext_field() {
    	$options = $this->get_options();
    	?>
    	<input type="text" name="<?php echo $this->longname?>_options[agreetext]" id="agreetext" value="<?php echo esc_attr($options['agreetext']); ?>" />
    	<p class="description"><?php _e('Text to agree.', $this->textdomain); ?></p>
    	<?php
    }

    /**
     * Renders the input field for the declinetext
     *
     * @since WP Cookie Tool 1.0
     */
    function render_declinetext_field() {
    	$options = $this->get_options();
    	?>
    	<input type="text" name="<?php echo $this->longname?>_options[declinetext]" id="declinetext" value="<?php echo esc_attr($options['declinetext']); ?>" />
    	<p class="description"><?php _e('Text to decline.', $this->textdomain); ?></p>
    	<?php
    }


    /**
     * Renders the Google Analytics input setting field.
     */
    function render_google_analytics_code() {
        $options = $this->get_options();
        ?>
        <input type="text" name="<?php echo $this->longname?>_options[google_analytics]" id="google-analytics" value="<?php echo esc_attr($options['google_analytics']); ?>" />
        <label class="description" for="google-analytics"><?php _e('Your account code', $this->textdomain)?></label>
        <p class="description"><?php _e('Your account code looks like this: UA-12345678-9', $this->textdomain) ?></p>
        <p class="description"><?php _e('Leave blank if you don\'t use Google Analytics or if you will insert the code on your own.', $this->textdomain) ?></p>
        <?php
    }


    function validate($input) {
    	$output = array();

    	if (isset($input['load_css'])) {
    		$output['load_css'] = 'on';
    	} else {
    		$output['load_css'] = 'off';
    	}

    	if (isset($input['link'])) {
    		$output['link'] = esc_url_raw($input['link']);
    	}

    	if (isset($input['link_name'])) {
    		$output['link_name'] = esc_html($input['link_name']);
    	}

    	if (isset($input['panel_class'])) {
    		$output['panel_class'] = esc_attr($input['panel_class']);
    	}

    	if (isset($input['message'])) {
    		$output['message'] = esc_html($input['message']);
    	}

    	if (isset($input['agreetext'])) {
    		$output['agreetext'] = esc_html($input['agreetext']);
    	}

        if (isset($input['declinetext'])) {
            $output['declinetext'] = esc_html($input['declinetext']);
        }

        if (isset($input['google_analytics'])) {
          $output['google_analytics'] = esc_html($input['google_analytics']);
      }

      return apply_filters($this->longname . '_options_validate', $output, $input);
  }

}

class wp_cookie_tool_init extends wp_cookie_tool {

	function render_cookie_div() {
		return '<div id="cookietool-settings"></div>';
	}

	function __construct() {
		add_action('wp_enqueue_scripts', array(&$this, 'add_styles'));
		add_action('wp_footer', array(&$this, 'config_script'), 100);
		load_plugin_textdomain( $this->textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_shortcode('wp_ct-cookie-div', array(&$this, 'render_cookie_div'));
        add_action( 'wp_head', array(&$this, 'google_analytics'), 20);
    }

    function add_styles() {
      $options = $this->get_options();
      if ($options['load_css'] == "on") {
       wp_enqueue_style($this->prefix . 'css', plugins_url('/cookietool.css', __FILE__));
   }
   wp_enqueue_script( $this->prefix . 'js', plugins_url('/cookietool.js', __FILE__), false, false, true );
}

function config_script() {
  $options = $this->get_options();

  $config_array = array(
                        "link" => $options['link'],
                        "linkName" => $options['link_name'],
                        "panelClass" => $options['panel_class'],
                        "message" => $options['message'],
                        "agreetext" => $options['agreetext'],
                        "declinetext" => $options['declinetext'],
                        "agreeStatusText" => __('You currently <strong>allow</strong> cookies in this site. <button type="button" class="button-basic" data-action="decline">Click here to disallow cookies</button>', $this->textdomain),
                        "disagreeStatusText" => __('You currently <strong>disallow</strong> cookies in this site. <button type="button" class="button-basic" data-action="agree">Click here to allow cookies</button>', $this->textdomain),
                        "notSetText" => __('You haven\'t yet established your configuration. <button type="button" class="button-basic" data-action="agree">Click here to allow cookies</button> or <button type="button" class="button-basic" data-action="decline">click here to disallow cookies</button>', $this->textdomain)
                        );
                        ?>
                        <script type="text/javascript">
                        /* <![CDATA[ */
                        CookieTool.Config.set(<?php echo json_encode($config_array) ?>);
                        CookieTool.API.ask();
                        if( document.getElementById('cookietool-settings') ) {
                         CookieTool.API.displaySettings(document.getElementById('cookietool-settings'));
                     }
                     /* ]]> */
                     </script>
                     <?php
                 }

    /*
    Displays Google Analytics tracking code if it's set in the options, main script is not included, so we can delay cookie insertion
    */
    function google_analytics() {
        $options = $this->get_options();
        if (!empty($options['google_analytics'])):
            if (!is_user_logged_in()):
                ?>
            <script type="text/javascript">

            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '<?php echo esc_js($options["google_analytics"]) ?>']);
            _gaq.push(['_trackPageview']);

            </script>

            <?php
            endif;
            endif;
        }
    }

    new wp_cookie_tool_init;
    new wp_cookie_tool_options;



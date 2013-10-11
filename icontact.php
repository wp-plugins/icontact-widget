<?php
/*
Plugin Name: iContact Widget
Plugin URI: http://www.seodenver.com/icontact-widget/
Description: Add the iContact signup form to your sidebar and easily update the display settings & convert the form from Javascript to faster-loading HTML.
Version: 1.3
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com
*/

/*
Copyright 2013 Katz Web Services, Inc.  (email: info@katzwebservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(class_exists('WP_Widget') && function_exists('register_widget')) {

	add_action( 'widgets_init', 'kwd_load_widgets' );

	function kwd_load_widgets() {
		register_widget( 'iContactWidget' );
	}

	class iContactWidget extends WP_Widget {

		var $version = '1.3';

	 	function iContactWidget() {
	    	$control_options = array('width'=>400); // 600 px wide please
	        $widget_options = array('description'=>'Add an iContact form to your and start adding to your lists!', 'classname' => 'icontact');
	        parent::WP_Widget(false, $name = 'iContact Signup Form', $widget_options, $control_options);

	        // Putting this here so that we can use functions like is_ssl()
	        $this->defaults = array(
				'id' => false,
				'error' => false,
	    		'title' => null,
	    		'formcode' => '',
	    		'inputsize' => 20,
	    		'override' => false,
	    		'edited_code' => '',
	    		'https' => is_ssl(),
	    		'submittext' => 'Submit',
	    		'tablewidth' => 260,
	    		'hide' => false,
	    		'code' => array(),
	    		'initiated' => false,
	    		'target' => 'no',
	    		'redirect' => is_ssl() ? 'https://www.icontact.com/signup-thanks' : 'http://www.icontact.com/signup-thanks',
	    		'errorredirect' => is_ssl() ? 'https://www.icontact.com/icontact-error' : 'http://www.icontact.com/icontact-error'
	    	);

	        add_action('wp_print_styles', array(&$this, 'print_styles'));
	        add_action('wp_print_footer_scripts', array(&$this, 'print_scripts'));
	        add_action('admin_head', array(&$this, 'widget_scripts'), 9999);

	        // Implement the shortcodes
	        add_shortcode('iContact', array(&$this, 'shortcode'));
			add_shortcode('icontact', array(&$this, 'shortcode'));

	        // Instead of hard-coding this, I'm adding it as a filter to allow others to change it easily
	        add_action('icontact_widget_style', array(&$this, 'style_filter'));
	    }

		function style_filter($style) {
			return preg_replace('/(\n|\r)(?:\t+)?\./ism', '$1.iContactForm .', $style);
		}

		function print_scripts() {
			if(is_admin()) { return; }
			$settings = $this->get_settings();
			if(isset($settings[$this->number])) {
				$instance = $settings[$this->number];
			} else { return; }
			if(isset($instance['code']['script']) && (!isset($instance['override']) || $instance['override'] !== 'yes')) {
	 			$script = '
	 			<script type="text/javascript">
	 			'.trim($instance['code']['script']).'
	 			</script>'."\n";
	 			$script = apply_filters('icontact_widget_script', apply_filters('icontact_widget_script_'.$this->number, $script));
	 			echo trim($script)."\n";
	 		}
		}

		function print_styles() {
			if(is_admin()) { return; }
			$settings = $this->get_settings();
			if(isset($settings[$this->number])) {
				$instance = $settings[$this->number];
			} else { return; }

			if(isset($instance['code']['style']) && (!isset($instance['override']) || $instance['override'] !== 'yes')) {
	 			$style = '
	 			<style>
	 			'.trim($instance['code']['style']).'
	 			</style>';
	 			$style = apply_filters('icontact_widget_style', apply_filters('icontact_widget_style_'.$this->number, $style));
	 			echo trim($style)."\n";
	 		}
 		}

 		function widget_scripts() {
 			global $pagenow;
 			if($pagenow !== 'widgets.php') { return; }
				?>
<script>

	jQuery(document).ready(function($) {

		$('div[id*="icontactwidget"] a.toggle_formcode').click(function(e) {
			var href = $(this).attr('href');
			$(href).parents('div.'+$(href).attr('id')).slideToggle();
			return false;
		});

		$('input[name*="override"]').on('click change load ready', function(e) {
			iContactHideGeneratedCode(e);
		});

		$(document).on('load ready icontact_hide_generated_code', function(e) {
			iContactHideGeneratedCode(e);
		});

		function iContactHideGeneratedCode(e) {

			// If the event passed is a widget, use that
			var $widgets = $(e.currentTarget).parents('div.widget');

			// Otherwise, process all widgets on page load
			if($widgets.length === 0) {
				$widgets = $('#widgets-right div[id*="icontactwidget"]');
			}

			// for each widget
			$widgets.each(function() {
				// Show/hide custom form HTML box
				if($('input[name*="override"]', $(this)).is(':checked')) {
					$('p[class*="generated_code"]', $(this)).show();
				} else {
					$('p[class*="generated_code"]', $(this)).hide();
				}
			});
		}

	});

	jQuery(document).ajaxSuccess(function(e, xhr, settings) {
		var widget_id_base = 'icontact';

		if((settings.data.search('action=save-widget') != -1 && settings.data.search('icontactwidget') != -1) ||
		   (settings.data.search('action=add-widget') != -1 && settings.data.search('icontactwidget') != -1)
		  ) {
		  	jQuery(document).trigger('icontact_hide_generated_code');
		}

	});

</script>
				<?php
 		}

 	 	function shortcode($atts) {
			global $post; // prevent before content
			$atts = extract(shortcode_atts($this->defaults, $atts));
			if(!is_numeric($id) || $initiated == 'no') { return; }
			if(!is_admin()) {

				$settings = $this->get_settings();
				if(isset($settings[$id])) { $instance = $settings[$id]; } else { return; }

				if(isset($settings[$id]['override']) && $settings[$id]['override'] == 'yes') {
		        	$output = $settings[$id]['code']['all'];
		        } else {
		        	$output = $settings[$id]['code']['form'];
		        }

				return apply_filters('icontact_signup_form_code', apply_filters('icontact_shortcode_code', $output.apply_filters('icontact_link', $this->add_link())));
			} // get sidebar settings, echo finalcode
		}

	    function widget($args, $instance) {
	        $output = '';
	        extract( $args );
	       	extract(shortcode_atts($this->defaults, $instance));

	    	if($hide === 'yes' || $initiated === 'no') { return; }

			// The form's not working
			if(empty($code['all']) || isset($code['error'])) {
				echo "\n\n<!-- There is an error with the iContact widget configuration. -->\n\n";
				return false;
			}

			// We have placed the scripts and styles in the head, so we take just the form code.
			// Also, users don't want to edit code inside the widget
			if(isset($code['style']) && $override !== 'yes') {
				$form = $code['form'];
			} else {
				$form = $code['all'];
			}

			$output .= $before_widget;
            $output .=  $before_title . $title . $after_title;
            $output .=  "\n\t".$form.apply_filters('icontact_link', $this->add_link())."\n\t";
			$output .=  $after_widget;

			$output = apply_filters('icontact_signup_form_code', apply_filters('icontact_widget_code', $output));
			echo $output;
			return;
	    }

	    function update($new_instance, $old_instance) {
			$instance = $new_instance;

			$code = $this->process_form($instance, $this->number);

			if(!isset($instance['override']) || $instance['override'] !== 'yes') {
				if(isset($code['style'])) {
					$instance['code']['style'] =  $code['style'];
				}
				if(isset($code['form'])) {
					$instance['code']['form'] =  $code['form'];
				}
				if(isset($code['script'])) {
					$instance['code']['script'] =  $code['script'];
				}
				if(isset($code['all'])) {
					$instance['code']['all'] =  $code['all'];
				}
				if(isset($code['error'])) {
					$instance['code']['error'] =  $code['error'];
				}
			} else {
				$instance['code']['all'] = $instance['generated_code'];
			}

			return $instance;
	    }



	    function form($instance = array()) {
	    	// Upgrade from previous versions
	    	if(isset($instance['initiated']) && $instance['initiated'] === true && empty($instance['code']['all'])) {
	       		$instance['initiated'] = 'yes';
	       		$instance['code'] = $this->process_form($instance, $this->number);
	       		$this->update($instance);
	       	}

	    	$configured = false;
	    	$settings = shortcode_atts($this->defaults, $instance);
	    	extract($settings);

	        if(is_int($this->number) || !$this->number) { $kwd_number = $this->number; } else { $kwd_number = '#';}

	        if($override != 'yes' && isset($code['error'])) {
	        	$error = '<div class="error inline"><div class="wrap"><h2>The code below didn&rsquo;t work.</h2><p><strong>There was an error processing the form code</strong> you entered into the "Automatic Sign-up Form Code" field.</p>
	        	<p>Please make sure you\'re pasting the iContact-generated Automatic Sign-up Form code, and try again. For more information on how to get the Automatic Sign-up Form code, <a href="http://help.icontact.com/node/107" title="iContact instructions on getting the required code" target="_blank">read these instructions</a>.</p>
	        	<p>If you still are having problems, please leave a comment on the <a href="http://www.seodenver.com/icontact-widget/" target="_blank">plugin support page</a>.</p></div></div>';
	       } else {
	       		$configured = true;
	       }

	       if(empty($code['all'])) {
	       	$configured = false;
	        ?>
	        <div class="updated inline">
	        	<h2>Let's get started!</h2>
	        	<h3>An iContact account is required to use this widget.</h3>
	        	<h3 class="howto" style="font-weight:normal;">You can test drive iContact with <a href="http://katz.si/ic" rel="nofollow" target="_blank">a 15 day free trial</a>. Check it out; you'll like it.</h3>
	        </div>
	       <?php
	       } else {
	       	$configured = true;
		       	if($initiated !== 'no') {
		       ?>
		       <div class="updated inline">
		        	<h2>The widget is active.</h2>
		        	<h3>Everything has been configured properly. Good job!</h3>
		        	<p>You can embed the form in post or page content by using the following code: <code>[icontact id=<?php echo $kwd_number; ?>]</code>. <?php if($kwd_number == '#') { ?><small>(The ID will show once the widget is saved for the first time.)</small><?php } ?></p>
		       		<p>If you make changes to your form, make sure to click Save!</p>
		        </div>
		       <?php
		       } else {?>
		       	<div class="updated inline">
		        	<h2 style="line-height:1.2; margin-bottom:.5em;">The code worked, but the widget is not yet active.</h2>
		        	<p>This widget still needs to be configured</strong>, and <strong>will not be active until it has been saved again</strong>.</p>
		        </div>
		       <?php }
	       }
	       ?>

	        <?php echo $error; ?>
	       <?php if($configured) { echo '<p><a href="#'.$this->get_field_id('formcode').'" class="toggle_formcode">Show/Hide Automatic Sign-up Form Code</a></p>'; }?>
	       	<div class="<?php echo $this->get_field_id('formcode'); ?>"<?php if($configured) { echo ' style="display:none;"'; }?>>
	       		 <h3>Enter the Automatic Signup Form Code</h3>
		       	<p class="howto">For information on how to get the Automatic Sign-up Form code, <a href="http://help.icontact.com/node/107" title="iContact instructions on getting the required code" target="_blank">read these instructions</a>.</p>
		       	<p>
		       		<label for="<?php echo $this->get_field_id('formcode'); ?>"><?php _e('Automatic Sign-up Form Code:'); ?>
		       			<textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('formcode'); ?>" name="<?php echo $this->get_field_name('formcode'); ?>" style="font-size:11px;"><?php echo $formcode; ?></textarea>
		       		</label>
		       	</p>
		       	<hr />
		    </div>
 			<?php if($configured) {?>
 			<h3>Configure Widget Settings</h3>

 			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

	        <input type="hidden" name="<?php echo $this->get_field_name('initiated');?>" value="yes" />
			<input type="hidden" name="<?php echo $this->get_field_name('target');?>" value="no" />
	        <?php
				$this->make_textfield($initiated, true, 'Submit', $submittext, $this->get_field_id('submittext'),$this->get_field_name('submittext'), 'Submit Button Text');

	            $this->make_textfield($initiated, true, '260', $tablewidth, $this->get_field_id('tablewidth'),$this->get_field_name('tablewidth'), 'Width of Form (in pixels or %)<br /><small>Example: <code>260</code> for 260px or <code>100%</code></small>');

	            $redirectError = false;
	            if(!$this->is_valid_url($redirect)) { $redirectError = 'Invalid Success Page URL; default will be used.'; }
	            $this->make_textfield($initiated, true, 'http://www.icontact.com/signup-thanks', $redirect, $this->get_field_id('redirect'),$this->get_field_name('redirect'), 'Page shown users are redirected to after signing up', $redirectError);
	            $errorredirectError = false;
	            if(!$this->is_valid_url($errorredirect)) { $errorredirectError = 'Invalid Error Page URL; default will be used.'; }
	            $this->make_textfield($initiated, true, 'http://www.icontact.com/icontact-error', $errorredirect, $this->get_field_id('errorredirect'),$this->get_field_name('errorredirect'), 'Error page shown to users on unsuccessful signup', $errorredirectError);

	        ?>
	        <p>
		        <label for="<?php echo $this->get_field_id('inputsize'); ?>"><span>Form Field Width</span>
				<select id="<?php echo $this->get_field_id('inputsize'); ?>" name="<?php echo $this->get_field_name('inputsize'); ?>">
				<?php
					$i = 10;
					$output = '';
					while($i < 41) {
						$output .= '<option value="10"'.selected($inputsize == $i, true, false).'>'.$i.' Characters</option>';
						$i++;
					}
					echo $output;
				  ?>
				  </select>
				  <span class="howto">Width of form inputs (in characters)</span></label>
			</p>
			<?php
	            $this->make_checkbox($https, $this->get_field_id('https'),$this->get_field_name('https'), 'Make Form HTTPS <span class="howto">If you are on a secure site (eCommerce, for example), you should check this.');

	            $this->make_checkbox($target, $this->get_field_id('target'),$this->get_field_name('target'), 'Open form in new window when submitted');

	            $this->make_checkbox($override, $this->get_field_id('override'),$this->get_field_name('override'), 'Make changes to the form HTML <span class="howto">If you want to modify how the form is displayed, you can edit the HTML by checking this box.</span>');

	            if(!empty($code) && isset($code['all'])) { ?>
	            <p class="<?php echo $this->get_field_id('generated_code'); ?>" style="display:none;">
	            	<label for="<?php echo $this->get_field_id('generated_code'); ?>"><strong><?php _e('Displayed Form Code:'); ?></strong>
	            		<span class="howto" style="margin-bottom:.5em;">Using this setting will make the Javascript and CSS display inline, exactly as shown below. When "Make changes to form HTML" is unchecked, the CSS is added to the <code>&lt;head&gt;</code> of the page, and the Javascript is added to the bottom.</span>
	            		<textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('generated_code'); ?>" name="<?php echo $this->get_field_name('generated_code'); ?>" style="font-size:11px"><?php echo $code['all']; ?></textarea>
	            	</label>
	            </p>
	            <?php  } ?>
	             <?php $this->make_checkbox($hide, $this->get_field_id('hide'),$this->get_field_name('hide'), 'Do not display widget in sidebar. <span class="howto">If you are exclusively using the <code>[icontact id='.$kwd_number.']</code> shortcode, not the sidebar widget. <strong>Note:</strong> you can use a widget in <em>both</em> sidebar and shortcode at the same time.</span>');
	        } else { // End if $configured ?><input type="hidden" name="<?php echo $this->get_field_name('initiated');?>" value="no" /><?php }

	    }

	    function make_textfield($initiated = false, $required=false, $default, $setting = '', $fieldid = '', $fieldname='', $title = '', $error = '') {
			$input = '';
			if(!$initiated || $initiated === 'no' || ($required && empty($setting))) { $setting = $default; }

		    if(!empty($error)) {
		    	 $input .= '<div class="error inline"><div class="wrap"><label for="'.$fieldid.'">'.wpautop($error).'</label></div></div>';
		    }

			$input .= '
			<p class="'.$fieldid.'">
				<label for="'.$fieldid.'">'.__($title).'
				<input type="text" class="widefat" id="'.$fieldid.'" name="'.$fieldname.'" value="'.$setting.'"/>
				</label>
			</p>';

			echo $input;
		}
		function make_checkbox($setting = '', $fieldid = '', $fieldname='', $title = '') {
			$checkbox = '
			<p class="'.$fieldid.'">
				<input type="checkbox" id="'.$fieldid.'" name="'.$fieldname.'" value="yes"';
					if($setting === 'yes') { $checkbox .= ' checked="checked"'; }
					$checkbox .= ' class="checkbox" />
				<label for="'.$fieldid.'">'.__($title).'</label>
			</p>';
		    echo $checkbox;
		}

		function is_valid_url($location, $default = '') {
			return $location;
	    	if(preg_match('/^(http\:\/\/|https\:\/\/)(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+[a-z]{2,6}/ism', $location) && parse_url($location)) {
	    		return $location;
	    	}
	    	if(empty($default)) { return false; } else { return $default; }
	    }

		function process_form($instance) {

			extract(shortcode_atts($this->defaults, $instance));

			if(empty($submit)) { $submit = 'Submit';}
			if(empty($formcode)) { return; }
			// Convert javascript to HTML by stripping js code
			$error = false;
			preg_match('/src="(.*)">/', $formcode, $matches);

			if(!empty($matches[0]))
			{
				$match = $matches[0];
			} else {
				$match = $matches[1];
			}

			$match = str_replace('src="', '', $match);
			$match = str_replace('">', '', $match);
			$src = $match;
			$code = '';
			if (preg_match('/^https?:\/\/.+/', $src)) {
				$code = wp_remote_retrieve_body( wp_remote_get($src) );

				// Added for possible GoDaddy errors
				// Code from http://davidwalsh.name/godaddy-curl-http-403-errors
				if(is_wp_error($code) || !$code) {
					$ch = @curl_init();
					curl_setopt($ch, CURLOPT_VERBOSE, 1);
					curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, FALSE);
					curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
					curl_setopt ($ch, CURLOPT_PROXY,'http://proxy.shr.secureserver.net:3128');
					curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt ($ch, CURLOPT_URL, $src);
					curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
					$code = curl_exec ($ch);
					@curl_close ($ch);
				}
			}

			if(empty($code)) { return array('error'=>true); }


			$icontactnum = preg_replace('/(?:.*?)f\=([0-9]+)(?:.*)/ism', '$1', $src);
			$code = str_replace($icontactnum, $icontactnum.$this->number, $code);

			// Fix issue where formID was being swapped out.
			$code = str_replace('\"'.$icontactnum.$this->number.'\"', '\"'.$icontactnum.'\"', $code);

			// Make HTTPS if needed
			if($https === 'yes') { $code = str_replace('http://', 'https://', $code); }

			// Remove JS Formatting
			$code = str_replace('document.write("', '', $code);
			$code = str_replace('<\/script>\n");', '</script>', $code);
			$code = str_replace('\n<\/form>\n");', "\n</form>", $code);
			$code = str_replace('\"', '"', $code);
			$code = str_replace("\'", "'", $code);
			$code = str_replace('TR>', 'tr>', $code);
			$code = str_replace('TD>', 'td>', $code);
			$code = str_replace('font>', 'font>', $code);
			$code = str_replace('<\/', '</', $code);
			$code = str_replace('\n', "\n", $code);
			$code = str_replace('if (document.location.protocol === "https:")', '', $code);

			// Fix XHTML issues
			$code = str_replace("<tr>\n      <td>&nbsp;</td>\n      <td><span class=\"required\">", "<tr class=\"required\">\n      <td>&nbsp;</td>\n      <td><span class=\"required\">", $code);
			$code = str_replace('type=text', 'type="text"', $code);
			$code = str_replace('type=hidden', 'type="hidden"', $code);
			$code = str_replace('name=formid', 'name="formid"', $code);
			$code = str_replace('name=reallistid', 'name="reallistid"', $code);
			$code = str_replace('name=doubleopt', 'name="doubleopt"', $code);
			$code = str_replace('name=errorredirect', 'name="errorredirect"', $code);
			$code = str_replace('name=redirect', 'name="redirect"', $code);
			$code = str_replace('valign=top', 'valign="top"', $code);
			$code = str_replace('align=right', 'align="right"', $code);
			$code = str_replace('align=left', 'align="left"', $code);
			$code = str_replace('method=post', 'method="post"', $code);
			$code = str_replace('method="post"', 'method="post" class="iContactForm"', $code);
			$code = str_replace('name=clientid', 'name="clientid"', $code);
			$code = str_replace('</tr><td', '</tr><tr><td', $code);
			$code = str_replace('<style>', '<style type="text/css">', $code);
			$code = str_replace('<input type="hidden" name="redirect"', '<div><input type="hidden" name="redirect"', $code);
			$code = str_replace('</form>', '</div></form>', $code);
			$code = preg_replace('/<input([^<]+?[^\/])>/i', '<input$1 />', $code); // Add trailing slashes to inputs
			$code = str_replace('<div id="SignUp">', '<div class="SignUp">', $code); // for multiple instances
			$code = str_replace("document.getElementById(\'", "document.getElementById('", $code);
			$code = str_replace('icpForm'.$icontactnum.$this->number.'.action = "https://app.icontact.com/icp/signup.php";', '', $code);
			$code = preg_replace('/<\/tr>\s+(\<input\ type\=\"hidden\".*?)\<tr(.*?)<\/table\>/ism', '</tr><tr$2</table><div>$1</div>', $code);

			// Enter custom values
			$code = str_replace('value="Submit"', 'value="'.$submittext.'"', $code);
			$code = str_replace('type="text" name="', 'type="text" size="'.$inputsize.'" name="', $code);
			$code = str_replace('<table width="260"', '<table width="'.$tablewidth.'"', $code);


			// In case they update their code, gotta have old and new pages.
			if($https) { $s = 's';} else { $s = '';}
			$redirect = $this->is_valid_url($redirect, 'http'.$s.'://www.icontact.com/signup-thanks');
			$code = str_replace('http://www.icontact.com/signup-thanks', $redirect, $code);
			$code = str_replace('http://www.icontact.com/www/signup/thanks.html', $redirect, $code);

			$errorredirect = $this->is_valid_url($errorredirect, 'http'.$s.'://www.icontact.com/icontact-error');
			$code = str_replace('http://www.icontact.com/icontact-error', $errorredirect, $code);
			$code = str_replace('http://www.icontact.com/www/signup/error.html', $errorredirect, $code);

			// Open form in new window?
			if($target === 'yes') { $code = str_replace('<form method', '<form target="_blank" method', $code); }


			// By doing this, we are breaking the code into three chunks; style, form, script
			// so that we can add them to the header where they belong.
			preg_match('/<style type="text\/css">(.*?)<\/style>(.*?)<script type="text\/javascript">(.*?)<\/script>/ism', $code, $matches);

			if(!empty($matches)) {
				$style = $matches[1];
				$form = $matches[2];
				$script = $matches[3];
				return array('style' => $style, 'form' => $form, 'script' => $script, 'all'=>$code);
			} else {
				// For some reason the code has changed; show the code in full.
				if(!$error) {
					return array('all' => $code);
				} else {
					return array('error'=>$errormsg);
				}
			}
		}

	    function add_link($code=null) {
	    	$link = '<a href="http://katz.si/icontact" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif; text-align:center; display:block; line-height:1; margin-top:.75em;"><font size="2">Email Marketing by iContact</font></a>';
	        // Please leave this in and give credit where credit is due.
	        $comment = '<!-- iContact Widget for WordPress by Katz Web Services, Inc. -->';
	        $code = str_replace($link, '', $code);
			$code = str_replace($comment, '', $code);

			// Added to accomodate iContact changing code
			if(substr($code, -3, 3) == '");') { $code = substr_replace($code, '', -3, 3); }

	       	$attr = $this->attr();
			if(!empty($attr)) { $code .= $attr.$comment; } else { $code .= $link.$comment; }
			return $code;
	    }

	    function attr() {
			global $post;// prevents calling before <HTML>
			if($post && !is_admin()) {
				$default = '<span class="link">For <a href="http://katz.si/icontact" rel="nofollow">Email Marketing</a> You Can Trust</span>';
				$url = 'http://www.katzwebservices.com/development/attribution.php?site='.htmlentities(substr(get_bloginfo('url'), 7)).'&from=ic_widget&version='.$this->version;
				// > 2.8
				if(function_exists('fetch_feed')) {
					include_once(ABSPATH . WPINC . '/feed.php');

					// If possible, use transient first.
					$content = get_transient( 'icontact_attr' );
					if($content && !is_wp_error( $content )) { return $content; }

					if ( !$rss = fetch_feed($url) ) { return false; }
					if(!is_wp_error($rss)) {
						// This list is only missing 'style', 'id', and 'class' so that those don't get stripped.
						// See http://simplepie.org/wiki/reference/simplepie/strip_attributes for more information.
						$strip = array('bgsound','expr','onclick','onerror','onfinish','onmouseover','onmouseout','onfocus','onblur','lowsrc','dynsrc');
						$rss->strip_attributes($strip);
						$rss->set_cache_duration(60*60*24*30);
						$rss_items = $rss->get_items(0, 1);

						// This is a one-item array
						foreach ( $rss_items as $item ) {
							$content = str_replace(array("\n", "\r"), ' ', $item->get_description());

							// 60 day cache
							set_transient( 'icontact_attr', $content, 60 * 60 * 24 * 60 );

							return $content;
						}
					}
					return $default;
				}
			}
		}

	}
}

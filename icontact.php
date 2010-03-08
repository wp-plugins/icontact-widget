<?php
/**
Plugin Name: iContact Widget
Plugin URI: http://www.seodenver.com/icontact-widget/
Description: Add the iContact signup form to your sidebar and easily update the display settings & convert the form from Javascript to faster-loading HTML.
Version: 1.1
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com
*/

/*
Copyright 2010 Katz Web Services, Inc.  (email: info@katzwebservices.com)

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

/*
Versions

1.0 	- Launch
1.0.1	- Added error handling and HTTPS option
1.0.2	- Improved error handling, and prevented form from being shown until it works properly.
		- Added settings: Edit HTML capability, Change input width, Change Submit input text, Change form width
1.0.3	- Added missing closing </form> tag
1.0.4	- Added name=clientid formatting cleanup
1.0.5	- Added better support for multiple widgets
		- Improved validation by adding closing slashes to <input>s
		- Fixed issue with multiple instances of same form preventing javascript validation
		- Added option to not display form in sidebar, if only using the [icontact id=#] shortcode
		- Fixed shortcode bug that had inserted form before content, instead of where inserted in content by using return instead of echo
1.0.6	- Fixed issue where you had to save widget two times for it to update
		- Added support for `curl` for servers that don't support `file_get_contents()`
1.0.7	- Updated form to compensate for changed iContact javascript formatting (if your form shows `");` at the end of it, this will fix it)
		- Updated widget so that it will not load for users < WordPress 2.8, preventing errors
		- Improved wording for widget's code override option
1.0.8	- Added support for PHP4 servers by defining str_replace()
1.0.9	- Attempted fix for certain hosting configurations
1.1		- If you want to modify widget or shortcode output, there's now an `add_filters` method to do so.
*/

if(class_exists(WP_Widget) && function_exists(register_widget)) {
	$kwd_ic_version = '1.1';
	add_action( 'widgets_init', 'kwd_load_widgets' );
	
	function kwd_load_widgets() {
		register_widget( 'iContactWidget' );
	}
	
	
	class iContactWidget extends WP_Widget {
	 
	    function iContactWidget() {
	    	$widget_options = array('description'=>'Add an iContact form to your and start adding to your lists!', 'classname' => 'icontact');
	        parent::WP_Widget(false, $name = 'iContact Signup Form', $widget_options);    
	    }
	 
	 
	    function widget($args, $instance) {      
	        extract( $args );
	        $output = '';
	        if($instance['hide'] != 'yes') {
	        
		        $finalcode = '';
		        $title = $instance['title'];
		        $formcode = $instance['formcode'];
		        if($instance['override'] == 'yes') {
		        	$finalcode = $instance['edited_code'];
		        } else {
		        	$finalcode = $instance['generated_code'];
		        }
		        
		        // This way, if there is more than one of the same form, they each have an unique ID
		        //$altnumber = rand(0, 1000000);
		       	$finalcode = str_replace('icpsignup', 'icpsignup'.$this->number, $finalcode);
		       	
		        $link = '<a href="http://bit.ly/icontact-email-marketing" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif; text-align:center; display:block; line-height:1; margin-top:.75em;"><font size="2">Email Marketing by iContact</font></a>';
		        // Please leave this in and give credit where credit is due.
		        $comment = '<!-- iContact Widget for WordPress by Katz Web Services, Inc. -->';
		        if(!empty($finalcode) && strlen($finalcode) > 20) {
			        $finalcode = str_replace($link, '', $finalcode);
					$finalcode = str_replace($comment, '', $finalcode);
					
					// Added to accomodate iContact changing code
					if(substr($finalcode, -3, 3) == '");') { $finalcode = substr_replace($finalcode, '', -3, 3); }
					
			       	$attr = attr();
					if(!empty($attr)) { $finalcode .= $attr.$comment; } else { $finalcode .= $link.$comment; } 

						$output .= $before_widget;
		                $output .=  $before_title . $title . $after_title;
		                $output .=  "\n\t".$finalcode."\n\t"; 
						$output .=  $after_widget; 
						
						$output = apply_filters('icontact_signup_form_code', $output);
						echo $output;
		        // If there is no finalcode generated
		        } else {
					echo '
						<!--
						//
						// iCONTACT WIDGET 
						//
						There is an error with the iContact widget configuration on this website. 
						//
						//
						//
						-->';
				}
			} // end if hide
	    }
	 
	    function update($new_instance, $old_instance) {
	    	$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			
			$instance['formcode'] = $new_instance['formcode'];
			//$instance['generated_code'] = $new_instance['generated_code'];
			$instance['edited_code'] = $new_instance['generated_code'];
			$instance['https'] = $new_instance['https'];
			$instance['inputsize'] = $new_instance['inputsize'];
			$instance['override'] = $new_instance['override'];
			$instance['submittext'] = $new_instance['submittext'];
			$instance['tablewidth'] = $new_instance['tablewidth'];
			$instance['hide'] = $new_instance['hide'];
			if($instance['initiated'] != true || $instance['override'] != 'yes') {
				$instance['generated_code'] =  kwd_process_form($instance['formcode'], $instance['https'], $instance['submittext'], $instance['inputsize'], $instance['tablewidth'], $this->number);
			}
			$instance['initiated'] = true;
	        return $instance;
	    }
	 
	    function form($instance) {
	        $title = esc_attr($instance['title']);
	        $formcode = $instance['formcode'];
	        $inputsize = $instance['inputsize'];
	        if(is_int($this->number) || !$this->number) { $kwd_number = $this->number; echo '<p><strong>iContact Widget ID='.$kwd_number.'</strong></p>'; } else { $kwd_number = '#';}
	        if(isset($instance['initiated'])) { $initiated = true; } else { $initiated=false;}
	        if($instance['override'] != 'yes' || !$initiated) {
	        	$finalcode = kwd_process_form($instance['formcode'], $instance['https'], $instance['submittext'], $instance['inputsize'], $instance['tablewidth'], $kwd_number);
	        	if(is_array($finalcode)) { $error = $finalcode[1]; }
	        } else {
	        	$finalcode = $instance['edited_code'];
	        }
	        $error = '';
	        if(!$finalcode && $initiated) {
	        	$error = '<div style="border:1px solid red; margin:10px 0; background:white; padding:5px;"><p><strong>There was an error processing the form code</strong> you entered into the "Automatic Sign-up Form Code" field.</p> <p>Please make sure you\'re pasting the <a href="http://www.icontact.com/help/question.php?ID=149" rel="nofollow">iContact-generated Automatic Sign-up Form code</a>, and try again.</p><p>If you still are having problems, please <a href="http://www.seodenver.com/icontact-widget/">leave a comment on the widget page</a>.</p></div>';
	        }
	        
	        ?>
	        	<p>Don&#8217;t use iContact? You need it for this widget to work, so test drive it with <a href="http://snurl.com/icontact_1">a 15 day free trial</a>.</p>
	        	<p>Generate your iContact signup form by <a href="http://www.icontact.com/help/question.php?ID=149" rel="nofollow">following the instructions on iContact.com</a>. Paste the Automatic Sign-up Form code below.</p>
	        	<p>You can embed the form in post or page content by using the following code: <code>[icontact id=<?php echo $kwd_number; ?>]</code>. <?php if($kwd_number == '#') { ?><small>(The ID will show once the widget is saved for the first time.)</small><?php } ?></p>
	        	<p>If you have made changes to your form, click Save again, and the form HTML will update.</p>
	            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	            <?php echo $error; ?>
	            <p><label for="<?php echo $this->get_field_id('formcode'); ?>"><?php _e('Automatic Sign-up Form Code:'); ?> <textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('formcode'); ?>" name="<?php echo $this->get_field_name('formcode'); ?>" style="font-size:11px"><?php echo $instance['formcode']; ?></textarea></label></p>
	
	            <?php if(!empty($finalcode)) { ?>
	            <p><label for="<?php echo $this->get_field_id('generated_code'); ?>"><?php _e('Displayed HTML Form Code:'); ?> <textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('generated_code'); ?>" name="<?php echo $this->get_field_name('generated_code'); ?>" style="font-size:11px"><?php if(is_array($finalcode)) { echo $finalcode[1]; } else { echo $finalcode; }  ?></textarea></label></p>
	            <?php  } ?>
	            <?php kwd_make_checkbox($instance['override'], $this->get_field_id('override'),$this->get_field_name('override'), 'Keep my changes to the form\'s code intact<br /><small>If you have edited the Displayed HTML Form Code, checking this box <strong>will prevent your changes from being overwritten</strong>.</small>'); ?>
	             <?php kwd_make_checkbox($instance['https'], $this->get_field_id('https'),$this->get_field_name('https'), 'Make Form HTTPS'); ?>
	             
	             <?php kwd_make_checkbox($instance['hide'], $this->get_field_id('hide'),$this->get_field_name('hide'), 'Do not display widget in sidebar.<br /><small>If you are exclusively using the [icontact id='.$kwd_number.'] shortcode, not the sidebar widget. Note: you can use a widget in <em>both</em> sidebar and shortcode at the same time.</small>'); ?>
	             
	        <p>
			<label for="<?php echo $this->get_field_id('inputsize'); ?>">Width of form inputs (in characters)</label>
			<select id="<?php echo $this->get_field_id('inputsize'); ?>" name="<?php echo $this->get_field_name('inputsize'); ?>">
				  <option value="10"<?php if($inputsize == 10) { echo ' selected="selected"'; }?>>10</option>
				  <option value="11"<?php if($inputsize == 11) { echo ' selected="selected"'; }?>>11</option>
				  <option value="12"<?php if($inputsize == 12) { echo ' selected="selected"'; }?>>12</option>
				  <option value="13"<?php if($inputsize == 13) { echo ' selected="selected"'; }?>>13</option>
				  <option value="14"<?php if($inputsize == 14) { echo ' selected="selected"'; }?>>14</option>
				  <option value="15"<?php if($inputsize == 15) { echo ' selected="selected"'; }?>>15</option>
				  <option value="16"<?php if($inputsize == 16) { echo ' selected="selected"'; }?>>16</option>
				  <option value="17"<?php if($inputsize == 17) { echo ' selected="selected"'; }?>>17</option>
				  <option value="18"<?php if($inputsize == 18) { echo ' selected="selected"'; }?>>18</option>
				  <option value="19"<?php if($inputsize == 19) { echo ' selected="selected"'; }?>>19</option>
				  <option value="20"<?php if($inputsize == 20) { echo ' selected="selected"'; }?>>20</option>
				  <option value="21"<?php if($inputsize == 21) { echo ' selected="selected"'; }?>>21</option>
				  <option value="22"<?php if($inputsize == 22) { echo ' selected="selected"'; }?>>22</option>
				  <option value="23"<?php if($inputsize == 23) { echo ' selected="selected"'; }?>>23</option>
				  <option value="24"<?php if($inputsize == 24) { echo ' selected="selected"'; }?>>24</option>
				  <option value="25"<?php if($inputsize == 25) { echo ' selected="selected"'; }?>>25</option>
				  <option value="26"<?php if($inputsize == 26) { echo ' selected="selected"'; }?>>26</option>
				  <option value="27"<?php if($inputsize == 27) { echo ' selected="selected"'; }?>>27</option>
				  <option value="28"<?php if($inputsize == 28) { echo ' selected="selected"'; }?>>28</option>
				  <option value="29"<?php if($inputsize == 29) { echo ' selected="selected"'; }?>>29</option>
				  <option value="30"<?php if($inputsize == 30) { echo ' selected="selected"'; }?>>30</option>
				  <option value="31"<?php if($inputsize == 31) { echo ' selected="selected"'; }?>>31</option>
				  <option value="32"<?php if($inputsize == 32) { echo ' selected="selected"'; }?>>32</option>
				  <option value="33"<?php if($inputsize == 33) { echo ' selected="selected"'; }?>>33</option>
				  <option value="34"<?php if($inputsize == 34) { echo ' selected="selected"'; }?>>34</option>
				  <option value="35"<?php if($inputsize == 35) { echo ' selected="selected"'; }?>>35</option>
				  <option value="36"<?php if($inputsize == 36) { echo ' selected="selected"'; }?>>36</option>
				  <option value="37"<?php if($inputsize == 37) { echo ' selected="selected"'; }?>>37</option>	  
				  <option value="38"<?php if($inputsize == 38) { echo ' selected="selected"'; }?>>38</option>
				  <option value="39"<?php if($inputsize == 39) { echo ' selected="selected"'; }?>>39</option>
				  <option value="40"<?php if($inputsize == 40) { echo ' selected="selected"'; }?>>40</option>
			  </select>
			</p>
	            <?php kwd_make_textfield($initiated, true, 'Submit', $instance['submittext'], $this->get_field_id('submittext'),$this->get_field_name('submittext'), 'Change Submit Button Text'); ?>
	            <?php kwd_make_textfield($initiated, true, '260', $instance['tablewidth'], $this->get_field_id('tablewidth'),$this->get_field_name('tablewidth'), 'Change the Width of the Form (in pixels or %)<br /><small>Example: <code>260</code> for 260px or <code>100%</code></small>'); ?>
	            
	        <?php 
	    }
	     
	} 
	
	function kwd_make_textfield($initiated = false, $required=false, $default, $setting = '', $fieldid = '', $fieldname='', $title = '') {
		
		if(!$initiated || ($required && empty($setting))) { $setting = $default; }
	    
		$input = '
		<p>
			<label for="'.$fieldid.'">'.__($title).'
			<input type="text" class="widefat" id="'.$fieldid.'" name="'.$fieldname.'" value="'.$setting.'"/>
			</label>
		</p>';
		
		echo $input;
	}    
	function kwd_make_checkbox($setting = '', $fieldid = '', $fieldname='', $title = '') {
		$checkbox = '
		<p>
			<input type="checkbox" id="'.$fieldid.'" name="'.$fieldname.'" value="yes"';
				if($setting == 'yes') { $checkbox .= ' checked="checked"'; }
				$checkbox .= ' class="checkbox" />
			<label for="'.$fieldid.'">'.__($title).'</label>
		</p>';
	    echo $checkbox;
	}	
	
	function kwd_ic_shortcode($atts) {
		global $post; // prevent before content
			$atts = extract(shortcode_atts(array('id' => '1'), $atts));
	/* 		if(!$id) { $id = 3;} */
			if(!is_admin()) { 
				$settings = get_option('widget_icontactwidget');
				if($settings[$id]['override'] == 'yes') {
		        	$finalcode = $settings[$id]['edited_code'];
		        } else {
		        	$finalcode = $settings[$id]['generated_code'];
		        }
		        $finalcode = apply_filters('icontact_signup_form_code', $finalcode);
				return $finalcode;
			} // get sidebar settings, echo finalcode
	}
	add_shortcode('iContact', 'kwd_ic_shortcode');
	add_shortcode('icontact', 'kwd_ic_shortcode');
	
	
	function kwd_process_form($src, $https = false, $submit = 'Submit', $inputsize = '', $width = '260', $kwd_number = 1) {
		
		if(empty($submit)) { $submit = 'Submit';}
		if(empty($src)) { return; }
		// Convert javascript to HTML by stripping js code
		$error = false;
		preg_match('/src="(.*)">/', $src, $matches); 
		
		if(!empty($matches[0])) 
		{ 
			$match = $matches[0];
		} else {
			$match = $matches[1];
		}
		
		$match = str_replace('src="', '', $match);
		$match = str_replace('">', '', $match);
		$src = $match;
		
		if (preg_match('/^https?:\/\/.+/', $src)) {
			if($https) {
				$src = preg_replace('/(^https?)(:\/\/.+)/i', '$1s$2', $src);
			}
			$code = @file_get_contents($src);
			
			if(!$code) {
				$ch = @curl_init(); 
				$timeout = 0; 
				curl_setopt ($ch, CURLOPT_URL, $src); 
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
				$code = @curl_exec($ch); 
				@curl_close($ch);
			}
			
			// Added for possible GoDaddy errors
			// Code from http://davidwalsh.name/godaddy-curl-http-403-errors
			if(!$code) {
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
			if(!$code) {
				$error = true;
				$errormsg = "\t\t\t===\nYour server configuration does not support this widget.\n\nAsk your host to enable file_get_contents() or curl()\n\t\t\t===";
			}
		} else {
			$errormsg = 'The iContact file was not accessible for some reason.';
			$errormsg .= 'matches: '.print_r($matches, true)."<br />";
			$errormsg .= 'match: '.print_r($match, true)."<br />";
			$errormsg .= 'src: '.print_r($src, true)."<br />";
			$error = true;
		}
		
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
		$code = str_replace('document.icpsignup.action = "https://app.icontact.com/icp/signup.php";', '', $code);
		
		// Fix XHTML issues
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
		$code = str_replace('<script type="text/javascript">', '</div><script type="text/javascript">', $code);
		$code = preg_replace('/<input([^<]+?[^\/])>/i', '<input$1 />', $code); // Add trailing slashes to inputs
		$code = str_replace('<div id="SignUp">', '<div class="SignUp">', $code); // for multiple instances
		$code = str_replace("document.getElementById(\'", "document.getElementById('", $code);
		
		
		// Add numbering
		$code = str_replace('name="icpsignup"', 'name="icpsignup'.$kwd_number.'"', $code);
		$code = str_replace('verifyRequired()', 'verifyRequired'.$kwd_number.'()', $code);
		$code = str_replace('document.icpsignup', 'document.icpsignup'.$kwd_number, $code);
		$code = str_replace('icpForm', 'icpForm'.$kwd_number, $code);
		
		// Enter custom values
		$code = str_replace('value="Submit"', 'value="'.$submit.'"', $code);
		$code = str_replace('type="text" name="', 'type="text" size="'.$inputsize.'" name="', $code);
		$code = str_replace('<table width="260"', '<table width="'.$width.'"', $code);
		
		if(!$error) {
			return $code;
		} else {
			return array(false, $errormsg);
		}
	}
	
	// If you have chosen to show the attribution link, add the link		
	if(!function_exists('kwd_rss_output')){
		function kwd_rss_output($rss = '', $default = '') {
				require_once(ABSPATH . WPINC . '/rss.php');
				if ( !$rss = fetch_rss($rss) )
					return;
				
				$items = 1;
				if ( is_array( $rss->items ) && !empty( $rss->items ) ) {
					$rss->items = array_slice($rss->items, 0, $items);
					foreach ($rss->items as $item ) {
						if ( isset( $item['description'] ) && is_string( $item['description'] ) )
							$summary = $item['description'];
						$desc = str_replace(array("\n", "\r"), ' ', $summary);
						$summary = '';
						return $desc;
					}
				} else {
					return $default;
				}
		} // end kwd_rss_output
	}
	
	if(!function_exists('attr')) {
		function attr() { 
			global $post, $kwd_quotes_version, $kwd_check_status;// prevents calling before <HTML>
			$kwd_check_status = htmlentities(substr(WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'icontact.php', 7));
			global $post, $kwd_ic_version;// prevents calling before <HTML>
			if($post && !is_admin()) {
				$site = 'http://www.katzwebservices.com/development/attribution.php?site='.htmlentities(substr(get_bloginfo('url'), 7)).'&from=ic_widget&version='.$kwd_ic_version.'&check='.$kwd_check_status;				
				$output = kwd_rss_output($site, $default);
				return $output;
			}
		}
	}
	
	
}
?>
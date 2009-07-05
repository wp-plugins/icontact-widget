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

add_action( 'widgets_init', 'kwd_load_widgets' );

function kwd_load_widgets() {
	register_widget( 'iContactWidget' );
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
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title . $title . $after_title; ?>
 
                      <?php echo $instance['finalcode']; //$finalcode; //echo get_option('kwd_icontact_html'); ?>
 
              <?php echo $after_widget; ?>
        <?php
    }
 
    function update($new_instance, $old_instance) { 
    	$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['formcode'] = $new_instance['formcode'];
		$instance['finalcode'] = $new_instance['finalcode'];
        return $instance;
    }
 
    function form($instance) {                
        $title = esc_attr($instance['title']);
        $formcode = $instance['formcode'];
        $finalcode = kwd_process_form($instance['formcode']);
        ?>
        	<p>Don&#8217;t use iContact? You need it for this widget to work, so test drive it with <a href="http://www.icontact.com/a.pl/483190">a 15 day free trial</a>.</p>
        	<p>Generate your iContact signup form by <a href="http://www.icontact.com/help/question.php?ID=149" rel="nofollow">following the instructions on iContact.com</a>. Paste the Automatic Sign-up Form code below.</p>
        	<p>If you have made changes to your form, click Save again, and the form HTML will update.</p>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('formcode'); ?>"><?php _e('Automatic Sign-up Form Code:'); ?> <textarea class="widefat" cols="20" rows="20" id="<?php echo $this->get_field_id('formcode'); ?>" name="<?php echo $this->get_field_name('formcode'); ?>"><?php echo $instance['formcode']; ?></textarea></label></p>
            <?php if(!empty($finalcode)) { ?>
            <p><label for="<?php echo $this->get_field_id('finalcode'); ?>"><?php _e('Displayed HTML Form Code:'); ?> <textarea class="widefat" cols="20" id="<?php echo $this->get_field_id('finalcode'); ?>" name="<?php echo $this->get_field_name('finalcode'); ?>"><?php echo $finalcode; ?></textarea></label></p>
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
	

function kwd_process_form($src) {
	// Convert javascript to HTML by stripping js code
	
	preg_match('/src="(.*)">/', $src, $matches); 
	
	$matches[0] = str_ireplace('src="', '', $matches[0]);
	$matches[0] = str_ireplace('">', '', $matches[0]);
	$src = $matches[0];
	
	$code = file_get_contents($src);
	
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
	$code .= '<a href="http://www.icontact.com/a.pl/483190" rel="nofollow" style="font-family: Arial, Helvetica, sans-serif;"><font size="2">Email Marketing by iContact</font></a>';
	$code .= '<!-- iContact Widget for WordPress by Katz Web Design -->';
	
	return $code;
}
 
?>
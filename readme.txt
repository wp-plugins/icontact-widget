=== iContact Widget ===
Tags: icontact, widget, newsletter, form, signup, newsletter widget, email newsletter form, newsletter form, newsletter signup, email widget, email marketing, newsletter, form, signup
Requires at least: 2.8
Tested up to: 3.4
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=iContact%20Newsletter%20Widget&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Add an iContact signup form to your sidebar or content and easily update how the form looks.

== Description ==

<h3>Simple iContact Signup Form Installation on Your Blog</h3>

Add the iContact 'Automatic Sign-up Form' to your sidebar with this widget. Simply paste the code from the form you created in iContact into this widget. Your form will be converted into HTML (instead of Javascript, which takes more time to load).

### iContact Widget Configuration Options

* Change the width of your form
* Change the submit button text
* Choose which page to redirect users to upon successful completion
* Open the form in a new window

The plugin includes __shortcode support__ -- add an iContact form into your page or post!

== Screenshots ==

1. How the widget appears in the Widgets panel 

== Frequently Asked Questions == 

= How do I use the new `add_filters()` functionality? (Added 1.1) =
If you want to change some code in the widget, you can use the WordPress `add_filter()` function to achieve this.

You can add code to your theme's `functions.php` file that will modify the widget or shortcode output. Here's an example:
<pre>
function my_example_function($widget) { 
	// The $widget variable is the output of the widget
	// This will replace 'this word' with 'that word' in the widget output.
	$widget = str_replace('this word', 'that word', $widget);
	// Make sure to return the $widget variable, or it won't work!
	return $widget;
}
add_filter('icontact_signup_form_code', 'my_example_function');
</pre>

= How do I remove the link to iContact? =
Add the following code to your theme's `functions.php` file:

`add_filter('icontact_link', create_function('$a', 'return false;'));`

= What is the plugin license? =
* This plugin is released under a GPL license.

== Upgrade Notice ==

= 1.2.1 =
* Fixes some minor backend display issues. No functionality changes.

== Changelog ==

= 1.2.1 =
* Fixes some widget javascript issues in the backend. Now the widget properly shows and hides the the "Make changes to the form HTML" checkbox shows and hides the "Displayed Form Code" textarea.

= 1.2 =
* Added support for servers without `curl()` by using WP's built-in `wp_remote_get()` functionality.
* Added options
	- Choose page URL to redirect users to on successful signup
	- Choose error page URL
	- Open forms in new window
* Added filters
	- `icontact_widget_style` - Modify the CSS style output
	- `icontact_widget_script`  - Modify the Javascript output
	- `icontact_link` - Modify iContact link output
* Rewrote plugin output functionality
	- CSS styles now added to website `<head>`
	- Javascript now added to website footer
	- The form's "* = Required Field" row now has a CSS class `required`
* Made widget interface better
	- Updated instructions to be more clear
	- Fixed link to iContact instructions
	- Improved layout of widget by hiding unnecessary items

= 1.1 = 
* If you want to modify widget or shortcode output, there's now an `add_filters` method to do so.

= 1.0.9.1 =
* Updated with the GPL license

= 1.0.9 =
* Should fix "The iContact file was not accessible for some reason." issue that was experienced by users with certain hosting configurations

= 1.0.8 =
* Fixed incompatibility for servers running PHP4 by adding `str_ireplace()` function definition

= 1.0.7 = 
* Updated form to compensate for changed iContact javascript formatting (if your form shows `");` at the end of it, this will fix it)
* Updated widget so that it will not load for users < WordPress 2.8, preventing errors
* Improved wording for widget's code override option

= 1.0.6 =
* Added support for `curl` for servers that don't support `file_get_contents()`
* Fixed issue where you had to save widget two times for it to update

= 1.0.5 =
* Added support for multiple widgets, and multiple instances of the same widget on a page
* Improved iContact code validation by adding closing slashes to <input>s
* Fixed issue with multiple instances of same form preventing javascript validation
* Added option to not display form in sidebar, if only using the [icontact id=#] shortcode
* Changed shortcode structure to `[icontact id=#]`. Once configured, widget now provides a reference ID
* Fixed shortcode bug that had inserted form before content, instead of where inserted in content.
* Improved code to save update form on the first save

= 1.0.4 = 
* Added `name=clientid` formatting cleanup

= 1.0.3 = 
* Added missing closing </form> tag to signup form.
* Added widget description for the wp-admin Widgets page.

= 1.0.2 =
* Improved error handling, and prevented form from being shown until it works properly.
* Added settings: Edit HTML capability, Change input width, Change Submit input text, Change form width

= 1.0.1 = 
* Added PHP `file_get_content()` error handling
* Added HTTPS form option

= 1.0 =
* Launched widget

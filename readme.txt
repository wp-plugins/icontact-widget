=== iContact Widget ===
Tags: icontact, widget, newsletter, form, signup, newsletter widget, email newsletter form, newsletter form, newsletter signup, email widget, email marketing, newsletter, form, signup
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=iContact%20Newsletter%20Widget&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Add the iContact signup form to your sidebar and easily update the display settings (form width and more). Automatically converts the form from Javascript to faster-loading HTML.

== Description ==

__Simple iContact Installation on Your Blog (Requires WordPress 2.8+)__

Add the iContact 'Automatic Sign-up Form' to your sidebar with this widget. Simply paste the code from the form you created in iContact into this widget. Your form will be converted into HTML (instead of Javascript, which takes more time to load).

Change the width of your form, the submit button text, and more! (Check out the screenshot.)

Includes __shortcode support__ -- add an iContact form in any page or post by typing `[iContact]` inside the page or post content.

== Screenshots ==

1. How the widget appears in the Widgets panel 

== Frequently Asked Questions == 

= Does this plugin require an iContact account? =

Yes, it does. __Don't use iContact? [Try it free for 15 days](http://snurl.com/icontact_1).__
iContact is a leader in email newsletters and surveys. Their toolset is cost effective, and full-featured compared to their competitors. If you haven't chosen a email newsletter company, you should [try iContact](http://snurl.com/icontact_1).

== Changelog ==

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
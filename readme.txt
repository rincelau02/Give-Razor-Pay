=== Give - Razorpay Gateway ===
Contributors: wordimpress
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, razorpay, gateway
Requires at least: 4.8
Tested up to: 4.9
Stable tag: 1.1.3
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

Razorpay Gateway Add-on for Give.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a payment gateway for razorpay.com.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.3: March 1st, 2018 =
* Fix: Resolved issue with decimal amounts being rounded off incorrectly. This resolves a conflict with Fee Recovery as well.

= 1.1.2: February 28th, 2018 =
* Fix: Added compatiblity with Give's newly release Currency Switcher add-on.
* Tweak: Bumped up Razorpay's minimum Give version to 2.0+. Please update before upgrading to this version.

= 1.1.1: November 1, 2017 =
* New: Improved the output if an error occurs for failed payments and other issues so the donor knows what happened and the admin can view logs for additional details.
* Fix: Resolved issue with excessive session checks causing payments to not process as expected.

= 1.1.0 =
* New: The plugin now uses the Razorpay order API to process donations.

= 1.0.1 =
* New: Added an uninstall.php file that removes settings if admin chooses when removing the plugin.
* Fix: Renamed function that was incorrectly named.

= 1.0 =
* Initial plugin release. Yippee!

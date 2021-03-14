=== Events ===

Description:	Create events and display in shortcode or widget.
Version:		1.2.1
Tags:			widget
Author:			azurecurve
Author URI:		https://development.azurecurve.co.uk/
Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/events/
Download link:	https://github.com/azurecurve/azrcrv-events/releases/download/v1.2.1/azrcrv-events.zip
Donate link:	https://development.azurecurve.co.uk/support-development/
Requires PHP:	5.6
Requires:		1.0.0
Tested:			4.9.99
Text Domain:	events
Domain Path:	/languages
License: 		GPLv2 or later
License URI: 	http://www.gnu.org/licenses/gpl-2.0.html

Create events and display in shortcode or widget.

== Description ==

# Description

Events allows events such as webinars or conferences to be created via a custom post type; categories, excerpt, details, start and end dates and times and a featured image are all supported.

In the options set defaults for the widget and shortcode.

Multiple widgets can be created, each assigned to display a category; settings for title, image size and limit for number of events to list can be set per widget.

The **event** shortcode accepts three parameters:
 * **slug** to select specific event.
 * **width** to set the size of the featured image.
 * **height** to set the size of the featured image.
 
Shortcode usage is **[event slug="december-2021" width=150 height=150]**; all parameters are optional and will use the defaults set via the settings page.

The **events** shortcode accepts four parameters:
 * **category** to restrict the output to the selected category.
 * **width** to set the size of the featured image.
 * **height** to set the size of the featured image.
 * **limit** to restrict the number of events to display.
 
Shortcode usage is **[events category="webinars" width=150 height=150 limit=5]**; all parameters are optional and will use the defaults set via the settings page.

Integrates with [To Twitter](https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/) from [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/) for automatic tweeting of announcement each time the announcement is made and a retweet after a specified amount of time.

== Installation ==

# Installation Instructions

 * Download the plugin from [GitHub](https://github.com/azurecurve/azrcrv-events/releases/latest/).
 * Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
 * Activate the plugin.
 * Configure relevant settings via the settings page in the admin control panel (azurecurve menu).
 
 == Screenshots ==

# Screenshots

1. Create new event in the custom post type.
2. Add widget to widget area, select category and amend defaults if necessary.
3. Widget displaying events in sidebar.
4. Page showing output of events shortcode.

== Frequently Asked Questions ==

# Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot file is in the plugins languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk; if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

== Changelog ==

# Changelog

### [Version 1.2.1](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.2.1)
 * Fix bug with saving of tweet and retweet days before options.
 
### [Version 1.2.0](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.2.0)
 * Add integration with [To Twitter](https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/) from [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/) for automatic tweeting and retweeting of events.

### [Version 1.1.1](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.1.1)
 * Remove debug code.
 
### [Version 1.1.0](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.1.0)
 * Add no event found message.
 * Add option to display widget only when events found.
 * Add function to handle multilevel default options correctly.
 * Fix bug with default category not working when adding widget.

### [Version 1.0.1](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.0.1)
 * Update screenshots.
 
### [Version 1.0.0](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.0.0)
 * Initial release.

== Other Notes ==

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://codepotent.com/classicpress/plugins/update-manager/) by [CodePotent](https://codepotent.com/) for fully integrated, no hassle, updates.

Some of the top plugins available from **azurecurve** are:
* [Add Twitter Cards](https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/)
* [Breadcrumbs](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/)
* [Series Index](https://development.azurecurve.co.uk/classicpress-plugins/series-index/)
* [To Twitter](https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/)
* [Theme Switcher](https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/)
* [Toggle Show/Hide](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/)
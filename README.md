# [Events](https://development.azurecurve.co.uk/classicpress-plugins/events/)
![Plugin Banner](/assets/images/banner-1544x500.png)

Events allows events such as webinars or conferences to be created via a custom post type.

## Description

Events allows events such as webinars or conferences to be created via a custom post type; categories, excerpt, details, start and end dates and times and a featured image are all supported.

In the options set defaults for the widget and shortcode.

Multiple widgets can be created, each assigned to display a category; settings for title, image size and limit for number of events to list can be set per widget.

The `event` shortcode accepts three parameters:
* `slug` to select specific event.
* `width` to set the size of the featured image.
* `height` to set the size of the featured image.
 
Shortcode usage is `[event slug="december-2026" width=150 height=150]`; all parameters are optional and will use the defaults set via the settings page if not supplied.

The `events` shortcode accepts four parameters:
* `category` to restrict the output to the selected category.
* `width` to set the size of the featured image.
* `height` to set the size of the featured image.
* `limit` to restrict the number of events to display.
 
Shortcode usage is `[events category="webinars" width=150 height=150 limit=5]`; all parameters are optional and will use the defaults set via the settings page.

All "is this event over yet" checks use your site's configured timezone (Settings > General > Timezone in the admin), not the timezone of the server itself.

## Installation

* Download the latest release of the plugin from [GitHub](https://github.com/azurecurve/azrcrv-events/releases/latest/).
* Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
* Activate the plugin.
* Configure relevant settings via the settings page in the admin control panel (azurecurve menu).
 
 == Screenshots ==

# Screenshots

1. Create new event in the custom post type.
2. Add widget to widget area, select category and amend defaults if necessary.
3. Widget displaying events in sidebar.
4. Page showing output of events shortcode.

## Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot file is in the plugin's assets/languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk; if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

### What happened to the To X integration?
The To X integration (and the earlier To Twitter integration it replaced) has been removed as of version 2.1.0, following changes to X's API that made the integration unviable. Events no longer posts or reposts to X in any way; existing per-event autopost settings are no longer used and can be ignored.

### Will uninstalling this plugin delete my events?
Not unless you've explicitly opted in via the "Delete event data on uninstall" checkbox on the Widget tab, which is off by default.

## Changelog

### [Version 2.0.1](https://github.com/azurecurve/azrcrv-events/releases/tag/v2.0.1)

* Update settings page and instructions to clarify purpose of fields.

### [Version 2.0.0](https://github.com/azurecurve/azrcrv-events/releases/tag/v2.0.0)

* Rebuilt on azurecurve's current, modular plugin pattern (namespaced, split across includes/ files, shared cross-plugin admin menu and tabs component).
* Replaced all jQuery with vanilla JavaScript.
* Fixed a timezone bug: scheduled posts/reposts to X, and "is this event over yet" checks, previously used the server's default PHP timezone rather than the site's configured timezone, which could post on the wrong day or at the wrong time. A single timezone-aware helper is now used everywhere a date is displayed, compared, or scheduled.
* Renamed the "To Twitter" integration to "To X", following azurecurve's own retirement of To Twitter in favour of a new To X plugin. Existing "to-twitter" settings and per-event autopost settings are migrated automatically.
* Added an opt-in "Delete event data on uninstall" setting; uninstalling now always clears any pending scheduled posts/reposts, and - only if that setting is enabled - also deletes event posts and their metadata (previously, uninstalling never deleted this data, silently, with no way to opt in).
* Added a "flush_rewrite_rules()" call on activation, fixing event/category URLs occasionally 404ing until something else happened to flush rewrite rules.
* Fixed a bug where the End Date field was marked as required despite the help text saying to leave it blank for a one-day event; End Date is now genuinely optional and defaults to the Start Date when left blank.
* Added Category, Start Date/Time and End Date/Time columns (Start Date/Time sortable) to the Events admin list page.
* Changed the default date format from m/d/Y to d/m/Y.
* Changed the displayed date/time range format from "start date-end date start time-end time" (e.g. "10/09/2026-13/09/2026 10:00-16:00") to "start date start time -end date end time" (e.g. "10/09/2026 10:00 -13/09/2026 16:00") for multi-day events.
* Added a Location field (free text) to events, set alongside the start/end dates. Location is shown in the Events admin list column, the Events widget, and the `[events]`/`[event]` shortcode output, directly below the date range.
* Renamed the "Event Dates" metabox to "Event Details" to reflect that it now also holds Location.
* Fixed several bugs found during the rebuild: missing output escaping in the Event Dates metabox; a broken settings-saved notice caused by a reference to an undefined variable; a "Use Featured Image" checkbox label that never rendered due to a missing echo; two strings using the wrong translation text domain; a missing defensive check that could emit a PHP warning for an event with incomplete date metadata; use of extract() in the widget class; unversioned/stale-versioned enqueued assets; a generic (rather than plugin-specific) Update Manager image-path filter that could affect other azurecurve plugins' icons; unescaped taxonomy output; and a metabox nonce action tied to a file path that would have broken under the new file structure.
* Removed the To Twitter integration, following changes to Twitter's API that made it nonviable.
* Update readme.md and remove readme.txt (not required for ClassicPress).
* Update azurecurve menu.

### [Version 1.3.5](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.5)

* Fix bug with short PHP declaration.
* Add missing Requires PHP tag in plugin header.

### [Version 1.3.4](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.4)

* Update plugin header and readme for compatibility with ClassicPress Directory v2.
* Update Update Manager to version 2.5.0.

### [Version 1.3.3](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.3)

* Update readme file for compatibility with ClassicPress Directory.

### [Version 1.3.2](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.2)

* Update readme files.
* Update language template.
* Fix bug with azurecurve menu.

### [Version 1.3.1](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.1)

* Update azurecurve menu.
* Update readme files.

### [Version 1.3.0](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.3.0)

* Update translations to escape strings.
* Update azurecurve menu and logo.

### [Version 1.2.2](https://github.com/azurecurve/azrcrv-events/releases/tag/v1.2.2)

* Remove extraneous code.

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

## Other Notes

### About azurecurve

**azurecurve** was one of the first plugin developers to start developing for ClassicPress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://directory.classicpress.net/plugins/update-manager) for fully integrated, no hassle, updates.

The plugins available from **azurecurve** are:

* Add Open Graph Tags - [details](https://development.azurecurve.co.uk/classicpress-plugins/add-open-graph-tags/) / [download](https://github.com/azurecurve/azrcrv-add-open-graph-tags/releases/latest/)
* Add Twitter Cards - [details](https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/) / [download](https://github.com/azurecurve/azrcrv-add-twitter-cards/releases/latest/)
* Avatars - [details](https://development.azurecurve.co.uk/classicpress-plugins/avatars/) / [download](https://github.com/azurecurve/azrcrv-avatars/releases/latest/)
* BBCode - [details](https://development.azurecurve.co.uk/classicpress-plugins/bbcode/) / [download](https://github.com/azurecurve/azrcrv-bbcode/releases/latest/)
* Breadcrumbs - [details](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/) / [download](https://github.com/azurecurve/azrcrv-breadcrumbs/releases/latest/)
* Broken Links - [details](https://development.azurecurve.co.uk/classicpress-plugins/broken-links/) / [download](https://github.com/azurecurve/azrcrv-broken-links/releases/latest/)
* Call-out Boxes - [details](https://development.azurecurve.co.uk/classicpress-plugins/call-out-boxes/) / [download](https://github.com/azurecurve/azrcrv-call-out-boxes/releases/latest/)
* Chroma - [details](https://development.azurecurve.co.uk/classicpress-plugins/chroma/) / [download](https://github.com/azurecurve/azrcrv-chroma/releases/latest/)
* Code - [details](https://development.azurecurve.co.uk/classicpress-plugins/code/) / [download](https://github.com/azurecurve/azrcrv-code/releases/latest/)
* Comment Validator - [details](https://development.azurecurve.co.uk/classicpress-plugins/comment-validator/) / [download](https://github.com/azurecurve/azrcrv-comment-validator/releases/latest/)
* Conditional Links - [details](https://development.azurecurve.co.uk/classicpress-plugins/conditional-links/) / [download](https://github.com/azurecurve/azrcrv-conditional-links/releases/latest/)
* Contact Forms - [details](https://development.azurecurve.co.uk/classicpress-plugins/contact-forms/) / [download](https://github.com/azurecurve/azrcrv-contact-forms/releases/latest/)
* Disable FLoC - [details](https://development.azurecurve.co.uk/classicpress-plugins/disable-floc/) / [download](https://github.com/azurecurve/azrcrv-disable-floc/releases/latest/)
* Display After Post Content - [details](https://development.azurecurve.co.uk/classicpress-plugins/display-after-post-content/) / [download](https://github.com/azurecurve/azrcrv-display-after-post-content/releases/latest/)
* Estimated Read Time - [details](https://development.azurecurve.co.uk/classicpress-plugins/estimated-read-time/) / [download](https://github.com/azurecurve/azrcrv-estimated-read-time/releases/latest/)
* Events - [details](https://development.azurecurve.co.uk/classicpress-plugins/events/) / [download](https://github.com/azurecurve/azrcrv-events/releases/latest/)
* Feed to Post - [details](https://development.azurecurve.co.uk/classicpress-plugins/feed-to-post/) / [download](https://github.com/azurecurve/azrcrv-feed-to-post/releases/latest/)
* Filtered Categories - [details](https://development.azurecurve.co.uk/classicpress-plugins/filtered-categories/) / [download](https://github.com/azurecurve/azrcrv-filtered-categories/releases/latest/)
* Flags - [details](https://development.azurecurve.co.uk/classicpress-plugins/flags/) / [download](https://github.com/azurecurve/azrcrv-flags/releases/latest/)
* Floating Featured Image - [details](https://development.azurecurve.co.uk/classicpress-plugins/floating-featured-image/) / [download](https://github.com/azurecurve/azrcrv-floating-featured-image/releases/latest/)
* Get GitHub File - [details](https://development.azurecurve.co.uk/classicpress-plugins/get-github-file/) / [download](https://github.com/azurecurve/azrcrv-get-github-file/releases/latest/)
* Icons - [details](https://development.azurecurve.co.uk/classicpress-plugins/icons/) / [download](https://github.com/azurecurve/azrcrv-icons/releases/latest/)
* Image Optimiser - [details](https://development.azurecurve.co.uk/classicpress-plugins/image-optimiser/) / [download](https://github.com/azurecurve/azrcrv-image-optimiser/releases/latest/)
* Images - [details](https://development.azurecurve.co.uk/classicpress-plugins/images/) / [download](https://github.com/azurecurve/azrcrv-images/releases/latest/)
* Insult Generator - [details](https://development.azurecurve.co.uk/classicpress-plugins/insult-generator/) / [download](https://github.com/azurecurve/azrcrv-insult-generator/releases/latest/)
* Load Admin CSS - [details](https://development.azurecurve.co.uk/classicpress-plugins/load-admin-css/) / [download](https://github.com/azurecurve/azrcrv-load-admin-css/releases/latest/)
* Loop Injection - [details](https://development.azurecurve.co.uk/classicpress-plugins/loop-injection/) / [download](https://github.com/azurecurve/azrcrv-loop-injection/releases/latest/)
* Lorem Ipsum Generator - [details](https://development.azurecurve.co.uk/classicpress-plugins/lorem-ipsum-generator/) / [download](https://github.com/azurecurve/azrcrv-lorem-ipsum-generator/releases/latest/)
* Maintenance Mode - [details](https://development.azurecurve.co.uk/classicpress-plugins/maintenance-mode/) / [download](https://github.com/azurecurve/azrcrv-maintenance-mode/releases/latest/)
* Markdown - [details](https://development.azurecurve.co.uk/classicpress-plugins/markdown/) / [download](https://github.com/azurecurve/azrcrv-markdown/releases/latest/)
* Nearby - [details](https://development.azurecurve.co.uk/classicpress-plugins/nearby/) / [download](https://github.com/azurecurve/azrcrv-nearby/releases/latest/)
* Page Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/page-index/) / [download](https://github.com/azurecurve/azrcrv-page-index/releases/latest/)
* Post Archive - [details](https://development.azurecurve.co.uk/classicpress-plugins/post-archive/) / [download](https://github.com/azurecurve/azrcrv-post-archive/releases/latest/)
* Quiz Engine - [details](https://development.azurecurve.co.uk/classicpress-plugins/quiz-engine/) / [download](https://github.com/azurecurve/azrcrv-quiz-engine/releases/latest/)
* Read GitHub File - [details](https://development.azurecurve.co.uk/classicpress-plugins/read-github-file/) / [download](https://github.com/azurecurve/azrcrv-read-github-file/releases/latest/)
* Redirect - [details](https://development.azurecurve.co.uk/classicpress-plugins/redirect/) / [download](https://github.com/azurecurve/azrcrv-redirect/releases/latest/)
* Remove Revisions - [details](https://development.azurecurve.co.uk/classicpress-plugins/remove-revisions/) / [download](https://github.com/azurecurve/azrcrv-remove-revisions/releases/latest/)
* RSS Feed - [details](https://development.azurecurve.co.uk/classicpress-plugins/rss-feed/) / [download](https://github.com/azurecurve/azrcrv-rss-feed/releases/latest/)
* RSS Suffix - [details](https://development.azurecurve.co.uk/classicpress-plugins/rss-suffix/) / [download](https://github.com/azurecurve/azrcrv-rss-suffix/releases/latest/)
* Series Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/series-index/) / [download](https://github.com/azurecurve/azrcrv-series-index/releases/latest/)
* Shortcodes in Comments - [details](https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/) / [download](https://github.com/azurecurve/azrcrv-shortcodes-in-comments/releases/latest/)
* Shortcodes in Widgets - [details](https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/) / [download](https://github.com/azurecurve/azrcrv-shortcodes-in-widgets/releases/latest/)
* SMTP - [details](https://development.azurecurve.co.uk/classicpress-plugins/smtp/) / [download](https://github.com/azurecurve/azrcrv-smtp/releases/latest/)
* Snippets - [details](https://development.azurecurve.co.uk/classicpress-plugins/snippets/) / [download](https://github.com/azurecurve/azrcrv-snippets/releases/latest/)
* String Inspector - [details](https://development.azurecurve.co.uk/classicpress-plugins/string-inspector/) / [download](https://github.com/azurecurve/azrcrv-string-inspector/releases/latest/)
* Strong Password Generator - [details](https://development.azurecurve.co.uk/classicpress-plugins/strong-password-generator/) / [download](https://github.com/azurecurve/azrcrv-strong-password-generator/releases/latest/)
* Tag Cloud - [details](https://development.azurecurve.co.uk/classicpress-plugins/tag-cloud/) / [download](https://github.com/azurecurve/azrcrv-tag-cloud/releases/latest/)
* Taxonomy Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-index/) / [download](https://github.com/azurecurve/azrcrv-taxonomy-index/releases/latest/)
* Taxonomy Order - [details](https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-order/) / [download](https://github.com/azurecurve/azrcrv-taxonomy-order/releases/latest/)
* Theme Switcher - [details](https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/) / [download](https://github.com/azurecurve/azrcrv-theme-switcher/releases/latest/)
* Timelines - [details](https://development.azurecurve.co.uk/classicpress-plugins/timelines) / [download](https://github.com/azurecurve/azrcrv-timelines/releases/latest/)
* Toggle Show/Hide - [details](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/) / [download](https://github.com/azurecurve/azrcrv-toggle-showhide/releases/latest/)
* Update Admin Menu - [details](https://development.azurecurve.co.uk/classicpress-plugins/update-admin-menu/) / [download](https://github.com/azurecurve/azrcrv-update-admin-menu/releases/latest/)
* URL Shortener - [details](https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/) / [download](https://github.com/azurecurve/azrcrv-url-shortener/releases/latest/)
* Username Protection - [details](https://development.azurecurve.co.uk/classicpress-plugins/username-protection/) / [download](https://github.com/azurecurve/azrcrv-username-protection/releases/latest/)
* View Counter - [details](https://development.azurecurve.co.uk/classicpress-plugins/view-counter/) / [download](https://github.com/azurecurve/azrcrv-view-counter/releases/latest/)
* Widget Announcements - [details](https://development.azurecurve.co.uk/classicpress-plugins/widget-announcements/) / [download](https://github.com/azurecurve/azrcrv-widget-announcements/releases/latest/)

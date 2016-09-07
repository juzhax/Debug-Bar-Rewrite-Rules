=== Debug Bar Rewrite Rules ===
Contributors: butuzov
Donate Link: http://wordpress.org
Tags: debug bar, rewrite rules
Requires at least: 3.4
Tested up to: 4.6
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Debug Bar Rewrite Rules adds a new panel to Debug Bar that displays information about WordPress Rewrites Rules (if used).

== Description ==

Debug Bar Transients adds information about WordPress Transients to a new panel in the Debug Bar. This plugin is an extension for [Debug Bar](http://wordpress.org/extend/plugins/debug-bar/), but it is also can work in standalone mode as admin tools page.

Once installed, you will have access to the following information:

* Number of existing rewrite rules
* List of rewrite rules
* List of available filter hooks that can affect rewrite rules.
* List of filters that affects rewrite rules.
Ability to search in rules with highlighting matches.
* Ability to test url and see what rules can be applied to it.
* Ability to flush rules directly from debug bar panel/tools page.

== Screenshots ==

1. Debug Bar Rewrite Rules Panel UI with a example data.
2. Searching in rules list alongside with filtering and highlighting occurrences.
3. Testing url for matches - show  matched rules and actual matches.

== Changelog ==

= 0.3 =
* [ui] UI Change - Domain input box width calculated with JS
* [bugfix] - e.preventDefault()
* [bugfix] - Double check for empty array in filters UI

= 0.2 =
* Code refactored from version 0.1

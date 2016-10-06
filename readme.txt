=== Gravity Forms Grouped Validation Add-On ===
Contributors: zaus
Donate link: http://drzaus.com/donate
Tags: contact form, gravity forms, validation, custom validation, gform_validation, add-on
Requires at least: 3.0
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv2 or later

Allows you to specify groups of 'at least one field required' in Gravity Forms.

== Description ==

An add-on to ['Gravity Forms'](http://www.gravityforms.com/), allows you to specify one or more groups of fields that require at least one field to be filled out.  Customize messaging per form or as default.

== Installation ==

1. Install plugin as usual
2. In Gravity Forms, edit a form
3. Add css class `gfv-group-req` to each field in the group ('Appearance' tab, 'Custom CSS Class' setting)
	* You may have as many custom css classes as desired, as long as one of them is `gfv-group-req`
	* For multiple groups, add a suffix after the class name like `gfv-group-req2` or `gfv-group-req-Second_Group`.  This suffix will also replace the `%s` placeholder with `Second Group` in the custom message.  Underscores (`_`) will be replaced with spaces.
4. Customize the messaging by editing the text file `gfv-grouped-val-msg.txt` included with the plugin (via Wordpress -- Plugins > Edit).  Given as a JSON-encoded file with keys corresponding to forms' id or title.  Use the placeholder `%s` if you want to include a group name in the message.

== Frequently Asked Questions ==

= It doesn't work right... =

Drop an issue at https://github.com/zaus/gf-grouped-val

== Screenshots ==

N/A.

== Changelog ==

= 0.4.1 =
* nicer customization

= 0.1 =
* started

== Upgrade Notice ==

N/A.
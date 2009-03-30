=== My Link Order ===
Contributors: froman118
Donate link: http://geekyweekly.com/mylinkorder
Tags: link, category, categories, order, sidebar, widget
Requires at least: 2.3.2
Tested up to: 2.7.1
Stable tag: 2.7.1

My Link Order allows you to set the order in which links and link categories will appear in the sidebar.

== Description ==

My Link Order allows you to set the order in which links and link categories will appear in the sidebar. Uses a drag 
and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes.

= Change Log =

2.7.1:

* If you're link categories don't show up for ordering your DB user account must have ALTER permissions, the plugin adds columns to store the order
* Added a call to $wpdb->show_errors(); to help debug any issues
* Added Spanish translation, thanks Karin

2.7:

* Updated for 2.7, now under the the new Links menu
* Moved to jQuery for drag and drop
* Removed finicky AJAX submission
* Translations added and thanks: Russian (Flector), Dutch (Anja)
* Keep those translations coming

2.6.1a:

* The plugin has been modified to be fully translated
* The widget now has a description

2.6.1:

* Finally no more taxonomy.php overwriting, well kind of. After you upgrade Wordpress visit the My Link Order page and it will perform the edit automatically.
* Thanks to Submarine at http://www.category-icons.com for the code.
* Also added string localization, email me if you are interested in translating.


== Installation ==

1. Copy plugin contents to /wp-content/plugins/my-link-order
2. Activate the My Link Order plugin on the Plugins menu
3. Go to the "My Link Order" tab under Manage and specify your desired order for link categories and links in each category
4. If you are using widgets then replace the standard "Links" widget with the "My Link Order" widget. That's it.
5. If you aren't using widgets, modify sidebar template to use correct filter(additional parameter seperated by ampersands):
	`wp_list_bookmarks('orderby=order&category_orderby=order');`


== Frequently Asked Questions ==

= Why modify a core file? =

The way link categories can be ordered is hardcoded at a very low level. Adding an ordering option at that level
makes it easy for people to modify their themes and helps overall compatibility.
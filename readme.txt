=== My Category Order ===
Contributors: froman118
Donate link: http://geekyweekly.com/mycategoryorder
Tags: categories, category, order, sidebar, widget
Requires at least: 2.5
Tested up to: 2.6.1
Stable tag: 2.6.1

My Category Order allows you to set the order in which categories will appear in the sidebar.

== Description ==

My Category Order allows you to set the order in which categories will appear in the sidebar. Uses a drag 
and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes.

= Change Log =

2.6.1 - Finally no more taxonomy.php overwriting, well kind of. After you upgrade Wordpress visit the My Category Order page and it will perform the edit automatically. Thanks to Submarine at http://www.category-icons.com for the code. Also added string localization, email me if you are interested in translating.

== Installation ==

1. Copy plugin contents to /wp-content/plugins/my-category-order
2. Activate the My Category Order plugin on the Plugins menu
3. Go to the "My Category Order" tab under Manage and specify your desired order for post categories
   
4. If you are using widgets then replace the standard "Category" widget with the "My Category Order" widget. That's it.

5. If you aren't using widgets, modify sidebar template to use correct orderby value:
	wp_list_categories('orderby=order&title_li=');

== Frequently Asked Questions ==

= Why modify a core file? =

The way categories can be ordered is hardcoded at a very low level. Adding an ordering option at that level
makes it easy for people to modify their themes and helps overall compatibility.

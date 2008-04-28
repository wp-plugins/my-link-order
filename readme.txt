=== My Category Order ===
Contributors: froman118
Donate link: http://geekyweekly.com/mycategoryorder
Tags: categories, category, order, sidebar, widget
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 2.5.1

My Category Order allows you to set the order in which categories will appear in the sidebar.

== Description ==

My Category Order allows you to set the order in which categories will appear in the sidebar. Uses a drag 
and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes.


== Installation ==

1. Move mycategoryorder.php to /wp-content/plugins/
2. Move taxonomy.php to /wp-includes/
3. Activate the My Category Order plugin on the Plugins menu
4. Go to the "My Category Order" tab under Manage and specify your desired order for post categories
   
5. If you are using widgets then replace the standard "Category" widget with the "My Category Order" widget. That's it.

6. If you aren't using widgets, modify sidebar template to use correct orderby value:
	wp_list_categories('orderby=order&title_li=');

== Frequently Asked Questions ==

= Why modify a core file? =

The way categories can be ordered is hardcoded at a very low level. Adding an ordering option at that level
makes it easy for people to modify their themes and helps overall compatibility. It also makes my life easier 
since I don't have to duplicate lots of core functions in my plugin just to add a case to one select block.


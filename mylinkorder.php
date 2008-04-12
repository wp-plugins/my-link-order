<?php
/*
Plugin Name: My Link Order
Plugin URI: http://www.geekyweekly.com/mylinkorder
Description: My Link Order allows you to set the order in which links and link categories will appear in the sidebar. Uses a drag and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes.
Version: 2.5
Author: froman118
Author URI: http://www.geekyweekly.com
Author Email: froman118@gmail.com
*/

function mylinkorder_init() {

function mylinkorder_menu()
{   if (function_exists('add_submenu_page')) {
        $location = "../wp-content/plugins/";
        add_submenu_page("edit.php", 'My Link Order', 'My Link Order', 2,"mylinkorder",'mylinkorder');
    }

}
function mylinkorder_js_libs() {
  if ( $_GET['page'] == "mylinkorder" ) {
	    wp_enqueue_script('scriptaculous');
	} 
}

add_action('admin_menu', 'mylinkorder_menu');
add_action('admin_menu', 'mylinkorder_js_libs');

function mylinkorder()
{
global $wpdb;
$mode = "";
$mode = $_GET['mode'];

$query = mysql_query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'") or die(mysql_error());

if (mysql_num_rows($query) == 0) {
	$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
}

$query2 = mysql_query("SHOW COLUMNS FROM $wpdb->links LIKE 'link_order'") or die(mysql_error());

if (mysql_num_rows($query2) == 0) {
	$wpdb->query("ALTER TABLE $wpdb->links ADD `link_order` INT( 4 ) NULL DEFAULT '0'");
}

if($mode == "act_OrderCategories")
{  
	$idString = $_GET['idString'];
	$catIDs = explode(",", $idString);
	$result = count($catIDs);
	for($i = 0; $i <= $result; $i++)
	{	$wpdb->query("UPDATE $wpdb->terms SET term_order = '$i' WHERE term_id ='$catIDs[$i]'"); }
}
else if($mode == "act_OrderLinks")
{  
	$idString = $_GET['idString'];
	$linkIDs = explode(",", $idString);
	$result = count($linkIDs);
	for($i = 0; $i <= $result; $i++)
	{	$wpdb->query("UPDATE $wpdb->links SET link_order = '$i' WHERE link_id ='$linkIDs[$i]'"); }
}
else if($mode == "dsp_OrderLinks")
{
	$catID = $_GET['catID'];
	$results=$wpdb->get_results("SELECT * FROM $wpdb->links l inner join $wpdb->term_relationships tr on l.link_id = tr.object_id inner join $wpdb->term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $wpdb->terms t on t.term_id = tt.term_id WHERE t.term_id = $catID ORDER BY link_order ASC");
    $cat_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id=$catID");
	?>
	
<div class='wrap'>
	<h2>Order Links for <?=$cat_name?></h2>
	<p>Order the links by dragging and dropping them into the desired order.</p>
	<div id="order" style="width: 500px; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2;"><?php
	foreach($results as $row)
	{
		echo "<div id='item_$row->link_id' class='lineitem'>$row->link_name</div>";
	}?>
	</div>

	<input type="button" id="orderButton" Value="Click to Order Links" onclick="javascript:orderLinks();">&nbsp;&nbsp;<strong id="updateText"></strong>
	<br /><br />
	<a href='edit.php?page=mylinkorder'>Go Back</a>

</div>

<?php
}
else
{
	$results=$wpdb->get_results("SELECT DISTINCT t.term_id, name FROM $wpdb->term_taxonomy tt inner join $wpdb->term_relationships tr on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $wpdb->terms t on t.term_id = tt.term_id where taxonomy = 'link_category' ORDER BY t.term_order ASC");
	?>
<div class='wrap'>
	<h2>My Link Order</h2>
	<p>Choose a category from the drop down to order the links in that category or order the categories by dragging and dropping them.</p>

	<h3>Order Links</h3>
	
	<select id="cats" name='cats'><?php
	foreach($results as $row)
	{
	    echo "<option value='$row->term_id'>$row->name</option>";
	}?>
	</select>
	&nbsp;<input type="button" name="edit" Value="Order Links in this Category" onClick="javascript:goEdit();">

	<h3>Order Link Categories</h3>

	<div id="order" style="width: 500px; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2;"><?php 
	foreach($results as $row)
	{
		echo "<div id='item_$row->term_id' class='lineitem'>$row->name</div>";
	}?>
	</div>
	<input type="button" id="orderButton" Value="Click to Order Categories" onclick="javascript:orderLinkCats();">&nbsp;&nbsp;<strong id="updateText"></strong>
</div>
<?php
}
?>
<style>
	div.lineitem {
		margin: 3px 0px;
		padding: 2px 5px 2px 5px;
		background-color: #F1F1F1;
		border:1px solid #B2B2B2;
		cursor: move;
	}
</style>

<script language="JavaScript" type="text/javascript">
	Sortable.create('order',{tag:'div'});

	function orderLinkCats() {

		$("orderButton").style.display = "none";
		$("updateText").innerHTML = "Updating Link Category Order...";
		var alerttext = '';
		var order = Sortable.serialize('order');
		alerttext = Sortable.sequence('order');

		new Ajax.Request('edit.php?page=mylinkorder&mode=act_OrderCategories&idString='+alerttext, {
		 onSuccess: function(){
      			new Effect.Highlight('order', {startcolor:'#F9FC4A', endcolor:'#CFEBF7',restorecolor:'#CFEBF7', duration: 1.5, queue: 'front'})
				new Effect.Highlight('order', {startcolor:'#CFEBF7', endcolor:'#ffffff',restorecolor:'#ffffff', duration: 1.5, queue: 'end'})
				$("updateText").innerHTML = "Link Categories updated successfully.";
				$("orderButton").style.display = "inline";
   			 }
		  });
		return false;
	}

	function orderLinks() {
		$("orderButton").style.display = "none";
		$("updateText").innerHTML = "Updating Link Order...";
		var alerttext = '';
		var order = Sortable.serialize('order');
		alerttext = Sortable.sequence('order');

		new Ajax.Request('edit.php?page=mylinkorder&mode=act_OrderLinks&idString='+alerttext, {
		 onSuccess: function(){
      			new Effect.Highlight('order', {startcolor:'#F9FC4A', endcolor:'#CFEBF7',restorecolor:'#CFEBF7', duration: 1.5, queue: 'front'})
				new Effect.Highlight('order', {startcolor:'#CFEBF7', endcolor:'#ffffff',restorecolor:'#ffffff', duration: 1.5, queue: 'end'})
				$("updateText").innerHTML = "Links updated successfully.";
				$("orderButton").style.display = "inline";
   			 }
		  });
		return false;
	}

    function goEdit ()
    {
		if($("cats").value != "")
			location.href="edit.php?page=mylinkorder&mode=dsp_OrderLinks&catID="+$("cats").value;
	}
</script>

<?php
    }
    if ( function_exists('register_sidebar_widget') && function_exists('register_widget_control') ){	

    	function wp_widget_mylinkorder($args) {
			extract($args);
			$options = get_option('widget_mylinkorder');
			$i = $options['show_images'] ? '1' : '0';
			$d = $options['show_description'] ? '1' : '0';
			$r = $options['show_rating'] ? '1' : '0';
			$u = $options['show_updated'] ? '1' : '0';
			$c = $options['categorize'] ? '0' : '1';
			$cat_title = $options['cat_title'];
			
			wp_list_bookmarks(array(
					'orderby' => 'order', 'category_orderby' => 'order',
					'title_before' => $before_title, 'title_after' => $after_title,
					'category_before' => $before_widget, 'category_after' => $after_widget, 
					'class' => 'linkcat widget','show_images' => $i,
					'show_description' => $d,'show_rating' => $r,'show_updated' => $u, 
					'categorize' => $c, 'title_li' => $cat_title)); 
		}

    }

    function wp_widget_mylinkorder_control() {
	$options = $newoptions = get_option('widget_mylinkorder');
	if ( $_POST['menu-submit'] ) {
	    $newoptions['show_images'] = isset($_POST['show_images']);
	    $newoptions['show_description'] = isset($_POST['show_description']);
	    $newoptions['show_rating'] = isset($_POST['show_rating']);
		$newoptions['show_updated'] = isset($_POST['show_updated']);
		$newoptions['categorize'] = isset($_POST['categorize']);
		$newoptions['cat_title'] = strip_tags(stripslashes($_POST['cat_title']));
	}
	if ( $options != $newoptions ) {
	    $options = $newoptions;
	    update_option('widget_mylinkorder', $options);
	}
	$show_images = $options['show_images'] ? 'checked="checked"' : '';
	$show_description = $options['show_description'] ? 'checked="checked"' : '';
	$show_rating = $options['show_rating'] ? 'checked="checked"' : '';
	$show_updated = $options['show_updated'] ? 'checked="checked"' : '';
	$categorize = $options['categorize'] ? 'checked="checked"' : '';
	$cat_title = attribute_escape($options['cat_title']);
	
?>

	    
	<p style="text-align:left; float:left;"><label for="show_images"><?php _e('Show Images?'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_images; ?> id="show_images" name="show_images" /></label></p>
	    
	<p style="text-align:right; float:right;"><label for="show_description"><?php _e('Show Descriptions?'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_description; ?> id="show_description" name="show_description" /></label></p>

	<p style="text-align:left; float:left;"><label for="show_rating"><?php _e('Show Rating?'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_rating; ?> id="show_rating" name="show_rating" /></label></p>

	<p style="text-align:right; float:right;"><label for="show_updated"><?php _e('Show Timestamp?'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_updated; ?> id="show_updated" name="show_updated" /></label></p>
	
		<p style="clear:both; text-align:right;"><label for="categorize"><?php _e('Uncategorized?'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $categorize; ?> id="categorize" name="categorize" /></label></p>
	
	<p style="text-align:right;"><label for="cat_title"><?php _e('Title (only used if Uncategorized is checked):'); ?><br /><input style="width: 250px;" id="cat_title" name="cat_title" type="text" value="<?php echo $cat_title; ?>" /></label></p>

	    <input type="hidden" id="menu-submit" name="menu-submit" value="1" />
<?php
    }

$class['classname'] = 'widget_links';
wp_register_sidebar_widget('mylinkorder', 'My Link Order', 'wp_widget_mylinkorder', $class);
wp_register_widget_control('mylinkorder', 'My Link Order', 'wp_widget_mylinkorder_control');

}

/* Delays plugin execution until Dynamic Sidebar has loaded first. */
add_action('plugins_loaded', 'mylinkorder_init');

?>

<?php
/*
Plugin Name: My Category Order
Plugin URI: http://www.geekyweekly.com/mycategoryorder
Description: My Category Order allows you to set the order in which categories will appear in the sidebar. Uses a drag and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes.
Version: 2.5.1
Author: froman118
Author URI: http://www.geekyweekly.com
Author Email: froman118@gmail.com
*/

function mycategoryorder_init() {

    function mycategoryorder_menu()
    {   if (function_exists('add_submenu_page')) {
	$location = "../wp-content/plugins/";
	add_submenu_page("edit.php", 'My Category Order', 'My Category Order', 2,"mycategoryorder",'mycategoryorder');
    }

    }
    function mycategoryorder_js_libs() {
	if ( $_GET['page'] == "mycategoryorder" ) {
	    wp_enqueue_script('scriptaculous');
	} 
    }
    add_action('admin_menu', 'mycategoryorder_menu');
    add_action('admin_menu', 'mycategoryorder_js_libs');

    function mycategoryorder()
    {

	global $wpdb;
	$mode = "";
	$mode = $_GET['mode'];
	$parentID = 0;
	if (isset($_GET['parentID']))
	    $parentID = $_GET['parentID'];

	$query = mysql_query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'") or die(mysql_error());
	
	if (mysql_num_rows($query) == 0) {
		$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
	}

	if($mode == "act_OrderCategories")
	{  $idString = $_GET['idString'];
	$catIDs = explode(",", $idString);
	$result = count($catIDs);
	for($i = 0; $i < $result; $i++)
	{	
	    $wpdb->query("UPDATE $wpdb->terms SET term_order = '$i' WHERE term_id ='$catIDs[$i]'");
	}
	}
	else
	{
	    $subCatStr = "";

		$results=$wpdb->get_results("SELECT t.term_id, t.name FROM $wpdb->term_taxonomy tt, $wpdb->terms t, $wpdb->term_taxonomy tt2 WHERE tt.parent = $parentID AND tt.taxonomy = 'category' AND t.term_id = tt.term_id AND tt2.parent = tt.term_id GROUP BY t.term_id, t.name HAVING COUNT(*) > 0 ORDER BY t.term_order ASC");
	    foreach($results as $row)
	    {
			$subCatStr = $subCatStr."<option value='$row->term_id'>$row->name</option>";
	    }
?>
	<div class='wrap'>
	<h2>My Category Order</h2>
	<p>Choose a category from the drop down to order subcategories in that category or order the categories on this level by dragging and dropping them into the desired order.</p>

<?php 
	    if($parentID != 0)
	    {
			$parentsParent = $wpdb->get_row("SELECT parent FROM $wpdb->term_taxonomy WHERE term_id = $parentID ", ARRAY_N);
			echo "<a href='edit.php?page=mycategoryorder&parentID=$parentsParent[0]'>Return to parent category</a>";
	    }

	    if($subCatStr != "")
	    { ?>
	<h3>Order Subcategories</h3>
	<select id="cats" name="cats">
		<?php echo $subCatStr; ?>
	</select>
	&nbsp;<input type="button" name="edit" Value="Order Subcategories" onClick="javascript:goEdit();">
<?php }
	$results=$wpdb->get_results("SELECT * FROM $wpdb->terms t inner join $wpdb->term_taxonomy tt on t.term_id = tt.term_id WHERE taxonomy = 'category' and parent = $parentID ORDER BY term_order ASC"); ?>
	<h3>Order Categories</h3>
	    <div id="order" style="width: 500px; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2;">
		<?php foreach($results as $row)
		{
			echo "<div id='item_$row->term_id' class='lineitem'>$row->name</div>";
		}?>
	</div>

	<input type="button" id="orderButton" Value="Click to Order Categories" onclick="javascript:orderCats();">&nbsp;&nbsp;<strong id="updateText"></strong>

<?php
	}
?>
</div>

<style>
	div.lineitem {
		margin: 3px 0px;
		padding: 2px 5px 2px 5px;
		background-color: #F1F1F1;
		border:1px solid #B2B2B2;
		cursor: move;
	}
</style>

	<script language="JavaScript">
		Sortable.create('order',{tag:'div'});
	
		function orderCats() {
	
			$("orderButton").style.display = "none";
			$("updateText").innerHTML = "Updating Category Order...";
			var alerttext = '';
			var order = Sortable.serialize('order');
			alerttext = Sortable.sequence('order');
	
			new Ajax.Request('edit.php?page=mycategoryorder&mode=act_OrderCategories&idString='+alerttext, {
			onSuccess: function(){
				new Effect.Highlight('order', {startcolor:'#F9FC4A', endcolor:'#CFEBF7',restorecolor:'#CFEBF7', duration: 1.5, queue: 'front'})
				new Effect.Highlight('order', {startcolor:'#CFEBF7', endcolor:'#ffffff',restorecolor:'#ffffff', duration: 1.5, queue: 'end'})
				$("updateText").innerHTML = "Categories updated successfully.";
	
				$("orderButton").style.display = "inline";
			}
			});
			return false;
		}
		function goEdit ()
		{
			if($("cats").value != "")
			location.href="edit.php?page=mycategoryorder&mode=dsp_OrderCategories&parentID="+$("cats").value;
		}
	</script>

<?php
    }

    if ( function_exists('register_sidebar_widget') && function_exists('register_widget_control') ){	

    function wp_widget_mycategoryorder($args) {
		extract($args);
		$options = get_option('widget_mycategoryorder');
		$c = $options['count'] ? '1' : '0';
		$h = $options['hierarchical'] ? '1' : '0';
		$d = $options['dropdown'] ? '1' : '0';
		$u = $options['update'] ? '1' : '0';
		$empty = $options['hide_empty'] ? '0' : '1';
		$title = empty($options['title']) ? __('Categories') : $options['title'];
		$i = empty($options['include']) ? '' : $options['include'];
		$e = empty($options['exclude']) ? '' : $options['exclude'];
		$ft = empty($options['feedtext']) ? '' : $options['feedtext'];
		$fi = empty($options['feedimage']) ? '' : $options['feedimage'];
	
		echo $before_widget;
		echo $before_title . $title . $after_title; 
	
		$cat_args = "orderby=order&order=ASC&show_count={$c}&hierarchical={$h}&hide_empty={$empty}&show_last_updated={&u}";
		
		if($e != '')
			$cat_args = $cat_args . "&exclude={$e}";
		if($i != '')
			$cat_args = $cat_args . "&include={$i}";
		if($ft != '')
			$cat_args = $cat_args . "&feed={$ft}";
		if($fi != '')
			$cat_args = $cat_args . "&feed_image={$fi}";
	
		if($d) {
			wp_dropdown_categories($cat_args . '&show_option_none= ' . __('Select Category'));
	?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				var dropdown = document.getElementById("cat");
				function onCatChange() {
					if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
						location.href = "<?php echo get_option('home'); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
					}
				}
				dropdown.onchange = onCatChange;
			/* ]]> */
			</script>
	
		<?php
		} else {
		?>
			<ul>
			<?php 
			wp_list_categories($cat_args . '&title_li='); 
			?>
			</ul>
			<?php
		}
	
		echo $after_widget;
    }

    function wp_widget_mycategoryorder_control() {
		$options = $newoptions = get_option('widget_mycategoryorder');
		if ( $_POST['mco_submit'] ) {
			$newoptions['count'] = isset($_POST['mco_count']);
			$newoptions['hierarchical'] = isset($_POST['mco_hierarchical']);
			$newoptions['dropdown'] = isset($_POST['mco_dropdown']);
			$newoptions['update'] = isset($_POST['mco_update']);
			$newoptions['hide_empty'] = isset($_POST['mco_empty']);
			$newoptions['feedtext'] = strip_tags(stripslashes($_POST['mco_feedtext']));
			$newoptions['feedimage'] = addslashes($_POST['mco_feedimage']);
			$newoptions['title'] = strip_tags(stripslashes($_POST['mco_title']));
			$newoptions['include'] = strip_tags(stripslashes($_POST['mco_include']));
			$newoptions['exclude'] = strip_tags(stripslashes($_POST['mco_exclude']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_mycategoryorder', $options);
		}
		$mco_count = $options['count'] ? 'checked="checked"' : '';
		$mco_hierarchical = $options['hierarchical'] ? 'checked="checked"' : '';
		$mco_dropdown = $options['dropdown'] ? 'checked="checked"' : '';
		$mco_empty = $options['hide_empty'] ? 'checked="checked"' : '';
		$mco_update = $options['update'] ? 'checked="checked"' : '';
		$mco_title = attribute_escape($options['title']);
		$mco_feedtext = attribute_escape($options['feedtext']);
		$mco_feedimage = attribute_escape($options['feedimage']);
		$mco_exclude = attribute_escape($options['exclude']);
		$mco_include = attribute_escape($options['include']);
		?>
		<p>
			<label for="mco_title"><?php _e('Title:'); ?></label> 
			<input style="width: 200px;" id="mco_title" name="mco_title" type="text" value="<?php echo $mco_title; ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $mco_dropdown; ?> id="mco_dropdown" name="mco_dropdown" />
			<label for="mco_dropdown"><?php _e('Show as Drop Down'); ?></label> 
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $mco_count; ?> id="mco_count" name="mco_count" />
			<label for="mco_count"><?php _e('Show Post Counts'); ?></label> 
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $mco_hierarchical; ?> id="mco_hierarchical" name="mco_hierarchical" />
			<label for="mco_hierarchical"><?php _e('Show Hierarchy'); ?></label> 
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $mco_update; ?> id="mco_update" name="mco_update" />
			<label for="mco_update"><?php _e('Show Update Date'); ?></label> 
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $mco_empty; ?> id="mco_empty" name="mco_empty" />
			<label for="mco_empty"><?php _e('Show Empty'); ?></label> 
		</p>
		<p>
			<label for="mco_feedtext"><?php _e('Feed Text:'); ?></label> 
			<input style="width: 200px;" id="mco_feedtext" name="mco_feedtext" type="text" value="<?php echo $mco_feedtext; ?>" />
		</p>
		<p>
			<label for="mco_feedimage"><?php _e('Feed Image URL:'); ?></label> 
			<input style="width: 200px;" id="mco_feedimage" name="mco_feedimage" type="text" value="<?php echo $mco_feedimage; ?>" />
			<br />
			<?php _e('Example: copy this icon icon,'); ?> <img src="<?php bloginfo('url'); ?>/wp-includes/images/rss.png" alt="RSS" />
		</p>
		<p>
			<label for="mco_exclude"><?php _e('Exclude:'); ?></label> 
			<input style="width: 200px;" id="mco_exclude" name="mco_exclude" type="text" value="<?php echo $mco_exclude; ?>" />
		</p>
		<p>
			<label for="mco_include"><?php _e('Include:'); ?></label> 
			<input style="width: 200px;" id="mco_include" name="mco_include" type="text" value="<?php echo $mco_include; ?>" />
		</p>
		<input type="hidden" id="mco_submit" name="mco_submit" value="1" />
<?php
    }
}

$class['classname'] = 'widget_categories';
wp_register_sidebar_widget('mycategoryorder', 'My Category Order', 'wp_widget_mycategoryorder', $class);
wp_register_widget_control('mycategoryorder', 'My Category Order', 'wp_widget_mycategoryorder_control', $options);

}

/* Delays plugin execution until Dynamic Sidebar has loaded first. */
add_action('plugins_loaded', 'mycategoryorder_init');

?>
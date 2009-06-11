<?php
/*
Plugin Name: My Link Order
Plugin URI: http://www.geekyweekly.com/mylinkorder
Description: My Link Order allows you to set the order in which links and link categories will appear in the sidebar. Uses a drag and drop interface for ordering. Adds a widget with additional options for easy installation on widgetized themes. Visit the My Link Order page after updating Wordpress to apply essential file patches.
Version: 2.8
Author: froman118
Author URI: http://www.geekyweekly.com
Author Email: froman118@gmail.com
*/

function mylinkorder_init() {

function mylinkorder_menu()
{   if (function_exists('add_submenu_page'))
        add_submenu_page(mylinkorder_getTarget(), 'My Link Order', 'My Link Order', 5, "mylinkorder", 'mylinkorder');
}

function mylinkorder_js_libs() {
	if ( $_GET['page'] == "mylinkorder" ) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}
}

//Switch page target depending on version
function mylinkorder_getTarget() {
	global $wp_version;
	if (version_compare($wp_version, '2.6.5', '>'))
		return "link-manager.php";
	else
		return "edit.php";
}

add_action('admin_menu', 'mylinkorder_menu');
add_action('admin_menu', 'mylinkorder_js_libs');

function mylinkorder()
{
global $wpdb;
$mode = "";
$mode = $_GET['mode'];
$success = "";
$catID = "";

$wpdb->show_errors();

$query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");

if ($query1 == 0) {
	$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
}

$query2 = $wpdb->query("SHOW COLUMNS FROM $wpdb->links LIKE 'link_order'");

if ($query2 == 0) {
	$wpdb->query("ALTER TABLE $wpdb->links ADD `link_order` INT( 4 ) NULL DEFAULT '0'");
}

if($mode == "act_OrderCategories")
{
	$idString = $_GET['idString'];
	$catIDs = explode(",", $idString);
	$result = count($catIDs);
	for($i = 0; $i <= $result; $i++)
	{	$wpdb->query("UPDATE $wpdb->terms SET term_order = '$i' WHERE term_id ='$catIDs[$i]'"); }
	$success = '<div id="message" class="updated fade"><p>'. __('Link Categories updated successfully.', 'mylinkorder').'</p></div>';
}

if($mode == "act_OrderLinks")
{
	$idString = $_GET['idString'];
	$linkIDs = explode(",", $idString);
	$result = count($linkIDs);
	for($i = 0; $i <= $result; $i++)
	{	$wpdb->query("UPDATE $wpdb->links SET link_order = '$i' WHERE link_id ='$linkIDs[$i]'"); }
	$success = '<div id="message" class="updated fade"><p>'. __('Links updated successfully.', 'mylinkorder').'</p></div>';
	$mode = "dsp_OrderLinks";
}

if($mode == "dsp_OrderLinks")
{
	$catID = $_GET['catID'];
	$results=$wpdb->get_results("SELECT * FROM $wpdb->links l inner join $wpdb->term_relationships tr on l.link_id = tr.object_id inner join $wpdb->term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $wpdb->terms t on t.term_id = tt.term_id WHERE t.term_id = $catID ORDER BY link_order ASC");
    $cat_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id=$catID");
	?>

<div class='wrap'>
	<h2><?php _e('Order Links for', 'mylinkorder') ?> <?=$cat_name?></h2>
	<?php echo $success; ?>
	<p><?php _e('Order the links by dragging and dropping them into the desired order.', 'mylinkorder') ?></p>
	<ul id="order" style="width: 500px; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;"><?php
	foreach($results as $row)
	{
		echo "<li id='$row->link_id' class='lineitem'>$row->link_name</li>";
	}?>
	</ul>

	<input type="button" id="orderButton" Value="<?php _e('Click to Order Links', 'mylinkorder') ?>" onclick="javascript:orderLinks();">&nbsp;&nbsp;<strong id="updateText"></strong>
	<br /><br />
	<a href='<?php echo mylinkorder_getTarget(); ?>?page=mylinkorder'><?php _e('Go Back', 'mylinkorder') ?></a>

</div>

<?php
}
else
{
	$results=$wpdb->get_results("SELECT DISTINCT t.term_id, name FROM $wpdb->term_taxonomy tt inner join $wpdb->term_relationships tr on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $wpdb->terms t on t.term_id = tt.term_id where taxonomy = 'link_category' ORDER BY t.term_order ASC");
	?>
<div class='wrap'>
	<h2><?php _e('My Link Order', 'mylinkorder') ?></h2>
	<?php echo $success; ?>
	<?php 
		
			mylinkorder_check_taxonomy_file(); 
	?>
	<p><?php _e('Choose a category from the drop down to order the links in that category or order the categories by dragging and dropping them.', 'mylinkorder') ?></p>

	<h3><?php _e('Order Links', 'mylinkorder') ?></h3>

	<select id="cats" name='cats'><?php
	foreach($results as $row)
	{
	    echo "<option value='$row->term_id'>$row->name</option>";
	}?>
	</select>
	&nbsp;<input type="button" name="edit" Value="<?php _e('Order Links in this Category', 'mylinkorder') ?>" onClick="javascript:goEdit();">

	<h3><?php _e('Order Link Categories', 'mylinkorder') ?></h3>

	<ul id="order" style="width: 500px; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;"><?php
	foreach($results as $row)
	{
		echo "<li id='$row->term_id' class='lineitem'>$row->name</li>";
	}?>
	</ul>
	<input type="button" id="orderButton" Value="<?php _e('Click to Order Categories', 'mylinkorder') ?>" onclick="javascript:orderLinkCats();">&nbsp;&nbsp;<strong id="updateText"></strong>
</div>
<?php
}
?>
<style>
	li.lineitem {
		margin: 3px 0px;
		padding: 2px 5px 2px 5px;
		background-color: #F1F1F1;
		border:1px solid #B2B2B2;
		cursor: move;
		width: 490px;
	}
</style>

<script language="JavaScript" type="text/javascript">

	jQuery(document).ready(function(){
		jQuery("#order").sortable({ 
			placeholder: "ui-selected", 
			revert: false,
			tolerance: "pointer" 
		});
	});

	function orderLinkCats() {
		jQuery("#orderButton").css("display", "none");
		jQuery("#updateText").html("<?php _e('Updating Link Category Order...', 'mylinkorder') ?>");
		
		idList = jQuery("#order").sortable("toArray");
		location.href = '<?php echo mylinkorder_getTarget(); ?>?page=mylinkorder&mode=act_OrderCategories&idString='+idList;
	}

	function orderLinks() {
		jQuery("#orderButton").css("display", "none");
		jQuery("#updateText").html("<?php _e('Updating Link Order...', 'mylinkorder') ?>");
		
		idList = jQuery("#order").sortable("toArray");
		location.href = '<?php echo mylinkorder_getTarget(); ?>?page=mylinkorder&mode=act_OrderLinks&catID=<?php echo $catID; ?>&idString='+idList;
	}

    function goEdit ()
    {
		if(jQuery("#cats").val() != "")
			location.href="<?php echo mylinkorder_getTarget(); ?>?page=mylinkorder&mode=dsp_OrderLinks&catID="+jQuery("#cats").val();
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
			$e = $options['exclude_category'];
			$include = $options['include_category'];
			$b = $options['between'];
			if($b == '')
				$b = "\n";
				
			$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);

			wp_list_bookmarks(array(
					'orderby' => 'order', 'category_orderby' => 'order',
					'title_before' => $before_title, 'title_after' => $after_title,
					'category_before' => $before_widget, 'category_after' => $after_widget,
					'class' => 'linkcat widget','show_images' => $i, 'between' => $b,
					'show_description' => $d,'show_rating' => $r,'show_updated' => $u, 'category' => $include,
					'categorize' => $c, 'title_li' => $cat_title, 'exclude_category' => $e));
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
		$newoptions['exclude_category'] = strip_tags(stripslashes($_POST['exclude_category']));
		$newoptions['include_category'] = strip_tags(stripslashes($_POST['include_category']));
		$newoptions['between'] = addslashes($_POST['between']);
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
	$exclude_category = attribute_escape($options['exclude_category']);
	$include_category = attribute_escape($options['include_category']);
	$between = $options['between'];

?>


	<p style="text-align:left; float:left;"><label for="show_images"><?php _e('Show Images?', 'mylinkorder'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_images; ?> id="show_images" name="show_images" /></label></p>

	<p style="text-align:right; float:right;"><label for="show_description"><?php _e('Show Descriptions?', 'mylinkorder'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_description; ?> id="show_description" name="show_description" /></label></p>

	<p style="text-align:left; float:left;"><label for="show_rating"><?php _e('Show Rating?', 'mylinkorder'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_rating; ?> id="show_rating" name="show_rating" /></label></p>

	<p style="text-align:right; float:right;"><label for="show_updated"><?php _e('Show Timestamp?', 'mylinkorder'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $show_updated; ?> id="show_updated" name="show_updated" /></label></p>

	<p style="clear:both; text-align:right;"><label for="categorize"><?php _e('Uncategorized?', 'mylinkorder'); ?>&nbsp;<input class="checkbox" type="checkbox" <?php echo $categorize; ?> id="categorize" name="categorize" /></label></p>

	<p style="text-align:right;"><label for="cat_title"><?php _e('Title (used if Uncategorized is checked):', 'mylinkorder'); ?><br /><input style="width: 250px;" id="cat_title" name="cat_title" type="text" value="<?php echo $cat_title; ?>" /></label></p>

	<p style="text-align:right;"><label for="between"><?php _e('Between (text between link and description):', 'mylinkorder'); ?><br /><input style="width: 250px;" id="between" name="between" type="text" value="<?php echo $between; ?>" /></label></p>

	<p style="text-align:right;"><label for="exclude_category"><?php _e('Exclude Categories (comma-delimited list of IDs):', 'mylinkorder'); ?><br /><input style="width: 250px;" id="exclude_category" name="exclude_category" type="text" value="<?php echo $exclude_category; ?>" /></label></p>

	<p style="text-align:right;"><label for="include_category"><?php _e('Include Categories (comma-delimited list of IDs):', 'mylinkorder'); ?><br /><input style="width: 250px;" id="include_category" name="include_category" type="text" value="<?php echo $include_category; ?>" /></label></p>

	<input type="hidden" id="menu-submit" name="menu-submit" value="1" />
<?php
    }

mylinkorder_loadtranslation();
$description = __( 'Set the order in which links will appear in the sidebar','mylinkorder' );
$widget_ops = array('classname' => 'widget_links', 'description' => $description );
wp_register_sidebar_widget('mylinkorder', 'My Link Order', 'wp_widget_mylinkorder', $class);
wp_register_widget_control('mylinkorder', 'My Link Order', 'wp_widget_mylinkorder_control');

}

add_action('plugins_loaded', 'mylinkorder_init');

function mylinkorder_loadtranslation() {
	load_plugin_textdomain('mylinkorder', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
}

function mylinkorder_check_taxonomy_file() {
	$path = ABSPATH . WPINC ;
	$filename = 'taxonomy.php';
	$fullfilename = $path.'/'.$filename;
	$message = '';
	$error = 0;
	$string = file_get_contents($fullfilename);
	$line_number = 0;
	
	$position = 0;
	
	global $wp_version;

	if (version_compare($wp_version, '2.7.2', '>'))
	{
		$searched_line = 'elseif ( empty($_orderby) || \'id\' == $_orderby )';
		$replace = 'else if ( \'order\' == $_orderby )
			$orderby = \'t.term_order\';'."\n".
			'elseif ( empty($_orderby) || \'id\' == $_orderby ) ';
	}
	else
	{
		$searched_line = '$orderby = \'t.term_group\';'."\n\t".'else'."\n";
		$replace = '$orderby = \'t.term_group\';
			else if ( \'order\' == $orderby )
			$orderby = \'t.term_order\';'."\n\t".'else'."\n";
	}
	
	// Search
	if (strpos($string,'t.term_order')===false) {
		$position = strpos($string, $searched_line);
		$line_number = substr_count(  substr($string, 0, $position)   ,"\n")+1;

		// Patch the file if it's writable
		if (is_writable($path.'/'.$filename)) {// patch the files
			$handle = fopen($fullfilename, "wb");
			$string = str_replace($searched_line, $replace, $string);
			if (!fwrite($handle, $string)) 
				$message = __('Error while writing to the file', 'mylinkorder').' '.$fullfilename.'.';
			else
				$message = __('File', 'mylinkorder').'&nbsp;<b>'.$fullfilename.'</b>&nbsp;'.__('has been patched successfully', 'mylinkorder').'.';

			fclose($handle);
		}
		else { // Or throw a message to the user
			$message  = __('The file', 'mylinkorder').'&nbsp;<b>'.$fullfilename.'</b>&nbsp;'. __('is not writable', 'mylinkorder').'.<br/>';
			$message .= __('You have 2 options', 'mylinkorder').':<br/>';
			$message .= '1. '.__('Change the permissions on the file and click on My Link Order again to patch it automatically', 'mylinkorder').'.<br/>';
			$message .= '2. '.__('Modify the file manually', 'mylinkorder').' :<br/>';
			$message .= __('After line number', 'mylinkorder').'&nbsp;<b>'.$line_number.'</b> :<br/>';
			$message .= '<code>'.str_replace('else','',$searched_line).'</code><br/>';
			$message .= __('add the following code:', 'mylinkorder').'<br/>';
			$message .= '<code>else if ( \'order\' == $orderby ) <br/>&nbsp;&nbsp;&nbsp;&nbsp;$orderby = \'t.term_order\';</code><br/>';
		}
	}

if (!empty($message)) {
			echo '<div id="message" class="updated fade"><p>'.$message.'</p></div>';
		}
}

?>

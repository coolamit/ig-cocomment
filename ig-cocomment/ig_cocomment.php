<?php
/*
Plugin Name: iG:CoComment
Plugin URI: http://blog.igeek.info/wp-plugins/ig-cocomment/
Feed URI: http://blog.igeek.info/still-fresh/category/wp-plugins/ig-cocomment/feed/
Description: Integrates <a href="http://www.cocomment.com/" target="_blank">coComment</a> into your blog, allowing its users to easily track their & others comments. <br /><strong>[</strong><a href="http://blog.igeek.info/wp-plugins/ig-cocomment/" target="_blank">Plugin Home</a><strong>]&nbsp;[</strong><a href="http://blog.igeek.info/still-fresh/category/wp-plugins/ig-cocomment/feed/" target="_blank">Plugin Feed (RSS2)</a><strong>]</strong>.
Version: 0.5
Author: Amit Gupta
Author URI: http://blog.igeek.info/
*/

/*******************************************************************
*	DON'T EDIT THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING
*	LOGIN TO WP-ADMIN SECTION TO CONFIGURE IN GUI, SEE THE
*	README FILE FOR MORE DETAILS
*******************************************************************/

define('IG_coComment_V', "0.5");	// Version of the Plugin
define('IG_coComment_OPT_NAME', "igco_options");	// Name of the Option stored in the DB
define('IG_coComment_NAME', "iG:CoComment");	// Name of the Plugin
define('IG_coComment_File', "ig_cocomment.php");	// Name of the Plugin File


$igCoWpVersion = floatval(get_bloginfo('version'));

// function for outputting coComment script
function igCoComment_Integrate() {
	global $user_ID,$user_identity;
	$htmlCode = "<script type=\"text/javascript\">\n";
	$htmlCode .= "var blogTool = \"WordPress\";\n";
	$htmlCode .= "var blogURL = \"".get_option('siteurl')."\";\n";
	$htmlCode .= "var blogTitle = \"".get_bloginfo('name')."\";\n";
	$htmlCode .= "var postURL = \"".get_permalink()."\";\n";
	$htmlCode .= "var postTitle = \"".the_title('','',false)."\";\n";
	if($user_ID) {
		$htmlCode .= "var commentAuthor = \"{$user_identity}\";\n";
	} else {
		$htmlCode .= "var commentAuthorFieldName = \"author\";\n";
	}
	$htmlCode .= "var commentAuthorLoggedIn = \"".((!$user_ID)?"false":"true")."\";\n";
	$htmlCode .= "var commentFormID = \"commentform\";\n";
	$htmlCode .= "var commentTextFieldName = \"comment\";\n";
	$htmlCode .= "var commentButtonName = \"submit\";\n";
	$htmlCode .= "var cocomment_force = false;\n";
	$htmlCode .= "</script>\n";
	if(IG_CoCo_Auto=="true") {
		$htmlCode .= "<script type=\"text/javascript\" src=\"http://www.cocomment.com/js/cocomment.js\"></script>\n";
	} elseif(IG_CoCo_TrackAll=="true") {
		$htmlCode .= "<script type=\"text/javascript\">\n";
		$htmlCode .= "cocoscript = document.createElement('script');\n";
		$htmlCode .= "cocoscript.setAttribute('id', 'cocomment-fetchlet');\n";
		$htmlCode .= "cocoscript.setAttribute('trackAllComments', 'true');\n";
		$htmlCode .= "cocoscript.setAttribute('src', 'http://www.cocomment.com/js/enabler.js');\n";
		$htmlCode .= "document.getElementsByTagName('head')[0].appendChild(cocoscript);\n";
		$htmlCode .= "</script>\n";
	}
	print($htmlCode);
}

//function for adding the sub-panel in the Options panel
function igCoCo_Menu() {
	if(function_exists('add_options_page')) {
		add_options_page(IG_coComment_NAME.'(v'.IG_coComment_V.') Configuration', IG_coComment_NAME, 8, basename(__FILE__), 'igCoCo_GUI');
	}
}


//function for the Admin GUI
function igCoCo_GUI() {
	global $user_level,$igCoWpVersion;
	get_currentuserinfo();
	//if user is not an admin equalling/above level 8, then don't give any GUI
	if ($user_level < 8) {
?><div class="wrap">
	<h2><?php print(IG_coComment_NAME."(v".IG_coComment_V.")"); ?> Configuration</h2>
	<br /><?php _e("<div style=\"color:#770000;\">You are not a <strong>LEVEL 8</strong> or above USER &amp; 
hence you cannot configure <strong>".IG_coComment_NAME."</strong>. If you are a <strong>LEVEL 8</strong> or above USER, 
then please Logout &amp; Login again.</div>"); ?><br />
</div><?php
		return;
	}
	if(!empty($_POST['igco_auto'])) {
		$igco_auto = strtolower(trim($_POST['igco_auto']));
		$igco_trackall = strtolower(trim($_POST['igco_trackall']));
		if($igco_auto=="true" && $igco_trackall=="true") {
			$igco_auto = "false";
		}
		$igCoCoOptNew = array();
		$igCoCoOptNew['VERSION'] = IG_coComment_V;
		$igCoCoOptNew['AUTO_ENABLE'] = $igco_auto;
		$igCoCoOptNew['TRACK_ALL'] = $igco_trackall;
		//update in the DB
		update_option(IG_coComment_OPT_NAME, $igCoCoOptNew);
		$igERR = "Successfully Saved Setting";
		unset($igCoCoOptNew);
	}
	//get fresh from DB
	$igCoCoOpt = get_option(IG_coComment_OPT_NAME);
	
	if(!empty($igERR)) {
		if($igCoWpVersion<2) {
			$igErrAttributes = " class=\"updated\"";
		} else {
			$igErrAttributes = " id=\"message\" class=\"updated fade\"";
		}
?>
<div<?php print($igErrAttributes); ?>><br /><strong><?php _e($igERR); ?></strong><br />&nbsp;</div>
<?php
	}
?>
<div class="wrap" style="width:86%;">
	<h2><?php print(IG_coComment_NAME."(v".IG_coComment_V.")"); ?> Configuration</h2>
	<br />You can configure <strong><?php print(IG_coComment_NAME); ?></strong> here. Its easy to configure as there are not 
	a lot of settings for you to change.<br /><br />
	<span style="color:#AA0000;"><strong>CAUTION:-</strong> Atleast one of the options below must be disabled. If you enable both 
	options then preference will be given to the "Track All" option.</span><br /><br />
	<fieldset name="igco_set1">
		<legend><?php _e('BASE SETTINGS'); ?></legend>
		<form method="post">
			<ul style="list-style:none;">
				<li>
					<label for="igco_auto"><strong>Auto Activate coComment:</strong></label>&nbsp;&nbsp;
					<select name="igco_auto" id="igco_auto">
						<option value="true"<?php if($igCoCoOpt['AUTO_ENABLE']=="true") { _e(" selected"); } ?>>YES</option>
						<option value="false"<?php if($igCoCoOpt['AUTO_ENABLE']=="false") { _e(" selected"); } ?>>NO</option>
					</select><br />
					<strong>{</strong> <em>Enabling this option will activate coComment automatically in supported browsers 
					for users logged into coComment. They won't have to click the bookmarklet to activate coComment explicitly. 
					In other words, enabling this option will make their life easier.</em> <strong>}</strong><br />&nbsp;
				</li>
				<li>
					<label for="igco_trackall"><strong>Allow coComment to Track all Comments:</strong></label>&nbsp;&nbsp;
					<select name="igco_trackall" id="igco_trackall">
						<option value="true"<?php if($igCoCoOpt['TRACK_ALL']=="true") { _e(" selected"); } ?>>YES</option>
						<option value="false"<?php if($igCoCoOpt['TRACK_ALL']=="false") { _e(" selected"); } ?>>NO</option>
					</select><br />
					<strong>{</strong> <em>Enabling this option will allow coComment to track all comments on the blog and not just 
					the ones made by coComment users. This is really beneficial since if not many coComment users comment on your blog 
					then also those who do use it will be notified of new comments on your posts. It is <strong>recommended</strong> to 
					keep this option enabled.</em> <strong>}</strong><br />&nbsp;
				</li>
				<li>
					<div class="submit" style="text-align:left;">
						<input type="submit" name="igco_update" value="<?php _e('Update Options'); ?> &raquo;&raquo;" title="Click to UPDATE your settings" />
					</div>
				</li>
			</ul>
		</form>
	</fieldset><br />
</div><br />&nbsp;
<?php
}

//function to install the plugin
function igCoComment_Install($igForceValues=false) {
	global $user_level;
	get_currentuserinfo();
	//if user is not an admin equalling/above level 8, then don't install
	if ($user_level < 8) { return; }
	//*****proceed with installation
	$igCoCoOpt = array(
						"VERSION" => IG_coComment_V,
						"AUTO_ENABLE" => "false",
						"TRACK_ALL" => "true"
					);
	//check if options exist in the DB
	$igCoCoOptionsDB = get_option(IG_coComment_OPT_NAME);
	if(empty($igCoCoOptionsDB)) {
		//options don't exist, so add them
		add_option(IG_coComment_OPT_NAME, $igCoCoOpt);
	} else {
		//check if forced install
		if($igForceValues==false) {
			//check them one by one & update
			$igCoCoOptUpdateCount = 0;
			foreach($igCoCoOpt as $igCoCoKey => $igCoCoValue) {
				if(!array_key_exists($igCoCoKey, $igCoCoOptionsDB)) {
					//update array with new OPTION
					$igCoCoOptionsDB[$igCoCoKey] = $igCoCoValue;
					$igCoCoOptUpdateCount++;
				} elseif(empty($igCoCoOptionsDB[$igCoCoKey])) {
					$igCoCoOptionsDB[$igCoCoKey] = $igCoCoValue;
					$igCoCoOptUpdateCount++;
				}
			}
			if($igCoCoOptUpdateCount>0) {
				update_option(IG_OPTIONS_NAME, $igCoCoOptionsDB);
			}
		} else {
			update_option(IG_coComment_OPT_NAME, $igCoCoOpt);
		}
	}
}

//define the iG:CoComment Options
function igCoComment_DefineOptions() {
	//check if options exist in the DB
	$igCoCoOptionsDB = get_option(IG_coComment_OPT_NAME);
	if(!empty($igCoCoOptionsDB)) {
		if(!defined("IG_CoCo_Auto")) {
			define('IG_CoCo_Auto', $igCoCoOptionsDB['AUTO_ENABLE']);
		}
		if(!defined("IG_CoCo_TrackAll")) {
			define('IG_CoCo_TrackAll', $igCoCoOptionsDB['TRACK_ALL']);
		}
	}
}

if((!empty($_GET['action']) && $_GET['action']=="deactivate") && (!empty($_GET['plugin']) && $_GET['plugin']==IG_coComment_File)) {
	//plugin deactivated
} elseif((!empty($_GET['activate'])) && ($_GET['activate']=='true')) {
	add_action('init', 'igCoComment_Install');
} else {
	igCoComment_DefineOptions();
}


// integrate with the comment form
add_action('comment_form', 'igCoComment_Integrate');
// add the sub-panel under the OPTIONS panel
add_action('admin_menu', 'igCoCo_Menu');
?>
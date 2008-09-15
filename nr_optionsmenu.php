<?php
/**
	* Utility functions to show the menu
	*/
function nr_Noserub_options () {
	echo '<div class="wrap"><h2>NoseRub</h2>';
	if ($_REQUEST['submit']) {
		nr_update_NoseRub_options();
	}
	nr_print_NoseRub_options_form();
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	if(($nr_apikey != "")&&($nr_url != "")){
		nr_print_NoseRub_vcard();
		nr_print_NoseRub_accounts();
		nr_print_NoseRub_contacts();
	}
	echo '</div>';
}
function nr_Noserub_menu () {
	add_options_page(
		'Noserub',	//Title
	'Noserub',	//Sub-menu title
	'manage_options',	//Security
	__FILE__,	//File to open
	'nr_NoseRub_options'	//Function to call
	);  
}
function nr_update_NoseRub_options() {
	$updated = false;
	if ($_REQUEST['nr_apikey']) {
		update_option('nr_apikey', $_REQUEST['nr_apikey']);
		$updated = true;
	}
	if ($_REQUEST['nr_url']) {
		$nrurl = trim($_REQUEST['nr_url']);
		if((strpos($nrurl,"http://") === FALSE)||(strpos($nrurl,"http://") > 0)){
			$_REQUEST['nr_url'] = "http://".$_REQUEST['nr_url'];
		}
		update_option('nr_url', $_REQUEST['nr_url']);
		$updated = true;
	}
	if ($_REQUEST['nr_feed']) {
		update_option('nr_feed', $_REQUEST['nr_feed']);
		delete_option("nr_feedcache");
		delete_option("nr_feedcache_ts");
		$updated = true;
	}
	if($_REQUEST['nr_location']){
		if(is_numeric($_REQUEST['nr_location'])){
			nr_set_location($_REQUEST['nr_location']);
			nr_update_locations(false);
			$updated = true;
		}
	}
	if($_REQUEST['nr_useas_openid']){
		if($_REQUEST['nr_useas_openid'] == "yes"){
			update_option("nr_useas_openid","yes");
			$updated = true;
		}
	} else {
		update_option("nr_useas_openid","no");
		$updated = true;
	}
	if ($updated) {
		echo '<div id="message" class="updated fade">';
		echo '<p>Options Updated</p>';
		echo '</div>';
	} else {
		echo '<div id="message" class="error fade">';
		echo '<p>Unable to update options</p>';
		echo '</div>';
	}
}
function nr_print_NoseRub_options_form(){
	if(!class_exists('SimplePie')){
		echo '<div class="notice"><p>You might want to install the ';
		echo '<a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin to avoid ';
		echo 'possible problems with other plugins.';
		echo '</p</div>';
	}
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$nr_feed = get_option("nr_feed");
	$nr_useas_openid = get_option("nr_useas_openid");
	$f .= "<h3>Settings</h3>";
	$f .= "<form method='post'>
		<table class='form-table'>
		<tr valign='top'>
		<th scope='row'>Noserub-URL:</th>
		<td><input type='text' id='nr_url' name='nr_url' value='".$nr_url."'/></td>
		</tr>
		<tr valign='top'>
		<th scope='row'>Noserub API-key:</th>
		<td><input type='text' id='nr_apikey' name='nr_apikey' value='".$nr_apikey."'/></td>
		</tr>";
	if(($nr_apikey != "")&&($nr_url != "")){
	$f .= "<tr valign='top'>
			<th scope='row'>Use this blog as OpenID:</th>
			<td><input type='checkbox' id='nr_useas_openid' name='nr_useas_openid' value='yes' ";
	if($nr_useas_openid == 'yes'){
		$f .= "checked='checked'";
	}
	$f .="/>yes</td>
		</tr>";
	}
	$f .= "</table>";
	$f .= "<p class='submit'>
		<input type='submit' value='Update Options &raquo;' name='submit' />
		</p>";
	$f .= "</form>";
	if(($nr_apikey != "")&&($nr_url != "")){
		nr_update_locations(false);
		$nr_locations = get_option("nr_locations_data");
		$nr_locs = unserialize($nr_locations);
		if($nr_locs){
			$nr_loc_array = array();
			$f .= "<h3>Location</h3>";
			$f .= "<form method='post'>
				<table class='form-table'>";

			foreach($nr_locs["data"]["Locations"] as $nr_loc){
				$nr_loc_array[$nr_loc["Location"]["id"]] = $nr_loc["Location"]["name"];
			}
			$nr_loc_now = $nr_locs["data"]["Identity"]["last_location_id"];
			$l = "<tr>
				<th scope='row'>Location:</th>
				<td>
				<select name='nr_location' size='1'>
				<option value=''></option>";
			foreach($nr_loc_array as $nr_loc_id => $nr_loc_name){
				$l.="<option value='".$nr_loc_id."'";
				if($nr_loc_id == $nr_loc_now){
					$l.= " selected='selected' ";
				}
				$l.=">".$nr_loc_name."</option>";
			}
			$l.="</select><br />Where are you <em>now</em>?
				</td>
				</tr>";
			$f .= $l;
			$f .= "</table>";
			$f .= "<p class='submit'>
				<input type='submit' value='Update Options &raquo;' name='submit' />
				</p>";
			$f .= "</form>";
			$f .= "<h3>Feed</h3>";
			$f .= "<form method='post'>
				<table class='form-table'>";
			nr_update_feeds();
			$nr_feeds = get_option("nr_feeds_data");
			$nr_feeds = unserialize($nr_feeds);
			$l = "<tr>
				<th scope='row'>Feed:</th>
				<td>
				<select name='nr_feed' size='1'>
				<option value=''></option>";
			foreach($nr_feeds["data"] as $nr_feeddata){
				$l.="<option value='".$nr_feeddata["Syndication"]["url"]["rss"]."'";
				if($nr_feeddata["Syndication"]["url"]["rss"] == $nr_feed){
					$l .= " selected='selected' ";
				}
				$l.=">".$nr_feeddata["Syndication"]["name"]."</option>";
			}
			$l.="</select><br />Which feed do you want to use?
				</td>
				</tr>";
			$f .= $l;
			$f .= "</table>";
			$f .= "<p class='submit'>
				<input type='submit' value='Update Options &raquo;' name='submit' />
				</p>";
			$f .= "</form>";
		} elseif($nr_apikey) {
			$f .= "<div>"._("Not seeing anything here? Maybe your API key is wrong.")."</div>";
		}
	}
	print $f;
}

function nr_print_NoseRub_vcard(){
	nr_update_vcard(false);
	$nr_vcard_data = unserialize(get_option("nr_vcard_data"));
	if($nr_vcard_data){
		$f .= "<h3>vCard</h3>";
		$f .= "<div class='vcard'>";
		$f .= 	"<h4><a class='fn url' href='".$nr_vcard_data["data"]["url"]."'>".
			$nr_vcard_data["data"]["firstname"]." ".
			$nr_vcard_data["data"]["lastname"]."</a></h4>\n";
		$f .= "<address>".$nr_vcard_data["data"]["address"]."</address>";
		$f .= "</div>";
		print $f;
	}
}

function nr_print_NoseRub_accounts(){
	nr_update_accounts(false);
	$nr_accounts_data = unserialize(get_option("nr_accounts_data"));
	if($nr_accounts_data){
		$f = "<h3>Accounts</h3>";
		$f .= "<ul>";
		foreach($nr_accounts_data["data"] as $account){
			$pu = parse_url($account["url"]);
			$f .= "<li><a href='".$account["url"]."' rel='me'>";
			$f .= "<img src='";
			if($account["icon"] != "rss.gif"){
				$f .= get_option('home')."/wp-content/plugins/noserub-for-wordpress/icons/".$account["icon"];
			} else {
				$f .= "http://www.google.com/s2/favicons?domain=".$pu["host"];
			}
			$f .= "' alt='' style='width:16px; height:16px; '/>";
			if($account["title"] != NULL){
				$f .= $account["title"];
			} elseif($pu["scheme"] != "http"){
				$f .= $pu["path"];
			} else {
				$f .= $pu["host"];
			}
			$f .= "</a></li>";
		}
		$f .= "</ul>";
	}
	print($f);
}

function nr_print_NoseRub_contacts(){
	nr_update_contacts(false);
	$nr_contacts_data = unserialize(get_option("nr_contacts_data"));
	if($nr_contacts_data){
		$f = "<h3>Contacts</h3>";
		foreach($nr_contacts_data["data"] as $nr_contact){
			$f .= "<div class='vcard'>";
			$f .= 	"<h4><a class='fn url' href='".$nr_contact["url"]."' rel='".$nr_contact["xfn"]."'>";
			if(($nr_contact["firstname"]=="")&&($nr_contact["lastname"] == "")){
				$fu = explode("/",$nr_contact["url"]);
				$c = (count($fu)-1);
				$f .=	$fu[$c];
			} else {
				$f .=	$nr_contact["firstname"]." ".
					$nr_contact["lastname"];
			}
			$f .=	"</a></h4>\n";
			if(substr($nr_contact["photo"],0,7) == "http://"){
				$f .= "<img src='".$nr_contact["photo"]."' alt='".$nr_contact["firstname"]." ".$nr_contact["lastname"]."' class='photo' />\n";
			}
			$f .= "</div>";
		}
		print $f;
	}
}

?>
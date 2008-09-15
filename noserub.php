<?php
/*
Plugin Name: NoseRub for WordPress
Plugin URI: http://wp_plugin.noserub.com/
Description: Gets the data from your NoseRub account and lets you use it on your weblog. Supernifty.<br />We advise you to install the <a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin, too. If you don't, NoseRub will still work, though.
Version: 0.1.2
Author: Dominik Schwind
Author URI: http://identoo.com/dominik
*/
require_once(dirname(__FILE__)."/nr_db_functions.php");
require_once(dirname(__FILE__)."/nr_optionsmenu.php");

function the_NoseRub_lifestream(){
	if(!class_exists('SimplePie')){
		require_once(dirname(__FILE__)."/simplepie/simplepie.inc");
	}
	
	require_once(dirname(__FILE__)."/nr_cache.php");
	
	$nr_url = get_option("nr_url");
	$nr_feed_url = get_option("nr_feed");
	if($nr_feed_url != ""){
		$urlex = explode("/",$nr_url);
		$nr_domain = $urlex["2"];
		if(substr($nr_feed_url,0,7) != "http://"){
			$nr_feed = "http://".$nr_domain.$nr_feed_url;
		} else {
			$nr_feed = $nr_feed_url;
		}
		$feed = new SimplePie();
		$feed->set_cache_class("NoseRub_cache");
		$feed->set_feed_url($nr_feed);
		$feed->init();
		$feed->handle_content_type();
		foreach($feed->get_items() as $item){ ?>
			<h3 class="title"><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h3>

			<?php echo $item->get_content(); ?>

			<p class="footnote"><?php echo $item->get_date(); ?></p>

			<?php
		}
	}
}

function widget_NoseRub_lifestream($args){
	extract($args);
	echo $before_widget;
	echo $before_title . 'NoseRub Lifestream'. $after_title;
	if(!class_exists('SimplePie')){
		require_once(dirname(__FILE__)."/simplepie/simplepie.inc");
	}
	
	require_once(dirname(__FILE__)."/nr_cache.php");
	
	$nr_url = get_option("nr_url");
	$nr_feed_url = get_option("nr_feed");
	if($nr_feed_url != ""){
		$urlex = explode("/",$nr_url);
		$nr_domain = $urlex["2"];
		if(substr($nr_feed_url,0,7) != "http://"){
			$nr_feed = "http://".$nr_domain.$nr_feed_url;
		} else {
			$nr_feed = $nr_feed_url;
		}
		$feed = new SimplePie();
		$feed->set_cache_class("NoseRub_cache");
		$feed->set_feed_url($nr_feed);
		$feed->init();
		$feed->handle_content_type();
		echo "<ul>";
		foreach($feed->get_items() as $item){
			echo "<li><a href='".$item->get_permalink()."'>".$item->get_title()."</a></li>\n";
		}
		echo "</ul>";
	}
	echo $after_widget;
}

function widget_NoseRub_vcard($args){
	extract($args);
	nr_update_vcard();
	echo $before_widget;
	echo $before_title . 'My NoseRub'. $after_title;
	$nr_vcard_data = unserialize(get_option("nr_vcard_data"));
	if($nr_vcard_data){
		$f .= "<div class='vcard'>";
		$f .= 	"<a class='fn url' href='".$nr_vcard_data["data"]["url"]."' rel='me'>".
				$nr_vcard_data["data"]["firstname"]." ".
				$nr_vcard_data["data"]["lastname"]."</a><br />\n";
		$f .= "<address class='adr'><span class='locality'>".$nr_vcard_data["data"]["address"]."</span></address></div>";
		print $f;
	} else {
		print "<div>"._("I don't know who I am.")."</div>";
	}
	echo $after_widget;
}

function widget_NoseRub_location($args){
	extract($args);
	nr_update_locations();
	echo $before_widget;
	echo $before_title . 'NoseRub Location'. $after_title;
	$nr_locations = get_option("nr_locations_data");
	$nr_locs = unserialize($nr_locations);
	$nr_loc_array = array();
	if(count($nr_locs["data"]["Locations"]) > 0){
		foreach($nr_locs["data"]["Locations"] as $nr_loc){
			$nr_loc_array[$nr_loc["Location"]["id"]] = $nr_loc["Location"]["name"];
		}
		$nr_loc_now = $nr_locs["data"]["Identity"]["last_location_id"];
		echo _("I am at ").$nr_loc_array[$nr_loc_now];
	} else {
		echo _("I don't know where I am.");
	}
	echo $after_widget;
}

function widget_NoseRub_contacts($args){
	extract($args);
	nr_update_contacts();
	echo $before_widget;
	echo $before_title . 'NoseRub Contacts'. $after_title;
	$nr_contacts_data = unserialize(get_option("nr_contacts_data"));
	if($nr_contacts_data){
		$f = "<ul>";
		foreach($nr_contacts_data["data"] as $nr_contact){
			$f .= "<li class='vcard'>";
			$f .= 	"<a class='fn url' href='".$nr_contact["url"]."' rel='".$nr_contact["xfn"]."'>";
			if(($nr_contact["firstname"]=="")&&($nr_contact["lastname"] == "")){
				$fu = explode("/",$nr_contact["url"]);
				$c = (count($fu)-1);
				$f .=	$fu[$c];
			} else {
				$f .=	$nr_contact["firstname"]." ".
						$nr_contact["lastname"];
			}
			$f .=	"</a>\n";
			$f .= "</li>";
		}
		$f .= "</ul>";
	} else {
		$f = "<div>"._("I don't seem to know anyone.")."</div>";
	}
	print $f;
	
	echo $after_widget;
}

function widget_NoseRub_accounts($args){
	extract($args);
	nr_update_accounts();
	echo $before_widget;
	echo $before_title . 'NoseRub Accounts'. $after_title;
	$nr_accounts_data = unserialize(get_option("nr_accounts_data"));
	if($nr_accounts_data){
		$f = "<ul>";
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
	} else {
		$f = "<div>"._("I don't seem to have any accounts, yet")."</div>";
	}
	print($f);
	echo $after_widget;
}

function nr_update_locations($cached = true){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$locations = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/locations";
	nr_apicall($locations,"locations",$cached);
}

function nr_update_vcard($cached = true){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$vcard = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/vcard";
	nr_apicall($vcard,"vcard",$cached);
}

function nr_update_contacts($cached = true){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$contacts = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/contacts";
	nr_apicall($contacts,"contacts",$cached);
}

function nr_update_accounts($cached = true){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$url = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/accounts";
	nr_apicall($url,"accounts",$cached);
}

function nr_set_location($id){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$locations = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/locations/set/$id";
	nr_apicall($locations,"setlocation",false);
}

function nr_update_feeds(){
	$nr_apikey = get_option("nr_apikey");
	$nr_url = get_option("nr_url");
	$urlex = explode("/",$nr_url);
	$nr_domain = $urlex["2"];
	$nr_user = $urlex["3"];
	$locations = "http://".$nr_domain."/api/".$nr_user."/".$nr_apikey."/sphp/feeds";
	nr_apicall($locations,"feeds",false);
}

/**
 * Utility functions for the API call
 */
function nr_apicall($nrapi_url,$nrapi_name = false,$cached = true){
	if(!$nrapi_name){
		$nrapi_name = md5($nrapi_url);
	}
	$nrapi_lastcall = get_option("nr_".$nrapi_name."_lastcall");
	if(($cached == false)||((time()-$nrapi_lastcall) > 3600)){
		$data = nr_url_get_contents($nrapi_url);
		if($data){
			update_option("nr_".$nrapi_name."_data",$data);
			update_option("nr_".$nrapi_name."_lastcall",time());
		} else {
			echo '<div id="message" class="error fade">';
			echo '<p>There was an error: Either your NoseRub is down or your API-Key is wrong.</p>';
			echo '</div>';
		}
	}
	$data = unserialize(get_option("nr_".$nrapi_name."_data"));
	if($data["code"] > 0){
		echo '<div id="message" class="error fade">';
		echo '<p>There was an error:'.$data["msg"].'</p>';
		echo '</div>';
	}
	return $data;
}

function nr_url_get_contents($url){
	if(function_exists("curl_init")){
		$ch = curl_init();
		if($ch === false){
			return false;
		}
		$timeout = 20;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		ob_start();
		curl_exec($ch);
		curl_close($ch);
		$file_contents = ob_get_contents();
		ob_end_clean();
	} elseif(function_exists("file_get_contents")){
		$file_contents = file_get_contents($url);
	}
	return $file_contents;
}

function nr_init(){
	register_sidebar_widget('NoseRub Lifestream','widget_NoseRub_lifestream');
	register_sidebar_widget('NoseRub Location','widget_NoseRub_location');
	register_sidebar_widget('NoseRub Contacts','widget_NoseRub_contacts');
	register_sidebar_widget('NoseRub vCard','widget_NoseRub_vcard');
	register_sidebar_widget('NoseRub Accounts','widget_NoseRub_accounts');
}

function nr_openid_header(){
	$nr_useas_openid = get_option("nr_useas_openid");
	if($nr_useas_openid == "yes"){
		$nr_url = get_option("nr_url");
		$pu = parse_url($nr_url);
		$xp = explode("/",$pu["path"]);
		$p = array();
		for($i = 0; $i < count($xp); $i++){
			if($xp[$i] != ""){
				$p[] = $xp[$i];
			}
		}
		$xp = implode("/",$p);
		$xrds = $pu["scheme"]."://".$pu["host"]."/".$xp."/xrds";
		$auth = $pu["scheme"]."://".$pu["host"]."/auth";
		print("\n");
		?>
	<link href="<?php echo $auth; ?>" rel="openid2.provider openid.server" />
	<link href="<?php echo $nr_url; ?>" rel="openid2.local_id openid.delegate" />
		<?php
		print("\n");
	}
}

add_action('admin_menu','nr_Noserub_menu');
add_action('widgets_init','nr_init');
add_action('wp_head', 'nr_openid_header');

register_activation_hook(__FILE__,"nr_set_NoseRub_options");
// register_deactivation_hook(__FILE__,"nr_unset_NoseRub_options");
?>
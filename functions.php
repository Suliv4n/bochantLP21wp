<?php 

add_action('pre_get_posts', 'display_concerts');


function display_concerts($query)
{	
	if($query->is_front_page() && $query->is_main_query())
	{
		/*
		$query->set('post_type', array('concert'));	
		$query->set('date_query', array('year'=>getdate()['year']-10, 'compare' => '>='));
		*/
		$query->set('post_type', array('concert'));	
		$query->set('date_query', array('year'=>array(2006,2008), 'compare' => 'BETWEEN', 'type'=>'INTEGER'));
	}
	
	return;
}

/*

function dashboard_widget_function()
{
	echo "Hello World,this is myfirst Dashboard Widget!";
}



function add_dashboard_widgets()
{
	wp_add_dashboard_widget('dashboard_widget','ExampleDashboardWidget','dashboard_widget_function');
}
add_action('wp_dashboard_setup','add_dashboard_widgets');
*/

// Function that outputs the contents of the dashboard widget
function dashboard_widget_function() {
	echo "Hello World, this is my first Dashboard Widget!";
}

// Function used in the action hook
function add_dashboard_widgets() {
	wp_add_dashboard_widget('dashboard_widget', 'Example Dashboard Widget', 'dashboard_widget_function');
	//add_meta_box('id', 'Dashboard Widget Title', 'dash_widget', 'dashboard', 'side', 'high');
}

// ajouter mes scripts
function mes_scripts(){
	
	if(!is_post_type_archive('concert')&&!is_post_type_archive('action'))
	{
		wp_enqueue_script( 'leaflet-js','http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js');
	}
	
	
	wp_enqueue_script( 'googlemap', 'http://maps.google.com/maps/api/js?sensor=false', null, null, false );
	wp_enqueue_script( 'geoloc', '/wp-content/themes/child/geoloc.js', 'googlemap', '1.0', false );
}

function mes_css(){
	wp_enqueue_style('leaflet-css','http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css');
}


function getPostWithLatLon($post_type="concert")
{	
	global $wpdb; 
	$query="SELECT ID, post_title, p1.meta_value as lat, p2.meta_value as lng 
			FROM wp_archetsposts, wp_archetspostmeta as p1, wp_archetspostmeta as p2
			WHERE wp_archetsposts.post_type='concert'
			AND p1.post_id = wp_archetsposts.ID 
			AND p2.post_id = wp_archetsposts.ID 
			AND p1.meta_key = 'lat'
			AND p2.meta_key = 'lng'";
			
	//$wpdb->prepare($query,50);
	
	$r = $wpdb->get_results($query);
	
	return $r;
}


function getMarkerList($post_type="concert")
{
	$results=getPostWithLatLon($post_type);
	
	$array=array();
	
	foreach($results as $result)
	{
		array_push($array, "var marker_$result->ID = L.marker([$result->lat, $result->lng]).addTo(map);");
		array_push($array, "var popup_$result->ID = L.popup().setContent('$result->post_title');");
		array_push($array, "popup_$result->ID.post_id = $result->ID");
		$array[] = "marker_$result->ID.bindPopup(popup_$result->ID)";
	}
	return implode(PHP_EOL,$array);
}


// fin fonction mes_scripts
 
// ajout au chargement de la page
add_action('init','mes_scripts');
add_action('init','mes_css');

// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'add_dashboard_widgets' );



//do_action( 'wp_dashboard_setup' );





function geolocalize($post_id)
{
	if(wp_is_post_revision($post_id)) 
		return;

	$post=get_post($post_id);
	if(!in_array($post->post_type,array('concert')))
		return;


	
	$lieu=get_post_meta($post_id,'wpcf-lieu',true);
		
	if(empty($lieu))
		return;
		
	$lat=get_post_meta($post_id,'lat',true);
	
	
	if(empty($lat))
	{
		$address=$lieu.',France';
		$result=doGeolocation($address);
	
		if(false===$result)
			return;
		
		try{
			$location = $result[0]['geometry']['location'];
			add_post_meta($post_id,'lat',$location["lat"]);
			add_post_meta($post_id,'lng',$location["lng"]);
		}
		
		catch(Exception $e)
		{
			return;
		}
	}
}

add_action('save_post','geolocalize');


function doGeolocation($address)
{
	$url="http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address);
	
	$opts = array('http' => array('proxy' => 'wwwcache.univ-orleans.fr:3128', 'request_fulluri' => true));
	$context = stream_context_create($opts);
	
	if($json=file_get_contents($url, false, $context))
	{
		$data=json_decode($json,TRUE);
		if($data['status']=="OK")
		{
			return $data['results'];
		}
	}
	return false;
}

add_action("wp_ajax_popup_content","get_content");
add_action("wp_ajax_nopriv_popup_content","get_content");



function get_content()
{
	if(!wp_verify_nonce($_REQUEST['nonce'],"popup_content"))
	{
		exit("d'où vient cette requête ?");
	}
	else
	{
		
		$post_id=$_REQUEST["post_id"];

		$post = get_post($post_id);
		$link = get_permalink($post_id);
		print "<div class=\"popup\"> <h1><a href=\"$link\">".$post->post_title."</a></h1>".$post->post_content."</div>"; //TODO rajouter lien vers concert single
		
		die();
		//!ceci sera renvoyé au client !
	}
}

?>

<!--
<div id="carte" style="width:400px;height:400px"  ></div>


<script>
	init("carte"); // Création de la map
</script>
-->


<div id="map"></div>

<script type="text/javascript">
	map = L.map('map',{center:[46,0.8],zoom:5});
	L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png',{maxZoom:18,attribution:'Mapdata&copy;<a href="http://openstreetmap.org">OpenStreetMap</a>contributors,<ahref="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>,Imagery©<a href="http://cloudmade.com">CloudMade</a>'}).addTo(map);

	<?php echo getMarkerList(); ?>

	
	map.on('popupopen',function(e){
		
		var post_id=e.popup.post_id;
		var nonce= '<?php print wp_create_nonce("popup_content");?>';
		jQuery.post("<?php print admin_url('admin-ajax.php');?>",
			{action:'popup_content',
			post_id:post_id,
			nonce:nonce},
			
			function(response)
			{
				console.log("resp",response);
				e.popup.setContent(response);
			});
	});
	

	


</script>





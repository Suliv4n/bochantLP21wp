var geocoder = new google.maps.Geocoder();
var carte;

function init(idcarte)
{
	var latlng =  new google.maps.LatLng(46.589069,2.391357); //Centrer en France
	
	var options = {
		center: latlng,
		zoom: 5,
		disableDefaultUI: true,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	carte = new google.maps.Map(document.getElementById(idcarte), options);
}

function creerMarqueur(adresse)
{
	geocoder.geocode({'address':adresse}, function(results, status){
		if(status == google.maps.GeocoderStatus.OK)
		{

				var marqueur = new google.maps.Marker({
					position: results[0].geometry.location,
					map: carte});
		}
		else
		{
			//alert('Erreur : ' + status);
		}
	});
}

// http://awardwinningfjords.com/2009/07/22/google-maps-with-jquery.html
$.fn.initGoogleMap = function(options) {

	  var defaults = {
	    lat: 46.384887,
	    long: 6.239002,
	    zoom: 11,
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	  };

	  options = $.extend(defaults, options || {});

	  var center = new google.maps.LatLng(options.lat, options.long);
	  map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));

	  infoWindow = new google.maps.InfoWindow;
};


$.fn.googleMap = function(address) {

  var latlng = new google.maps.LatLng(46.384887, 6.239002);
  var defaults = {
    lat: 46.384887,
    long: 6.239002,
    center: latlng,
    zoom: 14,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  map = new google.maps.Map(this.get(0), defaults);


  var geocoder = new google.maps.Geocoder();
  geocoder.geocode({ address: address }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK && results.length) {
      if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map
        });
      }
    }
  });
};



function addMarkerWithAddress(address) {
	var geocoder = new google.maps.Geocoder();
	  geocoder.geocode({ address: address }, function(results, status) {
	    if (status == google.maps.GeocoderStatus.OK && results.length) {
	      if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
	        //map.setCenter(results[0].geometry.location);
	        var marker = new google.maps.Marker({
	            position: results[0].geometry.location,
	            map: map
	        });
	      }
	    }
	  });

}


function addMarkerWithGeocode(lat, lng, text) {
	var latlng = new google.maps.LatLng(lat,lng);
	var marker = new google.maps.Marker({
		position: latlng,
		map: map
	});
	var html = text;
	bindInfoWindow(marker, map, infoWindow, html);
}


function bindInfoWindow(marker, map, infoWindow, html) {
	google.maps.event.addListener(marker, 'click', function() {
    	infoWindow.setContent(html);
      infoWindow.open(map, marker);
	});
}


//http://code.google.com/apis/maps/documentation/javascript/examples/directions-simple.html

function gmap_direction_initialize() {
	directionsDisplay = new google.maps.DirectionsRenderer();
	var asbv_nyon = new google.maps.LatLng(46.384887, 6.239002);
	var myOptions = {
		zoom:9,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		center: asbv_nyon
	};
	map = new google.maps.Map(document.getElementById("map_direction"), myOptions);
	directionsDisplay.setMap(map);
	gmap_direction_calcRoute();
}


function gmap_direction_calcRoute() {
	directionsService = new google.maps.DirectionsService();
	var start = document.getElementById("gmap_direction_start").value;
	var end = document.getElementById("gmap_direction_end").value;
	var request = {
		origin:start,
		destination:end,
		travelMode: google.maps.DirectionsTravelMode.DRIVING
	};
	directionsService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay.setDirections(response);
		}
	});
}



function disableAutocomplete(elementId) {
	var e = document.getElementById(elementId);
	if(e != null)
	{
		e.setAttribute("autocomplete", "off");
	}
}


$(document).ready(function() {

	//coloriage une ligne sur 2 des tableaux avec la class OddEven
	$('table.OddEven tr:not(:has(th)):odd').addClass('table_odd');

	$.datepicker.setDefaults($.datepicker.regional['fr-CH']);

	$('#toolbar > li').addClass('menu_main_enty');

	$('select#id_beneficiaire').change(function(test) {

		var id_beneficiaire = test['currentTarget']['form']['id_beneficiaire']['value'];
		eval("query = {'reload':'false', 'module':'beneficiaire', 'action':'ajax_get_beneficiaire_details', 'id_beneficiaire':" + id_beneficiaire + "};");

		$("#details_beneficiaire").empty();
		$.get('index.php', query, function(data) {
			$("#details_beneficiaire").append(data);
		});

	});


	$('#date_transport').change(function(test) {

		var id_beneficiaire = test['currentTarget']['form']['id_beneficiaire']['value'];
		var date_transport = test['currentTarget']['form']['date_transport']['value'];
		eval("query = {'reload':'false', 'module':'beneficiaire', 'action':'ajax_already_transport_same_date', 'id_beneficiaire':" + id_beneficiaire + ", 'date_transport':'" + date_transport + "'};");

		$("#warning_beneficiaire_already_transport_same_date").empty();
		$.get('index.php', query, function(data) {
			$("#warning_beneficiaire_already_transport_same_date").append(data);
		});

	});

	$('#heure_debut_heure').change(function(test) {

		var id_beneficiaire = test['currentTarget']['form']['id_beneficiaire']['value'];
		var date_transport = test['currentTarget']['form']['date_transport']['value'];

		var heure_debut_heure = test['currentTarget']['form']['heure_debut_heure']['value'];
		var heure_debut_minute = test['currentTarget']['form']['heure_debut_minute']['value'];

		eval("query = {'reload':'false', 'module':'beneficiaire', 'action':'ajax_already_transport_same_date_and_time', 'id_beneficiaire':" + id_beneficiaire + ", 'date_transport':'" + date_transport + "', 'heure_debut_heure':'" + heure_debut_heure + "', 'heure_debut_minute':'" + heure_debut_minute + "'};");

		$("#warning_beneficiaire_already_transport_same_date").empty();
		$.get('index.php', query, function(data) {
			$("#warning_beneficiaire_already_transport_same_date").append(data);

			if (data != '') {
				$('input[type=submit]').attr('disabled', 'disabled');
			} else {
				$('input[type=submit]').removeAttr('disabled');
			}
		});


	});


	$('#heure_debut_minute').change(function(test) {

		var id_beneficiaire = test['currentTarget']['form']['id_beneficiaire']['value'];
		var date_transport = test['currentTarget']['form']['date_transport']['value'];

		var heure_debut_heure = test['currentTarget']['form']['heure_debut_heure']['value'];
		var heure_debut_minute = test['currentTarget']['form']['heure_debut_minute']['value'];

		eval("query = {'reload':'false', 'module':'beneficiaire', 'action':'ajax_already_transport_same_date_and_time', 'id_beneficiaire':" + id_beneficiaire + ", 'date_transport':'" + date_transport + "', 'heure_debut_heure':'" + heure_debut_heure + "', 'heure_debut_minute':'" + heure_debut_minute + "'};");

		$("#warning_beneficiaire_already_transport_same_date").empty();
		$.get('index.php', query, function(data) {
			$("#warning_beneficiaire_already_transport_same_date").append(data);

			if (data != '') {
				$('input[type=submit]').attr('disabled', 'disabled');
			} else {
				$('input[type=submit]').removeAttr('disabled');
			}
		});


	});




	//$('#beneficiaire_adresse_facturation').hide();

	$('#autre_adresse_facturation').change(function() {
		$('#beneficiaire_adresse_facturation').slideToggle('slow');
	});





	$('#numeros_importants').hide();

	$('#sh_numeros_importants').click(function() {
		$('#numeros_importants').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher les numéros importants") {
			$link.text('Masquer les numéros importants');
		} else {
			$link.text('Afficher les numéros importants');
		}


		return false; //desactive le lien
	});



	$('.hide_transport_sans_chauffeur').hide();

	$('#show_remaining_transports_sans_chauffeur').click(function() {
		$('.hide_transport_sans_chauffeur').slideToggle('fast');
		$('.dashboard_info_no_transport_sans_chauffeur_urgent').slideToggle('slow');

		var $link = $(this);

		if ($link.text() == "Afficher les transports restants sans chauffeur") {
			$link.text('Masquer les transports restants sans chauffeur');
		} else {
			$link.text('Afficher les transports restants sans chauffeur');
		}


		return false; //desactive le lien
	});



	$('.hide_transport').hide();

	$('#show_remaining_transports').click(function() {
		$('.hide_transport').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher les transports restants déjà attribués") {
			$link.text('Masquer les transports restants déjà attribués');
		} else {
			$link.text('Afficher les transports restants déjà attribués');
		}


		return false; //desactive le lien
	});

	$('#show_past_transports').click(function() {
		$('.hide_transport').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher les transports du mois précédent") {
			$link.text('Masquer les transports du mois précédent');
		} else {
			$link.text('Afficher les transports du mois précédent');
		}


		return false; //desactive le lien
	});



	$('.hide_transporteur').hide();

	$('#show_remaining_transporteur').click(function() {
		$('.hide_transporteur').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher les transporteurs restants") {
			$link.text('Masquer les transporteurs restants');
		} else {
			$link.text('Afficher les transporteurs restants');
		}


		return false; //desactive le lien
	});



	$('.other_transport_point').hide();

	$('#show_other_transport_point_depart').click(function() {
		$('#other_point_depart').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Autre lieu") {
			$link.text('Masquer autre lieu');
		} else {
			$link.text('Autre lieu');
		}


		return false; //desactive le lien
	});

	$('#show_other_transport_point_arrivee').click(function() {
		$('#other_point_arrivee').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Autre lieu") {
			$link.text('Masquer autre lieu');
		} else {
			$link.text('Autre lieu');
		}


		return false; //desactive le lien
	});




	$('#find_driver_last_transports').hide();

	$('#show_find_driver_last_transports').click(function() {
		$('#find_driver_last_transports').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Voir les derniers transports de ce passager") {
			$link.text('Masquer les derniers transports de ce passager');
		} else {
			$link.text('Voir les derniers transports de ce passager');
		}


		return false; //desactive le lien
	});


	$('#beneficiaire_repondant').hide();

	$('#show_beneficiaire_repondant').click(function() {
		$('#beneficiaire_repondant').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher la partie concernant le répondant") {
			$link.text('Masquer la partie concernant le répondant');
		} else {
			$link.text('Afficher la partie concernant le répondant');
		}


		return false; //desactive le lien
	});



	$('#benevoles_inactifs').hide();

	$('#sh_benevoles_inactifs').click(function() {
		$('#benevoles_inactifs').slideToggle('fast');
		var $link = $(this);

		if ($link.text() == "Afficher les bénévoles passifs") {
			$link.text('Masquer les bénévoles passifst');
		} else {
			$link.text('Afficher les bénévoles passifs');
		}


		return false; //desactive le lien
	});






	$('.disableAutoComplete').attr('autocomplete', 'off');

	$(".date_picker").mousedown(function(){
    	$('.date_picker').datepicker({ changeMonth: false, changeYear: false});
    });

	$(".date_picker_from_now").mousedown(function(){
    	$('.date_picker_from_now').datepicker({ changeMonth: true, changeYear: true, minDate: 0 });
    });


	$(".MonthPicker").monthpicker(callback_monthpicker);


	$(".link_ajax_get").click(function() {

		//extraction de donnees de l'arg href
		var link = $(this).attr('href');
		var r = false

		if (link.indexOf("?") != -1) {
		    var query = link.split("?")[1];

			if (query.indexOf("action=cancel") != -1) {
				r = confirm("Souhaitez-vous vraiment supprimer ce transport ?");
			} else {
				r = true
			}

		}

		if (r == true) {
			//cache la ligne
			$(this).parent().parent().empty();
			eval("query = {'reload':'false', " + query.replace(/&/ig, "\",").replace(/=/ig, ":\"") + "\"};");

			$.get('index.php', query, function(data){
				if (data != '') {
					var json_array = eval('(' + data + ')');

						var html;
						html = json_array['msg'];

						//mount dans le header_info
						//$('#header_info').append(html);


						//mount le conenu dans la boite
						$("#dialog").empty();
						$("#dialog").append(html);

						//affichage de la boite
						$("#dialog").dialog({ height: 300, width: 500 });

				}
			});
		}

		return false; //desactiver le lien
	});




	$(".update_calendar_add_ajax_get").click(function() {

		td_element = $(this).parent();

		$(this).parent().removeClass('jours');
		$(this).parent().addClass('non_disponible');

		$(this).removeClass('update_calendar_add_ajax_get');
		$(this).addClass('update_calendar_remove_ajax_get');


		//extraction de donnees de l'arg href
		var link = $(this).attr('href');

		id = link.substring(link.indexOf('id='), link.indexOf('&', link.indexOf('id=')));

		date_custom = link.substring(link.indexOf('date_custom='));
		day_date_custom = date_custom.substring((date_custom.lastIndexOf('-'))+1);

		//ajoute le nouveau lien
		$(this).attr('href', '?module=transporteur&action=delete&sub_module=non_dispo_date_transport&' + id  + '&' + date_custom);

		if (link.indexOf("?") != -1) {
		    var query = link.split("?")[1];
		    eval("query = {'reload':'false', " + query.replace(/&/ig, "\",").replace(/=/ig, ":\"") + "\"};");
		}

		$.get('index.php', query, function(data){

		});

		return false; //desactiver le lien
	});



	$(".update_calendar_remove_ajax_get").click(function() {

		td_element = $(this).parent();

		$(this).parent().removeClass('non_disponible');
		$(this).parent().addClass('jours');

		$(this).removeClass('update_calendar_remove_ajax_get');
		$(this).addClass('update_calendar_add_ajax_get');

		//extraction de donnees de l'arg href
		var link = $(this).attr('href');

		id = link.substring(link.indexOf('id='), link.indexOf('&', link.indexOf('id=')));

		date_custom = link.substring(link.indexOf('date_custom='));
		day_date_custom = date_custom.substring((date_custom.lastIndexOf('-'))+1);

		//ajoute le nouveau lien
		$(this).attr('href', '?module=transporteur&action=add&sub_module=non_dispo_date_transport&' + id  + '&' + date_custom);

		if (link.indexOf("?") != -1) {
		    var query = link.split("?")[1];
		    eval("query = {'reload':'false', " + query.replace(/&/ig, "\",").replace(/=/ig, ":\"") + "\"};");
		}

		$.get('index.php', query, function(data){

		});

		return false; //desactiver le lien
	});



	$(".link_dialog").click(function() {
		//extraction de donnees de l'arg href
		var link = $(this).attr('href');
		if (link.indexOf("?") != -1) {
		    var query = link.split("?")[1];

		    eval("query = {" + query.replace(/&/ig, "\",").replace(/=/ig, ":\"") + "\"};");

		} else {

		}

		//rapatrie les donnees html
		$.get('index.php', query, function(data){
			//html = data.substring(data.indexOf('<div id="dyn_content">'),data.indexOf('</body>',data.indexOf('<div id="dyn_content">')));
			html = data.substring(data.indexOf('<div id="dyn_content">'),data.indexOf('<div id="area_footer">',data.indexOf('<div id="dyn_content">')));
			html = html.replace('<div id="dyn_content">', '<div class="hide" id="div_dialog">');
			html = html.replace('<div id="area_footer">', '');

			//mount le contenu dans la boite
			$("#div_dialog").remove();
			$(".ui-dialog").remove();
			$("#container").append(html);

			//affichage de la boite
			$("#div_dialog").dialog({ height: 550, width: 850 });
			google.maps.event.trigger(map, "resize");
		});


		return false; //desactiver le lien
	});


	$("#form_restore").submit(function() {
		html = '<div class="hide" id="div_dialog">Restauration en cours veuillez patienter...</div>';
		$("#div_dialog").remove();
		$(".ui-dialog").remove();
		$("#container").append(html);

		//affichage de la boite
		$("#div_dialog").dialog({ height: 150, width: 350 });

		//return false;
	});

	$("a.show_help").click(function() {
		$(".ui-dialog").remove();


		$("#help").dialog({ height: 550, width: 850 });

		return false; //desactive le lien
	});

	$('form').submit(function() {
		$('input[type=submit]', this).attr('disabled', 'disabled');

	});


	$('input[type=submit]#submit_add_plage_non_dispo_benevole').attr('disabled', 'disabled');



	$('form#auth_system').submit(function() {

		//affichage de la boite
		$("#div_dialog").dialog({ height: 150, width: 550 });

	});


	$('.plage_date').change(function(test) {
		var date_from_day = test['currentTarget']['form']['date_from_day']['value'];
		var date_from_month = test['currentTarget']['form']['date_from_month']['value'];
		var date_from_year = test['currentTarget']['form']['date_from_year']['value'];

		var date_to_day = test['currentTarget']['form']['date_to_day']['value'];
		var date_to_month = test['currentTarget']['form']['date_to_month']['value'];
		var date_to_year = test['currentTarget']['form']['date_to_year']['value'];

		if (date_from_day > 0 && date_from_month > 0 && date_from_year > 0 && date_to_day > 0 && date_to_month > 0 && date_to_year > 0) {
			$('input[type=submit]#submit_add_plage_non_dispo_benevole').removeAttr('disabled');
		} else {
			$('input[type=submit]#submit_add_plage_non_dispo_benevole').attr('disabled', 'disabled');
		}
	});


	var alternateRowColors = function($table) {
		$('tbody tr:odd', $table)
			.removeClass('table_even').addClass('table_odd');
		$('tbody tr:even', $table)
			.removeClass('table_odd').addClass('table_even');
	};

});

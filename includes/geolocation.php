<?php
/**
 * JobRoller Geoloaction functions
 * This file controls code for the Geolocation features.
 * Geolocation adapted from 'GeoLocation' plugin by Chris Boyd - http://geo.chrisboyd.net
 *
 * @todo: move to APP_GEO
 *
 * @version 1.1
 * @author AppThemes
 * @package JobRoller\Geolocation
 * @copyright 2010 all rights reserved
 */

define( 'JR_DEFAULT_ZOOM', 1 );

function _jr_get_geolocation_url( $address = '' ) {
	global $jr_options;

	$google_maps_json_url = ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/geocode/json';

	$lang = $jr_options->jr_gmaps_lang;
	$region = $jr_options->jr_gmaps_region;
	$api_key = $jr_options->gmaps_api_key;

	$args = array(
		'sensor' 	=> 'false',
		'language' 	=> $lang,
		// uncomment to get results restricted to a specific area - see 'Component Filtering' in https://developers.google.com/maps/documentation/geocoding/#RegionCodes
		//'components' => 'country:'.$region,
	);

	if ( is_array( $address ) ) {
		$args['latlng'] = implode( ',', $address );
	} elseif( $address ) {
		$args['address'] = urlencode( $address );
	}
	$args['region'] = $region;

	if ( $api_key ) {
		$args['key'] = $api_key;
	}

	$args = apply_filters( 'jr_geolocation_params', $args, 'json' );

	return add_query_arg( $args, $google_maps_json_url );
}

function _jr_get_js_geolocation_url( $callback ) {
	global $jr_options;

	$google_maps_js_url = ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js';

	$lang = $jr_options->jr_gmaps_lang;
	$region = $jr_options->jr_gmaps_region;
	$api_key = $jr_options->gmaps_api_key;

	$params = array(
		'v' => 3,
		'sensor' => 'false',
		'language' => $lang,
		'callback' => $callback,
		'region' => $region,
		// uncomment to get results restricted to a specific area - see 'Component Filtering' in https://developers.google.com/maps/documentation/geocoding/#RegionCodes
		//'components' => 'country:'.$region,
	);

	if ( $api_key ) {
		$params['key'] = $api_key;
	}

	$params = apply_filters( 'jr_geolocation_params', $params, 'javascript' );

	return add_query_arg( $params, $google_maps_js_url );
}

function jr_clean_coordinate($coordinate) {
	//$pattern = '/^(\-)?(\d{1,3})\.(\d{1,15})/';
	$pattern = '/^(\-)?(\d{1,3}).(\d{1,15})/';
	preg_match($pattern, $coordinate, $matches);
	if (isset($matches[0])) return $matches[0];
}

function jr_reverse_geocode( $latitude, $longitude ) {

	$url = _jr_get_geolocation_url( array( $latitude, $longitude ) );

	$result = wp_remote_get( esc_url_raw( $url ) );

	$json = json_decode( $result['body'] );

	// on error try over https
	if ( ! empty( $json->error_message ) ) {
		$url = str_replace( 'http', 'https', $url );

		$result = wp_remote_get( esc_url_raw( $url ) );
		$json = json_decode( $result['body'] );
	}

	if ( is_wp_error( $result ) || ! empty( $json->error_message ) ) {
		global $jr_log;
		$jr_log->write_log( __( 'Could not access Google Maps API. Your server may be blocking the request.', APP_TD ) );
		return false;
	}

	$address = $city = $country = $short_address_country = $short_country = $state = '';

	foreach ( $json->results as $result ) {
		foreach ( $result->address_components as $addressPart ) {
			if ( (in_array( 'locality', $addressPart->types )) && (in_array( 'political', $addressPart->types )) ) {
				$city = $addressPart->long_name;
			} elseif ( ( in_array( 'administrative_area_level_1', $addressPart->types ) ) && ( in_array( 'political', $addressPart->types ) )  ) {
				$state = $addressPart->long_name;
			} elseif( ( in_array( 'country', $addressPart->types ) ) && (in_array( 'political', $addressPart->types ) ) ) {
				$country = $addressPart->long_name;
				$short_country = $addressPart->short_name;
			}
		}
		if ( ($city) && ($state) && ($country) ) {
			break;
		}
	}

	if ( ($city != '') && ($state != '') && ($country != '') ) {
		$address = $city . ', ' . $state . ', ' . $country;
	} elseif ( ($city != '') && ($state != '') ) {
		$address = $city . ', ' . $state;
	} elseif( ($state != '') && ($country != '') ) {
		$address = $state . ', ' . $country;
	// fix for countries with no valid state
	} elseif( ($city != '') && ($country != '') ) {
		$address = $city . ', ' . $country;
	//
	} elseif ( $country != '' ) {
		$address = $country;
	}

	if ( $country=='United Kingdom' ) {
		$short_country = 'UK';
	}

	if ( ($city != '') && ($state != '') && ($country != '') ) {
		$short_address = $city;
		$short_address_country = $state . ', ' . $country;
	} else if ( ($city != '') && ($state != '') ) {
		$short_address = $city;
		$short_address_country = $state;
	} else if ( ($state != '') && ($country != '') ) {
		$short_address = $state;
		$short_address_country = $country;
		// fix for countries with no valid state
	} else if ( ($city != '') && ($country != '') ) {
		$short_address = $city;
		$short_address_country = $country;
		//
	} else if ( $country != '' ) {
		$short_address = $country;
		$short_address_country = '';
	}

	return array(
		'address' => $address,
		'country' => $country,
		'short_address' => $short_address,
		'short_address_country' => $short_address_country
	);
}

/**
 * Calculates the distance between two points on the surface of an Earth-sized sphere
 */
function jr_calc_earth_distance( $lat_1, $lng_1, $lat_2, $lng_2, $unit ) {
	$earth_radius = ('mi' == $unit) ? 3959 : 6371;

	$alpha    = ($lat_2 - $lat_1)/2;
	$beta     = ($lng_2 - $lng_1)/2;

	$a        = sin(deg2rad($alpha)) * sin(deg2rad($alpha)) +
	            cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) *
	            sin(deg2rad($beta)) * sin(deg2rad($beta));

	$distance = 2 * $earth_radius * asin(min(1, sqrt($a)));

	$distance = round( $distance, 4 );

	return $distance;
}

// Radial location search
function jr_radial_search( $location, $radius, $address_array = '' ) {
	global $wpdb, $jr_options;

	if ( ! function_exists('json_decode') || empty( $location ) ) {
		return false;
	}

	$unit = $jr_options->jr_distance_unit;

	// If address is not given, find it via Google Maps API or Cache
	if ( ! is_array( $address_array ) ) {
		$address = _jr_get_geolocation_url( $location );
		$address = esc_url_raw( $address );

		$cached = get_transient( 'jr_geo_' . sanitize_title( $location ) );

		if ( $cached && ! empty( $cached['results'] ) ) {
			$address = $cached;
		} else {
			$base_address = $address;

			$address = json_decode( wp_remote_retrieve_body( wp_remote_get( $base_address ) ), true );

			// on error try over https
			if ( ! empty( $address['error_message'] ) ) {
				$base_address = str_replace( 'http', 'https', $base_address );
				$address = json_decode( wp_remote_retrieve_body( wp_remote_get( $base_address ) ), true );
			}

			if ( is_array( $address ) ) {
				set_transient( 'jr_geo_' . sanitize_title( $location ), $address, 60 * 60 * 24 * 7 ); // Cache for a week
			}
		}

		if ( !empty( $address['results'][0] ) ) {
			// Put address info into a nice array format
			$address_array = array(
				'longitude' => $address['results'][0]['geometry']['location']['lng'],
				'latitude' => $address['results'][0]['geometry']['location']['lat']
			);

			$address_array['full_address'] = $address['results'][0]['formatted_address'];
		}
	}

	// use smart radius
	if ( ! $radius ) {

		if ( ! empty( $address['results'][0]['geometry'] ) ) {
			$geometry = $address['results'][0]['geometry'];

			// bounds are not always returned, so fall back to viewport
			$bounds_type = ! empty( $geometry['bounds'] ) ? 'bounds' : 'viewport';

			$distance_a = jr_calc_earth_distance(
				$geometry[$bounds_type]['northeast']['lat'],
				$geometry[$bounds_type]['southwest']['lng'],
				$geometry[$bounds_type]['southwest']['lat'],
				$geometry[$bounds_type]['southwest']['lng'],
				$unit
			);

			$distance_b = jr_calc_earth_distance(
				$geometry[$bounds_type]['northeast']['lat'],
				$geometry[$bounds_type]['northeast']['lng'],
				$geometry[$bounds_type]['northeast']['lat'],
				$geometry[$bounds_type]['southwest']['lng'],
				$unit
			);

			// Find the longest distance, so we can make a square that covers the full area.
			$longer_distance = $distance_a > $distance_b ? $distance_a : $distance_b;

			// Make a square out of the non-square bounds.
			$distance_c = sqrt( pow($longer_distance, 2) * 2 );

			/*
			 * Since distance is a diameter, and since the bounds are a square,
			 * use half the "diameter" of the square to make a radius (circle)
			 * so that it covers the area of the square bounds.
			 */
			$radius = $distance_c / 2;

		}

	}

	// Final fallback just in case radius is not set and smart_radius fails due to API not returning a bounds/viewport.
	if ( ! $radius ) {
		$radius = 50;
	}

	if ( is_array( $address_array ) ) {

		if ( ! empty( $address_array['longitude'] ) && ! empty( $address_array['latitude'] ) ) :

			$lat = $address_array['latitude'];
			$lng = $address_array['longitude'];
			$radius = (int) $radius;

			$R = 'mi' == $unit ? 3959 : 6371;

			// Geolocation using Haversine formula
			// https://developers.google.com/maps/articles/phpsqlsearch_v3?#findnearsql

			// assign a BIG distance to 'anywhere' jobs so they appear later on the listings
			$anywhere_data = $wpdb->prepare ("
				SELECT ID, 999999999 as distance, post_date FROM $wpdb->posts
				WHERE
				(
					ID NOT IN( SELECT $wpdb->postmeta.post_id FROM $wpdb->postmeta WHERE meta_key = 'geo_short_address' )
					OR ID IN ( SELECT $wpdb->postmeta.post_id FROM $wpdb->postmeta WHERE meta_key = 'geo_short_address' AND meta_value = '' )
				)
				AND $wpdb->posts.post_status = 'publish' AND post_type = '%s'
				ORDER BY  post_date DESC", get_query_var('post_type') );

			$geo_data =  "
					SELECT latitude.post_id, lat, lng, latitude.post_date FROM
						( SELECT post_id, meta_value lat, post_date
							FROM $wpdb->posts
							LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
							WHERE meta_key = '_jr_geo_latitude' AND $wpdb->posts.post_status = 'publish'
						) latitude,
						( SELECT post_id, meta_value lng, post_date
							FROM $wpdb->posts
							LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
							WHERE meta_key = '_jr_geo_longitude' AND $wpdb->posts.post_status = 'publish'
						) longitude
					WHERE  latitude.post_id = longitude.post_id
					AND latitude.post_id IN ( SELECT $wpdb->postmeta.post_id FROM $wpdb->postmeta WHERE meta_key = 'geo_short_address' AND meta_value <> '')
					";

			$radial_query = $wpdb->prepare( "
						SELECT post_id, ( %d * acos( cos( radians(%f) ) * cos( radians(lat) ) * cos( radians(lng) - radians(%f) ) + sin( radians(%f) ) * sin( radians(lat) ) ) ) AS distance, post_date
						FROM ( $geo_data ) as geo_data
						HAVING distance < %d
						UNION  ( $anywhere_data )
						ORDER BY COALESCE(distance, 999999999) ASC, post_date DESC", $R, $lat, $lng, $lat, $radius );

			$full_query = "SELECT post_id FROM ( $radial_query ) as jobs";

			$posts = $wpdb->get_col( $full_query );
			$result = array( 'address' => $address_array['full_address'], 'radius' => $radius, 'posts' => $posts );

			return $result;

		endif;
	}

}

// Shows the map on single job listings
function jr_job_map() {
	global $post;

	$title = str_replace('"', '&quot;', wptexturize($post->post_title));
	$long 	= get_post_meta($post->ID, '_jr_geo_longitude', true);
	$lat 	= get_post_meta($post->ID, '_jr_geo_latitude', true);

	if (!$long || !$lat) return;
	?>

	<div id="job_map" style="height: 300px; display:none;"></div>
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery.noConflict();
		(function($) {

			$(function() {

				// Map global vars
				var map;
				var marker;
				var center;

				// initialize Google Maps API
				function initMap() {

					// Define Map center
					center = new google.maps.LatLng('<?php echo $lat; ?>','<?php echo $long; ?>');

					// Define Map options
					var myOptions = {
					  'zoom': 10,
					  'center': center,
					  'mapTypeId': google.maps.MapTypeId.ROADMAP
					};

					// Load Map
					map = new google.maps.Map(document.getElementById('job_map'), myOptions);

					// Marker
					marker = new google.maps.Marker({ position: center, map: map, title: "<?php echo $title; ?>" });

				}

				// Slide Toggle
				$('a.toggle_map').click(function(){
			    	$('#share_form').slideUp();
			        $('#apply_form').slideUp();
					if (!map) initMap();
			        $('#job_map').slideToggle(function(){
						google.maps.event.trigger(map, 'resize');
						map.setCenter(center);
			        });
			        $('a.apply_online').removeClass('active');
			        $(this).toggleClass('active');
			        return false;
			    });

			});
		})(jQuery);
	/* ]]> */
	</script>
	<?php

}

function jr_radius_dropdown() {
	global $jr_options;
?>
	<div class="radius">
		<label for="radius"><?php _e('Radius:', APP_TD); ?></label>
		<select name="radius" class="radius" id="radius">
<?php
		$selected_radius = isset( $_GET['radius'] ) ? absint( $_GET['radius'] ) : 0;

		foreach ( array( 0, 1, 5, 10, 50, 100, 1000, 5000 ) as $radius ) {
			if ( ! $radius ) {
				$echo_radius = __( 'Auto', APP_TD );
			} else {
				$echo_radius = number_format_i18n( $radius ) . ' ' . $jr_options->jr_distance_unit;
			}
			?><option value="<?php echo esc_attr($radius); ?>" <?php selected( $selected_radius, $radius ); ?>><?php echo $echo_radius; ?></option><?php
		}
?>
		</select>
	</div><!-- end radius -->
<?php
}

// clear cached locations to make sure geolocation changes are applied
function _jr_clear_geolocation_cache() {
	global $wpdb;

	$wpdb->query( "
		DELETE FROM $wpdb->options WHERE option_name LIKE '%_jr_geo_%'
	");
}

function jr_geolocation_scripts( $job = '' ) {
	global $job_details, $post, $posted;

	if ( is_singular( APP_POST_TYPE ) ) {
		$map_id = 'job_map';
	} else {
		$map_id = 'geolocation-map';
	}

	$zoom = JR_DEFAULT_ZOOM;

	if ( $job ) {
		$posted['jr_geo_latitude'] = $job->jr_geo_latitude;
		$posted['jr_geo_longitude'] = $job->jr_geo_longitude;
	}

?>
	<script type="text/javascript">

		function initialize_map() {

			// skip if loaded on other pages
			if ( undefined == jQuery('#map_wrap').html() ) {
				return;
			}

			var hasLocation = false;
			var center = new google.maps.LatLng(0.0,0.0);

			var postLatitude =  '<?php if (isset($posted['jr_geo_latitude'])) echo $posted['jr_geo_latitude']; elseif (isset($job_details->ID)) echo get_post_meta($job_details->ID, '_jr_geo_latitude', true); elseif (isset($post->ID)) echo get_post_meta($post->ID, '_jr_geo_latitude', true); ?>';
			var postLongitude =  '<?php if (isset($posted['jr_geo_longitude'])) echo $posted['jr_geo_longitude']; elseif (isset($job_details->ID)) echo get_post_meta($job_details->ID, '_jr_geo_longitude', true); elseif (isset($post->ID)) echo get_post_meta($post->ID, '_jr_geo_longitude', true); ?>';

			if ( ( postLatitude != '' ) && ( postLongitude != '' ) ) {
				center = new google.maps.LatLng(postLatitude, postLongitude);
				hasLocation = true;
				jQuery("#geolocation-latitude").val(center.lat());
				jQuery("#geolocation-longitude").val(center.lng());
				reverseGeocode(center);
			}

		 	var myOptions = {
		      zoom: <?php echo $zoom; ?>,
		      center: center,
		      mapTypeId: google.maps.MapTypeId.ROADMAP
		    };

		    var geocoder = new google.maps.Geocoder();
			var map = new google.maps.Map(document.getElementById("<?php echo $map_id; ?>"), myOptions);

			var marker = '';

			if(!hasLocation) {
		    	map.setZoom(<?php echo $zoom; ?>);
		    } else {
		    	map.setZoom(9);
		    }

			google.maps.event.addListener(map, 'click', function(event) {
				reverseGeocode(event.latLng);
			});

			var currentAddress;
			var customAddress = false;

			jQuery("#geolocation-load").click(function(){
				if( jQuery("#geolocation-address").val() != undefined ) {
					customAddress = true;
					currentAddress = jQuery("#geolocation-address").val();
					geocode(currentAddress);
					return false;
				} else {
					marker.setMap(null);
					marker = '';
					jQuery("#geolocation-latitude").val('');
					jQuery("#geolocation-longitude").val('');
					return false;
				}
			});

			jQuery("#geolocation-address").keyup(function(e) {
				if ( e.keyCode == 13 ) {
					jQuery("#geolocation-load").click();
				}
			});

			function placeMarker(location) {
				if (marker=='') {
					marker = new google.maps.Marker({
						position: center,
						map: map,
						title:'Job Location'
					});
				}
				marker.setPosition(location);
				map.setCenter(location);
				if ( ( location.lat() != '' ) && ( location.lng() != '' ) ) {
					jQuery("#geolocation-latitude").val(location.lat());
					jQuery("#geolocation-longitude").val(location.lng());
				}
			}

			function geocode(address) {
				var geocoder = new google.maps.Geocoder();
			    if (geocoder) {
					geocoder.geocode({"address": address}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							placeMarker(results[0].geometry.location);
							reverseGeocode(results[0].geometry.location);
							if(!hasLocation) {
						    	map.setZoom(9);
						    	hasLocation = true;
							}
							jQuery("#geodata").html(results[0].geometry.location.lat() + ', ' + results[0].geometry.location.lng());
						}
					});
				}
			}

			function reverseGeocode(location) {
				var geocoder = new google.maps.Geocoder();
			    if (geocoder) {
					geocoder.geocode({"latLng": location}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {

						var address, country, state, short_address, short_address_country;

						var city = [];

						for ( var i in results ) {

						    var address_components = results[i]['address_components'];

						    for ( var j in address_components ) {

						    	var types = address_components[j]['types'];
						    	var long_name = address_components[j]['long_name'];
						    	var short_name = address_components[j]['short_name'];

						    	if ( jQuery.inArray('locality', types)>=0 && jQuery.inArray('political', types)>=0 ) {
									if (jQuery.inArray(long_name, city)<0) city.push(long_name);
						    	}
						    	else if ( jQuery.inArray('administrative_area_level_1', types)>=0 && jQuery.inArray('political', types)>=0 ) {
						    		state = long_name;
						    	}
						    	else if ( jQuery.inArray('country', types)>=0 && jQuery.inArray('political', types)>=0 ) {
						    		country = long_name;
						    	}
						    }

						    if((city) && (state) && (country)) break;
						}

						// fix for countries with no valid state
						if (!state)
							city = city[0];
						else
							city = city.join(", ");

						if((city) && (state) && (country))
							address = city + ', ' + state + ', ' + country;
						else if((city) && (state))
							address = city + ', ' + state;
						else if((state) && (country))
							address = state + ', ' + country;
						// fix for countries with no valid state
						else if((city) && (country)) {
							address = city + ', ' + country;
						}
						//
						else if(country)
							address = country;

						if((city) && (state) && (country)) {
							short_address = city;
							short_address_country = state + ', ' + country;
						} else if((city) && (state)) {
							short_address = city;
							short_address_country = state;
						} else if((state) && (country)) {
							short_address = state;
							short_address_country = country;
						// fix for countries with no valid state
						} else if((city) && (country)) {
							short_address = city;
							short_address_country = country;
						//
						} else if(country) {
							short_address = country;
							short_address_country = '';
						}

						// Set address field
						jQuery("#geolocation-address").val(address);

						// Set hidden address fields
						jQuery("#geolocation-short-address").val(short_address);
						jQuery("#geolocation-short-address-country").val(short_address_country);
						jQuery("#geolocation-country").val(country);

						// Place Marker
						placeMarker(location);

						return true;
					}

					});
				}
				return false;
			}

		}

		function loadScript() {
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = "<?php echo esc_url_raw( _jr_get_js_geolocation_url('initialize_map') ); ?>";
			document.body.appendChild(script);
		}

		jQuery(function(){
			// Prevent form submission on enter key
			jQuery("#submit_form").submit(function(e) {
				if (jQuery("input:focus").attr("id")=='geolocation-address') return false;
			});

			loadScript();
		});
	</script>
	<?php
}

/**
 * Retrieves the latitude and longitued given a location.
 *
 * @since 1.8
 */
function jr_get_coordinates_by_location( $location ) {

	if ( ! function_exists('json_decode') ) {
		return false;
	}

	$address = _jr_get_geolocation_url( $location );

	$cached = get_transient( 'jr_geo_'.sanitize_title( $location ) );

	if ( $cached ) {
		$address = $cached;
	} else {

		$address = json_decode( wp_remote_retrieve_body( wp_remote_get( $address ) ), true );
		if ( is_array( $address ) ) {
			set_transient( 'jr_geo_'.sanitize_title($location), $address, 60*60*24*7 ); // Cache for a week
		}

	}

	if ( ! empty( $address['results'][0] ) ) {
		// Put address info into a nice array format
		$address_array = array(
			'longitude' => $address['results'][0]['geometry']['location']['lng'],
			'latitude' 	=> $address['results'][0]['geometry']['location']['lat']
		);

		$address_array['full_address'] = $address['results'][0]['formatted_address'];

		return $address_array;
	}

	return $address;
}

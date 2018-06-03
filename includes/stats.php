<?php
/**
 *
 * Keeps track of views for daily and total
 * @author AppThemes
 *
 *
 */

// sidebar widget showing overall popular ads
function jr_todays_overall_count_widget( $post_type, $limit ) {
	global $wpdb;

	// get all the post view info to display
	$sql = $wpdb->prepare( "SELECT t.postcount, p.ID, p.post_title
				FROM $wpdb->app_pop_total AS t
				INNER JOIN $wpdb->posts AS p ON p.ID = t.postnum
				WHERE t.postcount > 0
				AND p.post_status = 'publish' AND p.post_type = %s
				ORDER BY t.postcount DESC LIMIT %d", $post_type, $limit );

	$results = $wpdb->get_results( $sql );

	echo '<ul class="pop">';

	// must be overall views
	if ( $results ) {

		foreach ( $results as $result )
			echo '<li><a href="'.get_permalink($result->ID).'">'.$result->post_title.'</a> ('.number_format($result->postcount).'&nbsp;'.__('views', APP_TD) .')</li>';

    } else {

		echo '<li>' . __('No jobs viewed yet.', APP_TD) . '</li>';

	}

	echo '</ul>';

}

// sidebar widget showing today's popular ads
function jr_todays_count_widget( $post_type, $limit ) {
	global $wpdb;

	$today_date = date( 'Y-m-d', current_time( 'timestamp' ) );

	// get all the post view info to display
	$sql = $wpdb->prepare( "SELECT t.postcount, p.ID, p.post_title
			FROM $wpdb->app_pop_daily AS t
			INNER JOIN $wpdb->posts AS p ON p.ID = t.postnum
			WHERE time = %s
			AND t.postcount > 0 AND p.post_status = 'publish' AND p.post_type = %s
			ORDER BY t.postcount DESC LIMIT %d", $today_date, $post_type, $limit );

	$results = $wpdb->get_results( $sql );

	echo '<ul class="pop">';

	// must be views today
	if ( $results ) {

		foreach ( $results as $result )
			echo '<li><a href="'.get_permalink($result->ID).'">'.$result->post_title.'</a> ('.number_format($result->postcount).'&nbsp;'.__('views', APP_TD) .')</li>';

	} else {

		echo '<li>' . __( 'No jobs viewed yet.', APP_TD ) . '</li>';
	}

	echo '</ul>';

}

// creates the charts on the dashboard
function jr_dashboard_charts() {
	global $wpdb;

	$sql = "SELECT COUNT(post_title) as total, post_date 
			FROM $wpdb->posts
			WHERE post_type = '%s' AND post_date > '%s' 
			GROUP BY DATE(post_date) 
			DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, APP_POST_TYPE, date( 'Y-m-d', strtotime('-30 days') ) ) );

	$listings = array();

	// put the days and total posts into an array
	foreach ( $results as $result ) {
		$the_day = date('Y-m-d', strtotime($result->post_date));
		$listings[$the_day] = $result->total;
	}

	// setup the last 30 days
	for( $i = 0; $i < 30; $i++ ) {
		$each_day = date('Y-m-d', strtotime('-'. $i .' days'));

		// if there's no day with posts, insert a goose egg
		if ( !in_array($each_day, array_keys($listings)) ) $listings[$each_day] = 0;
	}

	// sort the values by date
	ksort($listings);

	$args = array(
		'meta_query' => array(
			array(
				'key' => '_valid_resume_subscription_start',
				'value' => date( 'Y-m-d', strtotime('-30 days') ),
				'compare' => '>'
			),
		),
	);
	$subscribers = new WP_User_Query( $args );

	$sql = "SELECT COUNT(user_id) as total, meta_value as subscr_date 
			FROM $wpdb->usermeta 
			WHERE meta_key = '_valid_resume_subscription_start' AND meta_value > %d
			GROUP BY DATE(subscr_date) 
			DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, strtotime('-30 days') ) );

	$subscriptions = array();

	// put the days and total posts into an array
	foreach ( $results as $result ) {
		$the_day = date( 'Y-m-d', $result->subscr_date );
		$subscriptions[$the_day] = $result->total;
	}

	// setup the last 30 days
	for( $i = 0; $i < 30; $i++ ) {
		$each_day = date('Y-m-d', strtotime('-'. $i .' days'));

		// if there's no day with posts, insert a goose egg
		if ( !in_array($each_day, array_keys($subscriptions)) ) $subscriptions[$each_day] = 0;
	}

	// sort the values by date
	ksort($subscriptions);

	// Get sales - completed orders with a cost
	$sales = array();

	$sales = jr_daily_orders_sales( array( 'jr_sales_time_span' => date('Y-m-d', strtotime('-30 days')) ) );

	// legacy order table sales

	if ( $wpdb->get_results("SHOW TABLES LIKE '$wpdb->jr_orders' ") ) {
		$sql = "SELECT SUM(cost) as total, order_date 
				FROM $wpdb->jr_orders WHERE status = 'completed' AND order_date > '%s' 
				GROUP BY DATE(order_date) 
				DESC";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, date( 'Y-m-d', strtotime('-30 days') ) ) );

		// put the days and total posts into an array
		foreach ($results as $result) {
			$the_day = date( 'Y-m-d', strtotime($result->order_date) );
			if ( empty($sales[$the_day]) ) $sales[$the_day] = 0;
			$sales[$the_day] += $result->total;
		}
	}

	// setup the last 30 days
	for( $i = 0; $i < 30; $i++ ) {
		$each_day = date( 'Y-m-d', strtotime('-'. $i .' days') );

		// if there's no day with posts, insert a goose egg
		if ( !in_array($each_day, array_keys($sales)) ) $sales[$each_day] = 0;
	}

	// sort the values by date
	ksort($sales);
?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function () {

	var posts = [
		<?php
		foreach ($listings as $day => $value) {
			$sdate = strtotime($day);
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			//$theoutput[] = $newoutput;
			echo $newoutput;
		}
		?>
	]

	var subscriptions = [
		<?php
		foreach ($subscriptions as $day => $value) {
			$sdate = strtotime($day);
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			//$theoutput[] = $newoutput;
			echo $newoutput;
		}
		?>
	]

	var sales = [
		<?php
		foreach ($sales as $day => $value) {
			$sdate = strtotime($day);
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			//$theoutput[] = $newoutput;
			echo $newoutput;
		}
		?>
	];


	var placeholder = jQuery("#placeholder");

	var output = [
		{
			data: posts,
			label: "<?php _e('Job Listings', APP_TD) ?>",
			symbol: ''
		},
		{
			data: subscriptions,
			label: "<?php _e('Resumes Subscriptions', APP_TD) ?>",
			symbol: ''
		},
		{
			data: sales,
			label: "<?php _e('Total Sales', APP_TD) ?>",
			symbol: '<?php echo APP_Currencies::get_current_symbol(); ?>',
			yaxis: 2
		}
	];

	var options = {
       series: {
		   lines: { show: true },
		   points: { show: true }
	   },
	   grid: {
		   tickColor:'#f4f4f4',
		   hoverable: true,
		   clickable: true,
		   borderColor: '#f4f4f4',
		   backgroundColor:'#FFFFFF'
	   },
       xaxis: { mode: 'time',
				timeformat: "%m/%d"
	   },
	   yaxis: { min: 0 },
	   y2axis: { min: 0, tickFormatter: function (v, axis) { return v.toFixed(axis.tickDecimals) + " <?php echo APP_Currencies::get_current_symbol(); ?>" }},
	   legend: { position: 'nw' }
    };

	jQuery.plot(placeholder, output, options);

	// reload the plot when browser window gets resized
	jQuery(window).resize(function() {
		jQuery.plot(placeholder, output, options);
	});

	function showChartTooltip(x, y, contents) {
		jQuery('<div id="charttooltip">' + contents + '</div>').css( {
		position: 'absolute',
		display: 'none',
		top: y + 5,
		left: x + 5,
		opacity: 1
		}).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	jQuery("#placeholder").bind("plothover", function (event, pos, item) {
		jQuery("#x").text(pos.x.toFixed(2));
		jQuery("#y").text(pos.y.toFixed(2));
		if (item) {
			if (previousPoint != item.datapoint) {
                previousPoint = item.datapoint;

				jQuery("#charttooltip").remove();
				var x = new Date(item.datapoint[0]), y = item.datapoint[1];
				var xday = x.getDate(), xmonth = x.getMonth()+1; // jan = 0 so we need to offset month
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + "</b> " + item.series.label);
			}
		} else {
			jQuery("#charttooltip").remove();
			previousPoint = null;
		}
	});
});
// ]]>
</script>

<?php
}
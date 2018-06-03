<?php
/**
 * The Admin Dashboard Class.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Admin\Dashboard
 * @copyright 2010 all rights reserved
 */


/**
 * Theme Dashboard page.
 */
class JR_Admin_Dashboard extends APP_DashBoard {

	const SUPPORT_FORUM = 'http://forums.appthemes.com/external.php?type=RSS2';

	/**
	 * Sets up page.
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct( array(
			'page_title' => __( 'Dashboard', APP_TD ),
			'menu_title' => __( 'JobRoller', APP_TD ),
		) );

		add_filter( 'post_clauses', array( $this, 'filter_past_days' ), 10, 2 );

		$this->boxes[] = array( 'stats_30_days', $this->box_icon( 'at-chart-bar' ) . __( 'Last 30 Days', APP_TD ), 'side', 'high' );
		$this->boxes[] = array( 'support_forum',  $this->box_icon( 'at-discussion' ) . __( 'Forums', APP_TD ), 'normal', 'low' );

		$stats_icon = $this->box_icon( 'at-chart-pie' );
		$stats = array( 'stats', $stats_icon .  __( 'Overview', APP_TD ), 'normal' );
		array_unshift( $this->boxes, $stats );

	}

	/**
	 * Displays stats box.
	 *
	 * @return void
	 */
	public function stats_box() {
		global $jr_options;

		$users = array();
		$users_stats = $this->get_user_counts();

		$totals[ __( 'Users', APP_TD ) ] = array(
			'text' => $users_stats['total_users'],
			'url' => 'users.php',
		);
?>
		<div class="stats_overview">
			<h3><?php _e( 'New Registrations', APP_TD ); ?></h3>
			<div class="overview_today">
				<p class="overview_day"><?php _e( 'Today', APP_TD ); ?></p>
				<p class="overview_count"><?php echo $users_stats['job_listers_today']; ?></p>
				<p class="overview_type"><em><?php _e( 'Job Listers', APP_TD ); ?></em></p>
				<p class="overview_count"><?php echo $users_stats['job_seekers_today']; ?></p>
				<p class="overview_type_seek"><em><?php _e( 'Job Seekers', APP_TD ); ?></em></p>
				<?php if ( $jr_options->jr_allow_recruiters ): ?>
					<p class="overview_count"><?php echo $users_stats['recruiters_today']; ?></p>
					<p class="overview_type_recruiter"><em><?php _e( 'Recruiters', APP_TD ); ?></em></p>
				<?php endif; ?>
			</div>

			<div class="overview_previous">
				<p class="overview_day"><?php _e( 'Yesterday', APP_TD ); ?></p>
				<p class="overview_count"><?php echo $users_stats['job_listers_yesterday']; ?></p>
				<p class="overview_type"><em><?php _e( 'Job Listers', APP_TD ); ?></em></p>
				<p class="overview_count"><?php echo $users_stats['job_listers_yesterday']; ?></p>
				<p class="overview_type_seek"><em><?php _e( 'Job Seekers', APP_TD ); ?></em></p>
				<?php if ( $jr_options->jr_allow_recruiters ): ?>
					<p class="overview_count"><?php echo $users_stats['recruiters_yesterday']; ?></p>
					<p class="overview_type_recruiter"><em><?php _e( 'Recruiters', APP_TD ); ?></em></p>
				<?php endif; ?>
			</div>
		</div>
<?php

		$stats = array();

		$listings = $this->get_listing_counts();

		$totals[ __( 'Jobs', APP_TD ) ] = array(
			'text' => $listings['all'],
			'url' => add_query_arg( array( 'post_type' => APP_POST_TYPE ), admin_url( 'edit.php' ) ),
		);

		$this->output_list( $totals );

		$stats[ __( 'Jobs (Last 7 Days)', APP_TD ) ] = $listings['new'];
		if ( isset( $listings['publish'] ) ){
			$stats[ __( 'Live Jobs', APP_TD ) ] = array(
				'text' => $listings['publish'],
				'url' => add_query_arg( array( 'post_type' => APP_POST_TYPE, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ),
			);
		} else {
			$stats[ __( 'Live Jobs', APP_TD ) ] = 0;
		}
		if ( isset( $listings['pending'] ) ){
			$stats[ __( 'Pending Jobs', APP_TD ) ] = array(
				'text' => $listings['pending'],
				'url' => add_query_arg( array( 'post_type' => APP_POST_TYPE, 'post_status' => 'pending' ), admin_url( 'edit.php' ) ),
			);
		} else {
			$stats[ __( 'Pending Jobs', APP_TD ) ] = 0;
		}

		$resumes = $this->get_resumes_counts();

		if ( isset( $resumes['publish'] ) ){
			$stats[ __( 'Live Resumes', APP_TD ) ] = array(
				'text' => $resumes['publish'],
				'url' => add_query_arg( array( 'post_type' => APP_POST_TYPE_RESUME ), admin_url( 'edit.php' ) ),
			);
		} else {
			$stats[ __( 'Live Resumes', APP_TD ) ] = 0;
		}

		if ( current_theme_supports( 'app-payments' ) ){
			$orders = $this->get_order_counts();
			$stats[ __( 'Last 7 Days', APP_TD ) ] = appthemes_get_price( $orders['revenue'] );
			$stats[ __( 'Overall', APP_TD ) ] = appthemes_get_price( array_sum( jr_daily_orders_sales() ) );
		}

		$stats[ __( 'Version', APP_TD ) ] = JR_VERSION;
		$stats[ __( 'Support', APP_TD ) ] = html( 'a', array( 'href' => 'http://forums.appthemes.com', 'target' => '_blank' ), __( 'Forums', APP_TD ) );
		$stats[ __( 'Support', APP_TD ) ] .= ' | ' . html( 'a', array( 'href' => 'https://docs.appthemes.com', 'target' => '_blank' ), __( 'Docs', APP_TD ) );

		$this->output_list( $stats );
	}

	/**
	 * Displays charts box with stats for last 30 days.
	 *
	 * @return void
	 */
	public function stats_30_days_box() {
		echo '<div class="statsico">';
		jr_dashboard_charts();
		echo '</div>';
	}

	/**
	 * Displays recent forum posts box.
	 *
	 * @return void
	 */
	public function support_forum_box() {
		global $app_forum_rss_feed;
		echo '<div class="forumico">';
		wp_widget_rss_output( self::SUPPORT_FORUM, array( 'items' => 5, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1 ) );
		echo '</div>';
	}

	/**
	 * Returns/Outputs html list.
	 *
	 * @param array $items
	 * @param string $begin (optional)
	 * @param string $end (optional)
	 * @param bool $echo (optional)
	 *
	 * @return string|void
	 */
	private function output_list( $array, $begin = '<ul>', $end = '</ul>', $echo = true ) {

		$html = '';
		foreach( $array as $title => $value ){
			if ( is_array( $value ) ) {
				$html .= '<li>' . $title . ': <a href="' . $value['url'] . '">' . $value['text'] . '</a></li>';
			} else {
				$html .= '<li>' . $title . ': ' . $value . '</li>';
			}
		}

		$html = $begin . $html . $end;

		$html = html( 'div', array( 'class' => 'stats-info' ), $html );

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Returns an array of user counts.
	 *
	 * @return array
	 */
	private function get_user_counts() {
		global $wpdb;

		$users = (array) count_users();

		$capabilities_meta = $wpdb->prefix . 'capabilities';
		$date_today = date( 'Y-m-d' );
		$date_yesterday = date( 'Y-m-d', strtotime( '-1 days' ) );

		$users['job_listers_today'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered >= %s", $capabilities_meta, '%administrator%', '%job_lister%', $date_today ) );
		$users['job_listers_yesterday'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered BETWEEN %s AND %s", $capabilities_meta, '%administrator%', '%job_lister%', $date_yesterday, $date_today ) );

		$users['recruiters_today'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered >= %s", $capabilities_meta, '%administrator%', '%recruiter%', $date_today ) );
		$users['recruiters_yesterday'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered BETWEEN %s AND %s", $capabilities_meta, '%administrator%', '%recruiter%', $date_yesterday, $date_today ) );

		$users['job_seekers_today'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered >= %s", $capabilities_meta, '%administrator%', '%job_seeker%', $date_today ) );
		$users['job_seekers_yesterday'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->usermeta.meta_value LIKE %s AND $wpdb->users.user_registered BETWEEN %s AND %s", $capabilities_meta, '%administrator%', '%job_seeker%', $date_yesterday, $date_today ) );

		return $users;
	}

	/**
	 * Returns an array of job listing counts.
	 *
	 * @return array
	 */
	private function get_listing_counts() {

		$listings = (array) wp_count_posts( APP_POST_TYPE );

		$all = 0;
		foreach ( (array) $listings as $type => $count ){
			$all += $count;
		}
		$listings['all'] = $all;

		$yesterday_posts = new WP_Query( array(
			'post_type' => APP_POST_TYPE,
			'past_days' => 7,
		) );
		$listings['new'] = $yesterday_posts->post_count;

		return $listings;

	}

	/**
	 * Returns an array of resume counts.
	 *
	 * @return array
	 */
	private function get_resumes_counts() {

		$resumes = (array) wp_count_posts( APP_POST_TYPE_RESUME );

		$all = 0;
		foreach ( (array) $resumes as $type => $count ) {
			$all += $count;
		}
		$resumes['all'] = $all;

		return $resumes;
	}

	/**
	 * Returns an array of orders revenue.
	 *
	 * @return array
	 */
	private function get_order_counts( $args = array() ) {

		$orders = (array) wp_count_posts( APPTHEMES_ORDER_PTYPE );

		$week_orders = new WP_Query( array(
			'post_type' => APPTHEMES_ORDER_PTYPE,
			'post_status' => array( APPTHEMES_ORDER_COMPLETED, APPTHEMES_ORDER_ACTIVATED ),
			'past_days' => 7,
		) );

		$revenue = 0;
		foreach ( $week_orders->posts as $post ) {
			// payments framework meta key
			$revenue += (float) get_post_meta( $post->ID, 'total_price', true );
		}

		$orders['revenue'] = $revenue;

		return $orders;
	}

	/**
	 * Filter to refine WP Query by past days.
	 *
	 * @param array $clauses
	 * @param object $wp_query
	 *
	 * @return array
	 */
	public function filter_past_days( $clauses, $wp_query ) {
		global $wp_query;

		$past_days = intval( $wp_query->get( 'past_days' ) );
		if ( $past_days ) {
			$clauses['where'] .= ' AND post_data > \'' . date( 'Y-m-d', strtotime( '-' . $past_days . ' days' ) ) . '\'';
		}

		return $clauses;
	}


}

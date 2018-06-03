<?php
/**
 * Admin options for the 'Subscriptions' page.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Subscriptions
 * @copyright 2010 all rights reserved
 */


### Classes

class JR_Subscriptions_Admin extends APP_Tabs_Page {

	function __construct( $options = null ) {

		if ( ! is_a( $options, 'scbOptions' ) ) {
			$options = new scbOptions( 'jr_subscriptions', false );
		}

		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'Subscriptions', APP_TD ),
			'menu_title' => __( 'Subscriptions', APP_TD ),
			'page_slug' => 'jr-subscriptions',
			'parent' => 'app-dashboard',
			'screen_icon' => 'options-general',
			'admin_action_priority' => 10,
		);

		parent::__construct( $options );
	}

	protected function init_tabs() {
		$this->tabs->add( 'resumes', __( 'Resumes', APP_TD ) );
		$this->tabs->add( 'alerts', __( 'Alerts', APP_TD ) );

		$this->tab_resumes();
		$this->tab_alerts();
	}

	protected function tab_resumes() {
		$this->tab_sections['resumes']['list'] = array(
			'fields' => array(),
			'renderer' => array( $this, 'resumes_output' ),
		);
	}

	protected function tab_alerts() {
		$bt_export_to_csv = '<a href="admin.php?page='.$this->args['page_slug'].'&amp;tab=alerts&amp;jr-export-alert-subscr=true" class="add-new-h2" title="">'.__( 'Export to CSV', APP_TD ).'</a>';

		$this->tab_sections['alerts']['list'] = array(
			'title' => $bt_export_to_csv,
			'fields' => array(),
			'renderer' => array( $this, 'alerts_output' ),
		);
	}

	protected function resumes_output() {
		new JR_Resumes_Subscriptions_Admin( $this->args );
	}

	protected function alerts_output() {
		new JR_Alerts_Subscribers_Admin( $this->args );
	}

	/**
	 * Customized page content.
	 */
	function page_content() {

		do_action( 'tabs_' . $this->pagehook . '_page_content', $this );

		if ( isset( $_GET['firstrun'] ) ) {
			do_action( 'appthemes_first_run' );
		}

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

		$tabs = $this->tabs->get_all();

		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = key( $tabs );
		}

		$current_url = scbUtil::get_current_url();

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab_id => $tab_title ) {
			$class = 'nav-tab';

			if ( $tab_id == $active_tab ) {
				$class .= ' nav-tab-active';
			}

			$href = esc_url( add_query_arg( 'tab', $tab_id, $current_url ) );

			echo ' ' . html( 'a', compact( 'class', 'href' ), $tab_title );
		}
		echo '</h2>';

		foreach ( $this->tab_sections[ $active_tab ] as $section_id => $section ) {
			if ( isset( $section['title'] ) ) {
				echo html( 'h3', $section['title'] );
			}

			if ( isset( $section['renderer'] ) ) {
				call_user_func( $section['renderer'], $section, $section_id );
			} else {
				$this->render_section( $section['fields'] );
			}
		}
	}

}

class JR_Resumes_Subscriptions_Admin {

	function __construct( $args ) {
		$this->args = $args;
		$this->page_content();
	}

	function page_content() {
	?>
		<div class="wrap jobroller">
			<p><?php _e( 'Below is a list of all the current resume subscribers.', APP_TD ); ?></p>

			<?php
			if ( isset( $_GET['p'] ) ) {
				$page = $_GET['p'];
			} else {
				$page = 1;
			}

			$dir = 'ASC';
			$sort = 'ID';

			$per_page = 20;
			$total_pages = 1;

			$show = 'active';

			$totals = $this->get_count_subscriptions();

			if ( isset( $_GET['show'] ) ) {

				switch ( $_GET['show'] ) {
					case "inactive" :
						$show = 'inactive';
						$total_pages = ceil( $totals['inactive'] / $per_page );
						break;
					default :
						$show = 'active';
						$total_pages = ceil( $totals['active'] / $per_page );
				}

			} else {
				$_GET['show'] = '';
			}

			if ( isset( $_GET['dir'] ) ) {
				$posteddir = $_GET['dir'];
			} else {
				$posteddir = '';
			}

			if ( isset( $_GET['sort'] ) ) {
				$postedsort = $_GET['sort'];
			} else {
				$postedsort = '';
			}

			$subscribers = $this->list_subscriptions( $show, $per_page * ($page - 1), $per_page, $postedsort, $posteddir );
			?>
			<div class="tablenav">
				<?php if ( $total_pages > 1 ) :	?>
					<div class="tablenav-pages alignright">
						<?php
							echo paginate_links( array(
								'base' => 'admin.php?page='.$this->args['page_slug'].'&show=' . $_GET['show'] . '%_%&sort=' . $postedsort . '&dir=' . $posteddir,
								'format' => '&p=%#%',
								'prev_text' => __( '&laquo; Previous', APP_TD ),
								'next_text' => __( 'Next &raquo;', APP_TD ),
								'total' => $total_pages,
								'current' => $page,
								'end_size' => 1,
								'mid_size' => 5,
							) );
						?>
					</div>
				<?php endif; ?>

				<ul class="subsubsub">
					<li><a href="admin.php?page=<?php echo $this->args['page_slug']; ?>&show=active" <?php if ( $show == 'active' ) echo 'class="current"'; ?>><?php _e( 'Active', APP_TD ); ?> <span class="count">(<?php echo $totals['active']; ?>)</span></a> |</li>
					<li><a href="admin.php?page=<?php echo $this->args['page_slug']; ?>&show=inactive" <?php if ( $show == 'inactive' ) echo 'class="current"'; ?>><?php _e( 'Inactive', APP_TD ); ?> <span class="count">(<?php echo $totals['inactive']; ?>)</span></a></li>
				</ul>
			</div>

			<div class="clear"></div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-user"><a href="<?php echo $this->echo_subscription_link( 'user_id', 'ASC' ); ?>"><?php _e( 'User', APP_TD ) ?></a></th>
						<?php if ( 'inactive' != $show ) : ?>
							<th scope="col" class="manage-column column-plan"><a href="<?php echo $this->echo_subscription_link( 'name', 'ASC' ); ?>"><?php _e( 'Plan Name', APP_TD ) ?></a></th>
							<th scope="col" class="manage-column column-trial"><a href="<?php echo $this->echo_subscription_link( 'trial', 'ASC' ); ?>"><?php _e( 'Trial?', APP_TD ) ?></a></th>
							<th scope="col" class="manage-column column-start-date"><a href="<?php echo $this->echo_subscription_link( 'start_date', 'DESC' ); ?>"><?php _e( 'Start Date', APP_TD ) ?></a></th>
							<th scope="col" class="manage-column column-end-date"><a href="<?php echo $this->echo_subscription_link( 'end_date', 'ASC' ); ?>"><?php _e( 'End Date', APP_TD ) ?></a></th>
							<th scope="col" class="manage-column column-recurs"><?php _e( 'Recurs', APP_TD ) ?></th>
						<?php endif; ?>
					</tr>
				</thead>
				<?php
				if ( sizeof( $subscribers ) > 0 ) :
					$rowclass = '';
				?>
				<tbody id="list">
					<?php
					foreach ( $subscribers as $subscriber ) :

						$rowclass = 'even' == $rowclass ? 'alt' : 'even';

						$user_info = get_userdata( $subscriber->ID );

						// get meta data
						$plan_id = get_user_meta( $subscriber->ID, '_valid_resume_subscription', true );
						if ( $plan_id ) {
							$plan_data = get_post_custom( $plan_id );
						}

						$trial = get_user_meta( $subscriber->ID, '_valid_resume_trial', true );
						$start_date = (int) get_user_meta( $subscriber->ID, '_valid_resume_subscription_start', true );
						$end_date = (int) get_user_meta( $subscriber->ID, '_valid_resume_subscription_end', true );

						$manage_link = add_query_arg( 'user_id', $subscriber->ID, self_admin_url( 'user-edit.php' ) );
						?>
						<tr class="<?php echo $rowclass ?>">
							<td class="column-user"><?php if ( $user_info ) : ?>#<?php echo $user_info->ID; ?> &ndash; <strong><?php echo $user_info->first_name ?> <?php echo $user_info->last_name ?></strong> <a href="<?php echo esc_url( $manage_link ); ?>">(<?php echo $user_info->display_name; ?>)</a> <br/><a href="mailto:<?php echo $user_info->user_email ?>"><?php echo $user_info->user_email ?></a><?php endif; ?></td>
							<?php if ( 'inactive' != $show ) : ?>
								<td class="column-plan"><?php echo (!empty( $plan_data ) ? $plan_data['title'][0] : __( 'N/A', APP_TD ) ); ?></td>
								<td class="column-trial"><?php if ( $trial ) echo __( 'Yes', APP_TD ); else echo __( 'No', APP_TD ); ?></td>
								<td class="column-start-date"><?php if ( $start_date ) echo appthemes_display_date( $start_date ); else echo __( 'N/A', APP_TD ); ?></td>
								<td class="column-end-date"><?php if ( $end_date ) echo appthemes_display_date( $end_date ); else echo __( 'N/A', APP_TD ); ?></td>
								<td class="column-recurs"><?php echo (!empty( $plan_data ) && !$trial ? sprintf( _n( 'Every %s day', 'Every %s days', $plan_data[JR_FIELD_PREFIX . 'duration'][0], APP_TD ), $plan_data[JR_FIELD_PREFIX . 'duration'][0] ) : __( 'N/A', APP_TD ) ); ?></td>
							<?php endif; ?>
						</tr>

					<?php endforeach; ?>

				</tbody>

				<?php else : ?>
						<tr><td colspan="<?php if ( 'inactive' != $show ) : ?>6<?php else : ?>3<?php endif; ?>"><?php _e( 'No subscriptions found.', APP_TD ); ?></td></tr>
				<?php endif; ?>
			</table>
			<br/>

			<script type="text/javascript">
			/* <![CDATA[ */
				jQuery('a.end-subscription').click(function(){
				var answer = confirm ("<?php _e( 'Are you sure you want to end this subscription?', APP_TD ); ?>");
					if (answer) return true;
					return false;
				})
			/* ]]> */
			</script>

	</div><!-- end wrap -->
	<?php
	}

	protected function echo_subscription_link( $sort = 'id', $dir = 'ASC' ) {

		if ( isset( $_GET['show'] ) ) {
			$show = $_GET['show'];
		} else {
			$show = 'active';
		}

		if ( isset( $_GET['p'] ) ) {
			$page = $_GET['p'];
		} else {
			$page = 1;
		}

		if ( isset( $_GET['dir'] ) ) {
			$posteddir = $_GET['dir'];
		} else {
			$posteddir = '';
		}

		if ( isset( $_GET['sort'] ) ) {
			$postedsort = $_GET['sort'];
		} else {
			$postedsort = '';
		}

		echo 'admin.php?page='.$this->args['page_slug'].'&amp;show=' . $show . '&amp;p=' . $page . '&amp;sort=' . $sort . '&amp;dir=';

		if ( $sort == $postedsort ) {

			if ( $posteddir == $dir ) {

				if ( $posteddir == 'ASC' ) {
					echo 'DESC';
				} else {
					echo 'ASC';
				}

			} else {
				echo $dir;
			}

		} else {
			echo $dir;
		}

	}

	/**
	 * Returns the subscriptions list.
	 */
	protected function list_subscriptions ( $show = 'active', $offset = 0, $limit = 20, $orderby = 'user_id', $order = 'ASC' ) {

		$order_cols = array(
			'user_id',
			'name',
			'trial',
			'start_date',
			'end_date',
		);

		// sanitize order columns
		if ( ! $orderby || ( $orderby && ! in_array($orderby, $order_cols) ) ) {
			$orderby = 'user_id';
		}

		$sort_vals = array(
			'ASC',
			'DESC'
		);

		// sanitize sort column
		if ( ! $order || ( $order && ! in_array($order, $sort_vals) ) ) {
			$order = 'ASC';
		}

		$args = array (
			'number ' 	=> $limit,
			'offset' 	=> $offset,
			'orderby' 	=> $orderby,
			'order' 	=> $order,
		);
		$subscribers = jr_get_resume_subscribers( $show, $args );

		return $subscribers->get_results();

	}

	/**
	 * Returns the subscriptions list.
	 */
	function get_count_subscriptions () {

		$totals = array(
			'active' => jr_get_resume_subscribers( 'active' )->get_total(),
			'inactive' => jr_get_resume_subscribers( 'inactive' )->get_total(),
		);

		return $totals;
	}

}

class JR_Alerts_Subscribers_Admin {

	protected $args;

	function __construct( $args ) {
		$this->args = $args;
		$this->maybe_export_subscribers();
		$this->page_content();
	}

	public function page_content() {
		global $message;

		$message = '';
		?>
		<div class="wrap jobroller">
			<p><?php _e( 'Below is a list of all the current job alerts subscribers.', APP_TD ); ?></p>

			<?php
			if ( isset( $_GET['p'] ) ) {
				$page = $_GET['p'];
			} else {
				$page = 1;
			}

			$dir = 'ASC';
			$sort = 'ID';

			$per_page = 20;
			$total_pages = 1;

			$totals = $this->get_count_subscribers();
			$total_pages = ceil( $totals['total'] / $per_page );

			$show = 'all';

			if ( isset( $_GET['show'] ) ) {

				switch ( $_GET['show'] ) {
					default :
						$total_pages = ceil( $totals['total'] / $per_page );
						break;
				}

			} else {
				$_GET['show'] = '';
			}

			if ( isset( $_GET['dir'] ) ) {
				$posteddir = $_GET['dir'];
			} else {
				$posteddir = '';
			}

			if ( isset( $_GET['sort'] ) ) {
				$postedsort = $_GET['sort'];
			} else {
				$postedsort = '';
			}

			$alerts_subscribers = $this->list_alerts_subscribers( $show, $per_page * ($page - 1), $per_page, $postedsort, $posteddir );
			?>
			<?php if ( $total_pages > 1 ) :	?>
			<div class="tablenav">
				<div class="tablenav-pages alignright">
					<?php
						echo paginate_links( array(
							'base' => 'admin.php?page='.$this->args['page_slug'].'&show=' . $_GET['show'] . '%_%&sort=' . $postedsort . '&dir=' . $posteddir,
							'format' => '&p=%#%',
							'prev_text' => __( '&laquo; Previous', APP_TD ),
							'next_text' => __( 'Next &raquo;', APP_TD ),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						) );
					?>
				</div>
			</div>
			<?php endif; ?>

			<div class="clear"></div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-user" style="width:20%;"><a href="<?php echo $this->echo_subscribers_link( 'user_id', 'ASC' ); ?>"><?php _e( 'User', APP_TD ) ?></a></th>
						<th scope="col" class="manage-column column-keywords"><?php _e( 'Keywords', APP_TD ) ?></a></th>
						<th scope="col" class="manage-column column-location"><?php _e( 'Location', APP_TD ) ?></a></th>
						<th scope="col" class="manage-column column-job_type"><?php _e( 'Job Types', APP_TD ) ?></a></th>
						<th scope="col" class="manage-column column-job_cat"><?php _e( 'Job Categories', APP_TD ) ?></a></th>
					</tr>
				</thead>

				<?php
				if ( sizeof( $alerts_subscribers ) > 0 ) :
					$rowclass = '';
				?>
					<tbody id="list">
					<?php
						foreach ( $alerts_subscribers as $subscriber ) :

							$rowclass = 'even' == $rowclass ? 'alt' : 'even';

							$user_info = array();
							if ( $subscriber['user_id'] ) {
								$user_info = get_userdata( $subscriber['user_id'] );
							}

							$job_type = array();
							if ( !empty( $subscriber['criteria']['job_type'] ) ) {
								$job_type = $this->get_multiple_term_names_by( 'id', $subscriber['criteria']['job_type'], APP_TAX_TYPE );
							}

							$job_cat = array();
							if ( !empty( $subscriber['criteria']['job_cat'] ) ) {
								$job_cat = $this->get_multiple_term_names_by( 'id', $subscriber['criteria']['job_cat'], APP_TAX_CAT );
							}

							$manage_link = add_query_arg( 'user_id', $subscriber['user_id'], self_admin_url( 'user-edit.php' ) );
						?>
							<tr class="<?php echo $rowclass ?>">
								<td class="column-user"><?php if ( !empty( $user_info ) ) : ?>#<?php echo $user_info->ID; ?> &ndash; <strong><?php echo $user_info->first_name ?> <?php echo $user_info->last_name ?></strong> <a href="<?php echo esc_url( $manage_link ); ?>">(<?php echo $user_info->display_name; ?>)</a><br/><a href="mailto:<?php echo $user_info->user_email ?>"><?php echo $user_info->user_email ?></a><?php endif; ?></td>
								<td class="column-keywords"><?php if ( !empty( $subscriber['criteria']['keyword'] ) ) echo implode( ',', $subscriber['criteria']['keyword'] ); else echo __( '-', APP_TD ); ?></td>
								<td class="column-location"><?php if ( !empty( $subscriber['criteria']['location'] ) ) echo implode( ',', $subscriber['criteria']['location'] ); else echo __( 'Anywhere', APP_TD ); ?></td>
								<td class="column-job_type"><?php if ( !empty( $job_type ) ) echo $job_type; else echo __( 'All', APP_TD ); ?></td>
								<td class="column-job_cat"><?php if ( !empty( $job_cat ) ) echo $job_cat; else echo __( 'All', APP_TD ); ?></td>
							</tr>
						<?php endforeach; ?>

					</tbody>
				<?php else : ?>
						<tr><td colspan=5> <?php _e( 'No subscribers found.', APP_TD ) ?></td></tr>
				<?php endif; ?>
			</table>

			<?php $last_activity = get_transient( 'jr_job_alerts_last_activity' ); ?>

			<p><em><?php echo __( 'Last Alert Activity: ', APP_TD ) . ( $last_activity ? human_time_diff( time(), $last_activity ) . ' ago' : __( 'None', APP_TD ) ); ?></em></p>

		</div><!-- end wrap -->
	<?php
	}

	// Returns the subscriptions list
	function list_alerts_subscribers ( $show = '', $offset = 0, $limit = 20, $orderby = 'user_id', $order = 'ASC' ) {
		global $wpdb;

		if ( ! $orderby || 'user_id' != $orderby ) {
			$orderby = 'user_id';
		}

		$sort_vals = array(
			'ASC',
			'DESC'
		);

		// sanitize sort column
		if ( ! $order || ( $order && ! in_array($order, $sort_vals) ) ) {
			$order = 'ASC';
		}

		$sql = "
			SELECT user_id,
			 (SELECT meta_value FROM $wpdb->usermeta as jtype WHERE meta_key = 'jr_alert_meta_keyword' AND user_id = user_meta.user_id)     as alert_keyword,
			 (SELECT meta_value FROM $wpdb->usermeta as jtype WHERE meta_key = 'jr_alert_meta_job_type' AND user_id = user_meta.user_id)    as alert_jtype,
			 (SELECT meta_value FROM $wpdb->usermeta as jcat WHERE meta_key = 'jr_alert_meta_job_cat' AND user_id = user_meta.user_id)      as alert_jcat,
			 (SELECT meta_value FROM $wpdb->usermeta as location WHERE meta_key = 'jr_alert_meta_location' AND user_id = user_meta.user_id) as alert_location
			FROM $wpdb->usermeta as user_meta
			WHERE meta_key = 'jr_alert_status' AND meta_value = 'active' ";

		$subscribers = $wpdb->get_results( $sql . " ORDER BY ".$orderby." ".$order.($limit>0?" LIMIT $offset, $limit":"") );

		if ( $subscribers ):

			$alerts = array();
			foreach( $subscribers as $subscriber) :

				$user_id = $subscriber->user_id;
				$alert = array (
							'keyword'  => maybe_unserialize($subscriber->alert_keyword),
							'location' => maybe_unserialize($subscriber->alert_location),
							'job_type' => maybe_unserialize($subscriber->alert_jtype),
							'job_cat'  => maybe_unserialize($subscriber->alert_jcat),
				);

				$alerts[] = array (
					'user_id'  => $user_id,
					'criteria' => $alert
				);

			endforeach;
			$subscribers = $alerts;
		endif;

		return $subscribers;
	}

	function echo_subscribers_link( $sort = 'id', $dir = 'ASC' ) {

		if ( isset( $_GET['show'] ) ) {
			$show = $_GET['show'];
		} else {
			$show = 'all';
		}

		if ( isset( $_GET['p'] ) ) {
			$page = $_GET['p'];
		} else {
			$page = 1;
		}

		if ( isset( $_GET['dir'] ) ) {
			$posteddir = $_GET['dir'];
		} else {
			$posteddir = '';
		}

		if ( isset( $_GET['sort'] ) ) {
			$postedsort = $_GET['sort'];
		} else {
			$postedsort = '';
		}

		echo 'admin.php?page=' . $this->args['page_slug'] . '&amp;show=' . $show . '&amp;p=' . $page . '&amp;sort=' . $sort . '&amp;dir=';

		if ( $sort == $postedsort ) {

			if ( $posteddir == $dir ) {

				if ( $posteddir == 'ASC' ) {
					echo 'DESC';
				} else {
					echo 'ASC';
				}

			} else {
				echo $dir;
			}

		} else {
			echo $dir;
		}

	}


	// Returns the subscriptions list
	function get_count_subscribers () {
		global $wpdb;

		$query = "SELECT distinct count(distinct user_id) total FROM $wpdb->usermeta WHERE meta_key = 'jr_alert' ";
		$totals = $wpdb->get_row( $query, ARRAY_A );

		return $totals;
	}

	// loop through an array of terms and returns the corresponding term names
	function get_multiple_term_names_by( $field = 'id', $terms, $taxonomy, $delimiter = ',' ) {

		$names = array();

		foreach ( $terms as $term ) {
			$term = get_term_by( $field, $term, $taxonomy );
			if ( $term ) {
				$names[] = $term->name;
			}
		}

		return implode( $delimiter, $names );
	}

	function maybe_export_subscribers() {

		if ( empty( $_GET['jr-export-alert-subscr'] ) ) {
			return;
		}

		ob_end_clean();

		header( "Content-type: text/plain; charset=" . get_bloginfo( 'charset' ) );
		header( "Content-Disposition: attachment; filename=jobroller_alerts_export_" . date( 'Ymd' ) . ".csv" );

		$csv = array();

		$row = array( "User ID", "Name", "Email", "Keywords", "Locations", "Job Types", "Job Categories" );

		$csv[] = '"' . implode( '","', $row ) . '"';

		$row = array();

		$subscribers_list = $this->list_alerts_subscribers();

		if ( sizeof( $subscribers_list ) > 0 ) {

			foreach ( $subscribers_list as $subscriber ) {

				$user_info = array();
				if ( $subscriber['user_id'] ) {
					$user_info = get_userdata( $subscriber['user_id'] );
				}

				$job_type = array();
				if ( !empty( $subscriber['criteria']['job_type'] ) ) {
					$job_type = $this->get_multiple_term_names_by( 'id', $subscriber['criteria']['job_type'], APP_TAX_TYPE );
				}

				$job_cat = array();
				if ( !empty( $subscriber['criteria']['job_cat'] ) ) {
					$job_cat = $this->get_multiple_term_names_by( 'id', $subscriber['criteria']['job_cat'], APP_TAX_CAT );
				}

				$row[] = '#' . $user_info->ID;
				$row[] = $user_info->first_name . ' ' . $user_info->last_name;
				$row[] = $user_info->user_email;

				$row[] = !empty( $subscriber['criteria']['keyword'] ) ? implode( ',', $subscriber['criteria']['keyword'] ) : '';
				$row[] = !empty( $subscriber['criteria']['location'] ) ? implode( ',', $subscriber['criteria']['location'] ) : '';

				$row[] = !empty( $job_type ) ? $job_type : '';
				$row[] = !empty( $job_cat ) ? $job_cat : '';

				$row = array_map( 'trim', $row );
				$row = array_map( 'html_entity_decode', $row );
				$row = array_map( 'addslashes', $row );

				$csv[] = '"' . implode( '","', $row ) . '"';

				$row = array();

			}

		}

		echo implode( "\n", $csv );
		exit;
	}

}

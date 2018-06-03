<?php
/**
 * Admin options for the 'Alerts' page.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Alerts
 */

class JR_Alerts_Admin extends APP_Tabs_Page {

	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'Alerts Settings', APP_TD ),
			'menu_title' => __( 'Alerts', APP_TD ),
			'page_slug'  => 'jr-alerts',
			'parent'	 => 'app-dashboard',
		);

		add_action( 'tabs_jobroller_page_jr-alerts', array( $this, 'send_test' ) );
	}

	protected function init_tabs() {
		$this->tabs->add( 'general', __( 'General', APP_TD ) );
		$this->tabs->add( 'email', __( 'Email', APP_TD ) );

		$this->tab_general();
		$this->tab_email_format();
	}

	protected function tab_general() {

		$this->tab_sections['general']['job-alerts'] = array(
			'title' => __( 'Job Alerts', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_job_alerts',
					'type' => 'checkbox',
					'desc' => __( 'Allow job seekers to set job alerts based on specific criteria', APP_TD ),
					'tip' => __( "A new section will be available on the job seeker's dashboard where they can configure their alerts criteria.", APP_TD ),
				),
				array(
					'title' => __( 'Batch Size', APP_TD ),
					'name' => 'jr_job_alerts_batch_size',
					'type' => 'number',
					'desc' => __( 'Maximum allowed emails to be sent at a given time', APP_TD ),
					'tip' => __( "A value between 1-100 is recommended so you don't overload your server or trigger a spam filter. If emails aren't be sent/received, contact your host provider to find out their mass emailing limitations.", APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Job Limit', APP_TD ),
					'name' => 'jr_job_alerts_jobs_limit',
					'type' => 'number',
					'desc' => __( 'Maximum number of job listings sent in each email', APP_TD ),
					'tip' => __( 'Email alerts can contain a list of jobs or individual jobs.', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Frequency', APP_TD ),
					'name' => 'jr_job_alerts_cron',
					'type' => 'select',
					'values' => array(
						'ten_minutes' => __( 'Every Ten Minutes', APP_TD ),
						'twenty_minutes' => __( 'Every Twenty Minutes', APP_TD ),
						'thirty_minutes' => __( 'Every Thirty Minutes', APP_TD ),
						'hourly' => __( 'Once Hourly', APP_TD ),
						'daily' => __( 'Once Daily', APP_TD ),
					),
					'desc' => __( "How often you want to trigger the job alerts cron job", APP_TD ),
					'tip' => __( 'Based on your selection, the job alerts cron will pick up XX new jobs and look for matching user alerts. It will then split the mailing list in chunks of XX users (based on batch size) that will receive the jobs list. The remaining users (if any), will be included on the batch that will run one hour later.', APP_TD ),
				),
			),
		);

		$this->tab_sections['general']['rss-feeds'] = array(
			'title' => __( 'RSS Feed', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_job_alerts_feed',
					'type' => 'checkbox',
					'desc' => __( 'Generate a custom feed for each job seeker', APP_TD ),
					'tip' => __( "This gives job seekers more flexibility on how they can receive alerts.", APP_TD ),
				),
			),
		);
	}

	protected function tab_email_format() {

		$this->tab_sections['email']['options'] = array(
			'title' => __( 'Email', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'From', APP_TD ),
					'name' => 'jr_job_alerts_from_name',
					'type' => 'text',
					'desc' => '',
					'tip' => __( 'This is what your users will see as the "from" name.', APP_TD ),
				),
				array(
					'title' => __( 'Email', APP_TD ),
					'name' => 'jr_job_alerts_from_email',
					'type' => 'text',
					'desc' => '',
					'tip' => __( 'This is what your users will see as the "from" email address.', APP_TD ),
				),
				array(
					'title' => __( 'Subject', APP_TD ),
					'name' => 'jr_job_alerts_email_subject',
					'type' => 'text',
					'desc' => '',
					'tip' => '',
					'extra' => array(
						'class' => 'regular-text code',
					),
				),
				array(
					'title' => __( 'Allow HTML', APP_TD ),
					'name' => 'jr_job_alerts_email_type',
					'type' => 'select',
					'values' => array(
						'text/html' => __( 'Yes', APP_TD ),
						'text/plain' => __( 'No', APP_TD )
					),
					'desc' => '',
					'tip' => '',
				),
				array(
					'title' => __( 'Template', APP_TD ),
					'name' => 'jr_job_alerts_email_template',
					'type' => 'select',
					'values' => array_merge( array( 'standard' => __( 'Standard', APP_TD ) ), jr_job_alerts_get_templates() ),
					'desc' =>  '',
					'tip' => __( "'Standard' uses the text fields below. 'External' requires an external HTML file as the email template (advanced users only). Both options use the custom variables presented below.", APP_TD ),
				),
			),
		);

		$this->tab_sections['email']['standard'] = array(
			'title' => __( 'Standard Template', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Body Message', APP_TD ),
					'name' => 'jr_job_alerts_email_body',
					'type' => 'textarea',
					'sanitize' => 'appthemes_clean',
					'desc' => __( 'You may use the following variables within the email body and/or subject line. Each variable MUST have the percentage signs wrapped around it with no spaces.', APP_TD )
						. '<br /><br />' . sprintf( __( '%s - prints out the username', APP_TD ), '<code>%username%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job title', APP_TD ), '<code>%jobtitle%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the jobs list', APP_TD ), '<code>%joblist%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your website url', APP_TD ), '<code>%siteurl%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your site name', APP_TD ), '<code>%blogname%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your sites login url', APP_TD ), '<code>%loginurl%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the user dashboard url', APP_TD ), '<code>%dashboardurl%</code>' )
						. '<br /><br />' . __( 'Always test your new email after making any changes to make sure it is working and formatted correctly. If you do not receive an email, chances are something is wrong with your email body.', APP_TD ),
					'tip' => __( 'Enter the text you would like job seekers to see in the email alerts.', APP_TD ),
					'extra' => array(
						'rows' => 25,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
				array(
					'title' => __( 'Body Jobs', APP_TD ),
					'name' => 'jr_job_alerts_job_body',
					'type' => 'textarea',
					'sanitize' => 'appthemes_clean',
					'desc' => __( 'You may use the following variables within the email job body. Each variable MUST have the percentage signs wrapped around it with no spaces.', APP_TD )
						. '<br /><br />' . sprintf( __( '%s - prints out the job title', APP_TD ), '<code>%jobtitle%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the date/time posted', APP_TD ), '<code>%jobtime%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the full job details', APP_TD ), '<code>%jobdetails%</code>' )
						. '<br />' . sprintf( __( '%s - prints out an except of the job details. Replace # for the length to display', APP_TD ), '<code>%jobdetails_#%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job type', APP_TD ), '<code>%jobtype%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job category', APP_TD ), '<code>%jobcat%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job author', APP_TD ), '<code>%author%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job company', APP_TD ), '<code>%company%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job location', APP_TD ), '<code>%location%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job url', APP_TD ), '<code>%permalink%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the job thumbnail url', APP_TD ), '<code>%thumbnail_url%</code>' )
						. '<br /><br />' . __( 'Always test your new email after making any changes to make sure it is working and formatted correctly. If you do not receive an email, chances are something is wrong with your email body.', APP_TD ),
					'tip' => __( 'Enter the text you would like job seekers to see in the email job part.', APP_TD ),
					'extra' => array(
						'rows' => 25,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
			),
		);

		$this->tab_sections['email']['test'] = array(
			'title' => __( 'Testing', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'testalerts',
					'type' => 'checkbox',
					'tip' => __( 'Test your job alert email after making any changes above. The last five jobs will be sent.', APP_TD ),
					'desc' => __( 'Send a test email after saving changes', APP_TD ),
				),
			),
		);
	}

	### Helper Methods

	function send_test() {
		global $user_ID;

		// validate test emails
		if ( ! empty( $_POST['testalerts'] ) ) {

			$args = array(
				'post_type'		 => APP_POST_TYPE,
				'post_status'	 => 'publish',
				'posts_per_page' => 5,
			);
			$jobs = new WP_Query( $args );

			$result = jr_job_alerts_send_email( $user_ID, $jobs->posts );

			if ( ! $result ) {
				$notice = 'error|' . __( 'There were errors sending the test email. Please check your log file for more details.', APP_TD );
			} else {
				$notice = 'updated|' . __( 'Test email sent succesfully!', APP_TD );
			}

			$notice = explode( '|', $notice );
			echo scb_admin_notice( $notice[1], $notice[0] );
		}
	}

}

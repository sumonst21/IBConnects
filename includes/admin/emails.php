<?php
/**
 * Admin options for the 'Emails' page.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Emails
 */


### Classes

class JR_Emails_Admin extends APP_Tabs_Page {

	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'Email Settings', APP_TD ),
			'menu_title' => __( 'Emails', APP_TD ),
			'page_slug' => 'jr-email-settings',
			'parent' => 'app-dashboard',
		);

	}

	protected function init_tabs() {
		$this->tabs->add( 'general', __( 'General', APP_TD ) );
		$this->tabs->add( 'new_user', __( 'New User', APP_TD ) );

		$this->tab_general();
		$this->tab_new_registration();
	}

	protected function tab_general() {

		$this->tab_sections['general']['email_admin'] = array(
			'title' => __( 'Admin', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Recipient', APP_TD ),
					'name' => '_blank',
					'type' => '',
					'desc' => sprintf( __( '%1$s (<a href="%2$s">change</a>)', APP_TD ), get_option('admin_email'), 'options-general.php' ),
					'extra' => array(
						'style' => 'display: none;'
					),
				),
				array(
					'title' => __( 'New User', APP_TD ),
					'name' => 'jr_nu_admin_email',
					'type' => 'checkbox',
					'desc' => __( 'Send an email when a user registers on my site', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'New Job', APP_TD ),
					'name' => 'jr_new_ad_email',
					'type' => 'checkbox',
					'desc' => __( 'Send an email when a user registers on my site', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Job Applicants', APP_TD ),
					'name' => 'jr_bcc_apply_emails',
					'type' => 'checkbox',
					'desc' => __( 'Send an email when a user applies for a job listing', APP_TD ),
					'tip' => __( 'It will be sent as a bcc (blind carbon copy) email.', APP_TD ),
				),
			),
		);

		$this->tab_sections['general']['email_user'] = array(
			'title' => __( 'User', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Approved Job', APP_TD ),
					'name' => 'jr_new_job_email_owner',
					'type' => 'checkbox',
					'desc' => __( 'Send an email once their job listing is approved', APP_TD ),
					'tip' => __( 'This is triggered when post status changes from pending to published.', APP_TD ),
				),
				array(
					'title' => __( 'Expired Job', APP_TD ),
					'name' => 'jr_expired_job_email_owner',
					'type' => 'checkbox',
					'desc' => __( "Send email reminders prior to their job listing expiring", APP_TD ),
					'tip' => __( 'Send an email five days and then again one day before their job expires. A final email will be sent once their job has expired. This is triggered when post status changes from published to draft.', APP_TD ),
				),
			),
		);

	}

	protected function tab_new_registration() {

		$this->tab_sections['new_user']['new-user'] = array(
			'title' => '',
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_nu_custom_email',
					'type' => 'checkbox',
					'desc' => __( 'Send a custom new user notification email instead of the WordPress default one', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Name', APP_TD ),
					'name' => 'jr_nu_from_name',
					'type' => 'text',
					'desc' => '',
					'tip' => __( 'This is what your users will see as the "from" name.', APP_TD ),
				),
				array(
					'title' => __( 'Email', APP_TD ),
					'name' => 'jr_nu_from_email',
					'type' => 'text',
					'desc' => '',
					'tip' => __( 'This is what your users will see as the "from" email address.', APP_TD ),
				),
				array(
					'title' => __( 'Subject', APP_TD ),
					'name' => 'jr_nu_email_subject',
					'type' => 'text',
					'desc' => '',
					'tip' => '',
					'extra' => array(
						'class' => 'regular-text code',
					),
				),
				array(
					'title' => __( 'Allow HTML', APP_TD ),
					'name' => 'jr_nu_email_type',
					'type' => 'select',
					'values' => array(
						'text/html' => __( 'Yes', APP_TD ),
						'text/plain' => __( 'No', APP_TD ),
					),
					'desc' => '',
					'tip' => __( 'Allow html markup in the email body below. If you have delivery problems, keep this option disabled.', APP_TD ),
				),
				array(
					'title' => __( 'Body', APP_TD ),
					'name' => 'jr_nu_email_body',
					'type' => 'textarea',
					'sanitize' => 'appthemes_clean',
					'desc' => __( 'You may use the following variables within the email body and/or subject line. Each variable MUST have the percentage signs wrapped around it with no spaces.', APP_TD )
						. '<br /><br />' . sprintf( __( '%s - prints out the username', APP_TD ), '<code>%username%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the users email address', APP_TD ), '<code>%useremail%</code>' )
						. '<br />' . sprintf( __( '%s - prints out the users text password', APP_TD ), '<code>%password%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your website url', APP_TD ), '<code>%siteurl%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your site name', APP_TD ), '<code>%blogname%</code>' )
						. '<br />' . sprintf( __( '%s - prints out your sites login url', APP_TD ), '<code>%loginurl%</code>' )
						. '<br /><br />' . __( 'Always test your new email after making any changes to make sure it is working and formatted correctly. If you do not receive an email, chances are something is wrong with your email body.', APP_TD ),
					'extra' => array(
						'rows' => 25,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
			),
		);

	}

}

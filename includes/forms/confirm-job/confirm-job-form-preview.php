<?php $status = ( 'no' != _jr_moderate_jobs() ?  __( 'for approval', APP_TD ) : '' ); ?>

<p><?php echo sprintf( __( 'Your job is ready to be submitted, please make sure the details are correct and then click <strong>\'Confirm\'</strong> to submit your listing %s.', APP_TD ), $status ); ?></p>

<?php appthemes_load_template( 'includes/forms/preview-job/preview-job-fields.php', compact( $job, $preview_fields ) ); ?>

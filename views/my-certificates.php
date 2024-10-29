<?php if(!$in_shortcode):?><h1><?php _e('My Certificates', 'perception')?></h1><?php endif;?>

<?php if(!sizeof($certificates)) :?>
	<p><?php _e('You have not achieved any certificates yet.', 'perception')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<table class="widefat">
		<tr><th><?php _e('Certificate', 'perception')?></th><th><?php _e('Date', 'perception')?></th><th><?php _e('Completed course(s)', 'perception')?></th></tr>
		<?php foreach($certificates as $certificate):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><a href="<?php echo site_url("?perception_view_certificate=1&id=".$certificate->id."&student_id=".$student_id."&noheader=1&my_id=".$certificate->my_id)?>" target="_blank"><?php echo $certificate->title?></a></td>
			<td><?php echo date(get_option('date_format'), strtotime($certificate->date))?></td>
			<td><?php echo $certificate->courses?></td></tr>
		<?php endforeach;?>
	</table>
</div>

<?php echo do_action('perception-my-certificates-bottom');?>
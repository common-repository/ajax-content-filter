<div class="wrap">
	<h1><?php printf(__("Students Who Earned Certificate %s", 'perception'), stripslashes($certificate->title))?></h1>
	
	<p><a href="admin.php?page=perception_certificates"><?php _e('Back to all certificates', 'perception')?></a></p>
	
	<?php if(!sizeof($users)):?>
		<p><?php _e('No student has earned this certificate yet.', 'perception')?></p>
		</div>
	<?php return true;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('User name and email', 'perception')?></th><th><?php _e('Date earned', 'perception')?></th>
		<th><?php _e('View Certificate', 'perception')?></th>		
		<th><?php _e('Remove', 'perception')?></th></tr>	
		
		<?php foreach($users as $user):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo $user->user_nicename . " (".$user->user_email . ")"?></td>
			<td><?php echo date( $dateformat, strtotime($user->date) ); ?></td>			
			<td><a href="<?php echo site_url('?perception_view_certificate=1&id='.$certificate->id.'&student_id=' . $user->student_id . '&my_id=' . $user->student_certificate_id . '&noheader=1')?>" target="_blank"><?php _e('View / print', 'perception')?></a></td>
			<td><a href="#" onclick="PSPerceptionLMSRemoveUserCertificate(<?php echo $user->student_certificate_id?>);return false;"><?php _e('Remove', 'perception')?></a></td></tr>
		<?php endforeach;?>
	</table>
</div>

<script type="text/javascript" >
function PSPerceptionLMSRemoveUserCertificate(ucID) {
	if(confirm("<?php _e('Are you sure? The user will not be able to print this certificate.', 'perception')?>")) {
		window.location = 'admin.php?page=perception_student_certificates&id=<?php echo $certificate->id?>&delete=1&student_certificate_id=' + ucID;
	}
}
</script>
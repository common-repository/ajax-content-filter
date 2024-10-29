<h1><?php _e('My Courses', 'perception')?></h1>

<?php if(!sizeof($courses)) :?>
	<p><?php _e('No courses are available at this time.', 'perception')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<?php if(!empty($message)):?>
		<p class="perception-note"><?php echo $message?></p>
	<?php endif;?>	

	<table class="widefat">
		<tr><th><?php _e('Course title &amp; description', 'perception')?></th>
		<?php if(!$simplified):?><th><?php _e('Lessons', 'perception')?></th><?php endif;?>		
		<th><?php _e('Status', 'perception')?></th></tr>
		<?php foreach($courses as $course):
			$unenroll_allowed = get_post_meta($course->post_id, 'perception_unenroll', true);
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><a href="<?php echo get_permalink($course->post_id)?>" target="_blank"><?php echo $course->post_title?></a>
			<?php if(!empty($course->post_excerpt)): echo apply_filters('perception_content', stripslashes($course->post_excerpt)); endif;?></td>
			<?php if(!$simplified):?>
			<td><?php if(empty($course->status) or $course->status == 'pending'): 
				_e('Enroll to get access to the lessons', 'perception');
				else: ?>
					<a href="admin.php?page=perception_student_lessons&course_id=<?php echo $course->post_id?>&student_id=<?php echo $user_ID?>"><?php _e('View lessons', 'perception')?></a>
				<?php endif;?></td>
			<?php endif; // end if not simplified ?>	
			<td>
			<?php if(empty($course->status)): // not enrolled
			  // Konniciwa integration: can't enroll if the content is protected
			   $can_enroll = true;
			   if(!$is_manager): $can_enroll = PSPerceptionLMSCourseModel :: konnichiwa_access($course->post_id); endif;
				if($can_enroll): echo $_course->enroll_buttons($course, $is_manager);  else: _e('No access', 'perception'); endif;
			else: // enrolled
				if($course->status == 'pending'): _e('Pending enrollment', 'perception'); endif;
				if($course->status == 'rejected'): _e('Application rejected', 'perception'); endif;
				if($course->status == 'frozen'): _e('Frozen / no access', 'perception'); endif;
				if($course->status == 'enrolled'): 
					printf(__('Enrolled on %s', 'perception'), date_i18n(get_option('date_format'), strtotime($course->enrollment_date)));
					if($unenroll_allowed):?>
						<p><a href="#" onclick="perceptionUnenrollCourse(<?php echo $course->post_id?>);return false;"><?php _e('Un-enroll from this course', 'perception');?></a></p>
					<?php endif;
					do_action('perception-course-status', $course->post_id, $user_ID); 
				endif;
				if($course->status == 'completed'): printf(__('Completed on %s', 'perception'), 
					date_i18n(get_option('date_format'), strtotime($course->completion_date))); endif;
			endif;?>			
			</td></tr>
		<?php endforeach;?>
	</table>
</div>

<script type="text/javascript" >
function perceptionUnenrollCourse(id) {
	if(confirm("<?php _e('Are you sure? This will blank out all your progress in this course', 'perception')?>")) {
		<?php if($simplified): // in shortcode?>
		window.location='<?php echo $target_url;?>&unenroll=' + id;
		<?php else:?>
		window.location='admin.php?page=perception_my_courses&unenroll=' + id;
		<?php endif;?>
	}
}
</script>
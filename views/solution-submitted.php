<h1><?php _e('Solution submitted', 'perception');?></h1>

<div class="wrap">
	<?php if(!empty($total_size_error)):?>
		<p class="error"><strong><?php printf(__('The total size of your files is %sKB. The maximum accepted size is %sKB. The files were not uploaded.', 'perception'), number_format($total_size_error), get_option('perception_homework_size_total'));?></strong></p>
	<?php endif;?>
	<?php if(!empty($file_size_errors) and count($file_size_errors)):?>
		<p class="error"><strong><?php printf(__('The following files exceeded the file size limit of %sKB and were not uploaded: %s', 'perception'), get_option('perception_homework_size_per_file'), implode(', ', $file_size_errors));?></strong></p>
	<?php endif;?>
	<?php if(!empty($file_errors) and count($file_errors)):?>
		<p class="error"><strong><?php printf(__('The following files were not within the allowed file types and were not uploaded: %s', 'perception'), implode(', ', $file_errors));?></strong></p>
	<?php endif;?>
	<?php if(!empty($file_not_uploaded_errors) and count($file_not_uploaded_errors)):?>
		<p class="error"><strong><?php printf(__('The following files could not be uploaded: %s', 'perception'), implode(', ', $file_not_uploaded_errors));?></strong></p>
	<?php endif;?>
	<p><?php _e('A manager will review your solution and will approve or reject it. If the manager add any notes, they will appear in the assignments page for this course', 'perception')?></p>
	
	<?php if($in_shortcode):
		$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => $_GET['lesson_id']);
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url;?>"><?php _e('Back to the assignments', 'perception')?></a> | <a href="<?php echo get_permalink($lesson->ID);?>"><?php printf(__('Back to lesson "%s"', 'perception'), stripslashes($lesson->post_title));?></a></p>
	<?php else:?>	
		<p><a href="admin.php?page=perception_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $user_ID?>"><?php _e('Back to assignments in', 'perception')?> "<?php echo $lesson->post_title?>"</a> 
		<?php _e('from course','perception')?> "<a href="admin.php?page=perception_student_lessons&course_id=<?php echo $course->ID?>&student_id=<?php echo $user_ID?>"><?php echo $course->post_title?></a>"
		| <a href="<?php echo get_permalink($lesson->ID);?>"><?php printf(__('View lesson "%s"', 'perception'), stripslashes($lesson->post_title));?></a></p>
	<?php endif;?>	
</div>
<h1><?php _e('Send Note', 'perception')?></h1>

<div class="wrap">
	<p><?php _e('Use this page to send a note about assignments', 'perception')?> "<strong><?php echo $homework->title?></strong>" <?php _e('for student', 'perception')?> <strong><?php echo $student->user_login?></strong>
	</p>
	<p><b><?php _e('Note that same feedback and notes are valid for the whole assignment for that student and will be shown for every submitted solution by the student for this homework.', 'perception');?></b></p>
	
	<p><?php _e('Course:', 'perception')?> <strong><?php echo $course->post_title?></strong></p>
	<p><?php _e('Lesson:', 'perception')?> <strong><?php echo $lesson->post_title?></strong></p>

	<?php	if($in_shortcode):
		$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => $_GET['lesson_id']);
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url?>"><?php printf(__('Back to assignments for "%s"', 'perception'), $lesson->post_title);?></a></p>
		<?php else:?>
		<p><a href="admin.php?page=perception_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student->ID?>"><?php _e('Back to assignments for the student on this lesson', 'perception')?></a></p>
		<?php endif; // not in shortcode?>

	<div class="perception-form">
		<form method="post">
			<p align="center"><?php echo wp_editor('', 'note')?></p>
			<p align="center"><input type="submit" name='ok' value="<?php _e('Send note', 'perception')?>"></p>	
		</form>
	</div>
</div>
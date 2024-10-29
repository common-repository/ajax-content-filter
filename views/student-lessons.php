<?php if(!$simplified):?>
	<h1><?php _e('Course Progress:', 'perception')?> <?php echo $course->post_title?></h1>
	
	<?php if(current_user_can('perception_manage')):?>
		<p><a href="admin.php?page=perception_students&course_id=<?php echo $course->ID?>"><?php _e('Back to students in this course', 'perception')?></a></p>
	<?php endif;?>
	
	<?php if(!empty($error)):?>
		<p class="perception-error"><?php echo $error;?></p>
	<?php endif;?>
<?php endif;?>	

<div class="wrap">
	<?php if(!$simplified):?><p><?php _e('Student:', 'perception')?> <strong><?php echo !empty($student->nice_name)?$student->nice_name:$student->user_login?></strong></p><?php endif;?>

	<table class="widefat perception-table">
		<thead>
		<tr><th><?php _e('Lesson', 'perception')?></th>
		<?php if(!$simplified):?>
			<th><?php _e('Assignments', 'perception')?></th>
			<?php if($use_exams):?>
				<th><?php _e('Test/Exam', 'perception')?></th>
			<?php endif;
		endif; // end if not simplified ?>	
		<th><?php _e('Status', 'perception')?></th>		
		<?php if(!empty($use_grading_system) and !empty($atts['show_grade'])):?>
			<th><?php _e('Grade', 'perception')?></th>
		<?php endif;?>
		</tr>
		</thead>
		<tbody>
		<?php foreach($lessons as $lesson):
			$class = ('alternate' == @$class) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class?>"><td><a href="<?php echo get_permalink($lesson->ID)?>"><?php echo stripslashes($lesson->post_title)?></a>
			<?php if(!empty($show_excerpts) and !empty($lesson->post_excerpt)) echo wpautop($lesson->post_excerpt);?></td>
			<?php if(!$simplified):?>
				<td><?php if(!sizeof($lesson->homeworks)): echo __('None', 'perception'); 
				else:?> <a href="admin.php?page=perception_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student_id?>"><?php echo sizeof($lesson->homeworks)?></a>
				<?php endif;?></td>
				<?php if($use_exams):?>
					<td><?php if(empty($lesson->exam->ID)): _e('None', 'perception');
					else:?>
						<a href="<?php echo get_permalink($lesson->exam->post_id)?>" target="_blank"><?php echo $lesson->exam->name?></a>
					<?php endif;?></td>
				<?php endif; // end if $use_exams
			endif; // end if not simplified?>
			<td><?php if($student->ID == $user_ID or @$multiuser_access == 'view'): echo $lesson->status;
			else: ?>
				<form method="post">
				<select name="status" onchange="this.form.submit();">
					<option value="-1"<?php if($lesson->statuscode == -1) echo ' selected'?>><?php _e('Not started', 'perception')?></option>	
					<option value="0"<?php if($lesson->statuscode == 0) echo ' selected'?>><?php _e('In progress', 'perception')?></option>			
					<option value="1"<?php if($lesson->statuscode == 1) echo ' selected'?>><?php _e('Completed', 'perception')?></option>	
				</select>
				<?php if($lesson->statuscode == 0): echo " <a href='#' onclick='perceptionInProgress(".$lesson->ID.",".$student->ID.");return false;'>".__('[todo]', 'perception').'</a>'; endif;?>
				<input type="hidden" name="lesson_id" value="<?php echo $lesson->ID?>">
				<input type="hidden" name="change_status" value="1">
				</form>
			<?php endif;?></td>
			<?php if(!empty($use_grading_system) and !empty($atts['show_grade'])):?>
				<th><?php echo !empty($lesson->grade) ? $lesson->grade : __('N/a', 'perception');?></th>
			<?php endif;?>
			</tr>
		<?php endforeach; ?>	
		</tbody>
	</table>
	
	<?php if(!$simplified) do_action('perception_course_progress_view', $student->ID, $_GET['course_id'])?>
</div>

<script type="text/javascript" >
function perceptionInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'perception')?>", 
		'<?php echo admin_url("admin-ajax.php?action=perception_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}
</script>
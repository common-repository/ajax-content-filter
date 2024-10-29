<h1><?php printf(__('Viewing Solutions for "%s"', 'perception'), stripslashes($homework->title));?></h1>

<div class="wrap">
	<p><?php _e('Lesson:', 'perception')?> <strong><?php echo stripslashes($lesson->post_title)?></strong></p>	
	<p><?php _e('Course:', 'perception')?> <strong><?php echo stripslashes($course->post_title)?></strong></p>

	<?php if(!empty($show_everyone)):?>
		<p><a href="admin.php?page=perception_homeworks&lesson_id=<?php echo $lesson->ID?>&course_id=<?php echo $course->ID?>"><?php printf(__('Back to assignments for "%s"', 'perception'), $lesson->post_title);?></a></p>
		<p><strong><?php _e("Showing everyone's solutions on this assignment.", 'perception')?></strong></p>
		<p><?php _e('Note: when one solution is approved, the assignment will be considered completed by this student and they will not be asked to submit more solutions for it.', 'perception')?></p>
	<?php else: 
		if($in_shortcode):
		$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => intval($_GET['lesson_id']));
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url?>"><?php printf(__('Back to assignments for "%s"', 'perception'), $lesson->post_title);?></a></p>
		<?php else:?>
		<p><a href="admin.php?page=perception_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student->ID?>"><?php printf(__('Back to assignments for "%s"', 'perception'), $lesson->post_title);?></a></p>
		<?php endif; // not in shortcode?>
		<?php if($user_ID != $student->ID):?>
			<p><?php _e('Showing solutions submitted by', 'perception')?> <strong><?php echo $student->user_login?></strong></p>
			<p><?php _e('Note: when one solution is approved, the assignment will be considered completed by this student and they will not be asked to submit more solutions for it.', 'perception')?></p>
		<?php endif;		
	endif; // end if showing solutions for particular student
	if(!sizeof($solutions)):
			if(empty($show_everyone)) echo "<p>".__("The student has not submitted any solutions for this assignment yet.", 'perception')."</p>";
			else echo "<p>".__("No one has submitted any solutions for this assignment yet.", 'perception')."</p>";
			echo "</div>";
			return true;
	endif;?>
	
	<table class="widefat">
	<?php foreach($solutions as $solution):
	$solution_files = PSPerceptionLMSHomeworkController :: solution_files($homework, $solution);
	$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><th><?php printf(__('Solution submitted at %s', 'perception'), date(get_option('date_format'), strtotime($solution->date_submitted)));?>
		<?php if(!empty($show_everyone)):
		 echo __('by','perception')." <a href='admin.php?page=perception_lesson_homeworks&lesson_id=".$lesson->ID."&student_id=".$solution->student_id."' target='_blank'>".$solution->user_login."</a>";
		endif;?></th>
		<th><?php _e('Status', 'perception');?></th>
		<th><?php _e('Notes / Feedback', 'perception');?></th>
		<?php if($use_grading_system):?>
			<th><?php _e('Grade', 'perception')?></th>
		<?php endif;?></tr>
		<tr class="<?php echo $class?>"><td><?php echo apply_filters('perception_content', stripslashes($solution->content));?>
		<?php if(count($solution_files)):?>
			<p><?php _e('Attachments:', 'perception');
				foreach($solution_files as $file):?> 
				<a href="<?php echo admin_url('admin.php?page=perception_download_solution&id='.$solution->id.'&file_id='.$file->id.'&noheader=1')?>"><?php echo $file->file?></a>; 
				<?php endforeach;
		endif;?></p></td>
		<td><?php if(current_user_can('perception_manage')):?>
		<form method="post">
			<select name="status" onchange="this.form.submit();">
				<option value="pending" <?php if($solution->status=='pending') echo 'selected'?>><?php _e('Pending', 'perception')?></option>
				<option value="approved" <?php if($solution->status=='approved') echo 'selected'?>><?php _e('Approved', 'perception')?></option>
				<option value="rejected" <?php if($solution->status=='rejected') echo 'selected'?>><?php _e('Rejected', 'perception')?></option>
			</select>
			<input type="hidden" name="change_status" value="1">
			<input type="hidden" name="solution_id" value="<?php echo $solution->id?>">					
		</form>
		<?php else: echo $solution->status;
		endif;?></td>
		<td><p><?php if(!sizeof($solution->notes)): _e('None yet.', 'perception');
		else:?> <a href="#" onclick="Perception.loadNotes('<?php echo $homework->id?>', '<?php echo $solution->student_id?>');return false;"><?php printf(__('%d notes', 'perception'), sizeof($solution->notes))?></a>
		<?php endif;?></p>		
		<?php if($manager_mode):
				if(!empty($in_shortcode)):
				   	$permalink = get_permalink($post->ID);
				   	$params = array('id' => $homework->id, 'add_note' => 1, 'lesson_id' => $lesson->ID, 'student_id' => $solution->student_id, 'homework_id'=>$homework->id);
						$target_url = add_query_arg( $params, $permalink );?>
				   	<p><a href="<?php echo $target_url ?>"><?php _e('Add note / feedback', 'perception')?></a></p>
			  		 <?php else:?>
			<p><a href="admin.php?page=perception_add_note&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $solution->student_id?>&homework_id=<?php echo $homework->id?>"><?php _e('Add note / feedback', 'perception')?></a></p>
			<?php endif;?>
		<?php endif;?></td>
		<?php if($use_grading_system):?>
			<td><?php if(current_user_can('perception_manage')): ?>
				<form method="post">
				<input type="hidden" name="grade_solution" value="1">		
				<input type="hidden" name="id" value="<?php echo $solution->id?>">
				<select name="grade" onchange="this.form.submit();">
					<option value="">---------</option>
					<?php foreach($grades as $grade):
					 $grade = trim($grade);?>
					 	<option value="<?php echo $grade?>" <?php if($grade == $solution->grade) echo 'selected'?>><?php echo $grade;?></option>
					<?php endforeach;?> 
				</select>
				</form>			
			<?php else: echo $solution->grade ? $solution->grade : __('Not graded', 'perception');
			endif;?></td>
		<?php endif;?>				
		</tr>
	<?php endforeach;?>
	</table>
</div>

<script type="text/javascript" >
Perception.loadNotes = function(homeworkID, studentID) {
	tb_show("<?php _e('Notes', 'perception')?>", 
		'<?php echo admin_url("admin-ajax.php?action=perception_ajax&type=load_notes")?>&homework_id=' + homeworkID + 
		'&student_id=' + studentID);
}

Perception.deleteNote = function(studentID, noteID) {
	if(!confirm("<?php _e('Are you sure? There is no undo.', 'perception');?>")) return false;
	var url = '<?php echo admin_url("admin-ajax.php");?>';
	var data = {'action' : 'perception_ajax', 'type' : 'delete_note', 'id' : noteID, 'student_id' : studentID };
	jQuery.post(url, data, function(msg) {
		var parts = msg.split('|||');
		if(parts[0] == 'ERROR') alert(parts[1]);
		else jQuery('#homeworkNote-'+ noteID).hide();
	});
}
</script>
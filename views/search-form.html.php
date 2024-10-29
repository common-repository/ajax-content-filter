<div class="perception-search">
	<form class="perception-search-form" method="get" action="<?php echo home_url();?>">
		<h3><?php _e('Search in Courses and Lessons', 'perception');?></h3>
		<p>
			<input name="s" type="search" class="search-field" placeholder="<?php _e('Search...', 'perception');?>" value="<?php echo empty($_GET['s']) ? '' : $_GET['s']?>">
			<?php _e('in', 'perception');?> <select name="perception_course_id" onchange="perceptionSearchFillLessons(this.value);">
				<option value="0"><?php _e('All courses', 'perception');?></option>
				<?php foreach($courses as $course):
					$selected = (!empty($_GET['perception_course_id']) and $_GET['perception_course_id'] == $course->ID) ? 'selected="selected"' : '';?>
					<option value="<?php echo $course->ID?>" <?php echo $selected?>><?php echo stripslashes($course->post_title);?></option>
				<?php endforeach;?>
			</select>
			
			<select name="perception_lesson_id" id="perceptionLessonSearchSelector" style="display:<?php echo (count($current_lessons) or is_user_logged_in()) ? 'inline' : 'none'; ?>">
				<option value="0"><?php _e('All lessons', 'perception');?></option>
				<?php if(count($current_lessons)): 
					foreach($current_lessons as $lesson):
						$selected = (!empty($_GET['perception_lesson_id']) and $_GET['perception_lesson_id'] == $lesson->ID) ? 'selected="selected"' : '';?>
						<option value="<?php echo $lesson->ID?>" <?php echo $selected?>><?php echo stripslashes($lesson->post_title);?></option>
				<?php endforeach;
				endif;?>
			</select>
			
			<input type="submit" class="search-submit" value="<?php _e('Search', 'perception');?>" />
		</p>
		<input type="hidden" name="perception_search" value="1">
	</form>
</div>

<script type="text/javascript" >
// courses/lessons object for the dropdown
function perceptionSearchFillLessons(courseID) {
	var lessons = new Array();
	<?php foreach($courses as $course):?>
	lessons[<?php echo $course->ID?>] = [
		<?php foreach($course->lessons as $lesson):		
		echo '['.$lesson->ID.', "'.stripslashes($lesson->post_title).'"],';
		endforeach;?>
	];
	<?php endforeach;?>
	
	if(lessons[courseID] != null) {
		if(lessons[courseID].length > 0 ) { 
			jQuery('#perceptionLessonSearchSelector').show();
			var html = '<option value="0"><?php _e('All lessons', 'perception');?></option>';
			for(i=0; i < lessons[courseID].length; i++) {
				html += '<option value="' + lessons[courseID][i][0] + '">' + lessons[courseID][i][1]+ '</option>' + "\n";
			}
		}
		else jQuery('#perceptionLessonSearchSelector').hide();
		
		jQuery('#perceptionLessonSearchSelector').html(html);
	}
	else {
		jQuery('#perceptionLessonSearchSelector').show();
		jQuery('#perceptionLessonSearchSelector').html('<option value="0"><?php _e('All lessons', 'perception');?></option>');
	}
}
</script>
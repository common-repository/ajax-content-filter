<h4><?php _e('Assign to Course:', 'perception')?></h4>
 
<?php if(!sizeof($courses)) echo "<p>".__('No courses have been created yet!', 'perception')."</p>";?> 
<p><label><?php _e('Select course:', 'perception')?></label>
<select name="perception_course" <?php if($use_modules):?>onchange="perceptionChangeLessonCourse(this.value);"<?php endif;?>>
<?php foreach($courses as $course):?>
	<option value="<?php echo $course->ID?>"<?php if($course->ID == $course_id) echo 'selected'?>><?php echo $course->post_title?></option>
<?php endforeach;?>
</select>
<?php if(!empty($use_modules)):?>
		 <span id="perceptionLessonModule">
		<?php if(count($modules)):?>
			&nbsp;
			<?php _e('Select module:', 'perception');?>
			<select name="perception_module">
				<option value="0"><?php _e('- No module -', 'perception');?></option>
				<?php foreach($modules as $module):?>
					<option value="<?php echo $module->ID?>" <?php if(!empty($module_id) and $module->ID == $module_id) echo 'selected'?>><?php echo stripslashes($module->post_title);?></option>
				<?php endforeach;?>
			</select>
		<?php endif; // end if there are module?>
	</span>
<?php endif;?>
</p>
		
<h4><?php _e('Lesson Access', 'perception')?></h4>

<?php if(!sizeof($other_lessons)):
	if(empty($post->post_title)):?>
		<p><?php _e('You will be able to set lesson access after you create the lesson.', 'perception')?></p>
	<?php else:?>
		<p><?php _e('There are no other lessons in this course. So this lesson will be accessible to anyone who enrolled the course.', 'perception')?></p>
	<?php endif;
   else: 
echo '<p>'.__('This lesson will be accessible only after the following lessons are completed:','perception').'</p>'; 
foreach($other_lessons as $lesson):?>
	<p><input type="checkbox" name="perception_access[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $lesson_access)) echo "checked"?>> <?php echo $lesson->post_title?></p>
<?php endforeach;
endif;?>

<h4><?php _e('Lesson Completeness', 'perception')?></h4>

<p><?php _e('The minimum requirement for a lesson to be completed is to be visited by the student. However you can add some extra requirements here:', 'perception')?></p>

<p><input type="checkbox" name="perception_completion[]" value="admin_approval" <?php if(in_array('admin_approval', $lesson_completion)) echo 'checked'?>> <?php _e('Lesson completion will be manually verified and approved by the admin for every student.', 'perception')?></p>

<?php if(!empty($homeworks) and sizeof($homeworks)):?>
<p><b><?php _e('The following assignments/homework must be completed:', 'perception')?></b></p>
<ul>
	<?php foreach($homeworks as $homework):?>
		<li><input type="checkbox" name="perception_required_homeworks[]" value="<?php echo $homework->id?>"<?php if(in_array($homework->id, $required_homeworks)) echo 'checked'?>> <?php echo stripslashes($homework->title)?></li>
	<?php endforeach;?>
</ul>
<?php endif;?>

<?php if($use_exams and sizeof($exams)):?>	
	<p><b><?php _e('The following quiz must be completed (will take effect only if the quiz is published):', 'perception')?></b></p>
	<p><select name="perception_required_exam" onchange="perceptionLoadGrades(this.value);">
	<option value=""><?php _e('- No quiz required -', 'perception')?></option>
	<?php foreach($exams as $exam):?>
		<option value="<?php echo $exam->ID?>" <?php if($exam->ID == $required_exam) echo 'selected'?>><?php echo $exam->name?></option>
	<?php endforeach;?>
	</select> 
	<span id='perceptionGradeRequirement' style='display:<?php echo $required_exam?'inline':'none'?>'>
	<?php _e('with any of the following grade(s) achieved:', 'perception')?>
		<span id="perceptionGradeSelection">
			<?php if($required_exam):?>
				<select name="perception_required_grade[]" size="4" multiple="true">
					<option value=""><?php _e('- Any grade -')?></option>
					<?php foreach($required_grades as $grade):?>
						<option value="<?php echo $grade->ID?>" <?php if((!is_numeric($grade->gtitle) and in_array($grade->gtitle, $required_grade)) or in_array($grade->ID, $required_grade)) echo 'selected'?>><?php echo $grade->gtitle?></option>
					<?php endforeach;?>
				</select>
			<?php endif;?>
		</span>
	</span>
	<?php if($use_grading_system):?>
		<br><input type="checkbox" name="perception_watu_transfer_grade" value="1" <?php if(get_post_meta($post->ID, 'perception_watu_transfer_grade', true) == 1) echo 'checked'?>> <?php _e('The grade from the quiz automatically becomes grade for the lesson (only if the grade title exactly matches one of your grades in Perception)', 'perception');?>
	<?php endif;?></p>	
	
	<script type="text/javascript" >
	function perceptionLoadGrades(examID) {
		var exams = { <?php foreach($exams as $exam): echo $exam->ID.' : { ';
				foreach($exam->grades as $grade): echo $grade->ID.' : "'.str_replace('"','', $grade->gtitle).'", '; endforeach; 
			echo '}, '; endforeach;?>	
		}; // end exams object

		// construct grades dropdown
		if(!examID) {
			jQuery('#perceptionGradeRequirement').hide();
			return false;
		}	
		
		html = '<select name="perception_required_grade[]" size="4" multiple="true"> <option value=""><?php _e('- Any grade -')?></option>';
		if(!exams[examID]) return false;		
		exam = exams[examID];
		
		jQuery.each(exam, function(index, value){
			html += '<option value="'+index+'">' + value + '</option>';		
		});		
		
		jQuery('#perceptionGradeSelection').html(html);
		jQuery('#perceptionGradeRequirement').show();
	}
	</script>
<?php else: printf('<p style="font-weight:bold;">'.__('If you install %s or %s you can also require certain tests and quizzes to be completed.', 'perception'), 
	"<a href='http://wordpress.org/extend/plugins/watu/' target='_blank'>Watu</a>", "<a href='http://calendarscripts.info/watupro/' target='_blank'>WatuPRO</a>").'</p>';
endif;?>

<?php do_action('perception-lesson-requirements', $post);?>

<?php if(!empty($use_points_system)):?>
	<p><?php _e('Reward', 'perception')?> <input type="text" size="4" name="perception_award_points" value="<?php echo $award_points?>"> <?php _e('points for completing this lesson.', 'perception')?></p>
<?php endif;?>


<h3><?php _e('Shortcodes', 'perception')?></h3>

<p><?php _e('You can use the shortcode', 'perception')?> <input type="text" value="[perception-todo]" readonly="readonly" onclick="this.select();"> <?php _e('inside the lesson content to display what the student needs to do to complete the lesson.', 'perception')?>
<br> <?php printf(__('The same shortcode to use outside of the lesson page is %s', 'perception'), '<b>[perception-todo post_type="perception_lesson" post_id="'.$post->ID.'"]</b>');?></p>

<p><?php _e('You can use the shortcode', 'perception')?> <input type="text" value="[perception-lesson-status]" readonly="readonly" onclick="this.select();"> <?php _e('inside the lesson content to display its current status.', 'perception')?>
<br> <?php printf(__('The same shortcode to use outside of the lesson page is %s', 'perception'), '<b>[perception-lesson-status lesson_id="'.$post->ID.'"]</b>');?></p>

<p><?php _e('The shortcode', 'perception')?> <input type="text" value="[perception-mark]" readonly="readonly" onclick="this.select();"> <?php _e('will display a "Mark Completed" button so the student can mark the lesson completed themselves. <b>If such button is included in the lesson it will not be marked as completed until the student does it!</b> The button will appear <b>only after the "Lesson Completeness" requirements are satisfied.</b>', 'perception')?></p>

<p><?php _e('The shortcode', 'perception')?> <input type="text" value="[perception-course-link]" readonly="readonly" onclick="this.select();"> <?php _e('will display a link to the course that this lesson belongs to. You can pass attribute "text" to set a clickable text for the link. Otherwise the course title will be used.', 'perception')?></p>


<h4><?php _e('Use the Excerpt Box Below:', 'perception');?></h4>

<p><?php _e('If you enter some content in the Excerpt box on this page, the content will be shown to users who cannot access the lesson  for some reason: non-logged in students, students who did not enroll the course, students with unsatisfied lesson access requirements etc. The content will be shown instead of or before the default text that is shown in these cases. You can also show these excerpts by passing the <b>show_excerpts=1</b> attribute to the <b>perception-course-lessons</b> shortcode.', 'perception');?></p>

<?php if($use_modules):?>
<script type="text/javascript" >
function perceptionChangeLessonCourse(val) {
	jQuery('#perceptionLessonModule').html("<?php _e('Loading modules...', 'perception')?>");
	data = {'action': 'perception_ajax', 'type' : 'load_modules', 'course_id' : val, 'lesson_id' : <?php echo $post->ID?>};
	jQuery.post('<?php echo admin_url('admin-ajax.php')?>', data, function(msg) {
		jQuery('#perceptionLessonModule').html(msg);
	});
}
</script>
<?php endif;?>
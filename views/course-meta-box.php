<h4><?php _e('Enrollment mode:', 'perception')?></h4>

<p><b><?php _e('You can use the shortcode', 'perception')?></b> <input type="text" value="[perception-enroll]" onclick="this.select();" readonly size="14"> <b><?php _e('to display enrollment button (or enrolled/pending message) in the course content', 'perception')?></b></p>

<p><b><?php _e('The shortcode', 'perception')?></b> <input type="text" value="[perception-enroll course_id=<?php echo $post->ID?>]" onclick="this.select();" readonly size="24"> <b><?php _e('needs to be used if you want to display enroll button for this course on a different page. See the internal Help page for more parameters in this shortcode.', 'perception')?></b></p>
 
<p><input type="radio" name="perception_enroll_mode" value="free" <?php if(empty($enroll_mode) or $enroll_mode == 'free') echo 'checked'?>> <?php _e('Logged in users can enroll this course themselves.', 'perception')?></p>

<p><input type="radio" name="perception_enroll_mode" value="manual" <?php if(!empty($enroll_mode) and $enroll_mode == 'manual') echo 'checked'?>> <?php _e('Admin manually approves/enrolls students in courses', 'perception')?></p>

<p><input type="checkbox" id="perceptionUnEnrollChk" name="perception_unenroll" value="1" <?php if($unenroll_allowed) echo 'checked'?>> <?php _e('Allow students to unenroll from this course (this will cleanup any stats)', 'perception');?></p>

<?php if(!empty($currency)):?>
	<p><?php _e('Students need to pay a fee of', 'perception')?> <?php echo $currency?> <input type="text" size="6" name="perception_fee" value="<?php echo $fee?>"> <?php _e('to enroll this course. (Leave it 0 for no fee.)', 'perception')?></p>
<?php else:?>
	<p><?php printf(__('You can charge students for course enrollments. To do this you must first select currency in the "Payment Settings" section <a href="%s" target="_blank">here</a>.', 'perception'), 'admin.php?page=perception_options')?></p>	
<?php endif;?>

<p><input type="checkbox" name="perception_register_enroll" value="1" <?php if(!empty($register_enroll)) echo 'checked'?>> <?php printf(__('Automatically enroll in this course everyone who registers in the site with an <a href="%s" target="_blank">enabled user role</a>. Selecting this will ignore any course access prerequisites, payment requirements and any other enrollment restrictions.', 'perception'), 'admin.php?page=perception_options');?></p>

<h4><?php _e('Course Access / Pre-requisites', 'perception')?></h4>

<?php if(!sizeof($other_courses)):?>
	<p><?php _e('There are no other courses so every student can enroll in this course.', 'perception')?></p>
<?php else: 
echo '<p>'.__('This course will be accessible only after the following courses are completed:','perception').'</p>'; 
foreach($other_courses as $course):?>
	<p><input type="checkbox" name="perception_access[]" value="<?php echo $course->ID?>" <?php if(in_array($course->ID, $course_access)) echo "checked"?>> <?php echo $course->post_title?></p>
<?php endforeach;
endif;?>

<h4><?php _e('Restrict by role:', 'perception');?></h4>

<p><input type="checkbox" name="perception_require_roles" value="1" <?php if(!empty($require_roles)) echo 'checked'?> onclick="this.checked ? jQuery('#perceptionRequiredRoles').show() : jQuery('#perceptionRequiredRoles').hide();"> <?php _e('Require specific user roles to join this course', 'perception');?></p>

<div id="perceptionRequiredRoles" style='display:<?php echo empty($require_roles) ? 'none' : 'block';?>'>
	<?php foreach($roles as $key => $role):?>
			<span style="white-space:nowrap;"><input type="checkbox" name="perception_required_roles[]" value="<?php echo $key?>" <?php if(is_array($required_roles) and in_array($key, $required_roles)) echo 'checked'?>> <?php echo $role['name']?> &nbsp;</span>
	<?php endforeach;?>
</div>

<h4><?php _e('Course completeness', 'perception')?></h4>

<?php if(!count($lessons)):?>
	<p><?php _e('This course has no lessons assigned so it can never be completed. Please create and assign some lessons to this course.', 'perception')?></p>
<?php else:?>
	<p><?php _e('The following lessons must be completed in order to complete this course. Please select at least one.', 'perception')?></p>
	<ul>
		<?php foreach($lessons as $lesson):?>
			<li><input type="checkbox" name="perception_required_lessons[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $required_lessons)) echo 'checked'?>> <?php echo $lesson->post_title?></li>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(!empty($use_points_system)):?>
	<p><?php _e('Reward', 'perception')?> <input type="text" size="4" name="perception_award_points" value="<?php echo $award_points?>"> <?php _e('points for completing this course.', 'perception')?></p>
<?php endif;?>

<?php if(!empty($use_grading_system)):?>
	<p><input type="checkbox" value="1" name="perception_auto_grade" <?php if(!empty($auto_grade)) echo 'checked'?>> <?php printf(__('Automatically grade this course based on its lesson grades (<a href="%s" target="_blank">learn how this works</a>)', 'perception'), 'http://demo.perception-lms.org/grading/#courses');?></p>
<?php endif;?>

<?php if(!empty($bp_groups) and count($bp_groups)):?>
	<h4><?php _e('BuddyPress Integration', 'perception')?></h4>
	
	<p><?php _e('When someone enrolls this course join them in the following BuddyPress group:', 'perception');?> <select name="perception_bp_enroll_group" id="perceptionBPEnrollGroup">
		<option value="0"><?php _e('- No group -', 'perception');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_enroll_group == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
	</select>
	<?php do_action('perception-bp-tie-activity', $post, $bp_enroll_group);?>
	<?php _e('and remove them from the following group:', 'perception');?>
		<select name="perception_bp_enroll_group_remove">
		<option value="0"><?php _e('- No group -', 'perception');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_enroll_group_remove == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
		</select>
	</p>
	<p><?php _e('When someone completes this course join them in the following BuddyPress group:', 'perception');?> <select name="perception_bp_complete_group">
		<option value="0"><?php _e('- No group -', 'perception');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_complete_group == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
	</select>
	<?php _e('and remove them from the following group:', 'perception');?>
		<select name="perception_bp_complete_group_remove">
		<option value="0"><?php _e('- No group -', 'perception');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_complete_group_remove == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
		</select>	
	</p>
<?php endif;?>


<?php do_action('perception-course-meta-box', $post);?>

<h4><?php _e('Shortcodes', 'perception')?></h4>

<p><?php _e('You can use the shortcode', 'perception')?> <b>[perception-todo]</b> <?php _e('inside the course content to display what the student needs to do to complete the course.', 'perception')?> 
<br> <?php printf(__('The same shortcode to use outside of the course page is %s', 'perception'), '<b>[perception-todo post_type="perception_course" post_id="'.$post->ID.'"]</b>');?></p>
<p><?php _e('The shortcode', 'perception')?> <b>[perception-course-lessons]</b> <?php _e('will display the lessons in the course.','perception');?> <?php _e('It allows more advanced configurations explained on the ', 'perception');?> <a href="admin.php?page=perception_help"><?php _e('help page.', 'perception')?></a><br>
<?php printf(__('The shortcode <b>%s</b> on the other hand will display just the number of lessons in the course.', 'perception'), '[perception-num-lessons course_id="'.$post->ID.'"]');?></p>

<?php if(get_option('perception_use_modules') == 1):?>
<p><?php printf(__('Similar to the above, the shortcode <b>%s</b> will display the modules in this course.', 'perception'), '[perception-course-modules]');?></p>
<?php endif;?>

<p><?php printf(__('The shortcode <b>%s</b> can be used to display conditional content to logged in or non-logged in users like this: %s. To use it outside of the course page pass also the attribute <b>%s</b>', 'perception'), '[perception-condition]', '[perception-condition is_enrolled=1]'.__('Content enrolled users', 'perception').'[/perception-condition] [perception-condition is_enrolled=0]'.__('Content for not enrolled users', 'perception').'[/perception-condition]', 'course_id='.$post->ID);?> </p>
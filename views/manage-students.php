<div class="wrap">
	<h1><?php _e('Student Enrollments', 'perception')?></h1>
	
	<div class="postbox-container">
	<?php if(!sizeof($courses)):?>
	<p><?php _e('Nothing to do here as you have not created any courses yet!')?></p>
	<?php return true;
	endif;?>
	
	<?php if(!empty($error)):?>
		<div class="perception-error"><?php echo $error?></div>
	<?php endif;?>
	
	<form method="get">
		<input type="hidden" name="page" value="perception_students">
		<input type="hidden" name="offset" value="<?php echo $offset?>">
		<input type="hidden" name="ob" value="<?php echo $ob?>">
		<input type="hidden" name="dir" value="<?php echo $dir?>">
		<div class="wp-admin perception-form">
			<p><label><?php _e('Select course:', 'perception')?></label>
			<select name='course_id' onchange="this.form.submit();">
			<option value=""></option>
			<?php foreach($courses as $course):?>
				<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $course->ID == $_GET['course_id']) echo 'selected'?>><?php echo $course->post_title?></option>
			<?php endforeach;?>
			</select></p>
			<?php if(!empty($_GET['course_id'])):?>
				<p><b><?php _e('Enroll student in the course:', 'perception')?></b>
				 <input type="text" name="email" size="30" placeholder="<?php _e('Enter email or user login', 'perception')?>"> 
				 <b><?php _e('Tags (optional):', 'perception');?></b>
				 <input type="text" name="tags" size="20" placeholder="<?php _e('Separate with comma: tag1, tag 2...', 'perception');?>">
				<input type="submit" name="enroll" value="<?php _e('Enroll', 'perception')?>">
				<?php if(PSPerceptionLMSMultiUser :: check_access('mass_enroll_access', true) == 'all'):?>
				&nbsp; <a href="admin.php?page=perception_mass_enroll&course_id=<?php echo $_GET['course_id']?>"><?php _e('[Mass enroll students]', 'perception');?></a>
				<?php endif;?>				
				</p>
			<?php endif;?>
		</div>
	
	
	<p><?php _e('Filter by student/course status:', 'perception')?> <select name="status">
		<option value="" <?php if(empty($_GET['status'])) echo 'selected'?>><?php _e('Any status', 'perception')?></option>
		<option value="pending" <?php if(!empty($_GET['status']) and $_GET['status']=='pending') echo 'selected'?>><?php _e('Pending', 'perception')?></option>
		<option value="enrolled" <?php if(!empty($_GET['status']) and $_GET['status']=='enrolled') echo 'selected'?>><?php _e('Enrolled', 'perception')?></option>
		<option value="rejected" <?php if(!empty($_GET['status']) and $_GET['status']=='rejected') echo 'selected'?>><?php _e('Rejected', 'perception')?></option>
		<option value="completed" <?php if(!empty($_GET['status']) and $_GET['status']=='completed') echo 'selected'?>><?php _e('Completed', 'perception')?></option>
	</select>
   <?php _e('Filter by user login:', 'perception');?> <input type="text" name="user_login" value="<?php echo esc_attr(@$_GET['user_login'])?>"><br />
   <?php _e('Filter by email:', 'perception');?> <input type="text" name="user_email" value="<?php echo esc_attr(@$_GET['user_email'])?>">
   <?php _e('Filter by tag:', 'perception');?> <input type="text" name="filter_tags" value="<?php echo esc_attr(@$_GET['filter_tags'])?>">
   <input type="submit" name="filter" value="<?php _e('Filter students', 'perception');?>">	
	</p>
	
	<?php do_action('perception-show-students-filter');?>
	<p><?php _e('Per page:', 'perception');?> <select name="page_limit" onchange="this.form.submit()">
		<option value="10" <?php selected($page_limit, 10)?>>10</option>
		<option value="20" <?php selected($page_limit, 20)?>>20</option>
		<option value="50" <?php selected($page_limit, 50)?>>50</option>
		<option value="100" <?php selected($page_limit, 100)?>>100</option>
		<option value="200" <?php selected($page_limit, 200)?>>200</option>
		<option value="500" <?php selected($page_limit, 500)?>>500</option>
	</select></p>
	</form>
	
	<?php if(!empty($_GET['course_id'])):?>
		<?php if(!count($students)):?>
		<p><?php _e('There are no students enrolled in this course yet.', 'perception')?></p>
		<?php return false;
		endif;?>
		
		<p><?php _e('The below table shows all students enrolled in this course allong with the status for every lesson in it', 'perception')?></p>
		<p><a href="<?php echo basename($_SERVER['REQUEST_URI']);?>&export=1&noheader=1"><?php echo _e('Export students table', 'perception');?></a> <?php _e('(will export a comma delimited CSV file)', 'perception');?></p>
		
		
		
		<form method="post" action="admin.php?page=perception_students&course_id=<?php echo $_GET['course_id']?>">
		<table class="widefat">
			<tr>
				<?php if($multiuser_access != 'view'):?>
					<th><input type="checkbox" onclick="perceptionSelectAll(this.checked);"></th>
				<?php endif;?>
				<th><a href="admin.php?page=perception_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo esc_attr($_GET['status'])?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=display_name&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('Student', 'perception')?></a></th>
				<?php do_action('perception_manage_students_extra_th');?>
				<?php foreach($lessons as $lesson):?>
					<th><?php echo stripslashes($lesson->post_title);?></th>					
				<?php endforeach;?>		
				<th><?php _e('Status in course', 'perception')?></th>
				<?php	if($use_grading_system):?>
					<th><?php _e('Final grade', 'perception');?></th>
				<?php endif;?>
			</tr>	
			<?php foreach($students as $student):
				// this page linked in the first cell will be the same for student - when student clicks on enrolled course, 
				// they'll see the same table as the admin will see here
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
				<?php if($multiuser_access != 'view'):?>
					<td><input type="checkbox" name="student_ids[]" value="<?php echo $student->ID?>" class="perception_chk" onclick="perceptionShowHideMassButton();"></td>
				<?php endif;?>
				<td><a href="admin.php?page=perception_student_lessons&course_id=<?php echo intval($_GET['course_id'])?>&student_id=<?php echo $student->ID?>"><?php echo $student->user_login;
				echo '</a>';
				if($student->user_login != $student->display_name) echo "<br>" . $student->display_name;
				echo '<br>' . $student->user_email;?><br>
				<a href="#" onclick="jQuery('#studentEditTags<?php echo $student->scid?>').toggle();jQuery('#studentTags<?php echo $student->scid?>').toggle();return false;"><?php _e('Tags:', 'perception')?></a>  
				<?php echo '<span id="studentTags'.$student->scid.'"><i>'.str_replace(',', ', ', ($student->tags ? $student->tags : __('None', 'perception'))).'</i></span>';?>
				<div id="studentEditTags<?php echo $student->scid?>" style="display:none;"><input type="text" id="studentTagsFld<?php echo $student->scid?>" value="<?php echo str_replace(',', ', ', $student->tags);?>"><input type="button" value="<?php _e('Save', 'perception');?>" onclick="perceptionSaveTags(<?php echo $student->scid?>)"></div></td>
				<?php do_action('perception_manage_students_extra_td', $student);?>
				<?php foreach($lessons as $lesson):?>
					<td><?php if(in_array($lesson->ID, $student->completed_lessons)): _e('Completed', 'perception');
					elseif(in_array($lesson->ID, $student->incomplete_lessons)): echo "<a href='#' onclick='perceptionInProgress(".$lesson->ID.", ".$student->ID."); return false;'>".__('In progress', 'perception')."</a>";
					else: _e('Not started', 'perception'); endif;?>
					<?php	if($use_grading_system and !empty($student->relations[$lesson->ID]->grade)):?><br><?php printf(__('Grade: %s', 'perception'), $student->relations[$lesson->ID]->grade);?><?php endif;?></td>
				<?php endforeach;?>		
				<td><?php switch($student->perception_status):
					case 'pending': _e('Pending', 'perception');
						echo "<br>".sprintf(__('Since %s', 'watupro'), date_i18n($dateformat, strtotime($student->enrollment_date))).'<br>';  
					break;
					case 'enrolled': 
						_e('Enrolled', 'perception');
						echo "<br>".sprintf(__('Since %s', 'watupro'), date_i18n($dateformat, strtotime($student->enrollment_date))).'<br>';
					break;
					case 'rejected': _e('Rejected', 'perception'); break;
					case 'completed': 
						_e('Completed', 'perception');
						echo "<br>".sprintf(__('On %s', 'watupro'), date_i18n($dateformat, strtotime($student->completion_date))).'<br>';
					break;
					case 'frozen': _e('Frozen', 'perception'); break;
				endswitch;
				if($multiuser_access != 'view' and $student->perception_status=='pending'):?>
					(<a href="#" onclick="perceptionConfirmStatus('enrolled',<?php echo $student->ID?>);return false;"><?php _e('approve', 'perception')?></a> | <a href="#" onclick="perceptionConfirmStatus('rejected',<?php echo $student->ID?>);return false;"><?php _e('reject', 'perception')?></a>)
				<?php endif;
				if($multiuser_access != 'view' and ($student->perception_status == 'completed' or $student->perception_status == 'rejected' 
					or $student->perception_status == 'enrolled' or $student->perception_status == 'frozen')):?>
				(<a href="#" onclick="perceptionConfirmCleanup('<?php echo $student->ID?>');return false;"><?php _e('Cleanup', 'perception')?></a>)
				<?php endif;?></td>
				<td><?php echo empty($student->grade) ? __('n/a', 'perception') : $student->grade;?></td></tr>
			<?php endforeach;?>
		</table>
		
		<p align="center" id="perceptionMassBtn" style="display:none;"><input type="button" value="<?php _e('Mass cleanup selected students', 'perception');?>" onclick="perceptionMassCleanup(this.form);">
			<?php if(!empty($any_pending)):?>
				<input type="button" value="<?php _e('Mass approve', 'perception');?>" onclick="perceptionMassProcess(this.form, true);">
				<input type="button" value="<?php _e('Mass reject', 'perception');?>" onclick="perceptionMassProcess(this.form, false);">
				<input type="hidden" name="mass_approve" value="0">
				<input type="hidden" name="mass_reject" value="0">
			<?php endif;?>		
		</p>
		<input type="hidden" name="mass_cleanup" value="0">

		<?php wp_nonce_field('perception_manage_students');?>
		</form>
		
		<p align="center"><?php if($offset > 0):?>
			<a href="admin.php?page=perception_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo esc_attr(@$_GET['status'])?>&offset=<?php echo $offset - $page_limit?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('[previous page]', 'perception')?></a>
		<?php endif;?> 
		<?php if($count > ($page_limit + $offset)):?>
			<a href="admin.php?page=perception_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo esc_attr(@$_GET['status'])?>&offset=<?php echo $offset + $page_limit?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('[next page]', 'perception')?></a>
		<?php endif;?>	
		</p>
	<?php endif;?>
	</div>
</div>
<script type="text/javascript" >
function perceptionConfirmStatus(status, id) {	
	if(!confirm("<?php _e('Are you sure?','perception')?>")) return false;
	
	window.location="admin.php?page=perception_students&course_id=<?php echo intval(@$_GET['course_id'])?>&change_status=1&status="+status	
		+ "&student_id="+id;	
}

function perceptionInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'perception')?>", 
		'<?php echo admin_url("admin-ajax.php?action=perception_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}

function perceptionConfirmCleanup(studentID) {
	if(confirm("<?php _e('Are you sure to cleanup this record? It will be removed from the system and history and the user will be able to enroll or request enrollment again', 'perception')?>")) {
		window.location = 'admin.php?page=perception_students&course_id=<?php echo intval(@$_GET["course_id"])?>&status=<?php echo sanitize_text_field(@$_GET["status"])?>&cleanup=1&student_id='+studentID;
	}
}

function perceptionSelectAll(status) {
	if(status) jQuery('.perception_chk').attr('checked', true);
	else jQuery('.perception_chk').removeAttr('checked');
	
	perceptionShowHideMassButton();
}

function perceptionShowHideMassButton() {	
	var anyChecked = false;
	jQuery('.perception_chk').each(function(index){
		if(jQuery(this).is(':checked')) anyChecked = true;
	});
	
	if(anyChecked) jQuery('#perceptionMassBtn').show();
	else jQuery('#perceptionMassBtn').hide();
}

function perceptionMassCleanup(frm) {
	if(confirm("<?php _e('Are you sure?', 'perception');?>")) {
		frm.mass_cleanup.value = 1;
		frm.submit();
	}
}

// mass approve or reject
function perceptionMassProcess(frm, approve) {
	if(confirm("<?php _e('Are you sure?', 'perception');?>")) {
		if(approve) frm.mass_approve.value = 1;
		else frm.mass_reject.value = 1;
		frm.submit();
	}
}

// save tags
function perceptionSaveTags(id) {
	// get tags
	var tags = jQuery('#studentTagsFld' + id).val();
	
	jQuery('#studentTags' + id).html(tags);
	jQuery('#studentEditTags' + id).toggle();
	jQuery('#studentTags' + id).toggle();

	var url = "<?php echo admin_url("admin-ajax.php");?>";
	var data = {'action': 'perception_ajax', 'type': 'set_student_tags', 'tags' : tags, 'student_course_id': id};
	
	jQuery.post(url, data);
}
</script>
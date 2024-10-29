<h1><?php _e('Manage Assignment in', 'perception')?> <?php echo $this_course->post_title.' / '.$this_lesson->post_title?></h1>

<form method="post" onsubmit="return perceptionValidateForm(this);">
	<div class="postbox perception-form perception-box">
		<p><label><?php _e('Title:', 'perception')?></label> <input type="text" name="title" value="<?php echo stripslashes(@$homework->title)?>" size='80'></p>
		<p><label><?php _e('Description/Requirements:', 'perception')?></label>
		<?php echo wp_editor(stripslashes(@$homework->description), 'description');?></p>
		<p><input type="checkbox" name="accept_files" value="1" <?php if(!empty($homework->accept_files)) echo 'checked'?>> <?php _e('Accept file upload as solution', 'perception')?></p>
		
		<?php if(get_option('perception_use_points_system')):?>
			<p><?php _e('Reward', 'perception')?> <input type="text" size="4" name="award_points" value="<?php echo @$homework->award_points?>"> <?php _e('points for successfully completing this assignment.', 'perception')?></p>
		<?php endif;?>
		
		<p><input type="checkbox" name="limit_by_date" value="1" <?php if(!empty($homework->limit_by_date)) echo 'checked'?> onclick="this.checked ? jQuery('#limitByDate').show() : jQuery('#limitByDate').hide();"> <?php _e('Solutions will be accepted only within date interval:', 'perception');?>
			<span id="limitByDate" style='display:<?php echo empty($homework->limit_by_date) ? 'none' : 'inline';?>'>
			<?php _e('From:', 'perception')?> <input type="text" name="accept_date_from" value="<?php echo empty($homework->accept_date_from) ? date('Y-m-d') : $homework->accept_date_from?>" class="perceptionDatePicker">
			<?php _e('To:', 'perception')?> <input type="text" name="accept_date_to" value="<?php echo empty($homework->accept_date_to) ? date('Y-m-d') : $homework->accept_date_to?>" class="perceptionDatePicker">
		</p>
		
		<?php if(get_option('perception_use_grading_system') == 'on'):?>
		   <p><input type="checkbox" name="auto_grade_lesson"value="1" <?php if(!empty($homework->auto_grade_lesson)) echo 'checked'?>> <?php _e('The grade of this homework automatically becomes lesson grade.', 'perception');?></p>
      <?php endif;?>
		
		<?php do_action('perception_homework_form', @$homework);?>
		<p>
			<?php if(empty($homework->id)):?>
				<input type="submit" value="<?php _e('Create Assignment', 'perception')?>" name="ok">
			<?php else:?>
				<input type="submit" value="<?php _e('Save Assignment', 'perception')?>" name="ok">
				<input type="button" value="<?php _e('Delete Assignment', 'perception')?>" onclick="perceptionConfirmDelete(this.form, '<?php _e('Are you sure?','perception')?>');">
				<input type="hidden" name="del" value="0">
			<?php endif;?>		
		</p>
	</div>
	<?php wp_nonce_field('perception_homework');?>
</form>

<script type="text/javascript" >
jQuery(document).ready(function() {
    jQuery('.perceptionDatePicker').datepicker({
		dateFormat : 'yy-mm-dd'    
    });
});	

function perceptionValidateForm(frm) {
	if(frm.title.value == '') {
		alert("<?php _e('Please enter at least title for the assignment', 'perception')?>");
		frm.title.focus();
		return false;
	}
	
	if( (new Date(frm.accept_date_from.value).getTime() > new Date(frm.accept_date_to.value).getTime())) {
		alert("<?php _e('The end date is before the start date.', 'perception');?>");
		frm.accept_date_to.focus();
		return false;
	}
	
	return true;
}
</script>
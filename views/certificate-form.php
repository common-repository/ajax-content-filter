<h1><?php _e("Add/Edit Certificate", 'perception')?></h1>

<div class="wrap">
	<form class="perception-form" onsubmit="return perceptionValidateForm(this);" method="post">
		<p><label><?php _e('Certificate Title:', 'perception')?></label> <input type="text" name="title" value="<?php if(!empty($certificate->title)) echo $certificate->title?>" size="100"></p>
		
		<p><label><?php _e('Certificate Contents:', 'perception')?></label> 
		<?php if(get_option('perception_certificates_no_rtf')):?>
		<textarea name="content" cols="100" rows="20"><?php echo stripslashes(@$certificate->content)?></textarea>		
		<?php else:
		 wp_editor(stripslashes(@$certificate->content), 'content');
		 endif;?></p>
		
		<p><?php _e('You can use the following variables in the certificate contents:', 'perception')?></p>
		
		<p><strong>{{name}}</strong> <?php _e('- The user full name or login name (whatever is available)', 'perception')?><br>
		<strong>{{courses}}</strong> <?php _e('- The names of the courses which were completed to acquire this certificate', 'perception')?><br>
		<strong>{{courses-extended}}</strong> <?php _e('- The names and descriptions of the courses which were completed to acquire this certificate. The post "excerpt" will be used as course description.', 'perception')?><br>
		<strong>{{date}}</strong> <?php _e('- Date when the certificate was acquired', 'perception')?><br>
		<strong>{{expiration-date}}</strong> <?php _e('- Expiration date if the certificate expires', 'perception')?><br>
		<strong>{{id}}</strong> <?php _e('- Unique ID of this certificate', 'perception')?><br></p>
		
		
		<p><strong><?php _e('Assign this certificate upon completing all of the following courses:', 'perception')?></strong>
		
		<?php if(!sizeof($courses)): _e('You have not created any courses yet!', 'perception');
		else:?>
			<ul>
				<?php foreach($courses as $course):?>
					<li><input type="checkbox" name="course_ids[]" value="<?php echo $course->ID?>" <?php if(!empty($certificate->id) and strstr($certificate->course_ids, '|'.$course->ID.'|')) echo "checked"?>> <?php echo $course->post_title?></li>
				<?php endforeach;?>
			</ul>
		<?php endif;?>
		
		<?php do_action('perception-certificate-pdf-settings', @$certificate->id);?>
		
		<p>&nbsp;</p>
		<h3><?php _e('Certificate Expiration', 'perception')?></h3>
		
		<p><input type="checkbox" name="has_expiration" value="1" <?php if(!empty($certificate->has_expiration)) echo 'checked'?> onclick="this.checked ? jQuery('#certificateExpirtation').show() : jQuery('#certificateExpirtation').hide();"> <?php _e('This certificate expires after selected period of time or on a fixed date.', 'perception');?></p>
		
		<div id="certificateExpirtation" style="display:<?php echo empty($certificate->has_expiration) ? 'none' : 'block';?>">
		  <p><input type="radio" name="expiration_mode" value="period" <?php if(empty($certificate->expiration_mode) or $certificate->expiration_mode == 'period') echo 'checked'?>> <?php _e('Expiration period:', 'perception');?> <input type="text" name="expiration_period_num" size="3" value="<?php echo $expiration_num?>">
		  <select name="expiration_period_period">
		     <option value="month"><?php _e('months', 'perception');?></option>
		     <option value="year" <?php if(!empty($expiration_period) and $expiration_period == 'year') echo 'selected'?>><?php _e('years', 'perception');?></option>
		  </select>
			&nbsp;
			
			<input type="radio" name="expiration_mode" value="date" <?php if(!empty($certificate->expiration_mode) and $certificate->expiration_mode == 'date') echo 'checked'?>>
			
			<?php _e('Expiration date:', 'perception');?> <input type="text" name="expiration_date" class="datepicker" value="<?php echo $expiration_date?>">	  
		  </p>
		  <p><?php _e('Message to display when the certificate has expired. if you leave it empty, default message will be used.', 'perception');?><br>
		  	<?php if(get_option('perception_certificates_no_rtf')):?>
	   		<textarea name="expired_message" cols="80" rows="5"><?php echo stripslashes(@$certificate->expired_message)?></textarea>
	   	<?php else: wp_editor(stripslashes(@$certificate->expired_message), 'expired_message', array("editor_class" => 'i18n-multilingual')); endif; ?>
	   	</p>
		</div>

		
		<div align="center">
			<input type="submit" name="ok" value="<?php _e('Save Certificate', 'perception')?>">
			<?php if(!empty($certificate->id)):?>
				<input type="button" value="<?php _e('Delete', 'perception')?>" onclick="perceptionConfirmDelete(this.form);">
				<input type="hidden" name='del' value='0'>
			<?php endif;?>		
		</div>		
		<?php wp_nonce_field('perception_certificate');?>
	</form>
</div>

<script type="text/javascript">
function perceptionConfirmDelete(frm) {
	if(confirm("<?php _e('Are you sure?')?>")) {
		frm.del.value=1;
		frm.submit();
	}
}

function perceptionValidateForm(frm) {
	if(frm.title.value=='') {
		alert("<?php _e('Please enter certificate title', 'perception')?>");
		frm.title.focus();
		return false;
	}
	
	return true;
}

jQuery(function(){
	jQuery('.datepicker').datepicker({dateFormat: "yy-m-d"});
});
</script>
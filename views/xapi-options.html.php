<div class="wrap">
	<h1><?php _e('xAPI / Tin Can Options', 'perception');?></h1>
	
	<p><?php printf(__('xAPI / Tin Can integration requires installing the free <a href="%s" target="_blank">WP Experience API plugin</a>. Your learning record store options should be saved there. On this page you only set up which activities from your LMS will go into the record store.', 'perception'), 'https://wordpress.org/plugins/wp-experience-api/');?></p>
	
	<form method="post">
		<h2><?php _e('LMS Activities To Track', 'perception');?></h2>
		
		<p><input type="checkbox" name="enrolled_course" value="1" <?php if(!empty($options['enrolled_course'])) echo "checked"?>> <?php _e('Student enrolled course', 'perception');?></p>
		<p><input type="checkbox" name="completed_course" value="1" <?php if(!empty($options['completed_course'])) echo "checked"?>> <?php _e('Student completed course', 'perception');?></p>
		<p><input type="checkbox" name="exited_course" value="1" <?php if(!empty($options['exited_course'])) echo "checked"?>> <?php _e('Student exited course (un-enrolled or cleaned up by admin)', 'perception');?></p>
		
		<p><input type="checkbox" name="started_lesson" value="1" <?php if(!empty($options['started_lesson'])) echo "checked"?>> <?php _e('Student started lesson', 'perception');?></p>
		<p><input type="checkbox" name="completed_lesson" value="1" <?php if(!empty($options['completed_lesson'])) echo "checked"?>> <?php _e('Student completed lesson', 'perception');?></p>
		<p><input type="checkbox" name="submitted_solution" value="1" <?php if(!empty($options['submitted_solution'])) echo "checked"?>> <?php _e('Student submitted solution to assignment', 'perception');?></p>
		<p><input type="checkbox" name="solution_approved" value="1" <?php if(!empty($options['solution_approved'])) echo "checked"?>> <?php _e('Solution to assignment was approved', 'perception');?></p>
		<p><input type="checkbox" name="solution_rejected" value="1" <?php if(!empty($options['solution_rejected'])) echo "checked"?>> <?php _e('Solution to assignment was rejected', 'perception');?></p>
		
		<p><input type="submit" name="ok" value="<?php _e('Save Options', 'perception');?>"></p>
		<?php wp_nonce_field('perception_xapi');?>
	</form>
</div>
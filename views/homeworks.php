<h1><?php _e("Assignments / Homework", 'perception')?></h1>

<div class="wrap">
	<div class="postbox-container">
	<form method="get" action="admin.php">
		<input type="hidden" name="page" value="perception_homeworks">
		<p><?php _e('Select course:', 'perception')?> <select name="course_id" onchange="perceptionSelectCourse(this.value);">
		<option value=""><?php _e('- please select -', 'perception')?></option>
		<?php foreach($courses as $course):?>
			<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$course->ID) echo 'selected'?>><?php echo $course->post_title?></option>
		<?php endforeach;?>
		</select>
		
		<span id="perceptionLessonID">
		<?php if(!empty($_GET['course_id'])):?>
			<?php _e('Select lesson:','perception')?> <select name='lesson_id' onchange="this.form.submit();">
				<?php foreach($lessons as $lesson):?>
					<option value="<?php echo $lesson->ID?>"<?php if($lesson->ID == $_GET['lesson_id']) echo ' selected'?>><?php echo $lesson->post_title?></option>
				<?php endforeach;?>
			</select>
		<?php endif;?>	
		</span>
		</p>
	</form>
	
	<?php if(!empty($_GET['course_id']) and !empty($_GET['lesson_id'])):?>
	<p><a href="admin.php?page=perception_homeworks&course_id=<?php echo $_GET['course_id']?>&lesson_id=<?php echo $_GET['lesson_id']?>&do=add"><?php _e('Click here to create new assignment', 'perception')?></a></p>
		<?php if(sizeof($homeworks)):?>
			<table class="widefat">
				<tr><th><?php _e('Title', 'perception')?></th><th><?php _e('Edit','perception')?></th><th><?php _e('View solutions','perception')?></th>
				<?php do_action('perception_extra_th', 'homeworks');?>				
				</tr>
				<?php foreach($homeworks as $homework):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo stripslashes($homework->title)?></td><td><a href="admin.php?page=perception_homeworks&course_id=<?php echo $_GET['course_id']?>&lesson_id=<?php echo $_GET['lesson_id']?>&do=edit&id=<?php echo $homework->id?>"><?php _e('Edit', 'perception')?></a></td>
					<td>
					<?php if($homework->solutions):?>
						<a href="admin.php?page=perception_view_all_solutions&id=<?php echo $homework->id?>"><?php echo $homework->solutions?> <?php _e('solutions', 'perception')?></a>
					<?php else: _e('No solutions', 'perception'); endif;?></td>
					<?php do_action('perception_extra_td', 'homeworks', $homework);?></tr>
				<?php endforeach;?>
			</table>	
			<p><?php _e('Shortcode to publish these assignments on the front-end:', 'perception')?> <input type="text" readonly="readonly" onclick="this.select()" value='[perception-assignments lesson_id="<?php echo $_GET['lesson_id']?>"]' size="50"></p>
		<?php endif;?>
	<?php else: echo '<p>'.__('You have to select course and lesson before you can create assignments', 'perception').'</p>'; endif;?>
	</div>
	
</div>	

<script type="text/javascript" >
function perceptionSelectCourse(id) {
	if(!id) {
		jQuery('#perceptionLessonID').html('');
		return false;
	}	
	data = {'action' : 'perception_ajax', 'type': 'lessons_for_course', 'course_id' : id};
	jQuery.post(ajaxurl, data, function(msg){
		results = jQuery.parseJSON(msg);	
		html = "<?php _e('Select lesson:','perception')?> <select name='lesson_id' onchange='this.form.submit();'>";
		html += '<option value=""><?php _e('- please select -', 'perception')?></option>';
		for(i=0; i<results.length; i++) {
			html += '<option value="' + results[i].ID + '">' + results[i].post_title + '</option>';
		} 
		html += '</select>';
		jQuery('#perceptionLessonID').html(html);
	});
}
</script>
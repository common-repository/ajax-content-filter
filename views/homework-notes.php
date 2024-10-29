<h1><?php _e('Admin notes for assignment', 'perception')?> "<?php echo stripslashes($homework->title)?>"</h1>

<?php if(!sizeof($notes)): echo "<p>".__("There aren't any notes yet.", 'perception')."</p>"; endif;?>

<?php foreach($notes as $note):?>
	<div class="perception-box perception-dashed">
		<div id="homeworkNote-<?php echo $note->id?>">
			<h3><?php printf(__("Note by %s posted on %s", 'perception'), $note->username, date_i18n(get_option('date_format'), strtotime($note->datetime)));?></h3>	
			
			<?php echo apply_filters('perception_content', stripslashes($note->note));?>
			<?php if(current_user_can('perception_manage') and $multiuser_access == 'all'):?>
				<p><a href="#" onclick="Perception.deleteNote(<?php echo $_GET['student_id']?>, <?php echo $note->id?>);return false;"><?php _e('Delete note', 'perception');?></a></p>
			<?php endif;?>
		</div>
	</div>
<?php endforeach;?>
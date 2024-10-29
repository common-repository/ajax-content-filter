<?php
// handles point awarding, spending, and so on
class PerceptionPoint {
	static function award($user_id, $award_points, $explanation, $for_item_type = '', $for_item_id = 0) {
		global $wpdb;
		
		$points = get_user_meta($user_id, 'perception_points', true);
		$points = $points + $award_points;
		update_user_meta($user_id, 'perception_points', $points);
		$history_for_item_type = $for_item_type;
		$history_for_item_id = $for_item_id;
			
		// assign also to homework, lesson, course
		if(!empty($for_item_id)) {			
			if($for_item_type == 'homework') {
				$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_HOMEWORKS." 
					SET points = points + %d WHERE homework_id=%d AND student_id=%d", $award_points, $for_item_id, $user_id));
					
				// now get lesson ID and replace $for_item_type and $for_item_id to update the lesson too
				$for_item_id = $wpdb->get_var($wpdb->prepare("SELECT lesson_id FROM ".PERCEPTION_HOMEWORKS." WHERE id=%d", $for_item_id));
				$for_item_type = 'lesson';	
			}
			
			if($for_item_type == 'lesson') {
				// update on lesson
				$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_LESSONS." 
					SET points = points + %d WHERE lesson_id=%d AND student_id=%d", $award_points, $for_item_id, $user_id));
				
				// define course ID and put it as $for_item_id var
				$for_item_id = get_post_meta($for_item_id, 'perception_course', true);	
			}
			
			// always update on course
			$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_COURSES." 
					SET points = points + %d WHERE course_id=%d AND user_id=%d", $award_points, $for_item_id, $user_id));
		}
		
		// insert in history
		$wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='awarded_points',
			value=%s, num_value=%d, for_item_type=%s, for_item_id=%d, course_id=%d",
			$user_id, $explanation, $award_points, $history_for_item_type, $history_for_item_id, $for_item_id));		
			
		// connected to MoolaMojo?
		if(get_option('perception_moolamojo_points') == 1) {
			$table = ($for_item_type == 'homework') ? PERCEPTION_HOMEWORKS : $wpdb->posts;
			do_action("moolamojo_transaction", true, $award_points, $explanation, $user_id, $table, $for_item_id);
		}		
			
		do_action('perception_earned_points', $user_id, $award_points);	
			
		return true;	
	}
	
	// add custom column to the users table
	static function add_custom_column($columns) {		
		$columns['perception_points'] = sprintf(__('LMS Points', 'perception'));
	 	return $columns;		
	}
	
	static function manage_custom_column($empty='', $column_name, $id) {		
	  if( $column_name == 'perception_points' ) {
			if(!empty($_GET['perception_cleanup_points']) and $id == $_GET['perception_cleanup_points']) {
				update_user_meta($_GET['perception_cleanup_points'], 'perception_points', 0);
			}	
	  	
			// get the number of points
	  		$points = get_user_meta($id, 'perception_points', true);
	  		if($points) return $points . ' <a href="#" onclick="perceptionResetPoints(' .$id.');return false;">' . __('(Cleanup)', 'perception'). '</a>'; 
	  		else return "0";
	  }
		return $empty;
	}
}
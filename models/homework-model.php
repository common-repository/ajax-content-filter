<?php
class PSPerceptionLMSHomeworkModel {
	// custom post type Homework	
	static function register_homework_type() {		
		$homework_slug = get_option('perception_homework_slug');
	   if(empty($lesson_slug)) $homework_slug = 'perception-homework';
	   
		$args=array(
			"label" => __("PSPerception Homeworks", 'perception'),
			"labels" => array
				(
					"name"=>__("Homeworks", 'perception'), 
					"singular_name"=>__("Homework", 'perception'),
					"add_new_item"=>__("Add New Homework", 'perception'),
					'bp_activity_admin_filter' => __( 'Homeworks', 'perception' ),
	            'bp_activity_front_filter' => __( 'Homeworks', 'perception' ),
	            'bp_activity_new_post' => __( '%1$s created a new <a href="%2$s">Homework</a>', 'perception' ),
				   'bp_activity_comments_admin_filter' => __( 'Comments about Homeworks', 'perception' ),
				   'bp_activity_comments_front_filter' => __( 'Homework Comments', 'perception' ),
				   'bp_activity_new_comment'  => __( '%1$s commented on the <a href="%2$s">Homework</a>', 'perception' ),
				),
			"public"=> true,
			"show_ui"=>false,
			"has_archive"=>false,
			"rewrite"=> array("slug"=>$homework_slug, "with_front"=>false),
			"description"=>__("This will create a new post with homeworks for a lesson in your PSPerception LMS.",'perception'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats', 'buddypress-activity'),
			'bp_activity' => array(
            'action_id'             => 'new_homework',
            'contexts'              => array( 'activity', 'member' ),
            'comment_action_id'     => 'new_homework_comment',
            'position'              => 70,
        ),
			"taxonomies"=>array("category", 'post_tag'),
			"show_in_nav_menus"=>'false',
			'show_in_menu' => false,
		);
		register_post_type( 'perception_homework', $args );
	}		
	
	static function manage() {
		global $wpdb, $user_ID;
		$_course = new PSPerceptionLMSCourseModel();
		$_lesson = new PSPerceptionLMSLessonModel();
		
		$multiuser_access = 'all';
		$multiuser_access = PSPerceptionLMSMultiUser :: check_access('homework_access');
				
		// select courses
		$courses = $_course -> select();
		$courses = apply_filters('perception-homeworks-select-courses', $courses);
		
		// if course and lesson are selected, populate two variables for displaying titles etc
		if(!empty($_GET['course_id'])) $this_course = $_course -> select($_GET['course_id']);
		if(!empty($_GET['lesson_id'])) $this_lesson = $_lesson -> select($_GET['course_id'], 'single', $_GET['lesson_id']);
		
		// sanitize / prepare vars
		$_GET['course_id'] = intval(@$_GET['course_id']);
		$_GET['lesson_id'] = intval(@$_GET['lesson_id']);
		$accept_files = empty($_POST['accept_files']) ? 0 : 1;
      $award_points = intval(@$_POST['award_points']);
      $auto_grade_lesson = empty($_POST['auto_grade_lesson']) ? 0 : 1;
		
		switch(@$_GET['do']) {
			case 'add':
				// apply permissions from other plugins 
				do_action('perception-check-permissions', 'course', $_GET['course_id']);
				if(!empty($_POST['ok']) and check_admin_referer('perception_homework')) {
						$wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, 
						award_points=%d, editor_id=%d, limit_by_date=%d, accept_date_from=%s, 
						accept_date_to=%s, auto_grade_lesson=%d",
						$_GET['course_id'], $_GET['lesson_id'], sanitize_text_field($_POST['title']), 
						perception_strip_tags($_POST['description']), $accept_files, $award_points,						 
						$user_ID, intval(@$_POST['limit_by_date']), sanitize_text_field($_POST['accept_date_from']), 
						sanitize_text_field($_POST['accept_date_to']), $auto_grade_lesson));	
						
						$id = $wpdb->insert_id;		
						
						do_action('perception_add_homework', $id);		
						
						self :: create_homework_post($_GET['lesson_id']);
					
						//$_SESSION['perception_flash'] = __('Homework added', 'perception');
						perception_redirect("admin.php?page=perception_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
				
				perception_enqueue_datepicker();	
			
				if(@file_exists(get_stylesheet_directory().'/perception/homework.php')) require get_stylesheet_directory().'/perception/homework.php';
				else require(PERCEPTION_PATH."/views/homework.php");
			break;		
			
			case 'edit':
				// apply permissions from other plugins 
				do_action('perception-check-permissions', 'homework', $_GET['id']);
				
				if($multiuser_access == 'own') {
					$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
					$homework = $homework[0];
					if($homework->editor_id != $user_ID) wp_die(__('You are not allowed to edit or delete this assignment', 'perception'));
				}				
				
				if(!empty($_POST['del']) and check_admin_referer('perception_homework')) {
					 self::delete($_GET['id']);
					 
					 //$_SESSION['perception_flash'] = __('Homework deleted', 'perception');
					 perception_redirect("admin.php?page=perception_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
			
				if(!empty($_POST['ok']) and check_admin_referer('perception_homework')) {
						$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, award_points=%d,
						limit_by_date=%d, accept_date_from=%s, accept_date_to=%s, auto_grade_lesson=%d
						WHERE id=%d",
						$_GET['course_id'], $_GET['lesson_id'], sanitize_text_field($_POST['title']), 
						perception_strip_tags($_POST['description']), $accept_files, $award_points, 
						intval(@$_POST['limit_by_date']), sanitize_text_field($_POST['accept_date_from']), 
						sanitize_text_field($_POST['accept_date_to']), $auto_grade_lesson,
						$_GET['id']));		
						
						do_action('perception_save_homework', $_GET['id']);					
					
						//$_SESSION['perception_flash'] = __('Homework saved', 'perception');
						perception_redirect("admin.php?page=perception_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
				
				// select homework
				$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
				$homework = $homework[0];
				
				perception_enqueue_datepicker();	
			
				if(@file_exists(get_stylesheet_directory().'/perception/homework.php')) require get_stylesheet_directory().'/perception/homework.php';
				else require(PERCEPTION_PATH."/views/homework.php");
			break;			
			
			default:
				// if course is selected, find lessons
				if(!empty($_GET['course_id'])) {
					$lessons = $_lesson->select($_GET['course_id'], 'array', null, '');
				}			
			
				// list existing homeworks if course and lesson are selected
				if(!empty($_GET['course_id']) and !empty($_GET['lesson_id'])) {
					// apply permissions from other plugins - this allows other plugins to die here if user can't access the course
					do_action('perception-check-permissions', 'course', $_GET['course_id']);
					
					$own_sql = '';
					if($multiuser_access == 'own') $own_sql = $wpdb->prepare(" AND tH.editor_id=%d ", $user_ID);
					
					$homeworks = $wpdb->get_results($wpdb->prepare("SELECT tH.*, COUNT(tS.id) as solutions 
						FROM ".PERCEPTION_HOMEWORKS." tH LEFT JOIN ".PERCEPTION_STUDENT_HOMEWORKS." tS ON tS.homework_id = tH.id
						WHERE tH.course_id=%d AND tH.lesson_id=%d	$own_sql 
						GROUP BY tH.id ORDER BY tH.title", 
						$_GET['course_id'], $_GET['lesson_id']));
				} 
				
				if(@file_exists(get_stylesheet_directory().'/perception/homeworks.php')) require get_stylesheet_directory().'/perception/homeworks.php';
				else require(PERCEPTION_PATH."/views/homeworks.php");
			break;
		}
	}
	
	// shows homeworks assigned to a lesson
	static function lesson_homeworks($in_shortcode = false) {
		 global $wpdb, $user_ID, $post;
		 
		 // not my own homeworks? I need to have manage caps then
		 $manager_mode = false;
		 if($user_ID != $_GET['student_id']) {		 	
		 		if(!current_user_can('perception_manage')) wp_die(__('You are not allowed to see this page', 'perception'));		 		 		
		 }
		 $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", intval($_GET['student_id'])));
		 if(current_user_can('perception_manage')) $manager_mode = true;	
		 
		 
		 // select lesson
		 $lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", intval($_GET['lesson_id'])));

		 if(empty($lesson->ID)) return __('Invalid lesson ID.', 'perception'); 			 
		 
		 // course ID
		 $course_id = get_post_meta($lesson->ID, 'perception_course', true);
		 
		 // select the homeworks assigned to this lesson
		 $homeworks = self :: select($wpdb->prepare("WHERE lesson_id = %d", $lesson->ID)); 
		 $ids = array(0);
		 foreach($homeworks as $homework) $ids[] = $homework->id;
		 $id_sql = implode(", ", $ids);
		 
		 // select & match student solutions for each homework
		 $solutions = $wpdb -> get_results( $wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_HOMEWORKS."
		 	WHERE student_id = %d AND homework_id IN ($id_sql) ORDER BY id", intval($_GET['student_id'])) );	
		 	
		 // select & match notes for each homework
		 $notes = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".PERCEPTION_HOMEWORK_NOTES." 
		 	WHERE homework_id IN ($id_sql) AND student_id = %d", intval($_GET['student_id'])));	
		 	
		 	
		 foreach($homeworks as $cnt=>$homework) {
		 		$homework_solutions = array();
		 		$homework_notes = array();
		 		
		 		foreach($solutions as $solution) {
		 			if($solution -> homework_id == $homework->id) $homework_solutions[] = $solution; 
		 		}
		 		
		 		foreach($notes as $note) {
		 			if($note->homework_id == $homework->id) $homework_notes[] = $note;
		 		}
		 		
		 		// define homework status - if even 1 solution is approved, the homework status is true
		 		$homeworks[$cnt]->status = false;
		 		foreach($homework_solutions as $solution) {
		 			if($solution->status == 'approved') $homeworks[$cnt]->status = true;
		 		}
		 		
		 		$homeworks[$cnt]->solutions = $homework_solutions;
		 		$homeworks[$cnt]->notes = $homework_notes;
		 }
		 
		 $dateformat = get_option('date_format');		 
		 
		 wp_enqueue_script('thickbox',null,array('jquery'));
		 wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		 if(@file_exists(get_stylesheet_directory().'/perception/lesson-homeworks.php')) require get_stylesheet_directory().'/perception/lesson-homeworks.php';
		  else require(PERCEPTION_PATH."/views/lesson-homeworks.php");
	}
	
	// select homeworks
	static function select($where) {
		global $wpdb;
		
		$homeworks = $wpdb -> get_results("SELECT * FROM ".PERCEPTION_HOMEWORKS." $where ORDER BY id");
		
		return $homeworks;
	}
	
	// delete homework
	// for the moment delete only the DB record, but for the future 
	// consider deleting the solutions along with their files
	static function delete($id) {
			global $wpdb;
			$id = intval($id);
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_HOMEWORKS." WHERE id=%d", $id));
	}
	
	// full select homework - with lesson and course (used in few places)
	static function full_select($id) {
		global $wpdb;
		$_course = new PSPerceptionLMSCourseModel();		
		$_lesson = new PSPerceptionLMSLessonModel();
		$id = intval($id);
		
		// select this homework and lesson
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_HOMEWORKS."
			WHERE id=%d", $id));			
		// select course
		$course = $_course->select($homework->course_id);		
		// select lesson
		$lesson = $_lesson->select($course->ID, 'single', $homework->lesson_id);	
		
		return array($homework, $course, $lesson);
	}
	
	// grade the homework. If required, set the homework grade as lesson grade too
	// @param $grade - string, the grade
	// @param $id - int, the solution ID
	static function set_grade($grade, $id) {
	   global $wpdb;
	   $grade = sanitize_text_field($grade);
	   $id = intval($id);
	   
	   $wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_HOMEWORKS." SET grade=%s WHERE id=%d", $grade, $id));
      do_action('perception_graded_homework', $id, $grade);
      
      // now check if the homework should also grade the lesson
      $solution = $wpdb->get_row($wpdb->prepare("SELECT student_id, homework_id FROM ".PERCEPTION_STUDENT_HOMEWORKS." WHERE id=%d", $id));
      $homework = $wpdb->get_row($wpdb->prepare("SELECT lesson_id, auto_grade_lesson FROM ".PERCEPTION_HOMEWORKS." WHERE id=%d", $solution->homework_id));
      if($homework->auto_grade_lesson) {
         $wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_LESSONS." SET grade=%s 
            WHERE lesson_id=%d AND student_id=%d", $grade, $homework->lesson_id, $solution->student_id));
         do_action('perception_graded_lesson', $solution->student_id, $homework->lesson_id, $grade);   
      }
	} // end set_grade()
	
	// creates custom Homework post with the shortcode for that lesson IF it doesn't already exist
	static function create_homework_post($lesson_id) {
		global $wpdb;
		
		// check if post exists
		$post_exists = $wpdb->get_var("SELECT ID FROM {$wpdb->posts}
			WHERE post_status = 'publish' AND post_date < NOW()
			AND post_content LIKE '%[perception-assignments lesson_id=\"".$lesson_id."\"%' ORDER BY ID DESC"); 
			
		if(empty($post_exists)) {
			$lesson = get_post($lesson_id);
			
			$my_post = array(
				'post_type' => 'perception_homework',
			  'post_title'    => sprintf(__('Homework for Lesson %s', 'perception'), stripslashes($lesson->post_title)),
			  'post_content'  => '[perception-assignments lesson_id="'.$lesson_id.'"]',
			  'post_status'   => 'publish',
			  'post_category' => array( 8,39 )
			);
 		
			wp_insert_post( $my_post );
		}	
	} // end create_homework_post
}
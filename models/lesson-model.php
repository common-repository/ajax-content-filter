<?php
class PSPerceptionLMSLessonModel {
	// custom post type Lesson	
	static function register_lesson_type() {		
		$lesson_slug = get_option('perception_lesson_slug');
	   if(empty($lesson_slug)) $lesson_slug = 'perception-lesson';
	   
		$args=array(
			"label" => __("PSPerception Lessons", 'perception'),
			"labels" => array
				(
					"name"=>__("PS Lessons", 'perception'), 
					"singular_name"=>__("Lesson", 'perception'),
					"add_new_item"=>__("Add New Lesson", 'perception'),
					'bp_activity_admin_filter' => __( 'Lessons', 'perception' ),
	            'bp_activity_front_filter' => __( 'Lessons', 'perception' ),
	            'bp_activity_new_post' => __( '%1$s created a new <a href="%2$s">Lesson</a>', 'perception' ),
				   'bp_activity_comments_admin_filter' => __( 'Comments about Lessons', 'perception' ),
				   'bp_activity_comments_front_filter' => __( 'Lesson Comments', 'perception' ),
				   'bp_activity_new_comment'  => __( '%1$s commented on the <a href="%2$s">Lesson</a>', 'perception' ),
				),
			"public"=> true,
			"show_ui"=>true,
			'show_in_rest' => true,
			"has_archive"=>true,
			"rewrite"=> array("slug"=>$lesson_slug, "with_front"=>false),
			"description"=>__("This will create a new lesson in your PSPerception LMS.",'perception'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats', 'buddypress-activity'),
			'bp_activity' => array(
            'action_id'             => 'new_lesson',
            'contexts'              => array( 'activity', 'member' ),
            'comment_action_id'     => 'new_lesson_comment',
            'position'              => 70,
        ),
			"taxonomies"=>array("category", 'post_tag'),
			"show_in_nav_menus"=>'true',
			'show_in_menu' => 'perception_options',
			"register_meta_box_cb"=>array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'perception_lesson', $args );
	}
	
	static function meta_boxes() {
		add_meta_box("perception_meta", __("PSPerception Settings", 'perception'), 
							array(__CLASS__, "print_meta_box"), "perception_lesson", 'normal', 'high');
	}
	
	static function print_meta_box($post) {
		global $wpdb;
			
		$_course = new PSPerceptionLMSCourseModel();
		
		// select all existing courses
		$courses = $_course -> select();
		
		// which courses do this lesson belong to?
		$course_id = get_post_meta($post->ID, 'perception_course', true);
		
		// module?
		$use_modules = get_option('perception_use_modules');
		if($use_modules) {
			$module_id = get_post_meta($post->ID, 'perception_module', true);
			$_module = new PSPerceptionLMSModuleModel();
			$modules = $_module->select(0, $course_id);
		}
		
		// other lessons in this course
		$other_lessons = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'perception_course'
			AND tM.meta_value = %d
			WHERE post_type = 'perception_lesson'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY ID ASC",  $course_id, $post->ID));
		$other_lessons = apply_filters('perception-reorder-lessons', $other_lessons);		
			
		$lesson_access = get_post_meta($post->ID, 'perception_access', true);	
		if(!is_array($lesson_access)) $lesson_access = array();
		$lesson_completion = get_post_meta($post->ID, 'perception_completion', true);	
		if(!is_array($lesson_completion)) $lesson_completion = array();
		$required_homeworks = get_post_meta($post->ID, 'perception_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		$required_exam = get_post_meta($post->ID, 'perception_required_exam', true);
		$required_grade = get_post_meta($post->ID, 'perception_required_grade', true); 
		if(!is_array($required_grade)) $required_grade = array($required_grade);
		
		// select assignments
		$homeworks = PSPerceptionLMSHomeworkModel::select($wpdb->prepare(' WHERE lesson_id = %d', $post->ID));
				
		// select quizzes from Watu/WatuPRO
		$use_exams = get_option('perception_use_exams');
		
		
		if(!empty($use_exams)) {
			if($use_exams == 'watu') {
					$exams_table = $wpdb->prefix.'watu_master';
					$grades_table = $wpdb->prefix.'watu_grading';
			}
			if($use_exams == 'watupro') {
					$exams_table = $wpdb->prefix.'watupro_master';
					$grades_table = $wpdb->prefix.'watupro_grading';
			}
			
			$exams = $wpdb->get_results("SELECT * FROM $exams_table ORDER BY name");
			
			// fill grades
			$grades = $wpdb->get_results("SELECT * FROM $grades_table ORDER BY id");
			
			// grades of the currently selected exam. Will be filled only if such is selected
			$required_grades = array(); 
			
			foreach($exams as $cnt=>$exam) {
					$exam_grades = array();
					foreach($grades as $grade) {
							if(!empty($exam->reuse_default_grades) and empty($grade->exam_id)) $exam_grades[] = $grade;
							if(empty($exam->reuse_default_grades) and $grade->exam_id == $exam->ID) $exam_grades[] = $grade;
					}
					
					$exams[$cnt]->grades = $exam_grades;
					
					if($required_exam and $required_exam == $exam->ID) $required_grades = $exam_grades;
			}
		}

		$use_points_system = get_option('perception_use_points_system');
		$use_grading_system = get_option('perception_use_grading_system');
		$award_points = get_post_meta($post->ID, 'perception_award_points', true);
		if($award_points === '') $award_points = get_option('perception_points_lesson');		
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'perception_noncemeta' );		
		if(@file_exists(get_stylesheet_directory().'/perception/lesson-meta-box.php')) require get_stylesheet_directory().'/perception/lesson-meta-box.php';
		else require(PERCEPTION_PATH."/views/lesson-meta-box.php");
	}
	
	static function save_lesson_meta($post_id) {	
		global $wpdb;
			
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  	if ( empty($_POST['perception_noncemeta']) or !wp_verify_nonce( $_POST['perception_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  	if ( !current_user_can( 'edit_post', $post_id ) ) return;
	  	if ('perception_lesson' != $_POST['post_type']) return;
	  	
	  	$use_modules = get_option('perception_use_modules');
	  	  		  
	  	update_post_meta($post_id, "perception_course", intval($_POST['perception_course']));
	  	$_POST['perception_module'] = empty($_POST['perception_module']) ? 0 : intval($_POST['perception_module']);
	  	if($use_modules) update_post_meta($post_id, "perception_module", $_POST['perception_module']);
	  	update_post_meta($post_id, "perception_access", perception_int_array(@$_POST['perception_access']));
	  	update_post_meta($post_id, "perception_completion", @$_POST['perception_completion']);
	  	update_post_meta($post_id, "perception_required_homeworks", perception_int_array(@$_POST['perception_required_homeworks']));  	
	  	$_POST['perception_required_exam'] = empty($_POST['perception_required_exam']) ? 0 : intval($_POST['perception_required_exam']);
	  	update_post_meta($post_id, "perception_required_exam", $_POST['perception_required_exam']);
	  	update_post_meta($post_id, "perception_required_grade", perception_int_array(@$_POST['perception_required_grade']));
	  	$_POST['perception_watu_transfer_grade'] = empty($_POST['perception_watu_transfer_grade']) ? 0 : intval($_POST['perception_watu_transfer_grade']);
	  	update_post_meta($post_id, "perception_watu_transfer_grade", $_POST['perception_watu_transfer_grade']);
	  	$_POST['perception_award_points'] = empty($_POST['perception_award_points']) ? 0 : intval($_POST['perception_award_points']);
	  	if(isset($_POST['perception_award_points'])) update_post_meta($post_id, "perception_award_points", $_POST['perception_award_points']);
	}
	
	// select lessons in course ID
	// @param $is_module boolean: shows whether we are looking for course lessons or module lessons. 
	// If the var is true, $course_id is in fact module_id 
	function select($course_id, $format = 'array', $id = null, $ob = 'post_title', $dir = 'ASC', $is_module = false) {
		global $wpdb;
				
		$id_sql = '';
		if(!empty($id)) $id_sql = $wpdb->prepare(' AND tP.ID = %d ', $id);
		
		if(empty($ob)) {
			$ob = 'post_title';
			$reorder = true;
		}
		
		$parent_type = $is_module ? 'perception_module' : 'perception_course';
		
		$lessons = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = '$parent_type'
			AND tM.meta_value = %d
			WHERE post_type = 'perception_lesson'  AND (post_status='publish' OR post_status='draft') $id_sql
			ORDER BY $ob $dir",  $course_id));
			
		// external reorder?
		if(!empty($reorder)) $lessons = apply_filters('perception-reorder-lessons', $lessons);	
			
		if($format == 'array') return $lessons;
		
		if($format == 'single') return $lessons[0];
		
		if($format == 'json') echo json_encode($lessons);		
	}
	
	// students lessons in a selected course
	// @param $simplified boolean - when true doesn't assignment and text/exam  
	static function student_lessons($simplified = false, $ob = null, $dir = null, $in_shortcode=false, $show_excerpts = false, $is_module = false, $atts = null) {
		global $wpdb, $user_ID; 
		
		// student_id
		$student_id = (empty($_GET['student_id']) or !current_user_can('perception_manage')) ? $user_ID : $_GET['student_id'];
				
		// select this student
		$student = $wpdb -> get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
		
		// select this course or module
		$course = $wpdb -> get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE id=%d", $_GET['course_id']));
		
		// am I enrolled?
		if(!current_user_can('perception_manage') and !$in_shortcode) {
			$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES.
				" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status = 'completed')", $student_id, $course->ID));
			if(!$enrolled) {
				_e("You must enroll in the course first before you can see the lessons", 'perception');
				return false;
			}	
		} // end enrolled check	
		
		// change student-lesson status?
		if(!empty($_POST['change_status'])) {
			$multiuser_access = 'all';
			$multiuser_access = PSPerceptionLMSMultiUser :: check_access('students_access');
			
			if($multiuser_access == 'view') wp_die(__('You are not allowed to do this.', 'perception'));
				$result = PSPerceptionLMSStudentModel :: lesson_status($student->ID, $_POST['lesson_id'], $_POST['status']);
				if(!$result) $error = __('The lesson cannot be completed because there are unsatisfied requirements', 'perception');
		}
		
		// select lessons
		$_lesson = new PSPerceptionLMSLessonModel();
		
		$select_ob = empty($ob) ? 'post_title' : $ob;
		
		$lessons = $_lesson->select($course->ID, 'array', null, $ob, $dir, $is_module);
		
		$ids = array(0);
		foreach($lessons as $lesson) $ids[] = $lesson->ID;
		$id_sql = implode(",", $ids);
		
		// select homeworks and match to lessons
		$homeworks = PSPerceptionLMSHomeworkModel::select("WHERE lesson_id IN ($id_sql)");
		
		// using exams? select them too
		$use_exams = get_option('perception_use_exams');
		$exams_table = ($use_exams == 'watu') ? $wpdb->prefix.'watu_master' : $wpdb->prefix.'watupro_master';
		$shortcode = ($use_exams == 'watu') ? 'WATU' : 'WATUPRO';
		
		// select student-lesson relation so we can match status
		$student_lessons = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS."
			WHERE student_id = %d", $student_id));
			
		$use_grading_system = get_option('perception_use_grading_system');	
		
		foreach($lessons as $cnt=>$lesson) {
			$lesson_homeworks = array();
			foreach($homeworks as $homework) {
				if($homework->lesson_id == $lesson->ID) $lesson_homeworks[] = $homework;
			}
			$lessons[$cnt]->homeworks = $lesson_homeworks;
			
			if($use_exams) {
				$required_exam = get_post_meta($lesson->ID, 'perception_required_exam', true);
				
				if($required_exam) {
					$exam = $wpdb->get_row("SELECT tE.*, tP.id as post_id FROM $exams_table tE, {$wpdb->posts} tP
						WHERE tE.ID = $required_exam AND tP.post_content LIKE CONCAT('%[$shortcode ', tE.ID, ']%')
						AND tP.post_status='publish' AND post_title!=''");
						
					$lessons[$cnt]->exam = $exam;
				}					
			}
			
			// status
			$status = null;
			foreach($student_lessons as $l) {
				 if($l->lesson_id == $lesson->ID) {
				 	$status = $l;
				 	$lessons[$cnt]->grade = $l->grade;
				 }
			}			
			
			if(empty($status->id)) {
				$lessons[$cnt]->status = __('Not started', 'perception');
				$lessons[$cnt]->statuscode = -1;
			}
			else {
				if($status->status == 1) { 
					$lessons[$cnt]->status = __('Completed on', 'perception') . 
					' ' . date(get_option('date_format'), strtotime($status->completion_date));
					$lessons[$cnt]->statuscode = 1;
				}
				else {
					// in progress
					$lessons[$cnt]->status = "<a href='#' onclick='perceptionInProgress(".$lesson->ID.", ".$student_id.");return false;'>".__('In progress', 'perception')."</a>";
					$lessons[$cnt]->statuscode = 0;
				}					
			} // end defining status
		}
		
		// external reorder?
		if(empty($ob)) $lessons = apply_filters('perception-reorder-lessons', $lessons);
		
		// enqueue thickbox
		wp_enqueue_script('thickbox',null,array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');		
		if(@file_exists(get_stylesheet_directory().'/perception/student-lessons.php')) require get_stylesheet_directory().'/perception/student-lessons.php';
		else require(PERCEPTION_PATH."/views/student-lessons.php");
	}
	
	// check if user can access the lesson, mark lesson as started
	static function access_lesson($content) {
		global $wpdb, $post, $user_ID;		
		if(@$post->post_type != 'perception_lesson') return $content;		
		$_course = new PSPerceptionLMSCourseModel();
				
		if(!is_user_logged_in()) {
			//echo $post->ID.'<br>';
			// post excerpt?
			if(!empty($post->post_excerpt)) return wpautop($post->post_excerpt);
			return PERCEPTION_NEED_LOGIN_TEXT_LESSON;
		}
		
		// track visit
		PerceptionTrack::visit('lesson', $post->ID, $user_ID);
		
		$course_id = get_post_meta($post->ID, 'perception_course', true);
		$course = $_course -> select($course_id);
		
		// add link to parent course?
		if(get_option('perception_link_to_course') == 1) {			
			$link = stripslashes(get_option('perception_link_to_course_text'));
			$course_permalink = get_permalink($course_id);
			$link = str_replace('{{{course-link}}}', '<a href="'.$course_permalink.'">'.stripslashes($course->post_name).'</a>', $link);
			
			$content = $link . $content;
		}		
		
		// manager will always access lesson
		if(current_user_can('perception_manage')) {
			// class or other restrictions apply?
			list($no_access, $message) = apply_filters('perception-manager-lesson-access', array(false, ''), $user_ID, $course_id);
			if(!empty($no_access) and !current_user_can('manage_options')) {
				if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
				return $message;
			} 
			self :: mark_accessed(); return $content; 
		}
		
		// enrolled in the course?		
		$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $course_id));	
		if(!$enrolled) {
			if(!empty($post->post_excerpt)) $content = wpautop($post->post_excerpt);
			else $content = __('In order to see this lesson you first have to be enrolled in the course', 'perception').' <b>"'.$course->post_title.'"</b>';
			// self :: mark_accessed();
			return $content; // no need to run further queries
		}		
		
		// no access due to filters? (Classes from Perception PRO etc)
		list($no_access, $message) = apply_filters('perception-course-access', array(false, ''), $user_ID, $course_id);
		if(!empty($no_access)) {
			if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
			return $message;
		}		
			 
		// no access due to other lesson restrictions based on filters from other plugins		
		list($no_access, $message) = apply_filters('perception-lesson-access', array(false, ''), $user_ID, $post->ID);
		if(!empty($no_access)) {
			if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
			return $message;
		}		
		
		// can't access based on module restrictions?
		if(get_option('perception_use_modules')) {		   
		   // belongs to module?
		   $module_id = get_post_meta($post->ID, 'perception_module', true);
		   $module = get_post($module_id);
		   $module_access = get_post_meta($module_id, 'perception_access', true);
		   
         // any not completed?
         $not_completed_ids = null;
         if(!empty($module_access)) {
            foreach($module_access as $mid) {
                $is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_STUDENT_MODULES." WHERE
                  module_id=%d AND student_id=%d AND status='completed'", $mid, $user_ID));
                if(!$is_completed) {
                	// check on the fly, maybe lessons are completed but there are no requirements
                	$_module = new PSPerceptionLMSModuleModel();
                	if($_module -> is_ready($mid, $user_ID)) {
							// insert relation here
							$wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_STUDENT_MODULES." SET
								module_id=%d, student_id=%d, status='completed', enrollment_date=%s, completion_date=%s",
								$mid, $user_ID, date('Y-m-d', current_time('timestamp')), date('Y-m-d', current_time('timestamp')) ));                		
                		
                		$mid = 0; // unset $mid so it's not inserted as not completed
                	}
                	if($mid) $not_completed_ids[] = $mid;
                }  
            }
         }		   
	   
		   if(!empty($not_completed_ids)) {     
		       if(!empty($post->post_excerpt)) $content = wpautop($post->post_excerpt); 
		       $content = '<p>'.__('Before accessing a lesson from this module you must complete the following modules:','perception').'</p>';	
		       foreach($not_completed_ids as $id) {
   			 		$not_completed = get_post($id);   			 		
   			 		$content .= '<li><a href="'.get_permalink($id).'">'.stripslashes($not_completed->post_title).'</a></li>';
   			 }			 
			    $content	.= '<ul>';

			    return $content;
         }	
		} // end if using modules
				
		// can access based on other lesson restriction?
		$not_completed_ids = self :: unsatisfied_complete_requirements($post);
		
		if(!empty($not_completed_ids)) {
			 $content = '';
			 if(!empty($post->post_excerpt)) $content .= wpautop($post->post_excerpt);
			 $content .= '<p>'.__('Before accessing this lesson you must complete the following lessons:','perception').'</p>';			 
			 $content	.= '<ul>';
			
			 foreach($not_completed_ids as $id) {
			 		$not_completed = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE id=%d", $id));
			 		
			 		$content .= '<li><a href="'.get_permalink($id).'">'.$not_completed->post_title.'</a></li>';
			 }					 
			 
			 $content .= '</ul>';
			 // self :: mark_accessed();
			 return $content;
		}
		
		self :: mark_accessed();
		
		return $content;
	} // end access_lesson
	
	// small helper to check if lesson completion requirements are met
	// returns false if there are NO unsatisfied requirements, else 
	// returns the not comleted lesson IDs
	static function unsatisfied_complete_requirements($post) {
		global $wpdb, $user_ID;
		
		$lesson_access = get_post_meta($post->ID, 'perception_access', true);	
		if(!is_array($lesson_access)) $lesson_access = array();
		$completed_lessons = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS.
			" WHERE student_id = %d AND status = 1 ", $user_ID));
		$completed_ids = array(0);
		$not_completed_ids = false;
		foreach($completed_lessons as $l) $completed_ids[] = $l->lesson_id;
		if(sizeof($lesson_access)) {
			$not_completed_ids = array();
			foreach($lesson_access as $access) {
				if(!in_array($access, $completed_ids) and 'publish' === get_post_status( $access )) $not_completed_ids[] = $access;
			}
		}
		
		return $not_completed_ids;
	} // end unsatisfied_complete_requirements()
	
	// actually access lesson (after permission checks)
	// called only from self::access_lesson
	private static function mark_accessed() {
		global $wpdb, $post, $user_ID;
		
		// mark as accessed now (if record does not exist)
		$lesson_completion = get_post_meta($post->ID, 'perception_completion', true);		
		
		$exists = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS." 
			WHERE student_id=%d AND lesson_id=%d", $user_ID, $post->ID));
			
		if(empty($exists->id)) {
			  $wpdb -> query($wpdb->prepare("INSERT INTO ".PERCEPTION_STUDENT_LESSONS." SET
			  	lesson_id=%d, student_id=%d, status=%d, completion_date = %s, start_time=%s", 
			  	$post->ID, $user_ID, 0, date("Y-m-d", current_time('timestamp')), current_time('mysql')));
			  do_action('perception_started_lesson', $user_ID, $post->ID);
			  
			  $course_id = get_post_meta($post->ID, 'perception_course', true);	
			  
			  // insert in history
			  $wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HISTORY." SET
					user_id=%d, date=%s, datetime=%s, action='started_lesson', value=%s, num_value=%d, course_id=%d",
					$user_ID, date("Y-m-d", current_time('timestamp')), current_time('mysql'), 
					sprintf(__('Started reading lesson "%s"', 'perception'), $post->post_title), $post->ID, $course_id));
		} 
		
		// lessons belongs to module? We may need to enter student-module relation
		if(get_option('perception_use_modules') == 1) {
		   $module_id = get_post_meta($post->ID, 'perception_module', true);
		   if(!empty($module_id)) {
		      $exists = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_MODULES." 
			      WHERE student_id=%d AND module_id=%d", $user_ID, $module_id));
			   if(!$exists) {
			      $wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_STUDENT_MODULES." SET
			         module_id=%d, student_id=%d, status='started', enrollment_date=%s",
			         $module_id, $user_ID, date('Y-m-d', current_time('timestamp'))));
			   }   
		   }
		} // end module stuff
		
		// if ready, complete lesson
		// think about how to reduce these queries a little bit in the future
		if(self::is_ready($post->ID, $user_ID)) self::complete($post->ID, $user_ID);		
				
		do_action('perception_accessed_lesson', $user_ID, $post->ID);			
	}
	
	// checks if the lesson is ready to be considered "completed" for a given student. 
	// I.e. checks if all the requirements are completed
	// $admin_check - when admin checks completeness, we'll ignore the requirement for 
	// completed status - because we want to check only the other reqs
	static function is_ready($lesson_id, $student_id, $admin_check = false, $marking_by_student = false) {
		global $wpdb;
		
		// first let's check for already completed status. If such is there, obviously the lesson is ready for completing
		$student_lesson = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS."
			WHERE lesson_id=%d AND student_id=%d", $lesson_id, $student_id));				
		if(!empty($student_lesson->id) and $student_lesson->status == 1) return true;
				
		if(empty($student_lesson->id)) return false; // It can never be ready if it's not visited at all	
		
		// Homeworks check
		$required_homeworks = get_post_meta($lesson_id, 'perception_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		
		// select all existing homework IDs to make sure required assignment is not deleted
		$homeworks = $wpdb->get_results("SELECT id FROM ".PERCEPTION_HOMEWORKS);
		$hids = array(0);
		foreach($homeworks as $homework) $hids[] = $homework->id; 
		
		if(!empty($required_homeworks)) {			
			// select all completed homeworks of this student and see if all required are satisfied
			$completed_homeworks = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(homework_id) FROM ".
				PERCEPTION_STUDENT_HOMEWORKS." WHERE student_id=%d AND status='approved'", $student_id));
			$ids = array(0);
			
			foreach($completed_homeworks as $hw) $ids[] = $hw->homework_id;
			
			// if just one is not completed, return false
			foreach($required_homeworks as $required_id) {				
				if(!in_array($required_id, $ids) and in_array($required_id, $hids)) return false;
			}	
		}
		
		// Exam check
		if(!PSPerceptionLMSLessonModel::todo_exam($lesson_id, $student_id, 'boolean')) return false;
		
		// requirements from third party plugins		
		$other_requirements = apply_filters('perception-lesson-other-requirements', false, $lesson_id, $student_id);
		if($other_requirements) return false;
		
		// contains [perception-mark] check
		if(!$marking_by_student and !$admin_check) {
			$lesson = get_post($lesson_id);
			if(strstr($lesson->post_content, '[perception-mark]')) return false;
		}
		
		if(!$admin_check) {
			// if admin has to manually approve the lesson and has not done this yet (if he done it, we'd have "completed"
			// status already and not reach this point at all), then the lesson is not ready
			$lesson_completion = get_post_meta($lesson_id, 'perception_completion', true);	
			if(!is_array($lesson_completion)) $lesson_completion = array();
			
			if(in_array('admin_approval', $lesson_completion)) {
				// when doing this we have to update the DB field "pending admin approval" to 1
				// so we know that all this lesson-to-student relation is waiting for is the admin approval.
				// This is important for the new "To-do" page for managers.
				if(!$student_lesson->pending_admin_approval) {
					$wpdb->query($wpdb->prepare("UPDATE " . PERCEPTION_STUDENT_LESSONS." SET
						pending_admin_approval=1 WHERE id=%d", $student_lesson->id));
				}				
				
				return false;
			}
		} // end pending admin approval check
		
		return true;
	}
	
	// marks lesson as completed. If required, marks the corresponding course as completed as well
	static function complete($lesson_id, $student_id) {
		global $wpdb;
		$_course = new PSPerceptionLMSCourseModel();
	
		// find the lesson
		$lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $lesson_id));
		$course_id = get_post_meta($lesson->ID, 'perception_course', true);
		
		// get course
		$course = $_course->select($course_id);
		
		// mark lesson as completed - at this point we must have student-lesson record
		$student_lesson = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS."
			WHERE lesson_id=%d AND student_id=%d", $lesson->ID, $student_id));
		if(empty($student_lesson->id)) return false;
		
		// if the lesson is already completed, don't mark it again
		if($student_lesson->status == 1) return false;
		
		$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_LESSONS." 
		SET status = '1', completion_date = %s, completion_time=%s, pending_admin_approval=1
		WHERE id=%d", date("Y-m-d", current_time('timestamp')), current_time('mysql'), $student_lesson->id));
		
		// lessons belongs to module? We may need to enter student-module relation
		if(get_option('perception_use_modules') == 1) {
		   $module_id = get_post_meta($lesson_id, 'perception_module', true);
		   if(!empty($module_id)) {
            // are we ready to complete the module?
            $_module = new PSPerceptionLMSModuleModel();
            if($_module->is_ready($module_id, $student_id)) $_module->complete($module_id, $student_id);
		   }
		} // end module stuff
		
		// award points?
		$use_points_system = get_option('perception_use_points_system');
		if($use_points_system) {
			$award_points = get_post_meta($lesson_id, 'perception_award_points', true);
			if($award_points === '') $award_points = get_option('perception_points_lesson');
			if($award_points) {				
				PerceptionPoint :: award($student_id, $award_points, 
					sprintf(__('Received %d points for completing lesson "%s".', 'perception'), $award_points, $lesson->post_title),
					'lesson', $lesson_id);
			}
		}
		
		do_action('perception_completed_lesson', $student_id, $lesson_id);
		
		// insert in history
	   $wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='completed_lesson', value=%s, num_value=%d, course_id=%d",
			$student_id, sprintf(__('Completed lesson "%s"', 'perception'), $lesson->post_title), $lesson_id, $course_id));
		
		// now see if course should be completed
		if($_course->is_ready($course_id, $student_id)) $_course->complete($course_id, $student_id);
		
		return true;
	}
	
	// checks if lesson is completed
	static function is_completed($lesson_id, $student_id) {
		global $wpdb;		
		$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_STUDENT_LESSONS." 
			WHERE lesson_id=%d AND student_id=%d AND status='1'", $lesson_id, $student_id));
			
		return $id;		
	}
	
	// see what is to-do in a lesson - used when lesson is "in progress"
	// order of checks:
	// 1. homeworks required
	// 2. tests that must be completed
	// 3. admin approval
	static function todo($lesson_id, $student_id) {
		global $wpdb;
		$todo_homeworks = $todo_exam = $todo_admin_approval = NULL;
		
		// for homeworks automatically detect if there is a post that contains [perception-assignments lesson_id="X"]
		// If yes, generate proper submit link instead of going into the admin
		// ORDER BY post_type DESC is very important because we want "post" and "page" to have preference over the 
		// automatically created post with post type "perception_homework"
		$homework_posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts}
			WHERE post_status = 'publish' AND post_date < NOW()
			AND post_content LIKE '%[perception-assignments%' ORDER BY post_type DESC, ID DESC"); 
		$post_found = null;
		foreach($homework_posts as $post) {
			if(stristr($post->post_content, '[perception-assignments lesson_id="'.$lesson_id.'"]') 
				or stristr($post->post_content, '[perception-assignments lesson_id='.$lesson_id.']')) {
				$post_found = $post->ID;
				break;
			}
		}	
		
		// todo homeworks
		$required_homeworks = get_post_meta($lesson_id, 'perception_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		if(!empty($required_homeworks)) {
			// select all completed homeworks of this student and see if all required are satisfied
			$completed_homeworks = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(homework_id) FROM ".
				PERCEPTION_STUDENT_HOMEWORKS." WHERE student_id=%d AND status='approved'", $student_id));
			$ids = array(0);
			foreach($completed_homeworks as $hw) $ids[] = $hw->homework_id;			
			$todo_homeworks = array();
			
			foreach($required_homeworks as $required_id) {
				if(!empty($required_id) and !in_array($required_id, $ids)) {
					$homework = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_HOMEWORKS." WHERE id=%d", $required_id));
					if(empty($homework->id)) continue;					
					
					// define the submit link
					if($post_found) {						
						$permalink = get_permalink($post_found);
				   	$params = array('id' => $homework->id, 'submit_solution' => 1);
						$target_url = add_query_arg( $params, $permalink );
						$homework->submit_link = $target_url;
					}
					else $homework->submit_link = admin_url("admin.php?page=perception_submit_solution&id=".$homework->id);					
					
					$todo_homeworks[] = $homework;
				}
			}			
		}
		
		// todo exam
		$use_exams = get_option('perception_use_exams');
		$todo_exam = PSPerceptionLMSLessonModel::todo_exam($lesson_id, $student_id, 'id');
		
		if(!empty($todo_exam)) {
			if($use_exams == 'watu') {
				$todo_exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watu_master WHERE ID=%d", $todo_exam));
				$codesearch = "[WATU ".$todo_exam->ID."]";
			}
			
			if($use_exams == 'watupro') {
				$todo_exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE ID=%d", $todo_exam));
				$codesearch = "[WATUPRO ".$todo_exam->ID."]";
			}
			
			// find the post to match it to the exam
			$post = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%$codesearch%' 
				AND post_status='publish' AND post_title!='' ORDER BY post_date DESC");
			$todo_exam->post_link = get_permalink(@$post->ID); 	
		}
		
		// admin approval?
		$todo_admin_approval = false;
		$lesson_completion = get_post_meta($lesson_id, 'perception_completion', true);	
		if(is_array($lesson_completion) and in_array('admin_approval', $lesson_completion)) $todo_admin_approval = true;
		
		// perception-mark button?
		$todo_mark = false;
		$lesson = get_post($lesson_id);
		if(strstr($lesson->post_content, '[perception-mark')) $todo_mark = true;
		
		// todo stuff from third party plugins?		
		$other_todo = apply_filters('perception-lesson-other-todo', '', $lesson_id, $student_id); 
		
		$nothing = false;
		if(empty($todo_homeworks) and empty($todo_exam) and empty($todo_admin_approval) and empty($todo_mark) and empty($other_todo)) $nothing = true;
		
		// allow filtering the exam
		$todo_exam = apply_filters('perception-todo-exam', $todo_exam, $lesson_id, $student_id);
		
		// return todo
		return array("todo_homeworks" => $todo_homeworks, "todo_exam" => $todo_exam, 
			"todo_admin_approval" => $todo_admin_approval, "todo_mark"=>$todo_mark, "todo_nothing"=>$nothing, "todo_other"=>$other_todo);
	}
	
	// small helper that returns either todo exams or just boolean whether there are any
	static function todo_exam($lesson_id, $student_id, $mode = 'boolean') {
		global $wpdb;
		
		$todo_exam = null;
		
		$use_exams = get_option('perception_use_exams');
		if(!empty($use_exams)) {
			$required_exam = get_post_meta($lesson_id, 'perception_required_exam', true);
			$required_grade = get_post_meta($lesson_id, 'perception_required_grade', true); // multiple grades in array
			if(!is_array($required_grade)) $required_grade = array($required_grade);
			
			if(!empty($required_exam)) {
				
				// see if there is taking record at all
				if($use_exams == 'watu') {
					$takings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watu_takings 
						WHERE user_id=%d AND exam_id=%d",$student_id, $required_exam));
				}
				if($use_exams == 'watupro') {
					$takings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams 
						WHERE user_id=%d AND exam_id=%d",$student_id, $required_exam));						
				}
				
				if(empty($takings)) {
					if($mode == 'boolean') return false; // no takings at all, exam is not taken
					
					// else add in todo
					$todo_exam = $required_exam;
				}
			}
			
			if(!empty($required_grade[0]) and !empty($required_exam) and empty($todo_exam)) {
				// let's make sure they have achieved the grade
				$achieved_grade = false;
				
				foreach($takings as $taking) {
					foreach($required_grade as $rgrade) {		
						// used to be grade title, now it's grade ID so we have to handle both									
						if(preg_match("/^".trim($rgrade)."<p/", trim($taking->result)) 
							or (trim($rgrade) == trim($taking->result))
							or (trim(strip_tags($rgrade)) == trim(strip_tags($taking->result)))
							or ($rgrade == $taking->grade_id)) {
							$achieved_grade = true;
							break;
						}
					}					
				}
				
				if(!$achieved_grade) {
					
					if($mode == 'boolean') return false;
					
					$todo_exam = $required_exam;
				}
			}
		}
		
		if($mode == 'boolean') return true;
		else return $todo_exam;
	}
	
	// this handler is called when someone submits watu or watupro exam
	// it takes care to complete a lesson
	// $plugin is the name of the exam plugin - for now watu or watupro
	static function exam_submitted($taking_id, $plugin) {		
		global $wpdb, $user_ID;
				
		// now select taking so we have full data and exam ID
		if($plugin == 'watu') {
			$taking = $wpdb->get_row( $wpdb->prepare("SELECT tT.*, tG.gtitle as grade_title 
				FROM {$wpdb->prefix}watu_takings tT LEFT JOIN {$wpdb->prefix}watu_grading tG ON tG.ID = tT.grade_id 
				WHERE tT.ID=%d", $taking_id));
		}
		if($plugin == 'watupro') {
			$taking = $wpdb->get_row( $wpdb->prepare("SELECT tT.*, tG.gtitle as grade_title  
			FROM {$wpdb->prefix}watupro_taken_exams tT LEFT JOIN {$wpdb->prefix}watupro_grading tG ON tG.ID = tT.grade_id 
			WHERE tT.ID=%d", $taking_id));
		}
		
		if(empty($taking->ID)) return false;
		
		// select all my todo lessons
		$my_todo_lessons = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".PERCEPTION_STUDENT_LESSONS." WHERE student_id=%d AND status=0", $user_ID));
		if(!sizeof($my_todo_lessons)) return false;
		
		$my_todo_lesson_ids = array();
		foreach($my_todo_lessons as $my) $my_todo_lesson_ids[] = $my->lesson_id;
				
		// get all lessons that this user reads, need to complete, and require this exam ID
		$args = array("meta_key" => 'perception_required_exam', 'meta_value'=>$taking->exam_id, 'post_type' => 'perception_lesson', 'posts_per_page' => -1);
		$lessons = get_posts( $args );
						
		// if is_ready complete the lesson
		$use_grading_system = get_option('perception_use_grading_system');
		foreach($lessons as $lesson) {			
			
			// transfer grade?
			if($use_grading_system and !empty($taking->grade_title)) {
				$transfer_grade = get_post_meta($lesson->ID, 'perception_watu_transfer_grade', true);
				if($transfer_grade == 1) {
					$grades = array_map('trim', explode(",", stripslashes(get_option('perception_grading_system'))));
					if(in_array($taking->grade_title, $grades)) {
						// assign grade to lesson in perception-student-lessons table
						$student_lesson_id = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_STUDENT_LESSONS."
							WHERE lesson_id=%d AND student_id=%d", $lesson->ID, $user_ID));
						if(empty($student_lesson_id)) continue;
						
						$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_STUDENT_LESSONS." SET grade=%s WHERE id=%d", 
							$taking->grade_title, $student_lesson_id));
					}
				}
			} // end transferring grade			
			
			if(!in_array($lesson->ID, $my_todo_lesson_ids)) continue;			
			
			if(self::is_ready($lesson->ID, $user_ID)) self::complete($lesson->ID, $user_ID);	
		} // end foreach lesson
	}	
	
	// the two functions below are actually called on add_action and then transfer the call to exam_submitted
	static function exam_submitted_watu($taking_id) {
		if(!is_user_logged_in()) return false;
		
		// are we using watu exams in Perception?
		if(get_option('perception_use_exams') != 'watu') return false;
		
		self::exam_submitted($taking_id, 'watu');
	}
	
	static function exam_submitted_watupro($taking_id) {
		if(!is_user_logged_in()) return false;
		
		// are we using watu exams in Perception?
		if(get_option('perception_use_exams') != 'watupro') return false;
		
		self::exam_submitted($taking_id, 'watupro');
	}
	
	// adds course column in manage lessons page
	static function manage_post_columns($columns) {
		// add this after title column 
		$final_columns = array();
		foreach($columns as $key=>$column) {			
			$final_columns[$key] = $column;
			if($key == 'title') {
				$final_columns['perception_course'] = get_option('perception_use_modules') ? __( 'Course / Module', 'perception' ) : __( 'Course', 'perception' );
				$final_columns['perception_lesson_visits'] = __( 'Visits (unique/total)', 'perception' );
			}
		}
		return $final_columns;
	}
	
	// actually displaying the course column value
	static function custom_columns($column, $post_id) {
		switch($column) {
			case 'perception_course':
				$course_id = get_post_meta($post_id, "perception_course", true);
				$course = get_post($course_id);
				echo '<a href="post.php?post='.$course_id.'&action=edit">'.stripslashes($course->post_title).'</a>';
				
				if(get_option('perception_use_modules')) {
				   // get module if any
				   $module_id = get_post_meta($post_id, "perception_module", true);				   
				   $module = get_post($module_id);
				   if(!empty($module->ID)) echo '<br /><a href="post.php?post='.$module_id.'&action=edit">'.stripslashes($module->post_title).'</a>';
				}
			break;
			case 'perception_lesson_visits':
				// get unique and total visits
				list($total, $unique) = PerceptionTrack::get_visits('lesson', $post_id);
				echo $unique.' / '.$total;
			break;
		}
	}
	
	static function restrict_visible_comments($comments) {
		global $post, $wpdb, $user_ID;
		
		if ( !is_singular() or is_admin() or $post->post_type != 'perception_lesson' or current_user_can('perception_manage')) return $comments;
			
		 if(!is_user_logged_in()) return null;
		 
		 // logged in, but is he enrolled in the course?
		 $_course = new PSPerceptionLMSCourseModel();
		 $course_id = get_post_meta($post->ID, 'perception_course', true);
		 $course = $_course -> select($course_id);
		 $enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $course_id));	
		 if(!$enrolled) return null;
		 
		 return $comments;	
	} // end restrict_visible_comments()
	
	// add courses to the homepage and archive listings
	static function query_post_type($query) {
		if(!get_option('perception_show_lessons_in_blog')) return $query;
		
		if ( (is_home() or is_archive()) and $query->is_main_query() ) {
			$post_types = @$query->query_vars['post_type'];
			
			// empty, so we'll have to create post_type setting			
			if(empty($post_types)) {
				if(is_home()) $post_types = array('post', 'perception_lesson');
				else $post_types = array('post', 'perception_lesson');
			}
			
			// not empty, so let's just add
			if(!empty($post_types) and is_array($post_types)) {
				$post_types[] = 'perception_lesson';				
				$query->set( 'post_type', $post_types );
			}
		}		
		return $query;
	}
	
	// show filter lessons by Course in admin
	static function restrict_manage_posts() {
		 global $typenow;		
	    global $wp_query;
	    
	    if ($typenow == 'perception_lesson') {
			  $use_modules = get_option('perception_use_modules');	    	
			  $onchange = '';
			  if($use_modules == 1) {
			  		$onchange = 'onchange="perceptionLoadModules(this.value);"';	 
			  }
	    	
	        $_course = new PSPerceptionLMSCourseModel();
	        $courses = $_course->select();
	        echo '<select name="perception_course_id" id="perception_course_id" '.$onchange.'>';
	        echo '<option value="">'.__('All Courses', 'perception').'</option>';
	        foreach($courses as $course) {
	        	  $selected = (!empty($_GET['perception_course_id']) and $_GET['perception_course_id'] == $course->ID) ? ' selected' : '';
	        	  echo '<option value="'.$course->ID.'"'.$selected.'>'.stripslashes($course->post_title).'</option>';
	        }
	        echo '</select>';
	        
	        if($use_modules == 1) {
	        		echo '<span id="perceptionModuleSelector">';
	        	   if(!empty($_GET['perception_course_id'])) {
	        	   	$_module = new PSPerceptionLMSModuleModel();
	        	   	$modules = $_module->select(0, $_GET['perception_course_id']);
	        	   	
	        	   	if(count($modules)) {
	        	   		 echo '<select name="perception_module_id" id="perception_module_id">';
					       echo '<option value="">'.__('All Modules', 'perception').'</option>';
					       foreach($modules as $module) {
					        	  $selected = (!empty($_GET['perception_module_id']) and $_GET['perception_module_id'] == $module->ID) ? ' selected' : '';
					        	  echo '<option value="'.$module->ID.'"'.$selected.'>'.stripslashes($module->post_title).'</option>';
					       }
					       echo '</select>';
						}
	        	   }
	        	   echo '</span>';
			  }
	    }
	} // end restrict manage posts
	
	// actually filter the lessons by course
	static function parse_admin_query($query) {
		 global $pagenow;
    	$type = 'perception_lesson';
	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
	    }
	    if ( 'perception_lesson' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['perception_course_id']) && $_GET['perception_course_id'] != '') {
	        $query->query_vars['meta_key'] = 'perception_course';
	        $query->query_vars['meta_value'] = $_GET['perception_course_id'];
	    }
	    
	     if ( 'perception_lesson' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['perception_module_id']) && $_GET['perception_module_id'] != '') {
	        $query->query_vars['meta_key'] = 'perception_module';
	        $query->query_vars['meta_value'] = $_GET['perception_module_id'];
	    }
	} // end parse_admin_query
	
	// shows Quiz Results link when there is an associated quiz
	static function quiz_results_link($actions, $post) {
		if ( $post->post_type != 'perception_lesson' ) {
        return $actions;
     }
     
		$use_exams = get_option('perception_use_exams');
		if(empty($use_exams)) return $actions;
		
		// is there associated exam for this lesson?
		$required_exam = get_post_meta($post->ID, 'perception_required_exam', true);
		if(empty($required_exam)) return $actions;
		
		// get course - we need it for the link
		$course_id = get_post_meta($post->ID, 'perception_course', true);
		
		if($use_exams == 'watu' and defined('WATU_QUIZ_WORD')) {
			$actions['perception-quiz-results'] = '<a href="admin.php?page=watu_takings&exam_id='.$required_exam.'&perception_course_id='.$course_id.'">'.sprintf(__('%s results', 'perception'), ucfirst(WATU_QUIZ_WORD)).'</a>';
		} 
		
		if($use_exams == 'watupro' and defined('WATUPRO_QUIZ_WORD')) {
			$actions['perception-quiz-results'] = '<a href="admin.php?page=watupro_takings&exam_id='.$required_exam.'&perception_course_id='.$course_id.'">'.sprintf(__('%s results', 'perception'), ucfirst(WATUPRO_QUIZ_WORD)).'</a>';
		}    

      return $actions;

	} // end quiz result link
}
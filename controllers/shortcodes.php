<?php
// various Perception shortcodes
class PSPerceptionLMSShortcodesController {
	// what's todo in a lesson or course
   static function todo($atts) {
   	global $post, $user_ID;
   	// accept ordered or unordered list as argument
   	if(!is_user_logged_in()) return "";
   	
		$post_type = empty($atts['post_type']) ? $post->post_type : $atts['post_type'];
		if(!in_array($post_type, array('perception_lesson', 'perception_course', 'perception_module'))) $post_type = $post->post_type;   	
		$post_id = empty($atts['post_id']) ? $post->ID : intval($atts['post_id']);
   	
   	if($post_type == 'perception_lesson') {   		
   		$todo = PSPerceptionLMSLessonModel :: todo($post_id, $user_ID);  
   		
   		$list_tag = empty($atts[0]) ? 'ol' : $atts[0];
	   	if($list_tag !='ul' && $list_tag != 'ol') $list_tag = 'ol';
   		 		
   		ob_start();   		
   		if(@file_exists(get_stylesheet_directory().'/perception/lesson-todo.php')) require get_stylesheet_directory().'/perception/lesson-todo.php';
			else require(PERCEPTION_PATH."/views/lesson-todo.php");
   		if(!empty($todo['todo_nothing'])) _e('This lesson has been completed.', 'perception');
   		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;		
   	}
   	
   	if($post_type == 'perception_module') {
   		$_module = new PSPerceptionLMSModuleModel();  		
   		$list_tag = empty($atts[0]) ? 'ol' : $atts[0];
	   	if($list_tag !='ul' && $list_tag != 'ol') $list_tag = 'ol';
   		 		
   		$required_lessons = $_module->required_lessons($post_id, $user_ID);
   		$required_lessons = apply_filters('perception-reorder-lessons', $required_lessons);	
   		$content = "";
   		
   		if(!empty($required_lessons)) {
   			$content .= "<".$list_tag." class='perception-list'>\n";
   			foreach($required_lessons as $lesson) {
   				$content .= "<li".($lesson->perception_completed?' class="perception-completed" ':' class="perception-incomplete" ')."><a href='".get_permalink($lesson->ID)."'>".$lesson->post_title."</a> - ";
					if($lesson->perception_completed) $content .= __('Completed', 'perception');
					else $content .= __('Not completed', 'perception');			
   				
   				$content .= "</li>\n";
   			}   			
   			$content .= "</".$list_tag.">";
   		}	
   		
   		return $content;
   	}
   	
   	
   	if($post_type == 'perception_course') {
   		$_course = new PSPerceptionLMSCourseModel();
   		
			$list_tag = empty($atts[0]) ? 'ul' : $atts[0];
   		if($list_tag !='ul' && $list_tag != 'ol') $list_tag = 'ol';
   		
   		$required_lessons = $_course->required_lessons($post_id, $user_ID);
   		$required_lessons = apply_filters('perception-reorder-lessons', $required_lessons);	
   		$content = "";
   		
   		if(!empty($required_lessons)) {
   			$content .= "<".$list_tag." class='perception-list'>\n";
   			foreach($required_lessons as $lesson) {
   				$content .= "<li".($lesson->perception_completed?' class="perception-completed" ':' class="perception-incomplete" ')."><a href='".get_permalink($lesson->ID)."'>".$lesson->post_title."</a> - ";
					if($lesson->perception_completed) $content .= __('Completed', 'perception');
					else $content .= __('Not completed', 'perception');			
   				
   				$content .= "</li>\n";
   			}   			
   			$content .= "</".$list_tag.">";
   		}
   		
   		return $content;
   	}
   } // end todo
   
   // display enroll button
   static function enroll($atts) {
   	global $wpdb, $user_ID, $user_email, $post;
   	
   	if(!is_user_logged_in()) {
   		return sprintf(__('You need to be <a href="%s">logged in</a> to enroll in this course', 'perception'), wp_login_url(get_permalink( $post->ID )));
   	}
   	
   	// role restriction?
   	$require_roles = get_post_meta($post->ID, 'perception_require_roles', true);
		$required_roles = get_post_meta($post->ID, 'perception_required_roles', true); // this is the array of roles
		if($require_roles == 1 and !empty($required_roles) and is_array($required_roles)) {
			$user = wp_get_current_user();
			$restricted = true;
			foreach($required_roles as $required_role) {
				if ( in_array( $required_role, (array) $user->roles ) )  {
					$restricted = false;
					break;
				}
			}
			
			if($restricted) return __('Your user role is not allowed to join this course.', 'perception');
		} // end role restriction check
   	
   	// passed course id?
   	if(!empty($atts['course_id'])) {
   		$post = get_post($atts['course_id']);
   	}
   	
   	$enrolled = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_COURSES.
			" WHERE user_id = %d AND course_id = %d", $user_ID, $post->ID));
	
		if(empty($enrolled->id)) {			
			$currency = get_option('perception_currency');
			$is_manager = current_user_can('perception_manage');
			$_course = new PSPerceptionLMSCourseModel();
						
			// stripe integration goes right on this page
			$accept_stripe = get_option('perception_accept_stripe');
			$accept_paypal = get_option('perception_accept_paypal');
			$accept_other_payment_methods = get_option('perception_accept_other_payment_methods');
			$accept_moolamojo = get_option('perception_accept_moolamojo');
			if($accept_stripe) $stripe = PerceptionStripe::load();
			else $stripe = '';
			
			if(!empty($_POST['stripe_pay'])) {
				 PerceptionStripe::pay($currency);			
				 perception_redirect(get_permalink($post->ID));
			}	
		
			if(!empty($_POST['enroll'])) {
				// in case we use several shortcodes on the page make sure only the right course action is executed
				if(empty($atts['course_id']) or $atts['course_id'] == $_POST['course_id']) {
					$mesage = PSPerceptionLMSCoursesController::enroll($is_manager);				
					perception_redirect(get_permalink($post->ID));
				}	
			}	
			
			$_course->currency = $currency;
			$_course->accept_other_payment_methods = $accept_other_payment_methods;
			$_course->accept_paypal = $accept_paypal;
			$_course->accept_stripe = $accept_stripe;
			$_course->accept_moolamojo = $accept_moolamojo;				
			$_course->stripe = $stripe;		
			wp_enqueue_script('thickbox',null,array('jquery'));
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');	 
			$post->post_id = $post->ID;
			$post->fee = get_post_meta($post->ID, 'perception_fee', true); 
			return $_course->enroll_buttons($post, $is_manager, $atts);
		}	
		else {
			$post->perception_course_status_shown = true;			
			
			switch($enrolled->status) {
				case 'enrolled': return __('You are enrolled in this course.', 'perception'); break;
				case 'pending': return __('Your enroll request is received. Waiting for manager approval.', 'perception'); break;
				case 'completed': return __('You have completed this course.', 'perception'); break;
				case 'rejected': return __('Your enrollment request is rejected.', 'perception'); break;
			}
		}
	}
	
	// display user points
	static function points($atts) {
		global $user_ID;
		$user_id = $user_ID;
		if(!empty($atts[0]) and is_numeric($atts[0])) $user_id = $atts[0];		
		
		$points = get_user_meta($user_id, 'perception_points', true);
		if(empty($points)) $points = 0;
		return $points;
	}
	
	// displays leaderboard by points
	static function leaderboard($atts) {
		global $wpdb;
		
		$num_users = @$atts[0];
		if(!is_numeric($num_users)) $num_users = 10;
		
		$display = empty($atts[1]) ? 'usernames' : 'table';		

		// select top users
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.*, tM.meta_value as perception_points FROM {$wpdb->users} tU JOIN {$wpdb->usermeta} tM
			ON tU.ID = tM.user_id AND tM.meta_key = 'perception_points'
			ORDER BY perception_points DESC LIMIT %d", $num_users));
		
		$html = "";
		if($display == 'usernames') {
			$html .= "<ol class='perception-leaderboard'>";
			foreach($users as $user) $html.="<li>".$user->user_nicename."</li>";
			$html .= "</ol>";
		}
		else {
			$html .= "<table class='perception-leaderboard'><tr><th>".__('User', 'perception')."</th><th>".__('Points', 'perception')."</th></tr>";
			foreach($users as $user) $html.="<tr><td>".$user->user_nicename."</td><td>".$user->perception_points."</td></tr>";
			$html .="</table>";
		}
		
		return $html;
	}
	
	// same as course lessons but passes is_module as true
	static function module_lessons($atts) {
		if(empty($atts) or !is_array($atts)) $atts = array();
		$atts['is_module'] = true;
		return self :: lessons($atts);
	}
	
	// display lessons in this course 
	// in table, just <ul>, or in user-defined HTML
	static function lessons($atts) {		
		global $post;
		
		$status = @$atts[0];		
		$course_id = empty($atts[1]) ? $post->ID : $atts[1];
		
		// however if the current post is module and not a course, we actually want to show other modules in the same course
		// similar to this, we may want to show the modules from the same course that a lesson belongs to on a lesson page.
		if(empty($atts[1]) and ('perception_module' == @$post->post_type or 'perception_lesson' == @$post->post_type) and empty($atts['is_module'])) {
			$course_id = get_post_meta($post->ID, 'perception_course', true);
		} 	
		// when we are on lesson page and looking for module lessons, course_id is actually the module_id
		if(empty($atts[1]) and  'perception_lesson' == @$post->post_type and !empty($atts['is_module'])) {
			$course_id = get_post_meta($post->ID, 'perception_module', true);
		} 			
		
		$ob = empty($atts[2]) ? '' : "tP.".$atts[2];
		$dir = empty($atts[3]) ? 'ASC' : $atts[3];
		$list_tag = empty($atts[4]) ? 'ul' : $atts[4];
		$show_excerpts = @$atts['show_excerpts'] ? true : false;
		$is_module = empty($atts['is_module']) ? false : true;	
		
		// validate the user input
		if($list_tag !='ul' && $list_tag != 'ol') {
			$list_tag = 'ul';
		}
				
		// are we in the course desc page or in a lesson of this course?
		$this_post = get_post($course_id);
		//if($this_post->post_type == 'perception_lesson' ) $course_id = get_post_meta($course_id, 'perception_course', true);
		
		// when status column is NOT passed we have a simple task and won't call the student_lessons() method
		// this is because the student_lessons() method is for logged in users only. 
		if(empty($status) or !is_user_logged_in()) {		   
			$_lesson = new PSPerceptionLMSLessonModel();

			$lessons = $_lesson->select($course_id, 'array', null, $ob, $dir, $is_module);
			
			$content = "<".$list_tag." class='perception-list'>";
			foreach($lessons as $lesson) {
				$content .= "<li><a href='".get_permalink($lesson->ID)."'>".stripslashes($lesson->post_title)."</a>";
				if($show_excerpts and !empty($lesson->post_excerpt)) $content .= wpautop($lesson->post_excerpt);
				$content .="</li>";
			}
			$content .= "</".$list_tag.">";
			return $content;
		}	
		
		// status column is requested so we'll have to call the model method		
		ob_start();
		$_GET['course_id'] = $course_id;
		$simplified = empty($status) ? 2 : 1; // simplified is always at least 1 when called as shortcode. But will be 2 if status column is not requested
		
		PSPerceptionLMSLessonModel :: student_lessons($simplified, $ob, $dir, true, $show_excerpts, $is_module, $atts);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}	

   // displays modules within a course
   static function modules($atts) {
      global $post;
		
		$status = @$atts[0];
		
		// assume we are on course page showing its modules, unless course ID is passed.		
		$course_id = empty($atts[1]) ? $post->ID : $atts[1];
		
		// however if the current post is module and not a course, we actually want to show other modules in the same course
		// similar to this, we may want to show the modules from the same course that a lesson belongs to on a lesson page.
		if(empty($atts[1]) and ('perception_module' == @$post->post_type or 'perception_lesson' == @$post->post_type)) {			
			$course_id = get_post_meta($post->ID, 'perception_course', true);
		} 		
		
				
		$ob = empty($atts[2]) ? '' : "tP.".$atts[2];
		$dir = empty($atts[3]) ? 'ASC' : $atts[3];
		$list_tag = empty($atts[4]) ? 'ul' : $atts[4];
		$show_excerpts = @$atts['show_excerpts'] ? true : false;
		
		// validate the user input
		if($list_tag !='ul' && $list_tag != 'ol') {
			$list_tag = 'ul';
		}
      
      // for this version let's keep it simple. Modules will be listed without status column
      $_module = new PSPerceptionLMSModuleModel();

		$modules = $_module->select(null, $course_id, $ob, $dir);
			
		$content = "<".$list_tag." class='perception-list'>";
		foreach($modules as $module) {
			$content .= "<li><a href='".get_permalink($module->ID)."'>".stripslashes($module->post_title)."</a>";
			if($show_excerpts and !empty($module->post_excerpt)) $content .= wpautop($module->post_excerpt);
			$content .="</li>";
		}
		$content .= "</".$list_tag.">";
		return $content;
   }   	
		
	// displays simplified version of "My Courses" page
	static function my_courses($atts) {
		if(!is_user_logged_in()) return __('This content is for logged in users.', 'perception');
		// call the simplified version
		ob_start();
		PSPerceptionLMSCoursesController::my_courses(true, $atts);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	// displays simplified version of "My Certificates" page
	static function my_certificates() {
		if(!is_user_logged_in()) return __('This content is for logged in users.', 'perception');
		// call the simplified version
		ob_start();
		PSPerceptionLMSCertificatesController::my_certificates(true);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	// selects the next lesson or module in the course if any
	static function next_lesson($atts) {		
		global $post, $wpdb;

		if(empty($post->ID) or ($post->post_type != 'perception_lesson' and $post->post_type != 'perception_module')) return "";
		$next_text = ($post->post_type == 'perception_lesson') ? __('next lesson', 'perception') : __('next module', 'mamaste');
		$text = empty($atts[0]) ? $next_text : $atts[0];
		$cls = empty($atts['class']) ? '' : sanitize_text_field($atts['class']);
		
		// select next lesson
		$course_id = get_post_meta($post->ID, 'perception_course', true);
		
		$next_lesson = $wpdb->get_row($wpdb->prepare("SELECT tP.* FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'perception_course'
			WHERE tP.post_type = %s AND tM.meta_value = %d AND tP.ID > %d
			AND tP.post_status = 'publish' ORDER BY tP.post_date, tP.ID", $post->post_type, $course_id, $post->ID));
		if(empty($next_lesson->ID)) return "";
		
		return "<a href='".add_query_arg('nmst', time(), get_permalink($next_lesson->ID))."' class='$cls'>$text</a>";	
	}
	
	// selects the previous lesson or module in the course if any
	static function prev_lesson($atts) {
		global $post, $wpdb;
		if(empty($post->ID) or ($post->post_type != 'perception_lesson' and $post->post_type != 'perception_module')) return "";
		$prev_text = ($post->post_type == 'perception_lesson') ? __('previous lesson', 'perception') : __('previous module', 'mamaste');
		$text = empty($atts[0]) ? $prev_text : $atts[0];
		$cls = empty($atts['class']) ? '' : sanitize_text_field($atts['class']);
		
		// select prev lesson
		$course_id = get_post_meta($post->ID, 'perception_course', true);
		$prev_lesson = $wpdb->get_row($wpdb->prepare("SELECT tP.* FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'perception_course'
			WHERE tP.post_type = %s AND tM.meta_value = %d AND tP.ID < %d
			AND tP.post_status = 'publish' ORDER BY tP.ID DESC", $post->post_type, $course_id, $post->ID));
			
		if(empty($prev_lesson->ID)) return "";
		
		return "<a href='".get_permalink($prev_lesson->ID)."' class='$cls'>$text</a>";	
	}
	
	// selects the first lesson in the course 
	static function first_lesson($atts) {
		global $post, $wpdb;
		if(empty($post->ID) or $post->post_type != 'perception_course') return "";
		$cls = empty($atts['class']) ? '' : sanitize_text_field($atts['class']);
		
		// select first lesson		
		$first_lesson = $wpdb->get_row($wpdb->prepare("SELECT tP.* FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'perception_course'
			WHERE tP.post_type = 'perception_lesson' AND tM.meta_value = %d AND tP.post_status = 'publish'
			ORDER BY tP.ID LIMIT 1", $post->ID));
			
		$text = empty($atts[0]) ? $first_lesson->post_title : $atts[0];	
			
		if(empty($first_lesson->ID)) return "";
		
		return "<a href='".get_permalink($first_lesson->ID)."' class='$cls'>$text</a>";	
	}	
	
	// display grade on a course
	static function grade($atts) {
		global $wpdb, $user_ID;
		
		$grade = '';
		$course_id = intval(@$atts['course_id']);
		if(empty($atts['userlogin'])) $user_id = $user_ID;
		else {
			$user = get_user_by('login', $atts['userlogin']);
			$user_id = $user->ID;
		}
	
		// select grade
		if(!empty($course_id)) {
			$grade = $wpdb->get_var($wpdb->prepare("SELECT grade FROM ".PERCEPTION_COURSES."
				WHERE course_id = %d AND user_id = %d", $course_id, $user_id));
		}
		
		// lesson selected?
		if(!empty($atts['lesson_id'])) {
			$grade = $wpdb->get_var($wpdb->prepare("SELECT grade FROM ".PERCEPTION_STUDENT_LESSONS."
				WHERE lesson_id = %d AND student_id = %d", intval($atts['lesson_id']), $user_id));
		}
			
		if($grade != '') return $grade;
		else return @$atts['whenempty'];	
	}
	
	// mark lesson completed
	static function mark() {
		global $wpdb, $post, $user_ID;
		
		if(!is_user_logged_in()) return "";
		
		// is the lesson in progress?
		$in_progress = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_STUDENT_LESSONS." 
			WHERE lesson_id=%d AND student_id=%d AND status!=1", $post->ID, $user_ID));
		if(!$in_progress) return '';
		
		// ready for completion?
		if(PSPerceptionLMSLessonModel :: is_ready($post->ID, $user_ID, false, true)) {
			// display button or mark as completed
			if(!empty($_POST['mark'])) {
				PSPerceptionLMSLessonModel :: complete($post->ID, $user_ID);		
				return __('Lesson completed!', 'perception');
			}
			else {
				return '<form method="post" action="">
				<p class="perception-mark-button"><input type="submit" name="mark" value="'.__('Mark as completed', 'perception').'"></p>
				</form>';
			}
		}	 
	} // end mark
	
	// lesson assignments
	static function assignments($atts) {
		global $user_ID, $post, $wpdb;
		
		if(!empty($atts['lesson_id'])) $_GET['lesson_id'] = intval($atts['lesson_id']);
		if(empty($_GET['lesson_id'])) $_GET['lesson_id'] = $post->ID;	
		$lesson_id = intval($_GET['lesson_id']);	
		
		// prepare arguments
		$_GET['student_id'] = $user_ID;
		ob_start();
		
		// can't access based on module restrictions?
		if(get_option('perception_use_modules')) {
		   // belongs to module?
		   $module_id = get_post_meta($lesson_id, 'perception_module', true);
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
	                	if(PSPerceptionLMSModuleModel :: is_ready($mid, $user_ID)) {
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
		       $content = __('You cannot see these assignments because there are unsatisfied module access requirements.', 'perception');
			    return $content;
         }	
		} // end if using modules
		
		// returning the view solutions page		
		if(!empty($_GET['view_solutions'])) {
		 	PSPerceptionLMSHomeworkController :: view(true);
		 	$content = ob_get_clean();
			return $content;
		}
		
		// returning submit solution page
		if(!empty($_GET['submit_solution'])) {
		 	PSPerceptionLMSHomeworkController :: submit_solution(true);
		 	$content = ob_get_clean();
			return $content;
		}
		
		// comments on assignment from Perception Connect
		if(!empty($_GET['connect_comments']) and class_exists('PerceptionConComments')) {
			PerceptionConComments :: comments(true);
		 	$content = ob_get_clean();
			return $content;
		}
		
		// add notes page
		if(!empty($_GET['add_note'])) {
			PSPerceptionLMSNoteModel :: add_note(true);
		 	$content = ob_get_clean();
			return $content;
		}
		
		// normally we return the homeworks
		PSPerceptionLMSHomeworkModel :: lesson_homeworks(true);
		$content = ob_get_clean();
		return $content;		
	}
	
	// shows the certificates earned in a course, if any
	static function earned_certificates($atts) {
		global $post, $user_ID;
		if(!is_user_logged_in()) return '';
		
		$course_id = empty($atts['course_id']) ? @$post->ID : intval($atts['course_id']);
		if(empty($course_id)) return '';
		
		$text = @$atts['text'];
		
		return PSPerceptionLMSCertificatesController :: my_course_certificates($course_id, $user_ID, $text);
	}
	
	// link to the course that lesson belongs to
	static function course_link($atts) {
		global $post;
		$lesson_id = empty($atts['lesson_id']) ? $post->ID : intval($atts['lesson_id']);
		$course_id = get_post_meta($lesson_id, 'perception_course', true);
		$course = get_post($course_id);
		$text = empty($atts['text']) ? stripslashes($course->post_title) : $atts['text'];
		
		return '<a href="'.get_permalink($course_id).'">' . $text . '</a>';
	}
	
	// conditional shortcode that allows displaying the enclosed content only when certain condition is / is not met
	static function condition($atts, $content = null) {
		if(isset($atts['is_enrolled'])) return PSPerceptionLMSCoursesController :: is_enrolled_shortcode($atts, $content);
	}
	
	// create search form with courses and lessons
	static function search($atts) {
		ob_start();
		PSPerceptionLMSSearchController :: form();
		$content = ob_get_clean();
		return $content;	
	}
	
	// outputs the total number of published courses available on the site
	static function num_courses() {
		global $wpdb;
		
		$num_courses = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts}
			WHERE post_type = 'perception_course' 
			AND (post_status='publish' OR post_status='private')");
			
		return $num_courses;	 
	}
	
	// outptus the total number of students in the site or num. students in a given course
	static function num_students($atts) {
		global $wpdb;
		
		$course_id_sql = '';
		if(!empty($atts['course_id']) and is_numeric($atts['course_id'])) {
			$course_id_sql = $wpdb->prepare(" AND tC.course_id = %d ", $atts['course_id']);
		}
		
		$num_students = $wpdb->get_var("SELECT COUNT(tC.id) FROM " . PERCEPTION_COURSES." tC
			JOIN {$wpdb->posts} tP ON tP.ID = tC.course_id AND tP.post_type = 'perception_course'
			AND (tP.post_status='publish' OR tP.post_status='private')
			WHERE tC.status = 'enrolled' $course_id_sql");
		
		return $num_students;	 
	} // end num_students
	
	// outputs the num lessons in a course
	static function num_lessons($atts) {
		global $wpdb;
		
		if(empty($atts['course_id']) or !is_numeric($atts['course_id'])) return "";
		
		$num_lessons = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tP.ID) as num_lessons
			FROM {$wpdb->posts} tP JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID
			AND tM.meta_key='perception_course' AND tM.meta_value=%d
			WHERE tP.post_type='perception_lesson' AND (tP.post_status='publish' OR tP.post_status='private')", $atts['course_id']));
		
		return $num_lessons;	
	} // end num_lessons
	
	// outputs the number of assignments total, or in course / lesson
	static function num_assignments($atts) {
		global $wpdb;
		
		$course_id_sql = '';
		if(!empty($atts['course_id']) and is_numeric($atts['course_id'])) {
			$course_id_sql = $wpdb->prepare(" AND course_id = %d ", $atts['course_id']);
		}
		
		$lesson_id_sql = '';
		if(!empty($atts['lesson_id']) and is_numeric($atts['lesson_id'])) {
			$lesson_id_sql = $wpdb->prepare(" AND lesson_id = %d ", $atts['lesson_id']);
		}
		
		$num_homeworks = $wpdb->get_var("SELECT COUNT(id) FROM ".PERCEPTION_HOMEWORKS." WHERE 1 $course_id_sql $lesson_id_sql");
		
		return $num_homeworks;
	} // end num_assignments
	
	// displays data from user profile of the currently logged user
	static function userinfo($atts) {
		global $user_ID;
		
		$user_id = empty($atts['user_id']) ? $user_ID : intval($atts['user_id']);	
			
		$field = $atts['field'];
			
		$user = get_userdata($user_id);
		
		if(isset($user->data->$field) and !empty($user->data->$field)) return $user->data->$field;
		if(isset($user->data->$field) and empty($user->data->$field)) return @$atts['default'];
		
		// not set? must be in meta then
		$metas = get_user_meta($user_id);		
		if(count($metas) and is_array($metas)) {
			foreach($metas as $key => $meta) {
				if($key == $field and !empty($meta[0])) return $meta[0];
				if($key == $field and empty($meta[0])) return @$atts['default'];
			}
		}
		
		// nothing found, return the default if any
		return @$atts['default'];
	}
	
	// view of the gradebook
	static function gradebook($atts) {
	   $course_id = intval($atts['course_id']);
	   $public = (!empty($atts['public_view']) and $atts['public_view'] == 'false') ? false : true;
	   ob_start();
	   PSPerceptionLMSGradebookController :: view($course_id, $public, $atts);
	   $content = ob_get_clean();
	   return $content;
	}
	
	// the shortcode generator
	static function generator() {
	   global $wpdb;
	   
	   // select courses
	   $_course = new PSPerceptionLMSCourseModel();
	   $courses = $_course->select();
	   
	   $use_modules = get_option('perception_use_modules');
	   if($use_modules == 1 and !empty($_POST['course_id'])) {
	      $_module = new PSPerceptionLMSModuleModel();
	      $modules = $_module->select(0, $_POST['course_id']);    
      }
	   
	  include(PERCEPTION_PATH . '/views/shortcode-generator.html.php');
	}
	
	static function my_gradebook($atts) {
		ob_start();
		$course_id = empty($atts['course_id']) ? 0 : intval($atts['course_id']);
		PSPerceptionLMSGradebookController :: my_gradebook($course_id, true);
		$content = ob_get_clean();
	   return $content;
	}
	
	// shows lesson status - not started, in progress or completed
	static function lesson_status($atts) {
		global $post, $user_ID, $wpdb;
		$lesson_id = empty($atts['lesson_id']) ? intval(@$post->ID) : intval($atts['lesson_id']);  
		
		if(empty($lesson_id) or empty($user_ID)) return __('Not started', 'perception');
		
		// select student to lesson relation
		$student_lesson = $wpdb->get_row($wpdb->prepare("SELECT id, status FROM ".PERCEPTION_STUDENT_LESSONS." 
			WHERE student_id=%d AND lesson_id=%d", $user_ID, $lesson_id));
			
		if(empty($student_lesson->id)) return __('Not started', 'perception');
		if(empty($student_lesson->status)) return __('In progress', 'perception');
		return __('Completed', 'perception');
	}
}
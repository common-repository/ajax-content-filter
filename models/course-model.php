<?php
// possible course statuses: pending, rejected, enrolled, completed
class PSPerceptionLMSCourseModel {	
	// custom post type Course	
	static function register_course_type() {
		
		$course_slug = get_option('perception_course_slug');
	   if(empty($course_slug)) $course_slug = 'perception-course';
	  	   
		$args = array(
			"label" => __("PSPerception Courses", 'perception'),
			"labels" => array
				(
					"name"=>__("PS Courses", 'perception'), 
					"singular_name"=>__("Course", 'perception'),
					"add_new_item"=>__("Add New Course", 'perception')
				),
			"public"=> true,
			"show_ui"=>true,
			"has_archive"=>true,
			"rewrite"=> array("slug"=>$course_slug, "with_front"=>false),
			"description"=>__("This will create a new course in your PSPerception LMS.",'perception'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats'),
			"taxonomies"=>array("category", 'post_tag'),
			"show_in_nav_menus" => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'show_in_menu' => 'perception_options',
			"register_meta_box_cb" => array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'perception_course', $args );
		register_taxonomy_for_object_type('category', 'perception_course');
	}
	
	// add courses to the homepage and archive listings
	static function query_post_type($query) {
		if(!get_option('perception_show_courses_in_blog')) return $query;
		
		if ( (is_home() or is_archive()) and $query->is_main_query() ) {
			$post_types = @$query->query_vars['post_type'];
			
			// empty, so we'll have to create post_type setting			
			if(empty($post_types)) {
				if(is_home()) $post_types = array('post', 'perception_course');
				else $post_types = array('post', 'perception_course');
			}
			
			// not empty, so let's just add
			if(!empty($post_types) and is_array($post_types)) {
				$post_types[] = 'perception_course';				
				$query->set( 'post_type', $post_types );
			}
		}		
		return $query;
	}
	
	static function meta_boxes() {
		add_meta_box("perception_meta", __("PSPerception Settings", 'perception'), 
							array(__CLASS__, "print_meta_box"), "perception_course", 'normal', 'high');				
	}
	
	static function print_meta_box($post) {
			global $wpdb, $wp_roles;
			$roles = $wp_roles->roles;
			
			// select lessons in this course
			$_lesson = new PSPerceptionLMSLessonModel();
			$lessons = $_lesson -> select($post->ID);
			$lessons = apply_filters('perception-reorder-lessons', $lessons);	
						
			// required lessons
			$required_lessons = get_post_meta($post->ID, 'perception_required_lessons', true);	
			if(!is_array($required_lessons)) $required_lessons = array();
			
			// enrollment - for now free or admin approved, in the future also paid
			$enroll_mode = get_post_meta($post->ID, 'perception_enroll_mode', true);
			
			$fee = get_post_meta($post->ID, 'perception_fee', true);
			$currency = get_option('perception_currency');
			
			$use_points_system = get_option('perception_use_points_system');
			$award_points = get_post_meta($post->ID, 'perception_award_points', true);
			if($award_points === '') $award_points = get_option('perception_points_course');
			
			$use_grading_system = get_option('perception_use_grading_system');
			if(!empty($use_grading_system)) {
				$auto_grade = get_post_meta($post->ID, 'perception_auto_grade', true);
			}
						
			// other courses
			$other_courses = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP			
			WHERE post_type = 'perception_course'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY post_title", $post->ID));

			// course will be accessible after these course(s) are completed			
			$course_access = get_post_meta($post->ID, 'perception_access', true);	
			if(!is_array($course_access)) $course_access = array();
			
			$unenroll_allowed = get_post_meta($post->ID, 'perception_unenroll', true);
			$register_enroll = get_post_meta($post->ID, 'perception_register_enroll', true);
			
			// required roles?
			$require_roles = get_post_meta($post->ID, 'perception_require_roles', true);
			$required_roles = get_post_meta($post->ID, 'perception_required_roles', true); // this is the array of roles
			
			// buddypress?
			if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {
				// select BP groups
				$bp_groups = BP_Groups_Group::get(array(
									'type'=>'alphabetical',
									'per_page'=>999
									));
									
				$bp = get_post_meta($post->ID, 'perception_buddypress', true);
				$bp_enroll_group = @$bp['enroll_group'];
				$bp_complete_group = @$bp['complete_group'];
				$bp_enroll_group_remove = @$bp['enroll_group_remove'];
				$bp_complete_group_remove = @$bp['complete_group_remove'];
			}			
			
			wp_nonce_field( plugin_basename( __FILE__ ), 'perception_noncemeta' );			  
			if(@file_exists(get_stylesheet_directory().'/perception/course-meta-box.php')) require get_stylesheet_directory().'/perception/course-meta-box.php';
			else require(PERCEPTION_PATH."/views/course-meta-box.php");
	}
	
	
	static function save_course_meta($post_id) {
			global $wpdb;			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  		if ( empty($_POST['perception_noncemeta']) or !wp_verify_nonce( $_POST['perception_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	 	 	if ('perception_course' != $_POST['post_type']) return;
			
			update_post_meta($post_id, "perception_enroll_mode", sanitize_text_field($_POST['perception_enroll_mode']));
			update_post_meta($post_id, "perception_required_lessons", perception_int_array($_POST['perception_required_lessons']));			
			update_post_meta($post_id, "perception_fee", floatval($_POST['perception_fee']));
			update_post_meta($post_id, "perception_access", perception_int_array($_POST['perception_access']));
			$unenroll = empty($_POST['perception_unenroll']) ? 0 : 1;
			update_post_meta($post_id, "perception_unenroll", $unenroll);
			if(isset($_POST['perception_award_points'])) update_post_meta($post_id, "perception_award_points", intval($_POST['perception_award_points']));
			$require_roles = empty($_POST['perception_require_roles']) ? 0 : 1;
			$required_roles = empty($_POST['perception_require_roles']) ? array() : esc_sql($_POST['perception_required_roles']);
			update_post_meta($post_id, "perception_require_roles", $require_roles);
			update_post_meta($post_id, "perception_required_roles", $required_roles);
			
			$use_grading_system = get_option('perception_use_grading_system');
			$auto_grade = empty($_POST['perception_auto_grade']) ? 0 : 1;
			if(!empty($use_grading_system)) update_post_meta($post_id, 'perception_auto_grade', $auto_grade);
			
			$register_enroll = empty($_POST['perception_register_enroll']) ? 0 : 1;
			update_post_meta($post_id, 'perception_register_enroll', $register_enroll);
			
			if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {
				$bp = array('enroll_group' => intval($_POST['perception_bp_enroll_group']), 'complete_group' => intval($_POST['perception_bp_complete_group']),
					'enroll_group_remove' => intval($_POST['perception_bp_enroll_group_remove']), 
					'complete_group_remove' => intval($_POST['perception_bp_complete_group_remove']) );
				update_post_meta($post_id, 'perception_buddypress', $bp);
			}
	}	
	
	// select existing courses
	function select($id = null) {
		global $wpdb;
		
		$id_sql = $id ? $wpdb->prepare(' AND ID = %d ', $id) : '';
		
		$courses = $wpdb->get_results("SELECT *, ID as post_id FROM {$wpdb->posts}
		WHERE post_type = 'perception_course'  AND (post_status='publish' OR post_status='draft')
		$id_sql ORDER BY post_title");
				
		if($id) return $courses[0];
		
		return $courses;	
	}
	
	// let's keep it simple for the moment - display text showing whether the user is enrolled or not
	static function enroll_text($content) {
		global $wpdb, $user_ID, $post;
				
		if(@$post->post_type != 'perception_course') return $content;
		
		// track the visit
		if(is_user_logged_in()) PerceptionTrack::visit('course', $post->ID, $user_ID);
		
		// if the shortcode is there don't show this
		if(strstr($content, '[perception-enroll]')) return $content;
		
		// enrolled? 
		$enrolled = false;
		if(is_user_logged_in()) {
			$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $post->ID));
		}			
		if($enrolled) $text = __('You are enrolled in this course. Check "My courses" link in your dashboard to see the lessons and to-do list', 'perception');
		else $text = PERCEPTION_NEED_LOGIN_TEXT_COURSE;
		
		$status_text = '';
		if(!empty($post->perception_course_status_shown)) $status_text = "<p>".$text."</p>";
		
		return $content.$status_text;		
	}
	
	// checks if all requirements for completion are satisfied
	function is_ready($course_id, $student_id) {
		$required_lessons = get_post_meta($course_id, 'perception_required_lessons', true);	
		if(!is_array($required_lessons)) $required_lessons = array();
		
		foreach($required_lessons as $lesson) {
			if(!PSPerceptionLMSLessonModel::is_completed($lesson, $student_id)) return false;
		}	
		
		// all completed, so it's ready
		return true;
	}
	
	// actually marks course as completed
	function complete($course_id, $student_id) {
		global $wpdb;
		
		$student_course = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".PERCEPTION_COURSES."
			WHERE course_id=%d AND user_id=%d", $course_id, $student_id));
		
		if(empty($student_course->id)) return false;
		
		// if the course is already completed, don't mark it again
		if($student_course->status == 'completed') return false;
		
		$course = get_post($course_id);
		
		$wpdb->query($wpdb->prepare("UPDATE ".PERCEPTION_COURSES." SET status = 'completed',
			completion_date = %s, completion_time=%s WHERE id=%d", 
			date("Y-m-d", current_time('timestamp')), current_time('mysql'), $student_course->id));
			
		// should we assign certificates?
		$_cert = new PSPerceptionLMSCertificateModel();
		$_cert -> complete_course($course_id, $student_id);
		
		// award points?
		$use_points_system = get_option('perception_use_points_system');
		if($use_points_system) {
			$award_points = get_post_meta($course_id, 'perception_award_points', true);
			if($award_points === '') $award_points = get_option('perception_points_course');
			if($award_points) {				
				PerceptionPoint :: award($student_id, $award_points, sprintf(__('Received %d points for completing course "%s".', 'perception'), 
					$award_points, $course->post_title, 'course', $course_id));
			}
		}
		
		// grade course
		PSPerceptionLMSGradebookController :: auto_grade_course($course_id, $student_id);
		
		// add custom action
		do_action('perception_completed_course', $student_id, $course_id);	
		
		// insert in history
	  $wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='completed_course', value=%s, num_value=%d, course_id=%d",
			$student_id, sprintf(__('Completed course "%s"', 'perception'), $course->post_title), $course_id, $course_id));
			
		// join BP group?
		if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {				
			$bp = get_post_meta($course_id, 'perception_buddypress', true);

			if(!empty($bp['complete_group'])) groups_join_group( $bp['complete_group'], $student_id);
			if(!empty($bp['complete_group_remove'])) groups_leave_group( $bp['complete_group_remove'],  $student_id  ); 

		}		
	}
	
	// returns all the required lessons along with mark whether they are completed or not
	function required_lessons($course_id, $student_id) {
		global $wpdb;

		$required_lessons_ids = get_post_meta($course_id, 'perception_required_lessons', true);	
		if(!is_array($required_lessons_ids) || empty($required_lessons_ids)) return array();
		
		$required_lessons = $wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE ID IN (".implode(",", $required_lessons_ids).") 
			AND (post_status='publish' OR post_status='private') ORDER BY ID");
		
		foreach($required_lessons as $cnt => $lesson) {
			$required_lessons[$cnt]->perception_completed = 0;
			if(PSPerceptionLMSLessonModel::is_completed($lesson->ID, $student_id)) $required_lessons[$cnt]->perception_completed = 1;
		}	
		return $required_lessons;
	}
	
	// enrolls or applies to enroll a course
	function enroll($student_id, $course_id, $status, $mass_enroll = false, $tags = '') {
		global $wpdb;
		
		// checks from other plugins, for example Perception PRO
		$no_access = $message = null;
		list($no_access, $message) = apply_filters('perception-course-access', array(false, ''), $student_id, $course_id);
		// echo $no_access.'a';
		if($no_access and empty($this->ignore_restrictions)) wp_die($message);
		
		// role restriction?
   	$require_roles = get_post_meta($course_id, 'perception_require_roles', true);
		$required_roles = get_post_meta($course_id, 'perception_required_roles', true); // this is the array of roles
		if($require_roles == 1 and !empty($required_roles) and is_array($required_roles)) {
			$user = wp_get_current_user();
			$restricted = true;
			foreach($required_roles as $required_role) {
				if ( in_array( $required_role, (array) $user->roles ) )  {
					$restricted = false;
					break;
				}
			}
			
			if($restricted) wp_die(__('Your user role is not allowed to join this course.', 'perception'));
		} // end role restriction check
		
		// check for course access requirements
		$course_access = get_post_meta($course_id, 'perception_access', true);

		if(!empty($course_access) and is_array($course_access)) {
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $student_id, $required_course));
				if(!$is_completed and empty($this->ignore_restrictions)) wp_die(__('You cannot enroll this course - other courses have to be completed first.', 'perception'));	
			}
		}
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_COURSES." SET
					course_id = %d, user_id = %d, status = %s, enrollment_date = %s, enrollment_time=%s,
					completion_date='1900-01-01', comments='', tags=%s",
					$course_id, $student_id, $status, date("Y-m-d", current_time('timestamp')), current_time('mysql'), $tags ) );
					
		if($result !== false) {
			if($mass_enroll) do_action('perception_admin_enrolled_course',  $student_id, $course_id, $status);
			else do_action('perception_enrolled_course', $student_id, $course_id, $status);
			
			// insert in history
			$course = get_post($course_id);
			$wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='enrolled_course', value=%s, num_value=%d, course_id=%d",
				$student_id, sprintf(__('Enrolled in course %s. Status: %s', 'perception'), $course->post_title, $status), $course_id, $course_id));
				
			// join BP group?
			if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {				
				$bp = get_post_meta($course_id, 'perception_buddypress', true);
				if(!empty($bp['enroll_group'])) groups_join_group( $bp['enroll_group'], $student_id);
				if(!empty($bp['enroll_group_remove'])) groups_leave_group( $bp['enroll_group_remove'],  $student_id  ); 
			}	
		}	// end success		
	}
	
	// auto enroll student in courses when they register to the site
	static function register_enroll($user_id) {
		global $wpdb;
		
		// user has allowed role?
		if(!user_can($user_id, 'perception')) return false;
		
		// get courses that have auto enroll on registration enabled
		$args = array( 'post_type' => 'perception_course', 'posts_per_page' => -1, 
			'meta_key' => 'perception_register_enroll', 'meta_value' => 1);
		$courses = get_posts($args);
		
		$_course = new PSPerceptionLMSCourseModel();
		$_course->ignore_restrictions = true;
		
		foreach($courses as $course) {
			// enroll status - pending or enrolled?	
			$enroll_mode = get_post_meta($course->ID, 'perception_enroll_mode', true);	
			$status = ($enroll_mode == 'manual') ? 'pending' : 'enrolled';
			
			$_course->enroll($user_id, $course->ID, $status);
		}	 
	} // end register_enroll
	
	// displays enroll buttons
	// @param $course - the course to enroll in
	// @param $is_manager (boolean) - whether the user manages the LMS
	// @param $atts - additional attributes. When available they usually come from shortcode calling
	function enroll_buttons($course, $is_manager, $atts = null) {
		global $user_ID;
		
		$currency = $this->currency;
		$accept_other_payment_methods = $this->accept_other_payment_methods;
		$accept_paypal = $this->accept_paypal;
		$accept_stripe = $this->accept_stripe;		
		$accept_moolamojo = $this->accept_moolamojo;
		$stripe = $this->stripe;
		
		// school account signup?
		$is_school = empty($atts['is_school']) ? 0 : 1;
		
		// checked for prerequisites
		list($can_enroll, $enroll_prerequisites) = $this->enroll_prerequisites($course);
		
		// can't enroll?
		if(empty($can_enroll)) {
			return $enroll_prerequisites;
		}			
		
		$output = '';	
		if(!empty($course->fee)) $course->fee = apply_filters('perception-coupon-applied', $course->fee, $course->post_id); // coupon code from other plugin?
		
		// handle school account price (schools management is a pro feature)
		if(class_exists('PerceptionPROSchool') and $is_school) $course->fee = PerceptionPROSchool :: school_price('course', $course);
		
		$paid_button_text =  sprintf(__('Enroll for %1$s %2$s', 'perception'), $currency, @$course->fee);
		$free_button_text = __('Click to Enroll', 'perception');
		if(!empty($atts['paid_button_text'])) $paid_button_text =  @sprintf($atts['paid_button_text'], $currency, $course->fee);
		if(!empty($atts['free_button_text'])) $free_button_text = $atts['free_button_text'];
						
		if(!empty($course->fee) and !$is_manager) {	
			// coupon codes and discount filters from other plugins
			$output = apply_filters('perception-coupon-form', $output, $course->post_id);		
			
			// Allow Pro or other third party plugin to skip displaying the buttons. 
			// If content contains comment "<!--PERCEPTION-RETURN-OUTPUT-->", strip it and return
			if(strstr($output, '<!--PERCEPTION-RETURN-OUTPUT-->')) {
				$output = str_replace('<!--PERCEPTION-RETURN-OUTPUT-->', '', $output);
				return $output;
			}				
				
			if($accept_paypal or $accept_other_payment_methods or $accept_moolamojo) { 
				$url = admin_url("admin-ajax.php?action=perception_ajax&type=course_payment");
				$box_title = __('Payment for course', 'perception');
				$output .= "<strong><a href='#' onclick=\"perceptionEnrollCourse('".$box_title."', ".$course->post_id.", ".$user_ID.", '".$url."', ".$is_school.");return false;\">".$paid_button_text."</a></strong>"; 
			}
			if($accept_stripe) {
				$output .= '<form method="post">
				  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
				          data-key="'.$stripe['publishable_key'].'"
				          data-amount="'.($course->fee*100).'" data-description="'.$course->post_title.'" data-currency="'.$currency.'"></script>
				<input type="hidden" name="stripe_pay" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">';
				if(!empty($is_school)) $output .= '<input type="hidden" name="is_school" value="1">';
				$output .= '</form>';
			} // end if accept stripe
		}	
		else {
			$output .= '<form method="post">
				<input type="submit" value="'.$free_button_text.'">
				<input type="hidden" name="enroll" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">
			</form>';				
		}  
		
		return $output;
	} // end enroll buttons
	
	// adds visits column in manage courses page
	static function manage_post_columns($columns) {
		// add this after title column 
		$final_columns = array();
		foreach($columns as $key=>$column) {			
			$final_columns[$key] = $column;
			if($key == 'title') {				
				$final_columns['perception_course_visits'] = __( 'Visits (unique/total)', 'perception' );
			}
		}
		return $final_columns;
	}
	
	// actually displaying the course column value
	static function custom_columns($column, $post_id) {
		switch($column) {			
			case 'perception_course_visits':
				// get unique and total visits
				list($total, $unique) = PerceptionTrack::get_visits('course', $post_id);
				echo $unique.' / '.$total;
			break;
		}
	}
	
	// check course pre-requisites
	// returns array($can_enroll, $enroll_prerequisites)
	function enroll_prerequisites($course) {
		global $wpdb, $user_ID;
		// can enroll? or are there unsatisfied pre-requisites
		$can_enroll = true;		
		$enroll_prerequisites = '';
		// check for course access requirements
		$course_access = get_post_meta($course->post_id, 'perception_access', true);
		
		if(!empty($course_access) and is_array($course_access)) {
			$enroll_prerequisites = __('These courses should be completed before you can enroll:', 'perception');
			
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".PERCEPTION_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $user_ID, $required_course));
				if(!$is_completed) {
					$can_enroll = false; // even one failed is enough;
					$required_course_post = get_post($required_course);
					$enroll_prerequisites .= ' <b>' . $required_course_post->post_title. '</b>;';
				}
			} // end foreach course access
		}
		
		return array($can_enroll, $enroll_prerequisites);
	} // end enroll_prerequisites()
	
	// add "Manage lessons" & "Manage Modules" links in admin
	static function post_row_actions($actions, $post) {
		if($post->post_type == 'perception_course') {
			$use_modules = get_option('perception_use_modules');
			if($use_modules) {
				$url = admin_url( 'edit.php?s&post_status=all&post_type=perception_module&perception_course_id='.$post->ID );
				$actions['perception_manage_modules'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( __( 'Manage Modules', 'perception' ) ) );
			}			
			
			$url = admin_url( 'edit.php?s&post_status=all&post_type=perception_lesson&perception_course_id='.$post->ID );
			$actions['perception_manage_lessons'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( __( 'Manage Lessons', 'perception' ) ) );
		}
		
		 return $actions;
	} // end post_row_actions
	
	// Konnichiwa & Konnichiwa Pro Integration: check access to enroll course
	static function konnichiwa_access($course_id) {
		global $wpdb, $user_ID;
		$course = get_post($course_id);		
		$can_enroll = true;
		
		if(class_exists('KonnichiwaContent')) {
			$_content = new KonnichiwaContent();
			$access = $_content->get_access($course);
			if(!empty($access)) {
				if($access !== 'registered') {
					$can_enroll = false;
					$plans = explode("|", $access);
					$plans = array_filter($plans);
					if(empty($plans)) return $content;
					
					// get active user plans
					$subs = $wpdb->get_results($wpdb->prepare("SELECT plan_id FROM ".KONN_SUBS."
						WHERE user_id=%d AND expires >= CURDATE() AND status=1", $user_ID));
					 	
					foreach($subs as $sub) {
						// if even one is found we're all ok to return the content
						if(in_array($sub->plan_id, $plans)) $can_enroll = true;
					}	
					
					// no subs at all?
					if(!count($subs)) $can_enroll = false;
				} // end if $access != 'registered'
			}	 // end if !empty($access)
		} // end Konnichiwa PRO
		
		if(class_exists('KonnichiwaProContent')) {
			$_content = new KonnichiwaProContent();
			$access = $_content->get_access($course);
			if(!empty($access)) {				
				if($access !== 'registered') {				
					$can_enroll = false;					
					
					$plans = explode("|", $access);				
					$plans = array_filter($plans);
					if(empty($plans)) return $content;
					
					if(!is_numeric(end($plans))) {
						$access_after = array_pop($plans);
						$access_after = str_replace('access-after-', '', $access_after);
					} 
					
					// get active user plans
					$subs = $wpdb->get_results($wpdb->prepare("SELECT id, plan_id, date FROM ".KONPRO_SUBS."
						WHERE user_id=%d AND expires >= CURDATE() AND status=1 ORDER BY date", $user_ID));
					 	
					foreach($subs as $sub) {
						// if even one is found we're all ok to return the content
						if(in_array($sub->plan_id, $plans)) {
							// is there drip / delayed access defined?						
							if(!empty($access_after)) {						
								$target_time = strtotime($sub->date) + 24 * 3600 * $access_after;
								if($target_time > current_time('timestamp')) {
									$can_enroll = false;
									break;
								}
							} // end delayed access check
							
							// found, so true:	
							$can_enroll = true;					
						}
					}	
					
					// no subscriptions found?
					if(!count($subs)) $can_enroll = false;
				} // end if $access != 'registered'
			}	 // end if !empty($access)
		} // end Konnichiwa PRO
		
		return $can_enroll;
	} // end konnichiwa_access
}
<?php
// main model containing general config and UI functions
class PSPerceptionLMS {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	$old_version = get_option('perception_version');
   	update_option( 'perception_version', "1.47");
   	if(!$update) self::init();
	  
	  // enrollments to courses
   	if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_COURSES."'") != PERCEPTION_COURSES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_COURSES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`course_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`user_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` VARCHAR(255) NOT NULL DEFAULT '',
					`enrollment_date` DATE NOT NULL DEFAULT '2000-01-01',			
					`completion_date` DATE NOT NULL DEFAULT '2000-01-01',
					`comments` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
		 // relations to modules
   	if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_STUDENT_MODULES."'") != PERCEPTION_STUDENT_MODULES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_STUDENT_MODULES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`module_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` VARCHAR(255) NOT NULL DEFAULT '',
					`enrollment_date` DATE NOT NULL DEFAULT '2000-01-01',			
					`completion_date` DATE NOT NULL DEFAULT '2000-01-01',
					`comments` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }	  
	  
	  
	  // assignments - let's not use custom post type for this
	  if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_HOMEWORKS."'") != PERCEPTION_HOMEWORKS) {        
			$sql = "CREATE TABLE `" . PERCEPTION_HOMEWORKS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`course_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`title` VARCHAR(255) NOT NULL DEFAULT '',
					`description` TEXT NOT NULL,
					`accept_files` TINYINT NOT NULL DEFAULT 0 /* zip only */
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // student - assignments relation
		if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_STUDENT_HOMEWORKS."'") != PERCEPTION_STUDENT_HOMEWORKS) {        
			$sql = "CREATE TABLE `" . PERCEPTION_STUDENT_HOMEWORKS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` VARCHAR(255) NOT NULL DEFAULT '',
					`date_submitted` DATE NOT NULL DEFAULT '2000-01-01',
					`content` TEXT NOT NULL,
					`file` VARCHAR(255) NOT NULL DEFAULT ''
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // file uploads to homework solutions
		if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_SOLUTION_FILES."'") != PERCEPTION_SOLUTION_FILES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_SOLUTION_FILES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`solution_id` INT UNSIGNED NOT NULL DEFAULT 0,	
					`file` VARCHAR(255) NOT NULL DEFAULT '',
					`fileblob` LONGBLOB
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
			  
	  // assignment notes (usually used as feedback from the teacher to the student. Student can't reply)
		if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_HOMEWORK_NOTES."'") != PERCEPTION_HOMEWORK_NOTES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_HOMEWORK_NOTES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`teacher_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`note` TEXT NOT NULL,
					`datetime` DATETIME NOT NULL DEFAULT '2000-01-01'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  // student to lessons relation - only save record if student has completed a lesson
		if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_STUDENT_LESSONS."'") != PERCEPTION_STUDENT_LESSONS) {        
			$sql = "CREATE TABLE `" . PERCEPTION_STUDENT_LESSONS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` INT UNSIGNED NOT NULL DEFAULT 0,
					`completion_date` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_CERTIFICATES."'") != PERCEPTION_CERTIFICATES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_CERTIFICATES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_ids` VARCHAR(255) NOT NULL DEFAULT '',
				  `title` VARCHAR(255) NOT NULL DEFAULT '',
				  `content` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_STUDENT_CERTIFICATES."'") != PERCEPTION_STUDENT_CERTIFICATES) {        
			$sql = "CREATE TABLE `" . PERCEPTION_STUDENT_CERTIFICATES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `certificate_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `student_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2000-01-01'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	 
	  // payment records	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_PAYMENT_METHOD."'") != PERCEPTION_PAYMENT_METHOD) {        
			$sql = "CREATE TABLE `" . PERCEPTION_PAYMENT_METHOD . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `amount` DECIMAL(8,2),
				  `status` VARCHAR(100) NOT NULL DEFAULT 'failed',
				  `paycode` VARCHAR(100) NOT NULL DEFAULT '',
				  `paytype` VARCHAR(100) NOT NULL DEFAULT 'paypal'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 
	 
	   // tracks the visits on a give course or lesson
	   // 1 record per user/date
	   if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_VISITS."'") != PERCEPTION_VISITS) {        
			$sql = "CREATE TABLE `" . PERCEPTION_VISITS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `visits` INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 	 
	  
	  // history of various actions, for example points awarded and spent
	   if($wpdb->get_var("SHOW TABLES LIKE '".PERCEPTION_HISTORY."'") != PERCEPTION_HISTORY) {        
			$sql = "CREATE TABLE `" . PERCEPTION_HISTORY . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `datetime` DATETIME,
				  `action` VARCHAR(255) NOT NULL DEFAULT '',
				  `value` VARCHAR(255) NOT NULL DEFAULT '', /* some textual value if required */
				  `num_value` INT UNSIGNED NOT NULL DEFAULT 0 /* some numeric value, for example points */
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  		  
	  
	  // add extra fields in new versions
	  perception_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),	  
		  array("name"=>"fileblob", "type"=>"LONGBLOB"),
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* points earned */
	  ), PERCEPTION_STUDENT_HOMEWORKS);
	  
	   perception_add_db_fields(array(
		  array("name"=>"filepath", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
	  ), PERCEPTION_SOLUTION_FILES);
	  
	  
	   perception_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),	  
		  array("name"=>"enrollment_time", "type"=>"DATETIME"),
		  array("name"=>"completion_time", "type"=>"DATETIME"),
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* cumulative points from  the course itself, lessons and homeworks */
		  array("name"=>"tags", "type"=>"TEXT"), /* Allow to tag each student to course enrollment, for example by year or source, etc*/
	  ), PERCEPTION_COURSES);
	  
	   perception_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
		  array("name"=>"start_time", "type"=>"DATETIME"),
		  array("name"=>"completion_time", "type"=>"DATETIME"),	  
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* cumulative points from the lesson itself and homeworks */
		  array("name"=>"pending_admin_approval", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* all other is done, the lesson relation is pending admin approval */ 
	  ), PERCEPTION_STUDENT_LESSONS);
	  
	  perception_add_db_fields(array(
		  array("name"=>"award_points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), 
		  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"limit_by_date", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"accept_date_from", "type"=>"DATE"),
		  array("name"=>"accept_date_to", "type"=>"DATE"),
		  array("name"=>"auto_grade_lesson", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	  ), PERCEPTION_HOMEWORKS);
	  
	  perception_add_db_fields(array(		   
		  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"has_expiration", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),	      		
    		array("name"=>"expiration_period", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* SQL-friendly text like "3 month" or "1 year"*/
    		array("name"=>"expired_message", "type"=>"TEXT"),	   
    		array("name"=>"expiration_mode", "type"=>"VARCHAR(255) NOT NULL DEFAULT 'period'"), /* period or date */
    		array("name"=>"expiration_date", "type"=>"DATE"), /* when expiration_mode='date' */   		
	  ), PERCEPTION_CERTIFICATES);
	  
	  perception_add_db_fields(array(		   
		  array("name"=>"for_item_type", "type"=>"VARCHAR(100) NOT NULL DEFAULT '' "), /* when awarding points etc to know is it course or lesson etc */
		  array("name"=>"for_item_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* when awarding points etc to know the id of the course or lesson etc */
		  array("name"=>"group_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* from classes in Perception PRO when available */
		  array("name"=>"course_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* any activity happens within a course */
		  array("name"=>"module_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* to handle the future modules */
	  ), PERCEPTION_HISTORY);
	  
	  // add student role if not exists
    $res = add_role('student', 'Student', array(
          'read' => true, // True allows that capability
          'perception' => true));   
    if(!$res) {
    	// role already exists, check the capability
    	$role = get_role('student');
    	if(!$role->has_cap('perception')) $role->add_cap('perception');
    }          	
    
    // add manage cap to the admin / superadmin by default
    $role = get_role('administrator');
    if(!$role->has_cap('perception_manage')) $role->add_cap('perception_manage');
    
    // update fileblob
    if($old_version < 1.27) {
    	$wpdb->query("ALTER TABLE ".PERCEPTION_STUDENT_HOMEWORKS." CHANGE fileblob fileblob LONGBLOB");
    }
    
    // fush rewrite rules
    PSPerceptionLMSCourseModel::register_course_type();
    PSPerceptionLMSLessonModel::register_lesson_type();
    PSPerceptionLMSModuleModel::register_module_type();
    PSPerceptionLMSHomeworkModel::register_homework_type();
    flush_rewrite_rules();	  	  
	  // exit;
   }
   
   // main menu
   static function menu() {   	  	
		$perception_cap = current_user_can('perception_manage') ? 'perception_manage' : 'perception';   	
		$use_grading_system = get_option('perception_use_grading_system');
		$homework_menu = $students_menu = $certificates_menu = $gradebook_menu = $settings_menu = $massenroll_menu = $help_menu = $plugins_menu = true;
		if(!current_user_can('administrator') and current_user_can('perception_manage')) {
			// perform these checks only for managers that are not admins, otherwise it's pointless use of resourses
			global $user_ID, $wp_roles;
			$role_settings = unserialize(get_option('perception_role_settings'));
			$roles = $wp_roles->roles;
			// get all the currently enabled roles
			$enabled_roles = array();
			foreach($roles as $key => $role) {
				$r=get_role($key);
				if(!empty($r->capabilities['perception_manage'])) $enabled_roles[] = $key;
			}
					
			// admin can do everything					
			$user = new WP_User( $user_ID );
			$homework_menu = PSPerceptionLMSMultiUser :: item_access('homework_access', $role_settings, $user, $enabled_roles); 
			$students_menu = PSPerceptionLMSMultiUser :: item_access('students_access', $role_settings, $user, $enabled_roles);
			$massenroll_menu = PSPerceptionLMSMultiUser :: item_access('mass_enroll_access', $role_settings, $user, $enabled_roles);
			$certificates_menu = PSPerceptionLMSMultiUser :: item_access('certificates_access', $role_settings, $user, $enabled_roles);
			$gradebook_menu = PSPerceptionLMSMultiUser :: item_access('gradebook_access', $role_settings, $user, $enabled_roles);
			$settings_menu = PSPerceptionLMSMultiUser :: item_access('settings_access', $role_settings, $user, $enabled_roles);			
			$help_menu = PSPerceptionLMSMultiUser :: item_access('help_access', $role_settings, $user, $enabled_roles);
			$plugins_menu = PSPerceptionLMSMultiUser :: item_access('plugins_access', $role_settings, $user, $enabled_roles);
		}
		
		// if a manager has no access to the settings page, let's turn the to-do into the main menu
		if($settings_menu) {
			add_menu_page(__('PSPerception LMS', 'perception'), __('PSPerception LMS', 'perception'), "perception_manage", "perception_options", array(__CLASS__, "options"));
			add_submenu_page('perception_options', __("PS To Do", 'perception'), __("PS To Do", 'perception'), 'perception_manage', 'perception_todo', array('PerceptionToDo', "manager_todo"));
		}
		else {
			add_menu_page(__('PSPerception LMS', 'perception'), __('PSPerception LMS', 'perception'), "perception_manage", "perception_options", array('PerceptionToDo', "manager_todo"));
			add_submenu_page('perception_options', __("PS To Do", 'perception'), __("PS To Do", 'perception'), 'perception_manage', 'perception_options', array('PerceptionToDo', "manager_todo"));
		}
   	   		
		if($homework_menu) add_submenu_page('perception_options', __("PS Assignments", 'perception'), __("PS Assignments", 'perception'), 'perception_manage', 'perception_homeworks', array('PSPerceptionLMSHomeworkModel', "manage"));
		if($students_menu) add_submenu_page('perception_options', __("PS Students", 'perception'), __("PS Students", 'perception'), 'perception_manage', 'perception_students', array('PSPerceptionLMSStudentModel', "manage"));		
		if($certificates_menu) {
			add_submenu_page('perception_options', __("PS Certificates", 'perception'), __("PS Certificates", 'perception'), 'perception_manage', 'perception_certificates', array('PSPerceptionLMSCertificatesController', "manage"));
			add_submenu_page(NULL, __("PS Students Earned Certificate", 'perception'), __("PS Students Earned Certificate", 'perception'), 'perception_manage', 'perception_student_certificates', array('PSPerceptionLMSCertificatesController', "student_certificates"));			
		}
		if($gradebook_menu and !empty($use_grading_system)) add_submenu_page('perception_options', __("PS Gradebook", 'perception'), __("PS Gradebook", 'perception'), 'perception_manage', 'perception_gradebook', array('PSPerceptionLMSGradebookController', "manage"));
		if($settings_menu) add_submenu_page('perception_options', __("PS Settings", 'perception'), __("PS Settings", 'perception'), 'perception_manage', 'perception_options', array(__CLASS__, "options"));     


		if($help_menu) add_submenu_page('perception_options', __("PS Help", 'perception'), __("PS Help", 'perception'), 'perception_manage', 'perception_help', array(__CLASS__, "help"));        
		
   		
		// not visible in menu
		add_submenu_page( NULL, __("PS Student Lessons", 'perception'), __("PS Student Lessons", 'perception'), $perception_cap, 'perception_student_lessons', array('PSPerceptionLMSLessonModel', "student_lessons"));
		add_submenu_page( NULL, __("PS Homeworks", 'perception'), __("PS Homeworks", 'perception'), $perception_cap, 'perception_lesson_homeworks', array('PSPerceptionLMSHomeworkModel', "lesson_homeworks"));
		add_submenu_page( NULL, __("PS Send note", 'perception'), __("PS Send note", 'perception'), 'perception_manage', 'perception_add_note', array('PSPerceptionLMSNoteModel', "add_note"));
		add_submenu_page( NULL, __("PS Submit solution", 'perception'), __("PS Submit solution", 'perception'), $perception_cap, 'perception_submit_solution', array('PSPerceptionLMSHomeworkController', "submit_solution"));
		add_submenu_page( NULL, __("PS View solutions", 'perception'), __("PS View solutions", 'perception'), $perception_cap, 'perception_view_solutions', array('PSPerceptionLMSHomeworkController', "view"));
		add_submenu_page( NULL, __("PS View all solutions", 'perception'), __("PS View all solutions", 'perception'), 'perception_manage', 'perception_view_all_solutions', array('PSPerceptionLMSHomeworkController', "view_all"));
		add_submenu_page( NULL, __("PS View Certificate", 'perception'), __("PS View Certificate", 'perception'), $perception_cap, 'perception_view_certificate', array('PSPerceptionLMSCertificatesController', "view_certificate"));
		add_submenu_page( NULL, __("PS Download solution", 'perception'), __("PS Download solution", 'perception'), $perception_cap, 'perception_download_solution', array('PSPerceptionLMSHomeworkController', "download_solution"));
		add_submenu_page( 'perception_options', __("PS Multi user configuration", 'perception'), __("PS Multi user configuration", 'perception'), 'manage_options', 'perception_multiuser', array('PSPerceptionLMSMultiUser', "manage"));
		if($massenroll_menu) add_submenu_page( NULL, __("PS Mass enroll students", 'perception'), __("PS Mass enroll students", 'perception'), 'perception_manage', 'perception_mass_enroll', array('PSPerceptionLMSCoursesController', "mass_enroll"));
		add_submenu_page( 'perception_options', __("PS Shortcode generator", 'perception'), __("PS Shortcode generator", 'perception'), 'perception_manage', 'perception_shortcode_generator', array('PSPerceptionLMSShortcodesController', "generator"));
		
		do_action('perception_lms_admin_menu');
		
		// should we display "My Courses" link?
		if(current_user_can('perception_manage') and !current_user_can('administrator')) {
			$role_settings = unserialize(get_option('perception_role_settings'));
			$current_user = wp_get_current_user();
			$role = array_shift($current_user->roles);
			if(!empty($role_settings[$role]['no_mycourses'])) $dont_show_mycourses = true;			
		}
		
		if(empty($dont_show_mycourses)) {
			// student menu
			$menu = add_menu_page(__('PS My Courses', 'perception'), __('PS My Courses', 'perception'), $perception_cap, "perception_my_courses", array('PSPerceptionLMSCoursesController', "my_courses"));
				add_submenu_page('perception_my_courses', __("PS My Certificates", 'perception'), __("PS My Certificates", 'perception'), $perception_cap, 'perception_my_certificates', array('PSPerceptionLMSCertificatesController', "my_certificates"));
				if(!empty($use_grading_system)) add_submenu_page('perception_my_courses', __("PS My Gradebook", 'perception'), __("PS My Gradebook", 'perception'), $perception_cap, 'perception_my_gradebook', array('PSPerceptionLMSGradebookController', "my_gradebook"));
		}		
			
		do_action('perception_lms_user_menu');	
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'perception-css', PERCEPTION_URL.'css/main.css?v=1');
	  wp_enqueue_style( 'perception-css' );
   
   	wp_enqueue_script('jquery');
	   
	   // Perception's own Javascript
		wp_register_script(
				'perception-common',
				PERCEPTION_URL.'js/common.js',
				false,
				'0.1.0',
				false
		);
		wp_enqueue_script("perception-common");
		
		$translation_array = array('ajax_url' => admin_url('admin-ajax.php'),
		'all_modules' => __('All Modules', 'perception'));	
		wp_localize_script( 'perception-common', 'perception_i18n', $translation_array );	
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'0.1.0',
				false
		);
	}
	
	// initialization
	static function init() {
		global $wpdb;		
		load_plugin_textdomain( 'perception', false, PERCEPTION_RELATIVE_PATH."/languages/" );
		// if (!session_id()) @session_start(); Not used now
		
		// define table names 
		define( 'PERCEPTION_COURSES', $wpdb->prefix. "perception_courses");
		define( 'PERCEPTION_LESSON_COURSES', $wpdb->prefix. "perception_lesson_courses");
		if(!defined('PERCEPTION_HOMEWORKS')) define( 'PERCEPTION_HOMEWORKS', $wpdb->prefix. "perception_homeworks");
		define( 'PERCEPTION_STUDENT_HOMEWORKS', $wpdb->prefix. "perception_student_homeworks");
		define( 'PERCEPTION_HOMEWORK_NOTES', $wpdb->prefix. "perception_homework_notes");
		define( 'PERCEPTION_STUDENT_LESSONS', $wpdb->prefix. "perception_student_lessons");
		define( 'PERCEPTION_CERTIFICATES', $wpdb->prefix. "perception_certificates");
		define( 'PERCEPTION_STUDENT_CERTIFICATES', $wpdb->prefix. "perception_student_certificates");
		define( 'PERCEPTION_PAYMENT_METHOD', $wpdb->prefix. "perception_payments");
		define( 'PERCEPTION_VISITS', $wpdb->prefix. "perception_visits");
		define( 'PERCEPTION_HISTORY', $wpdb->prefix. "perception_history");
		define( 'PERCEPTION_STUDENT_MODULES', $wpdb->prefix. "perception_student_modules");
		define( 'PERCEPTION_SOLUTION_FILES', $wpdb->prefix. "perception_solution_files");
		
		define( 'PERCEPTION_VERSION', get_option('perception_version'));
		
		self :: define_filters();
		
		// shortcodes
		add_shortcode('perception-todo', array("PSPerceptionLMSShortcodesController", 'todo'));		
		add_shortcode('perception-enroll', array("PSPerceptionLMSShortcodesController", 'enroll'));
		add_shortcode('perception-points', array("PSPerceptionLMSShortcodesController", 'points'));
		add_shortcode('perception-leaderboard', array("PSPerceptionLMSShortcodesController", 'leaderboard'));
		add_shortcode('perception-mycourses', array("PSPerceptionLMSShortcodesController", 'my_courses'));
		add_shortcode('perception-mycertificates', array("PSPerceptionLMSShortcodesController", 'my_certificates'));
		add_shortcode('perception-course-lessons', array("PSPerceptionLMSShortcodesController", 'lessons'));
		add_shortcode('perception-course-modules', array("PSPerceptionLMSShortcodesController", 'modules'));
		add_shortcode('perception-module-lessons', array("PSPerceptionLMSShortcodesController", 'module_lessons'));
		add_shortcode('perception-next-lesson', array("PSPerceptionLMSShortcodesController", 'next_lesson'));
		add_shortcode('perception-prev-lesson', array("PSPerceptionLMSShortcodesController", 'prev_lesson'));
		add_shortcode('perception-first-lesson', array("PSPerceptionLMSShortcodesController", 'first_lesson'));
		add_shortcode('perception-grade', array("PSPerceptionLMSShortcodesController", 'grade'));
		add_shortcode('perception-mark', array("PSPerceptionLMSShortcodesController", 'mark'));
		add_shortcode('perception-assignments', array("PSPerceptionLMSShortcodesController", 'assignments'));
		add_shortcode('perception-earned-certificates', array("PSPerceptionLMSShortcodesController", 'earned_certificates'));
		add_shortcode('perception-course-link', array("PSPerceptionLMSShortcodesController", 'course_link'));
		add_shortcode('perception-condition', array("PSPerceptionLMSShortcodesController", 'condition'));
		add_shortcode('perception-search', array("PSPerceptionLMSShortcodesController", 'search'));
		add_shortcode('perception-num-courses', array("PSPerceptionLMSShortcodesController", 'num_courses'));
		add_shortcode('perception-num-students', array("PSPerceptionLMSShortcodesController", 'num_students'));
		add_shortcode('perception-num-lessons', array("PSPerceptionLMSShortcodesController", 'num_lessons'));
		add_shortcode('perception-num-assignments', array("PSPerceptionLMSShortcodesController", 'num_assignments'));
		add_shortcode('perception-userinfo', array("PSPerceptionLMSShortcodesController", 'userinfo'));
		add_shortcode('perception-gradebook', array("PSPerceptionLMSShortcodesController", 'gradebook'));
		add_shortcode('perception-mygradebook', array("PSPerceptionLMSShortcodesController", 'my_gradebook'));
		add_shortcode('perception-lesson-status', array("PSPerceptionLMSShortcodesController", 'lesson_status'));
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("PerceptionPayment", "parse_request"));
		
		// wp_loaded actions
		add_action('wp_loaded', array(__CLASS__, "wp_loaded"));
		
		// exam related actions and filters
		add_action('watu_exam_submitted', array('PSPerceptionLMSLessonModel','exam_submitted_watu'));
		add_action('watupro_completed_exam', array('PSPerceptionLMSLessonModel','exam_submitted_watupro'));
		add_action('watupro_completed_exam_edited', array('PSPerceptionLMSLessonModel','exam_submitted_watupro'));
		add_filter( 'post_row_actions', array('PSPerceptionLMSLessonModel','quiz_results_link'), 10, 2 );
		
		// custom columns
		//add_filter('manage_perception_lesson_posts_columns', array('PSPerceptionLMSLessonModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('PSPerceptionLMSLessonModel','custom_columns'), 10, 2 );
		//add_filter('manage_perception_course_posts_columns', array('PSPerceptionLMSCourseModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('PSPerceptionLMSCourseModel','custom_columns'), 10, 2 );
		add_action('restrict_manage_posts',array('PSPerceptionLMSLessonModel','restrict_manage_posts'));
		add_action('parse_query',array('PSPerceptionLMSLessonModel','parse_admin_query'));
		add_action('restrict_manage_posts',array('PSPerceptionLMSModuleModel','restrict_manage_posts'));
		add_action('parse_query',array('PSPerceptionLMSModuleModel','parse_admin_query'));
		add_filter( 'post_row_actions', array('PSPerceptionLMSCourseModel','post_row_actions'), 10, 2 );
		add_filter( 'post_row_actions', array('PSPerceptionLMSModuleModel','post_row_actions'), 10, 2 );		
		
		// certificates
		add_action('template_redirect', array('PSPerceptionLMSCertificatesController', 'certificate_redirect'));
		
		// comments on lessons shouldn't be visible for unenrolled
		add_filter('comments_array', array('PSPerceptionLMSLessonModel','restrict_visible_comments'));
		
		// add points in custom column on the users page
		if(get_option('perception_use_points_system') != '') {				
			add_filter('manage_users_columns', array('PerceptionPoint', 'add_custom_column'));
			add_action('manage_users_custom_column', array('PerceptionPoint','manage_custom_column'), 10, 3);
		} 
		
		// auto enroll in courses
		add_action('user_register', array('PSPerceptionLMSCourseModel', 'register_enroll'));
		
		$version = get_option('perception_version');
		if($version != '1.47') self::install(true);

		// default 'you need to be logged in' messages for lessons and courses
		if(get_option('perception_need_login_text_lesson') == '') {
			$text = sprintf(__('You need to be <a href="%s">logged in</a> to access this lesson.', 'perception'), wp_login_url());
			update_option('perception_need_login_text_lesson', $text);
		}

		if(get_option('perception_need_login_text_course') == '') {
			$text = sprintf(__('You can enroll in this course from your student dashboard. You need to be <a href="%s">logged in</a>.', 'perception'), wp_login_url());
			update_option('perception_need_login_text_course', $text);
		}
		
		define('PERCEPTION_NEED_LOGIN_TEXT_LESSON', stripslashes(get_option('perception_need_login_text_lesson')));

		define('PERCEPTION_NEED_LOGIN_TEXT_COURSE', stripslashes(get_option('perception_need_login_text_course')));
	}
	
	// handle Perception vars in the request
	static function query_vars($vars) {
		$new_vars = array('perception');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
		
	// parse Perception vars in the request
	static function template_redirect() {
		global $wp, $wp_query, $wpdb;
		$redirect = false;		
		 
	  if($redirect) {
	   	if(@file_exists(TEMPLATEPATH."/".$template)) include TEMPLATEPATH."/perception/".$template;		
			else include(PERCEPTION_PATH."/views/templates/".$template);
			exit;
	  }	   
	}	
			
	// manage general options
	static function options() {
		global $wp_roles, $wp_rewrite;				
		$is_admin = current_user_can('administrator');		
		$multiuser_access = 'all';
		$multiuser_access = PSPerceptionLMSMultiUser :: check_access('settings_access');
		
		if(!empty($_POST['perception_options']) and check_admin_referer('save_options', 'nonce_options')) {
			$roles = $wp_roles->roles;			
			
			foreach($roles as $key=>$r) {
				if($key == 'administrator') continue;
				
				$role = get_role($key);

				// use PSPerception
				if(in_array($key, $_POST['use_roles'])) {					
    			if(!$role->has_cap('perception')) $role->add_cap('perception');
				}
				else $role->remove_cap('perception');
				
				// manage PSPerception - allow only admin change this
				if($is_admin) {
					if(@in_array($key, $_POST['manage_roles'])) {					
	    				if(!$role->has_cap('perception_manage')) $role->add_cap('perception_manage');
					}
					else $role->remove_cap('perception_manage');
				}	// end if can_manage_options
			} // end foreach role 
			
			$use_modules = empty($_POST['use_modules']) ? 0 : 1;
			$show_courses_in_blog = empty($_POST['show_courses_in_blog']) ? 0 : 1;
			$show_lessons_in_blog = empty($_POST['show_lessons_in_blog']) ? 0 : 1;
			update_option('perception_show_courses_in_blog', $show_courses_in_blog);
			update_option('perception_show_lessons_in_blog', $show_lessons_in_blog);
			$_POST['course_slug'] = preg_replace('/[^\w\-]/', '', $_POST['course_slug']);
			$_POST['lesson_slug'] = preg_replace('/[^\w\-]/', '', $_POST['lesson_slug']);
			$_POST['module_slug'] = preg_replace('/[^\w\-]/', '', $_POST['module_slug']);
			update_option('perception_use_modules', $use_modules);
			update_option('perception_course_slug', $_POST['course_slug']);
			update_option('perception_lesson_slug', $_POST['lesson_slug']);
			update_option('perception_module_slug', $_POST['module_slug']);
			$link_to_course = empty($_POST['link_to_course']) ? 0 : 1;
			update_option('perception_link_to_course', $link_to_course);
			update_option('perception_link_to_course_text', sanitize_text_field($_POST['link_to_course_text']));
			$mycourses_only_enrolled = empty($_POST['mycourses_only_enrolled']) ? 0 : 1;
			update_option('perception_mycourses_only_enrolled', $mycourses_only_enrolled);
			$wp_rewrite->flush_rules();  
			
			// login texts
			update_option('perception_need_login_text_lesson', $_POST['need_login_text_lesson']);
			update_option('perception_need_login_text_course', $_POST['need_login_text_course']);
			
			do_action('perception-saved-options-main');
		}
		
		if(!empty($_POST['perception_exam_options']) and check_admin_referer('save_exam_options', 'nonce_exam_options')) {
				update_option('perception_use_exams', sanitize_text_field($_POST['use_exams']));
				update_option('perception_cleanup_exams', sanitize_text_field(@$_POST['cleanup_exams']));
				$access_exam_started_lesson = empty($_POST['access_exam_started_lesson']) ? 0 : 1; 
				update_option('perception_access_exam_started_lesson', $access_exam_started_lesson);
				do_action('perception-saved-options-exams');
		}
		
		if(!empty($_POST['save_homework_options']) and check_admin_referer('perception_homework_options')) {
			update_option('perception_allowed_file_types', sanitize_text_field($_POST['allowed_file_types']));
			
			$store_filesystem = empty($_POST['store_filesystem']) ? 0 : 1;			
			update_option('perception_store_files_filesystem', $store_filesystem);
			
			$file_upload_progress = empty($_POST['file_upload_progress']) ? 0 : 1;			
			update_option('perception_file_upload_progress', $file_upload_progress);
			
			$protected_folder = preg_replace("/[^A-z0-9_]/", "", $_POST['protected_folder']);
			update_option('perception_protected_folder', $protected_folder);
			
			// if folder does not exist create it
			if(!empty($protected_folder)) {
				$dir = wp_upload_dir();
				if(!file_exists($dir['basedir'].'/'.$protected_folder)) {
					mkdir($dir['basedir'].'/'.$protected_folder, 0755);
					$fp = fopen($dir['basedir'].'/'.$protected_folder.'/.htaccess', 'wb');
					$contents = 'deny from all';
					fwrite($fp, $contents);
					fclose($fp);
				}
			}
			
			update_option('perception_homework_size_total', intval($_POST['homework_size_total']));
			update_option('perception_homework_size_per_file', intval($_POST['homework_size_per_file']));
			
		} // end homework options
		
		if(!empty($_POST['perception_payment_options']) and check_admin_referer('save_payment_options', 'nonce_payment_options')) {
			update_option('perception_accept_other_payment_methods', @$_POST['accept_other_payment_methods']);
			update_option('perception_other_payment_methods', $_POST['other_payment_methods']);
			if(empty($_POST['currency'])) $_POST['currency'] = sanitize_text_field($_POST['custom_currency']);
			update_option('perception_currency', sanitize_text_field($_POST['currency']));
			update_option('perception_accept_paypal', (empty($_POST['accept_paypal']) ? 0 : 1));
			update_option('perception_paypal_sandbox', (empty($_POST['paypal_sandbox']) ? 0 : 1));
			update_option('perception_paypal_id', sanitize_text_field($_POST['paypal_id']));
			update_option('perception_paypal_return', esc_url_raw($_POST['paypal_return']));
			update_option('perception_use_pdt', (empty($_POST['use_pdt']) ? 0 : 1));
			update_option('perception_pdt_token', sanitize_text_field($_POST['pdt_token']));
			
			update_option('perception_accept_stripe', (empty($_POST['accept_stripe']) ? 0 : 1));
			update_option('perception_stripe_public', sanitize_text_field($_POST['stripe_public']));
			update_option('perception_stripe_secret', sanitize_text_field($_POST['stripe_secret']));
			
			update_option('perception_accept_moolamojo', (empty($_POST['accept_moolamojo']) ? 0 : 1));
			update_option('perception_moolamojo_price', intval($_POST['moolamojo_price']));
			update_option('perception_moolamojo_button', perception_strip_tags($_POST['moolamojo_button']));
			
			do_action('perception-saved-options-payments');
		} 
		
		if(!empty($_POST['perception_grade_options']) and check_admin_referer('perception_grade_options')) {
			$use_grading_system = empty($_POST['use_grading_system']) ? 0 : 1;
			$use_points_system = empty($_POST['use_points_system']) ? 0 : 1;
			$moolamojo_points = empty($_POST['moolamojo_points']) ? 0 : 1; // connect to MoolaMojo?
			update_option('perception_use_grading_system', $use_grading_system);
			update_option('perception_grading_system', sanitize_text_field($_POST['grading_system']));
			update_option('perception_use_points_system', $use_points_system);
			update_option('perception_points_course', intval($_POST['points_course']));
			update_option('perception_points_lesson', intval($_POST['points_lesson']));
			update_option('perception_points_homework', intval($_POST['points_homework']));
			update_option('perception_moolamojo_points', $moolamojo_points);
			
			do_action('perception-saved-options-grading');
		}
		
		// select all roles in the system
		$roles = $wp_roles->roles;
				
		// what exams to use
		$use_exams = get_option('perception_use_exams');
		
		// see if watu/watuPRO are available and activate
		$current_plugins = get_option('active_plugins');
		$watu_active = $watupro_active = false;
		if(in_array('watu/watu.php', $current_plugins)) $watu_active = true;
		if(in_array('watupro/watupro.php', $current_plugins)) $watupro_active = true;
			
		$accept_other_payment_methods = get_option('perception_accept_other_payment_methods');
		$accept_paypal = get_option('perception_accept_paypal');
		$accept_stripe = get_option('perception_accept_stripe');
		
		$accept_moolamojo = get_option('perception_accept_moolamojo');
		$moolamojo_button = get_option('perception_moolamojo_button');
		if(empty($moolamojo_button)) $moolamojo_button = "<p align='center'>".__('You can also buy access to this {{{item}}} with {{{credits}}} virtual credits from your balance. You currently have [moolamojo-balance] credits total.', 'perception')."</p><p align='center'>{{{button}}}</p>";
		
		$currency = get_option('perception_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
	   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
	   "ILS"=>"ILS", "INR"=>"INR", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
	   "SGD"=>"SGD", "ZAR"=>"ZAR");		
	   $currency_keys = array_keys($currencies);  
	   
	   $use_grading_system = get_option('perception_use_grading_system');
	   $grading_system = stripslashes(get_option('perception_grading_system'));
	   if(empty($grading_system)) $grading_system = "A, B, C, D, F";
	   $use_points_system = get_option('perception_use_points_system');
	   
	   $payment_errors = get_option('perception_errorlog');
	   // strip to reasonable length
	   $payment_errors = substr($payment_errors, 0, 10000);
	   
	   $course_slug = get_option('perception_course_slug');
	   if(empty($course_slug)) $course_slug = 'perception-course';
	   $lesson_slug = get_option('perception_lesson_slug');
	   if(empty($lesson_slug)) $lesson_slug = 'perception-lesson';
	   $module_slug = get_option('perception_module_slug');
	   if(empty($module_slug)) $module_slug = 'perception-module';
	   $use_modules = get_option('perception_use_modules');
	   
	   $use_pdt = get_option('perception_use_pdt');
	   
	   $link_to_course = get_option('perception_link_to_course');
	   $link_to_course_text = stripslashes(get_option('perception_link_to_course_text'));
	   if(empty($link_to_course_text)) $link_to_course_text = __('<p>Course: {{{course-link}}}</p>', 'perception');
		
		if(@file_exists(get_stylesheet_directory().'/perception/options.php')) require get_stylesheet_directory().'/perception/options.php';
		else require(PERCEPTION_PATH."/views/options.php");
	}	
	
	static function help() {
		require(PERCEPTION_PATH."/views/help.php");
	}	
	
	static function register_widgets() {
		// register_widget('PerceptionWidget');
	}
	
	// manually apply Wordpress filters on the content
	// to avoid calling apply_filters('the_content')	
	static function define_filters() {
		global $wp_embed, $watupro_keep_chars;
		
		add_filter( 'perception_content', 'wptexturize' ); // Questionable use!
		add_filter( 'perception_content', 'convert_smilies' );
	   add_filter( 'perception_content', 'convert_chars' );
		add_filter( 'perception_content', 'shortcode_unautop' );
		add_filter( 'perception_content', 'do_shortcode' );
		
		// Compatibility with specific plugins
		// qTranslate
		if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
			add_filter('perception_content', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
			add_filter('perception_qtranslate', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
			add_filter( 'perception_qtranslate', 'wptexturize' );
		}
	}
	
	// personal data eraser
	static function register_eraser($erasers) {
		 $erasers['perception'] = array(
		    'eraser_friendly_name' => __( 'PSPerception LMS', 'perception' ),
		    'callback'             => array('PSPerceptionLMSStudentModel', 'erase_data')
		    );
		    
		  return $erasers;
	}
	
	// erase student's personal data when the WP Data Eraser is called
	static function erase_data($email_address, $page = 1) {
		 global $wpdb;

		 $number = 200; // Limit us to avoid timing out
  		 $page = (int) $page;
  		 
  		 // find student
  		 $user = get_user_by('email', $email_address);
  		 
  		 if($page == 1) {
  		 	  // delete history
	  		 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_HISTORY." WHERE user_id=%d", $user->ID));
	  		 
	  		 // delete student-courses, student-lessons, student-modules and student-certificates relations
	  		 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_COURSES." WHERE user_id=%d", $user->ID));
			 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_STUDENT_LESSONS." WHERE student_id=%d", $user->ID));
			 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_COURSES." WHERE student_id=%d", $user->ID));
			 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_STUDENT_CERTIFICATES." WHERE student_id=%d", $user->ID));
			 
			 // delete homework notes
			 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_HOMEWORK_NOTES." WHERE student_id=%d", $user->ID));
			 
			 // delete visits
			 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_VISITS." WHERE user_id=%d", $user->ID));
  		 }  		 
  		 
  		 // remove homework solutions & files
  		 $homework_removed = false;
  		 
  		 $solutions = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM ".PERCEPTION_STUDENT_HOMEWORKS." 
	  		 WHERE student_id=%d ORDER BY id LIMIT %d", $user->ID, $page));
	  	 $number = $wpdb->get_var("SELECT FOUND_ROWS()"); 
	  	 	 
	  	 foreach($solutions as $solution) {
	  	 	 // select soltion files and delete them
	  	 	 $files = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".PERCEPTION_SOLUTION_FILES." 
	  	 	 	WHERE student_id=%d AND solution_id=%d", $user->ID, $solution->id));
	  	 	 	
	  	 	 // delete the physical files if any	
	  	 	 foreach($files as $file) {
	  	 	 	if(!empty($file->filepath)) @unlink($file->filepath);
	  	 	 }
	  	 	 
	  	 	 // delete the DB files with query
	  	 	 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_SOLUTION_FILES." 
	  	 	 	WHERE student_id=%d AND solution_id=%d", $user->ID, $solution->id));
	  	 	 
	  	 	 // now delete the solution
	  	 	 $wpdb->query($wpdb->prepare("DELETE FROM ".PERCEPTION_STUDENT_HOMEWORKS." WHERE id=%d", $solution->id));
	  	 }	 
	  	 
	  	 $done = count( $solutions ) <= $number; 
	  	 return array( 'items_removed' => true,
		    'items_retained' => false, // always false in this example
		    'messages' => array(), // no messages in this example
		    'done' => $done,
		  );
  		 
	} // end erase_data
	
	// call actions on WP loaded
	static function wp_loaded() {
	   if(!empty($_GET['perception_pdt'])) PerceptionPayment::paypal_ipn();	   
	}	
}
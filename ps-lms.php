<?php
/*
Plugin Name: PERCEPTION LMS
Plugin URI: http://3.13.175.124/wp_projects/ps-lms/
Description: Perception Learning Management System for WordPress. Courses, modules, lessons, gradebook, and everything you need. 
Author: Perception System
Version: 1.0.0
Author URI: https://www.perceptionsystem.com
License: GPLv2 or later
Text Domain: perception
*/

define( 'PERCEPTION_PATH', dirname( __FILE__ ) );
define( 'PERCEPTION_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'PERCEPTION_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require_once(PERCEPTION_PATH."/helpers/htmlhelper.php");
require(PERCEPTION_PATH."/models/perception-model.php");
require(PERCEPTION_PATH."/models/lesson-model.php");
require(PERCEPTION_PATH."/models/course-model.php");
require(PERCEPTION_PATH."/models/module-model.php");
require(PERCEPTION_PATH."/models/homework-model.php");
require(PERCEPTION_PATH."/models/student-model.php");
require(PERCEPTION_PATH."/models/note-model.php");
require(PERCEPTION_PATH."/models/certificate-model.php");
require(PERCEPTION_PATH."/models/payment.php");
require(PERCEPTION_PATH."/models/stripe-model.php");
require(PERCEPTION_PATH."/models/track.php");
require(PERCEPTION_PATH."/models/point.php");
require(PERCEPTION_PATH."/controllers/ajax.php");
require(PERCEPTION_PATH."/controllers/courses.php");
require(PERCEPTION_PATH."/controllers/homeworks.php");
require(PERCEPTION_PATH."/controllers/certificates.php");
require(PERCEPTION_PATH."/controllers/shortcodes.php");
require(PERCEPTION_PATH."/controllers/gradebook.php");
require(PERCEPTION_PATH."/controllers/multiuser.php");
require(PERCEPTION_PATH."/controllers/search.php");
require(PERCEPTION_PATH."/controllers/todo.php");

add_action('init', array("PSPerceptionLMSCourseModel", "register_course_type"));
add_action('init', array("PSPerceptionLMSModuleModel", "register_module_type"));
add_action('init', array("PSPerceptionLMSHomeworkModel", "register_homework_type"));
add_action('init', array("PSPerceptionLMSLessonModel", "register_lesson_type"));
add_action('init', array("PSPerceptionLMS", "init"));

register_activation_hook(__FILE__, array("PSPerceptionLMS", "install"));
add_action('admin_menu', array("PSPerceptionLMS", "menu"));
add_action('admin_enqueue_scripts', array("PSPerceptionLMS", "scripts"));

// show the things on the front-end
add_action( 'wp_enqueue_scripts', array("PSPerceptionLMS", "scripts"));

// widgets
add_action( 'widgets_init', array("PSPerceptionLMS", "register_widgets") );

// other actions
add_action('save_post', array('PSPerceptionLMSLessonModel', 'save_lesson_meta'));
add_action('save_post', array('PSPerceptionLMSCourseModel', 'save_course_meta'));
add_action('save_post', array('PSPerceptionLMSModuleModel', 'save_module_meta'));
add_filter('pre_get_posts', array('PSPerceptionLMSCourseModel', 'query_post_type'));
add_filter('pre_get_posts', array('PSPerceptionLMSLessonModel', 'query_post_type'));
add_filter('pre_get_posts', array('PSPerceptionLMSModuleModel', 'query_post_type'));
add_filter('pre_get_posts', array('PSPerceptionLMSSearchController', 'pre_get_posts'));
add_action('wp_ajax_perception_ajax', 'perception_ajax');
add_action('wp_ajax_nopriv_perception_ajax', 'perception_ajax');
add_filter('the_content', array('PSPerceptionLMSLessonModel', 'access_lesson'));
add_filter('the_content', array('PSPerceptionLMSCourseModel', 'enroll_text'));

// erase personal data?
add_filter('wp_privacy_personal_data_erasers', array('PSPerceptionLMS', 'register_eraser'), 10);

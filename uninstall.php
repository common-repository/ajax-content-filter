<?php
global $wpdb;

if(!defined('WP_UNINSTALL_PLUGIN') or !WP_UNINSTALL_PLUGIN) exit;
    
// clenaup all data
if(get_option('perception_cleanup_db')==1)
{
	// now drop tables	
	$wpdb->query("DROP TABLE `".PERCEPTION_COURSES."`");
	$wpdb->query("DROP TABLE `".PERCEPTION_LESSON_COURSES."`");
	$wpdb->query("DROP TABLE `".PERCEPTION_HOMEWORKS."`");
	$wpdb->query("DROP TABLE `".PERCEPTION_STUDENT_HOMEWORKS."`");
	$wpdb->query("DROP TABLE `".PERCEPTION_HOMEWORK_NOTES."`");
	$wpdb->query("DROP TABLE `".PERCEPTION_STUDENT_LESSONS."`");
	   
}
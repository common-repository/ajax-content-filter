<div class="wrap">
	<h1><?php _e('PSPerception LMS Help', 'perception');?></h1>
	
	<h2><?php _e('Available Shortcodes', 'perception');?></h2>
	<table class="form-table">
		<tr>
			<th><input type="text" value='[perception-todo]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('This is flexible shortcode that can be placed inside a lesson or course content. It will display what the logged in student still needs to do to complete the given lesson or course. You can pass "ul" or "ol" as first argument to define ordered or unordered list.', 'perception');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-enroll]' onclick="this.select();" readonly="readonly"></th>
			<td><?php printf(__('displays enroll button or "enrolled/pending enrollment" message in the course. The shortcode accepts argument <b>%s</b> to specify course ID outside of the course page. You can also pass parameters <b>%s</b> and <b>%s</b> to specify the text to be shown for the enroll buttons. The first one is used when the student can enroll for free, and the second one - when they have to pay a fee.', 'perception'), 'course_id', 'free_button_text', 'paid_button_text');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-mycourses]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('Displays simplified version of the student dashboard - the same table with all the available courses but without the "view lessons" link. Instead of this link you can include the shortcode for "lessons in course" (given below) in the course page itself.', 'perception')?><br>
	<?php printf(__('You can pass the attribute <b>%s</b> to limit this page to displaying courses the user has enrolled to. Note that it will display even courses with pending or rejected enrollment, frozen access, and completed courses', 'perception'), 'enrolled=1');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-mycertificates]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('Displays the "My certificates" page.', 'perception')?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-mygradebook]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('Displays the "My Gradebook" page if you use a grading system.', 'perception')?> <br />
	<?php _e('You can pass attribute "course_id" if you want to show the user a specific course gradebooking without the course drop-down selector.', 'perception');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-earned-certificates]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('Displays links to the certificates earned by the currently logged user in the course. This works for the current course when placed in a course page. You can also use the shortcode by passing parameter "course_id" like this: [perception-earned-certificates course_id=5]. You can also add some text which will be conditionally displayed where there are any certificates earned. For example: [perception-earned-certificates course_id=5 text="You have earned certificates for completing this course:"]', 'perception')?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-course-lessons]' onclick="this.select();" readonly="readonly"> </th>
			<td><?php _e('or', 'perception')?> <b>[perception-course-lessons status y]</b> <?php _e('or', 'perception')?> <b>[perception-course-lessons status y orderby direction list_tag]</b> <?php _e('will display all the lessons in a course along with links to them. If you use the second format and pass "status" as first argument, the shortcode will output the lessons in a table where the second argument will be the current status (started, not started, completed). You can also pass a number in place of the argument "y" to specify a course ID (otherwise current course ID is used). This might be useful if you are manually or programmatically creating some list of courses along with the lessons in them. If you want to use the course ID argument but not the status column, pass 0 in pace of status like this: [perception-course-lesson 0 5]. The third format lets you specify ordering using SQL field names from the posts table. For example [perception-course-lessons 0 0 post_date DESC] will return the lessons displayed by the order of publishing, descending (latest lesson will be shown on top). In place of "list_tag" you can pass "ul" or "ol". If empty, defaults to "ul".', 'perception');?> <b><?php _e('Note that status column will be shown only for logged in users.', 'perception')?></b><br>
	<?php _e('The named argument <b>show_excerpts</b> can be used to show the post excerpts like this: show_excerpts=1. Example: [perception-course-lessons 0 0 post_date DESC show_excerpts=1]', 'perception');?> </p>
	<?php _e('If you use a grading system, the named argument <b>show_grade</b> can be used to show the grade given to the lesson like this: show_grade=1. Example: [perception-course-lessons status show_grade=1]. It will work only when "status" is passed.', 'perception');?> </p>
	<p><?php printf(__('Confused?! Use the <a href="%s">shortcode generator</a>.', 'perception'), 'admin.php?page=perception_shortcode_generator');?></td>
		</tr>
		<tr>
			<th><?php if(get_option('perception_use_modules') == 1):?></th>
			<td><p><input type="text" value='[perception-module-lessons]' onclick="this.select();" readonly="readonly"> <?php _e('works exactly as above and accepts the same arguments but lists lessons inside a whole module.', 'perception');?></p>
	<?php endif;?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-first-lesson]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('or', 'perception')?> <b>[perception-first-lesson "hyperlinked text"]</b>
		<?php _e('can be used only in a course page and will display the first lesson from the course. You can replace "hyperlinked text" with your own text. If you omit the parameter the link will say "first lesson".', 'perception')?><br />
		<?php printf(__('You can also assign a CSS class to the link by passing named parameter class. Examples: %s and %s', 'perception'), '[perception-first-lesson class="my-css-class"]', '[perception-first-lesson "First lesson" class="my-css-class"]');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-next-lesson]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('or', 'perception')?><b>[perception-next-lesson "hyperlinked text"]</b>
		<?php _e('can be used only in a lesson and will display the next lesson from the course. You can replace "hyperlinked text" with your own text. If you omit the parameter the link will use the lesson title as hyperlinked text. The same shortcode can be used on module pages for link to next module in the course.', 'perception')?> <br>
		<?php printf(__('You can also assign a CSS class to the link by passing named parameter class. Examples: %s and %s', 'perception'), '[perception-next-lesson class="my-css-class"]', '[perception-next-lesson "Next lesson" class="my-css-class"]');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-prev-lesson]' onclick="this.select();" readonly="readonly"></th>
			<td><?php _e('or', 'perception')?> <b>[perception-prev-lesson "hyperlinked text"]</b>
		<?php _e('Similar to the above, used to display a link for the previous lesson in this course. Note that lessons are ordered in the order of creation. The same shortcode can be used on module pages for link to previous module in the course.', 'perception')?><br />
		<?php printf(__('You can also assign a CSS class to the link by passing named parameter class. Examples: %s and %s', 'perception'), '[perception-prev-lesson class="my-css-class"]', '[perception-prev-lesson "Previous lesson" class="my-css-class"]');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-assignments lesson_id="X"]' onclick="this.select();" readonly="readonly" size="30"></th>
			<td><?php _e('(where X is lesson ID) will output the assignments to the lesson on the front-end. The links to submit and view solutions will also work. You can omit the "lesson_id" parameter and pass it as URL variable. This could be useful if you are manually building a page with lessons and want to give links to assignments from it.', 'perception')?></td>
		</tr>
		<tr>
			<th>
			<input type="text" value='[perception-search]' onclick="this.select();" readonly="readonly" size="14">
			</th>
			<td>
			<?php _e('Generates a search form for searching in courses and lessons. Non logged in users or users not enrolled in courses can search only within the course contents. When user is enrolled in a course, the search also searches in lessons and let them restrict to a specific lesson.', 'perception')?>
			</td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-num-courses]' onclick="this.select();" readonly="readonly" size="20"></th>
			<td><?php _e('Displays the total number of available (published) courses on the site.', 'perception')?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-num-students]' onclick="this.select();" readonly="readonly" size="20"></th>
			<td><?php printf(__('Displays the total number of students enrolled in courses on the site. Add the attribute <b>%s</b> to show the number of students enrolled in a given course (replace X with the course ID).', 'perception'), 'course_id=X');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-num-assignments]' onclick="this.select();" readonly="readonly" size="20"></th>
			<td><?php printf(__('Displays the total number of assignmentson the site. Add the attribute <b>%s</b> to show the number of assignments in a given course (replace X with the course ID) and <b>%s</b> to show the number of assignments in a given lesson (replace X with the lesson ID).', 'perception'), 'course_id=X', 'lesson_id=X');?></td>
		</tr>
		<tr>
			<th><input type="text" value='[perception-userinfo field="user_nicename" default="Guest"]' onclick="this.select();" readonly="readonly" size="40"></th>
			<td>
			<?php _e('Displays data from user profile. The "field" parameter should contain a field name from the WP users or usermeta table. The parameter "default" sets what to display if the user is not logged in or the data is empty. You can also pass attribute "user_id" to specify user other than the currently logged in user.', 'perception');?>
			</td>
		</tr>
	</table>

	
	<p> </p>	
	
	<?php if(get_option('perception_use_points_system') != ''):?>
		<h3><?php _e('Shortcodes enabled by using points system', 'perception');?></h3>
		<ol>
			<li><input type="text" size="12" readonly onclick="this.select();" value="[perception-points]"> <?php _e('and', 'perception');?> <input type="text" size="14" readonly onclick="this.select();" value="[perception-points x]"> <?php _e('(where "x" is given user ID) outputs the total number of points the user has earned.', 'perception')?> </li>
			<li><input type="text" size="18" readonly onclick="this.select();" value="[perception-leaderboard x]"> <?php _e('and', 'perception');?> <input type="text" size="24" readonly onclick="this.select();" value="[perception-leaderboard x points]"> <?php _e('displays a leaderboard based on collected points. Replace "x" with the number of users you want to show. When you use the second shortcode the usernames will be shown in a table with the points collected in front of them.', 'perception');?> </li>
		</ol>
	<?php endif;?>
	
	<h2><?php _e('Customizing the Look of Course and Lesson Pages', 'perception');?> </h2>
	
	<p><?php printf(__('Courses and lessons in PSPerception LMS are custom post types. How to create your custom post type templates is explained <a href="%s" target="_blank">on this page</a>. In short, here are the templates you may want to create are:<b>%s</b>.', 'perception'), 'https://codex.wordpress.org/Post_Type_Templates', 'archive-perception_course.php, single-perception_course.php, archive-perception_lesson.php, single-perception_lesson.php');?></p>
	
	
		
	<h2><?php _e('Redesigning and Customizing the Views / Templates', 'perception');?></h2>
	
	<p style="color:red;"><b><?php _e('Only for advanced users!', 'perception')?></b></p>
	
	<p><?php _e('You can safely customize all files from the "views" folders by placing their copies in your theme folder. Simply create folder "perception" <b>in your theme root folder</b> and copy the files you want to custom from "views" folder directly there.', 'perception')?></p>

	<p><?php _e('For example:', 'perception')?></p>
	
	<ol>
		<li><?php _e('If you are using the Twenty Fourteen theme, you should create folder "perception" under it so the structure will now be something like <b>wp-content/themes/twentyfourteen/perception</b>. (The files that are above the new "perception" folder should remain where they are)', 'perception')?></li>
		<li><?php _e('Then if you want to modify the "Manage Certificates" page copy the file certificates.php from the plugin "views" folder and place it in the new "perception" folder so you will have  <b>wp-content/themes/twentyfourteen/perception/certificates.php</b>', 'perception')?></li>	
	</ol>	
	
	<p><?php _e("Don't worry if you use modified WordPress directory structure and don't have 'wp-content' folder. The trick will work with any structure as long as you follow the same logic.", 'perception')?></p>
	
	<p><?php _e('Then feel free to modify the code, but of course be careful not to mess with the PHP or Javascript inside. This will let you change the design and even part of the functionality and not lose these changes when the plugin is upgraded. Be careful: we can not provide support for your custom versions of our views.', 'perception')?></p>
		
	<?php do_action('perception-help');?>	
</div>
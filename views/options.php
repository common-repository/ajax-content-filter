<div class="wrap">
	<h1><?php _e("PSPerception LMS Options", 'perception')?></h1>
	
	<form method="post" class="perception-form">
		<div class="postbox wp-admin perception-box">
			<div class="ps-card"><h2><?php _e('WordPress roles with access to the learning material', 'perception')?></h2>
			
			<p><?php _e('By default PSPerception LMS creates a role "student" which is the only role allowed to work with the learning material. The idea behind this is to allow the admin have better control over which users can access it. However, you can enable the other existing user roles here. Note that this setting is regarding consuming content, and not creating it.', 'perception')?></p>
			
			<p><?php foreach($roles as $key=>$r):
				if($key=='administrator') continue;
				$role = get_role($key);?>
				<input type="checkbox" name="use_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('perception')) echo 'checked';?>> <?php _e($role->name, 'perception')?> &nbsp;
			<?php endforeach;?></p>
			</div>
			<div class="ps-card">
			<?php if($is_admin):?>
				<h2><?php _e('WordPress roles that can administrate the LMS', 'perception')?></h2>
				
				<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'perception')?></p>
				
				<p><?php foreach($roles as $key=>$r):
					if($key=='administrator') continue;
					$role = get_role($key);?>
					<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('perception_manage')) echo 'checked';?>> <?php _e($role->name, 'perception')?> &nbsp;
				<?php endforeach;?></p>
			
				<?php if(current_user_can('manage_options')):?>
					<p><a href="admin.php?page=perception_multiuser" target="_blank"><?php _e('Fine-tune these settings.', 'perception')?></a></p>
				<?php endif;?>
				
				<h2><?php _e('Using Modules', 'perception');?></h2>
				
				<p><?php _e('You may want to use Modules between Courses and Lessons for better organization of the learning material.', 'perception');?></p>
				
				<p><input type="checkbox" name="use_modules" value="1" <?php if(!empty($use_modules)) echo 'checked'?> onclick="this.checked ? jQuery('#perceptionModulesSlug').show() : jQuery('#perceptionModulesSlug').hide();"> <?php _e('Enable modules between courses and lessons. (Modules are in beta version and not yet fully covered in the whole LMS and addons).', 'perception');?></p>
			</div>
			<div class="ps-card">
				<h2><?php _e('URL identificators for Perception courses, lessons, and modules', 'perception')?></h2>
				
				<p><?php _e('These are the parts of the URLs that identify a post as PSPerception LMS lesson or course. These URL slugs are shown at the browser address bar and are parts of all links to courses and lessons. By default they are "perception-course" and "perception-lesson". You can change them here.', 'perception')?></p>
				
				<p><label><?php _e('Course URL slug:', 'perception')?></label> <input type="text" name="course_slug" value="<?php echo $course_slug?>"></p>
				<p id="perceptionModulesSlug" style='display:<?php echo $use_modules ? 'block' : 'none';?>'>
					<label><?php _e('Module URL slug:', 'perception')?></label> <input type="text" name="module_slug" value="<?php echo $module_slug?>">				
				</p>
				<p><label><?php _e('Lesson URL slug:', 'perception')?></label> <input type="text" name="lesson_slug" value="<?php echo $lesson_slug?>"></p>
				
				<p><?php _e('These slugs can contain only numbers, letters, dashes, and underscores. It is your responsibility to ensure they do not overlap with the URL identificators of another custom post type.', 'perception')?></p>
				
				<p><input type="checkbox" name="link_to_course" value="1" <?php if($link_to_course == 1) echo 'checked'?> onclick="this.checked ? jQuery('#linkToCourseText').show() : jQuery('#linkToCourseText').hide();"> <?php _e('Automatically link to the course page from each lesson.', 'perception');?></p>
				<div style='display:<?php echo $link_to_course ? 'block' : 'none';?>;margin-left:50px;' id="linkToCourseText">
					<p><?php _e('Link HTML:', 'perception');?> <textarea rows="2" cols="30" name="link_to_course_text"><?php echo $link_to_course_text?></textarea>
					<?php printf(__('The tag %s will automatically be replaced with the hyperlinked course title.','perception'), '{{{course-link}}}');?></p>
				</div>
			<?php endif;?>
			</div>
			<div class="ps-card">
			<h2><?php _e('Default "You need to be logged in" texts', 'perception')?></h2>
			
			<p><?php _e('These are shown on lesson / course pages when a non logged in visitors visits them. For lessons the text is shown only when there is no lesson excerpt.', 'perception');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e('Text on course pages:', 'perception');?> </th>
						<td><textarea rows="3" cols="60" name="need_login_text_course"><?php echo stripslashes(get_option('perception_need_login_text_course'));?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Text on lesson pages:', 'perception');?></th>
						<td><textarea rows="3" cols="60" name="need_login_text_lesson"><?php echo stripslashes(get_option('perception_need_login_text_lesson'));?></textarea></td>
					</tr>
			</tbody>
			<table>
			</div>
			<div class="ps-card">
			<h2><?php _e('Blog / Archive Pages Behavior', 'perception')?></h2>
			
			<p><input type="checkbox" name="show_courses_in_blog" value="1" <?php if(get_option('perception_show_courses_in_blog')) echo 'checked'?>> <?php _e('Show courses as blog posts in home and archive pages', 'perception')?></p>		
			<?php if($use_modules):?>
				<p><input type="checkbox" name="show_modules_in_blog" value="1" <?php if(get_option('perception_show_modules_in_blog')) echo 'checked'?>> <?php _e('Show modules as blog posts in home and archive pages', 'perception')?></p>		
			<?php endif;?>
			<p><input type="checkbox" name="show_lessons_in_blog" value="1" <?php if(get_option('perception_show_lessons_in_blog')) echo 'checked'?>> <?php _e('Show lessons as blog posts in home and archive pages', 'perception')?></p>		
			</div>
			<div class="ps-card">
			<h2><?php _e('My Courses Page Behavior', 'perception')?></h2>
			
			<p><input type="checkbox" name="mycourses_only_enrolled" value="1" <?php if(get_option('perception_mycourses_only_enrolled') == 1) echo 'checked'?>> <?php _e('Show only the courses student is enrolled in, completed, or pending enrollment.', 'perception');?></p>

			
			<?php echo do_action('perception-options-main');?>						
			
			</div>
			<p><input type="submit" class="button button-primary" value="<?php _e('Save Options', 'perception')?>" name="perception_options"></p>
			<?php echo wp_nonce_field('save_options', 'nonce_options');?>
		</div>
		
	</form>
	
	<form method="post" class="perception-form">
		<div class="postbox wp-admin perception-box">
		<div class="ps-card">
			<h2><?php _e('Grade and Point Systems', 'perception')?></h2>
			
			<p><input type="checkbox" name="use_grading_system" <?php if($use_grading_system) echo 'checked'?> onclick="this.checked ? jQuery('#gradeSystem').show() : jQuery('#gradeSystem').hide();"> <?php _e('Use grading system*', 'perception');?></p>
			<p><?php _e('* Using a grading system allows you to rate student performance in courses, lessons, and assignments, and keeping a gradebook. Grading individual lessons is optional.', 'perception')?> </p>
			
			<div id="gradeSystem" style='display:<?php echo $use_grading_system ? 'block' : 'none'?>'>
				<p><?php _e('Enter your grades in the box, separated by comma. Start with the best possible grade and go right to the worst:', 'perception')?>
				<input type="text" name="grading_system" value="<?php echo $grading_system;?>" size="40"></p>
			</div>
			
			<hr>
			
			<p><input type="checkbox" name="use_points_system" <?php if($use_points_system) echo 'checked'?> onclick="this.checked ? jQuery('#pointsSystem').show() : jQuery('#pointsSystem').hide();"> <?php _e('Use points system*', 'perception');?></p>
			<p><?php _e('* Points system can be used alone or together with a grading system. It lets you reward your students with points for completing lessons, courses, or assignments. These points will be displayed, and in the future (and in additional plugins) used to create leaderboards, redeem rewards, etc.', 'perception')?> </p>
			
			<div id="pointsSystem" style='display:<?php echo $use_points_system ? 'block' : 'none'?>'>
				<p><?php _e('Default reward values. They can be overridden for every individual course, lesson, or assignment.', 'perception')?> <br />
				<strong><?php _e('When you change the numbers here, it affects courses, lessons and homework you create after the change. The change will not affect already created courses, lessons, and assignments.', 'perception');?></strong></p>
				
				<p><?php _e('Reward', 'perception')?> <input type="text" name="points_course" size="4" value="<?php echo get_option('perception_points_course')?>"> <?php _e('points for completing a course', 'perception')?></p>
				
				<p><?php _e('Reward', 'perception')?> <input type="text" name="points_lesson" size="4" value="<?php echo get_option('perception_points_lesson')?>"> <?php _e('points for completing a lesson', 'perception')?></p>
				
				<p><?php _e('Reward', 'perception')?> <input type="text" name="points_homework" size="4" value="<?php echo get_option('perception_points_homework')?>"> <?php _e('points for successfully completing a homework / assignment', 'perception')?></p>
				
				<p><input type="checkbox" name="moolamojo_points" value="1" <?php if(get_option('perception_moolamojo_points')) echo "checked"?>> <?php printf(__('Connect to <a href="%s" target="_blank">MoolaMojo</a> so when points are awarded in PSPerception LMS the same number of virtual credits is earned. The MoolaMojo plugin must be installed and active.', 'perception'), 'https://wordpress.org/plugins/moolamojo/');?></p>
				
				<h3><?php _e('Shortcodes enabled by using points system', 'perception');?></h3>
				<p><?php _e('If you activate points system the following shortcodes become available:', 'perception');?></p>
				
				<ol>
					<li><input type="text" size="12" readonly onclick="this.select();" value="[perception-points]"> <?php _e('and', 'perception');?> <input type="text" size="14" readonly onclick="this.select();" value="[perception-points x]"> <?php _e('(where "x" is given user ID) outputs the total number of points the user has earned.', 'perception')?> </li>
					<li><input type="text" size="18" readonly onclick="this.select();" value="[perception-leaderboard x]"> <?php _e('and', 'perception');?> <input type="text" size="24" readonly onclick="this.select();" value="[perception-leaderboard x points]"> <?php _e('displays a leaderboard based on collected points. Replace "x" with the number of users you want to show. When you use the second shortcode the usernames will be shown in a table with the points collected in front of them.', 'perception');?> </li>
				</ol>
			</div>

			<?php echo do_action('perception-options-grading');?>			
			
			<input type="hidden" name="perception_grade_options" value="1">
			<?php wp_nonce_field('perception_grade_options');?>
			<p><input type="submit" class="button button-secondary" value="<?php _e('Save grade and points settings', 'perception')?>"></p>
		</div>
				</div>
	</form>		
	
	<form method="post" class="perception-form">
		<div class="postbox wp-admin perception-box">
		<div class="ps-card">
			<h2><?php _e('Assignments / Homework', 'perception')?></h2>
			
			<p><?php _e('Allowed file extensions for assignments that accept file uploads:', 'perception');?>
			<input type="text" size="30" name="allowed_file_types" value="<?php echo get_option('perception_allowed_file_types');?>" placeholder="Example: zip, doc, pdf"><br>
			<?php _e("If you leave this empty all file types will be accepted.", 'perception');?></p>
			
			<p><input type="checkbox" name="store_filesystem" value="1" <?php if(get_option('perception_store_files_filesystem') == '1') echo 'checked';?>> <?php printf(__('Store the files in the filesystem instead of the database. This is not secure: these files are accessible to everyone. At the very least we suggest installing <a href="%s" target="_blank">Protect uploads</a> to disallow browsing the uploads folder. Better option is to set custom folder below.', 'perception'), 'https://wordpress.org/plugins/protect-uploads/');?></p>
			
			<p><input type="checkbox" name="file_upload_progress" value="1" <?php if(get_option('perception_file_upload_progress') == '1') echo 'checked';?>> <?php _e('Use ajax-based file upload with a progress bar.', 'perception');?> <b><?php _e('Note: this will not work in Internet Explorer 7-9 and other old browsers.', 'perception');?></b></p>
			
			<p><?php _e('Use custom protected folder:', 'perception');?> <input type="text" name="protected_folder" value="<?php echo get_option('perception_protected_folder');?>" placeholder="<?php _e('Example: perception_homework', 'perception');?>">
			<?php _e('Name only, not full path. Use only letters, numbers and underscore. The folder will be created under folder uploads.', 'perception');?></p>
			
			<p><?php _e('Total file size limit per submitted solution:', 'perception');?> <input type="text" name="homework_size_total" size="4" value="<?php echo get_option('perception_homework_size_total');?>"> <?php _e('KB', 'perception');?>
			&nbsp;<?php _e('Maximum size of each uploded file:', 'perception');?> <input type="text" name="homework_size_per_file" size="4" value="<?php echo get_option('perception_homework_size_per_file');?>"> <?php _e('KB', 'perception');?>
			<?php _e('(Leave empty or enter 0 in any of the file size limit boxes to set unlimited size.)', 'perception');?></p>
			
			<p><input type="submit" class="button button-secondary" name="save_homework_options" value="<?php _e('Save homework settings', 'perception')?>"></p>
		</div>
		<?php wp_nonce_field('perception_homework_options');?>
			</div>
	</form>	
	
	<form method="post" class="perception-form">
		<div class="postbox wp-admin perception-box">
		<div class="ps-card">
			<h2><?php _e('Payment Settings', 'perception')?></h2>
			
			<p><label><?php _e('Payment currency:', 'perception')?></label> <select name="currency" onchange="this.value ? jQuery('#customCurrency').hide() : jQuery('#customCurrency').show(); ">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			<option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'perception')?></option>
			</select>
			<input type="text" id="customCurrency" name="custom_currency" style='display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>' value="<?php echo $currency?>"></p>
			
			<p><?php _e('Here you can specify payment methods that you will accept to give access to courses. When a course requires payment, the enrollment (pending or active - depends on your other course settings) will be entered after the payment is completed.', 'perception')?></p>
			
			<p><input type="checkbox" name="accept_paypal" value="1" <?php if($accept_paypal) echo 'checked'?> onclick="this.checked?jQuery('#paypalDiv').show():jQuery('#paypalDiv').hide()"> <?php _e('Accept PayPal', 'perception')?></p>
			
			<div id="paypalDiv" style='display:<?php echo $accept_paypal?'block':'none'?>;'>
				<p><input type="checkbox" name="paypal_sandbox" value="1" <?php if(get_option('perception_paypal_sandbox')=='1') echo 'checked'?>> <?php _e('Use Paypal in sandbox mode', 'watupro')?></p>
				<p><label><?php _e('Your Paypal ID:', 'perception')?></label> <input type="text" name="paypal_id" value="<?php echo get_option('perception_paypal_id')?>"></p>
				<p><label><?php _e('After payment go to:', 'perception')?></label> <input type="text" name="paypal_return" value="<?php echo get_option('perception_paypal_return');?>" size="40"> <br />
				<?php _e('When left blank it goes to the course page. If you enter specific full URL, the user will be returned to that URL.', 'perception')?> </p>
				
				<?php if(empty($use_pdt)):?>
				<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'perception')?></b></p>
				<?php endif;
				if(!perception_is_secure() and empty($use_pdt)):?>
					<p style="color:red;font-weight:bold;"><?php _e('Your site is not running on SSL so Paypal IPN will typicall not work. You MUST use the PDT option below.', 'perception');?></p>
				<?php endif;?>				
			
			<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'perception'), 'http://blog.calendarscripts.info/watupro-intelligence-module-using-paypal-data-transfer-pdt-instead-of-ipn/');?></p>
			
			<div id="paypalPDTToken" style='display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>'>
				<p><label><?php _e('Paypal PDT Token:', 'perception');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('perception_pdt_token');?>" size="60"></p>
			</div>
			</div>
			
			<p><input type="checkbox" name="accept_stripe" value="1" <?php if($accept_stripe) echo 'checked'?> onclick="this.checked?jQuery('#stripeDiv').show():jQuery('#stripeDiv').hide()"> <?php _e('Accept Stripe', 'perception')?></p>
			
			<div id="stripeDiv" style='display:<?php echo $accept_stripe?'block':'none'?>;'>
				<p><label><?php _e('Your Public Key:', 'perception')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('perception_stripe_public')?>"></p>
				<p><label><?php _e('Your Secret Key:', 'perception')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('perception_stripe_secret')?>"></p>
			</div>
			
			<p><input type="checkbox" name="accept_moolamojo" <?php if($accept_moolamojo) echo 'checked';?> value="1" onclick="this.checked ? jQuery('#perceptionPayMoola').show() : jQuery('#perceptionPayMoola').hide();"> <?php printf(__('Accept virtual credits from <a href="%s" target="_blank">MoolaMojo</a> (The plugin must be installed and active).', 'perception'), 'https://moolamojo.com')?></p>

			<div id="perceptionPayMoola" style='display:<?php echo $accept_moolamojo ? 'block' : 'none';?>'>
				<p><label><?php printf(__('Cost of 1 %s in virtual credits:', 'perception'), $currency)?></label> <input type="text" name="moolamojo_price" value="<?php echo get_option('perception_moolamojo_price')?>" size="6"></p>
				<p><b><?php _e('Design of the payment button.', 'perception')?></b>
				<?php _e('You can use HTML and the following codes:', 'perception')?> {{{credits}}} <?php _e('for the price in virtual credits,', 'perception')?> {{{button}}} <?php _e('for the payment button itself and', 'perception')?> [moolamojo-balance] <?php _e('to display the currently logged user virtual credits balance.', 'perception')?></p>
				<p><textarea name="moolamojo_button" rows="7" cols="50"><?php echo stripslashes($moolamojo_button)?></textarea></p>
				<hr>	
			</div>
			
			<p><input type="checkbox" name="accept_other_payment_methods" value="1" <?php if($accept_other_payment_methods) echo 'checked'?> onclick="this.checked?jQuery('#otherPayments').show():jQuery('#otherPayments').hide()"> <?php _e('Accept other payment methods.', 'perception')?> 
				<span class="perception_help"><?php _e('This option lets you paste your own button HTML code or other manual instructions, for example bank wire. These payments will have to be processed manually unless you can build your own script to verify them.','perception')?></span></p>
				
			<div id="otherPayments" style='display:<?php echo $accept_other_payment_methods?'block':'none'?>;'>
				<p><?php _e('Enter text or HTML code for payment button(s). You can use the following variables: {{course-id}}, {{course-name}}, {{user-id}}, {{amount}}.', 'perception')?></p>
				<textarea name="other_payment_methods" rows="8" cols="80"><?php echo stripslashes(get_option('perception_other_payment_methods'))?></textarea>
				<p><?php printf(__('If you want to use Instamojo we have a <a href="%s" target="_blank">free plugin</a> for integration with the service.', 'perception'), 'http://blog.calendarscripts.info/instamojo-integration-for-perception-lms/');?></p>			
			</div>	
			
			<?php echo do_action('perception-options-payments');?>
			
			<p><input type="submit" class="button button-secondary" value="<?php _e('Save payment settings', 'perception')?>"></p>
			
			<?php if(!empty($payment_errors)):?>
				<p><a href="#" onclick="jQuery('#perceptionErrorlog').toggle();return false;"><?php _e('View payments errorlog', 'perception')?></a></p>
				<div id="perceptionErrorlog" style="display:none;"><?php echo nl2br($payment_errors)?></div>
			<?php endif;?>	
		</div>
		
		<input type="hidden" name="perception_payment_options"  value="1">
		<?php echo wp_nonce_field('save_payment_options', 'nonce_payment_options');?>	
				</div>
	</form>
	
	<form method="post" class="perception-form">
		<div class="postbox wp-admin perception-box">
		<div class="ps-card">
			<h2><?php _e('Exam/Test Related Settings', 'perception')?></h2>
			
			<p><?php _e('Perception LMS utilizes the power of existing WordPress plugins to handle exams, tests and quizzes. At this moment it can connect with two plugins:', 'perception')?> <a href="http://wordpress.org/extend/plugins/watu/">Watu</a> <?php _e('(Free) and ', 'perception')?> <a href="http://calendarscripts.info/watupro/?r=perception">WatuPRO</a> <?php _e('(Premium)', 'perception')?></p>
			
			<p><?php _e('If you have any of these plugins installed and activated, please choose which one to use for handling tests below:', 'perception')?></p>
			
			<p><input type="radio" name='use_exams' <?php if(empty($use_exams)) echo 'checked'?> value="0"> <?php _e('I don not need to create any exams or tests.', 'perception')?></p>
			
			<?php if($watu_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watu')) echo 'checked'?> value="watu"> <?php _e('I will create exams with Watu.', 'perception')?></p>
			<?php endif;?>
			
			<?php if($watupro_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watupro')) echo 'checked'?> value="watupro"> <?php _e('I will create exams with WatuPRO.', 'perception')?></p>
			<?php endif;?>
			
			<?php if($watu_active or $watupro_active):?>
				<p><input type="checkbox" name="access_exam_started_lesson" value="1" <?php if(get_option('perception_access_exam_started_lesson') == '1') echo 'checked'?>> <?php _e('Exams that are required by lessons will be accessible only after the associated lesson has been started.', 'perception')?> </p>
				<p><input type="checkbox" name="cleanup_exams" value="yes" <?php if(get_option('perception_cleanup_exams') == 'yes') echo 'checked'?>> <?php _e('When I cleanup student course data from the "Manage Students" page I want any related exam data for this student also to be REMOVED.', 'perception')?> </p>
			<?php endif;?>
			
			<p><input type="submit" class="button button-secondary" value="<?php _e('Save Exam Options', 'perception')?>" name="perception_exam_options"></p>
		</div>
				</div>
		
		<?php echo do_action('perception-options-exams');?>		
		
		<?php echo wp_nonce_field('save_exam_options', 'nonce_exam_options');?>
	</form>	
</div>	
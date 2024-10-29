<div class="wrap">
   <h1><?php _e('Shortcode generator', 'perception');?></h1>
   
   <form method="post">
      <h3><?php _e('Generate shortcode for listing lessons in  course or module:', 'perception');?></h3>
      <table class="form-table">
            <tbody>
               <tr>
                  <th><?php _e('Select course:','perception');?></th>
                  <td><select name="course_id" onchange="PSPerceptionLMSSelectCourse(this.value);">
         <option value=""><?php _e('Dynamic / Current (place shortcode inside the course contents)', 'perception');?></option>
         <?php foreach($courses as $course):?>
            <option value="<?php echo $course->ID?>" <?php if(!empty($_POST['course_id']) and $_POST['course_id'] == $course->ID) echo 'selected';?>><?php echo stripslashes($course->post_title);?></option>
         <?php endforeach;?>
      </select></td>
               </tr>
               <tr>
                  <th><?php if($use_modules == 1):?></th>
                  <td><span id="perceptionModules" style="display:<?php echo empty($_POST['course_id']) ? 'none' : 'inline';?>">        
            <span id="perceptionModuleID">
               <?php _e('Select module:', 'perception');?>
               <select name="perception_module">
                  <option value=""><?php _e('- No module-', 'perception');?></option>
                  <?php if(count($modules)):
                     foreach($modules as $module):?>
                     <option value="<?php echo $module->ID?>" <?php if(!empty($_POST['perception_module']) and $_POST['perception_module'] == $module->ID) echo 'selected';?>><?php 
                        echo stripcslashes($module->post_title);?></option>
                  <?php endforeach; 
                  endif;?>
               </select>
            </span>
         </span>
      <?php endif;?>   
               </td>
               </tr>
               <tr>
                  <th></th>
                  <td><input type="checkbox" name="status" value="1" <?php if(!empty($_POST['status'])) echo 'checked'?> onclick="if(!this.checked && this.form.show_grade.checked) this.form.show_grade.checked=false;if(this.checked) {jQuery('#listTagColumn').hide();} else {jQuery('#listTagColumn').show();}"> <?php _e('Include status column.', 'perception');?></td>
               </tr>
               <tr>
                  <th><?php _e('Order by:', 'perception');?></th>
                  <td><select name="orderby">
         <option value=""><?php _e('Default', 'perception');?></option>
         <option value="post_date" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_date') echo 'selected'?>>post_date</option>
         <option value="post_title" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_title') echo 'selected'?>>post_title</option>
         <option value="post_status" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_status') echo 'selected'?>>post_status</option>
         <option value="menu_order" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'menu_order') echo 'selected'?>>menu_order</option>
         <option value="comment_count" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'comment_count') echo 'selected'?>>comment_count</option>
      </select></td>
               </tr>
               <tr>
                  <th><?php _e('Direction:', 'perception');?></th>
                  <td><select name="dir">
         <option value="ASC"><?php _e('Ascending', 'perception');?></option>
         <option value="DESC" <?php if(!empty($_POST['dir']) and $_POST['dir'] == 'DESC') echo 'selected'?>><?php _e('Descending', 'perception');?></option>
      </select></td>
               </tr>
               <tr>
                  <th></th>
                  <td><p style="display:<?php echo empty($_POST['status']) ? 'block' : 'none';?>" id="listTagColumn"><?php _e('List tag:', 'perception')?><select name="list_tag">
         <option value="ul"><?php _e('Unnumerated list ("ul")', 'perception');?></option>
         <option value="ol" <?php if(!empty($_POST['list_tag']) and $_POST['list_tag'] == 'ol') echo 'selected'?>><?php _e('Numerated list ("ol")', 'perception');?></option>
      </select></td>
               </tr>
               <tr>
                  <th>
                  <?php _e('Show post excerpts', 'perception');?>
                  </th>
                  <td>
                  <input type="checkbox" name="show_excerpts" value="1" <?php if(!empty($_POST['show_excerpts'])) echo 'checked'?>>
                  </td>
               </tr>
               <tr>
                  <th><?php _e('Show lesson grade (requires the include status column to be included)', 'perception');?> </th>
                  <td>
                  <input type="checkbox" name="show_grade" value="1" <?php if(!empty($_POST['show_grade'])) echo 'checked'?> onclick="if(this.checked && !this.form.status.checked) this.checked=false;">
                  </td>
               </tr>
            </tbody>
      </table>
      <p class="submit"><input type="submit" name="generate_course_lessons" class="button button-primary" value="<?php _e('Generate shortcode', 'perception')?>"></p>
      <?php if(!empty($_POST['generate_course_lessons'])):?>
         <p><input type="text" value='[<?php
         echo empty($_POST['perception_module']) ? 'perception-course-lessons ' : 'perception-module-lessons ';
         echo empty($_POST['status']) ? '0 ' : 'status ';
         echo empty($_POST['course_id']) ? '0 ' : (empty($_POST['perception_module']) ? $_POST['course_id'].' ' : $_POST['perception_module'].' ');
         if(!empty($_POST['orderby'])): echo $_POST['orderby'].' '.$_POST['dir'].' ';
         else: echo "0 ASC ";
         endif;
         if(!empty($_POST['list_tag'])) echo $_POST['list_tag'].' ';
         if(!empty($_POST['show_excerpts'])) echo 'show_excerpts=1 '; 
         if(!empty($_POST['show_grade'])) echo 'show_grade=1 ';
         ?>]' size="60" readonly="readonly" onclick="this.select();"></p>
      <?php endif;?>
   </form>
</div>

<script type="text/javascript">
function PSPerceptionLMSSelectCourse(courseID) {
   <?php if($use_modules):?>
   data = {'action' : 'perception_ajax', 'type': 'load_modules', 'course_id': courseID};
   jQuery.post('<?php echo admin_url("admin-ajax.php");?>', data, function(msg){
       jQuery('#perceptionModules').show();
      jQuery('#perceptionModuleID').html(msg);
   });
   <?php endif;?>
   return true;
}
</script>
<?php
// stripe integration model
class PerceptionStripe {
	static function load() {
		require_once(PERCEPTION_PATH.'/lib/Stripe.php');
 
		$stripe = array(
		  'secret_key'      => get_option('perception_stripe_secret'),
		  'publishable_key' => get_option('perception_stripe_public')
		);
		 
		\Stripe\Stripe::setApiKey($stripe['secret_key']);
		
		return $stripe;
	}
	
	static function pay($currency) {
		global $wpdb, $user_ID, $user_email;
		$_course = new PSPerceptionLMSCourseModel();
		
		$token  = $_POST['stripeToken'];
		$course = get_post($_POST['course_id']);
		$fee = get_post_meta($course->ID, 'perception_fee', true);
		$fee = apply_filters('perception-coupon-applied', $fee, $course->ID);	// coupon code from other plugin?	
		
		// school price?
		$is_school = 0;
	   if(class_exists('PerceptionPROSchool') and !empty($_POST['is_school'])) {
	      $fee = PerceptionPROSchool :: school_price('course', $course);
	      $is_school = 1;
	   }	
		 
		try {
			 $customer = \Stripe\Customer::create(array(
		      'email' => $user_email,
		      'card'  => $token
		    ));				
			
			  $charge = \Stripe\Charge::create(array(
			      'customer' => $customer->id,
			      'amount'   => $fee*100,
			      'currency' => $currency
			  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		 
		// !!!!in the next version avoid this copy-paste
		// almost the same code is in models/payment.php for the paypal payments
		$wpdb->query($wpdb->prepare("INSERT INTO ".PERCEPTION_PAYMENT_METHOD." SET 
						course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='stripe'", 
						$_POST['course_id'], $user_ID, $fee, $token));
						
		do_action('perception-paid', $user_ID, $fee, "course", $_POST['course_id'], $is_school);				
						
		// enroll accordingly to course settings - this will be placed in a method once we 
		// have more payment options
		$enroll_mode = get_post_meta($course->ID, 'perception_enroll_mode', true);	
		if(!PSPerceptionLMSStudentModel :: is_enrolled($user_ID, $course->ID))  {
			$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
			$_course->enroll($user_ID, $course->ID, $status);
		}	
	}
}
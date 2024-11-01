<?php
class WatuChimpBridge {
   static function main() {
   	  global $wpdb;
   	  
   	  // save MailChimp API key and password
   	  if(!empty($_POST['set_key']) and check_admin_referer('watuchimp_settings')) {
			  $double_optin = empty($_POST['no_optin']) ? 0 : 1;   	  	
   	  	
   	  	  update_option('watuchimp_api_key', $_POST['api_key']);
   	  	  update_option('watuchimp_no_optin', $double_optin);
   	  }
   	  $api_key = get_option('watuchimp_api_key');
   	  if(!empty($api_key) and strstr($api_key, '-')) list($nothing, $dc) = explode('-', $api_key);
   	  else $dc = '';
   	  
   	  $url = 'https://'.$dc.'.api.mailchimp.com/2.0/';
   	  
   	  // select exams
   	  $exams = $wpdb->get_results("SELECT * FROM ".WATU_EXAMS." ORDER BY name");
   	  
   	  // select mailing lists from mailchimp
   	  $request = array(
   	  	 "apikey" => $api_key, 
   	  	 "sort_field" => "name",
   	  	 "sort_dir" => "ASC"
   	  );
   	  $data_string = json_encode($request);       
   	  $curl_result = $ch = curl_init($url.'lists/list/');
   	  
		  if(!$curl_result) wp_die("You need CURL library installed to send remote POST requests to MailChimp. Please contact your hosting support.");	   	  
   	              	                                                          
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			    'Content-Type: application/json',                                                                                
			    'Content-Length: ' . strlen($data_string))                                                                       
			);                                                                                                                   
			 
			$json_result = curl_exec($ch);   
			
			$result = json_decode($json_result);			
			$lists = @$result->data;
			   	  
   	  // add/edit/delete relation
   	  if(!empty($_POST['add']) and check_admin_referer('watuchimp_rule')) {
				// no duplicates		
				$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WATUCHIMP_RELATIONS."
					WHERE exam_id=%d AND list_id=%s AND grade_id=%d", $_POST['exam_id'], $_POST['list_id'], $_POST['grade_id']));   	  	
   	  	
   	  	if(!$exists) {
					$wpdb->query($wpdb->prepare("INSERT INTO ".WATUCHIMP_RELATIONS." SET 
						exam_id = %d, list_id=%s, grade_id=%d", $_POST['exam_id'], $_POST['list_id'], $_POST['grade_id']));
					}   	  
   	  }
   
   		if(!empty($_POST['save']) and check_admin_referer('watuchimp_rule')) {
				$wpdb->query($wpdb->prepare("UPDATE ".WATUCHIMP_RELATIONS." SET 
					exam_id = %d, list_id=%s, grade_id=%d WHERE id=%d", 
					$_POST['exam_id'], $_POST['list_id'], $_POST['grade_id'], $_POST['id']));   	  
   	  }
   	  
			if(!empty($_POST['del']) and check_admin_referer('watuchimp_rule')) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".WATUCHIMP_RELATIONS." WHERE id=%d", $_POST['id']));
			}   	  
   	  
   	  // select existing relations
   	  $relations = $wpdb->get_results("SELECT * FROM ".WATUCHIMP_RELATIONS." ORDER BY id");
   	  
   	  // select all non-category grades and match them to exams and relations
   	  $grades = $wpdb->get_results("SELECT * FROM ".WATU_GRADES." ORDER BY gtitle");
   	  
   	  foreach($exams as $cnt=>$exam) {
   	  	  $exam_grades = array();
   	  	  foreach($grades as $grade) {
   	  	  	if($grade->exam_id == $exam->ID) $exam_grades[] = $grade;
			  }
			  
			  $exams[$cnt]->grades = $exam_grades;
   	  }
   	  
   	  foreach($relations as $cnt=>$relation) {
   	  	  $rel_grades = array();
   	  	  foreach($grades as $grade) {
   	  	  	if($grade->exam_id == $relation->exam_id) $rel_grades[] = $grade;
			  }
			  
			  $relations[$cnt]->grades = $rel_grades;
   	  }
   	     	  
   	  include(WATUCHIMP_PATH."/views/main.html.php");
   }

	 // actually subscribe the user
	 static function complete_exam($taking_id) {
	 	  global $wpdb;
	 	  
	 	  // select taking		
	 	  $taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATU_TAKINGS." 	
	 	  	WHERE ID=%d", $taking_id));
	 	 
	 	  // if email not available, return false
			if(empty($taking->user_id) and empty($taking->email)) return false;
			
	 	  // see if there are any relations for this exam ID
	 	  $relations = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUCHIMP_RELATIONS." 
		 	  WHERE exam_id=%d", $taking->exam_id));
		 	
		 	if(!sizeof($relations)) return false;  
		 		
		$email = $taking->email;
		$name = @$taking->name; // NYI because we have no name in Watu
		if(empty($email)) {
			$user = get_userdata($taking->user_id);
      	$email = $user->user_email;
      	$name = $user->display_name;
		}
		
		// name still empty? Default to guest although that's not a great idea
		if(empty($name)) $name = 'Guest';
		
		// break $name into $fname and $lname if possible. If no space, $fname = $name
		if(strstr($name, ' ')) {
			$parts = explode(' ', $name);
			$fname = $parts[0];
			array_shift($parts);
			$lname = implode(' ', $parts);
		}
		else {
			$fname = $name;
			$lname = '';
		}
      
      // add to MailChimp
      $api_key = get_option('watuchimp_api_key');
   	list($nothing, $dc) = explode('-', $api_key);
   	$url = 'https://'.@$dc.'.api.mailchimp.com/3.0/';
   	
   	   	
   	$status = (get_option('watuchimp_no_optin') == '1') ? 'subscribed' : 'pending';  
   	
   	// select mailing lists from mailchimp
   	foreach($relations as $relation) {
		  // check grade
		  if(!empty($relation->grade_id) and $relation->grade_id != $taking->grade_id) continue;
		  
		  $api_url = 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $relation->list_id;
		  
		  // member already exists? Do not add them
			$result = wp_remote_get(
			  $api_url.'/members/'.md5(strtolower($email)),
			  [
			  	  'headers' => ['Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )]
			  ]
			);
			
			$exists = false;
			if(isset($result['body'])) {
				$result_body = json_decode($result['body']);
				if(!empty($result_body->status) and $result_body->status == 'subscribed') $exists = true;
			}
			
			if($exists) return true;
			
			
		  
   	  $args = array(
					'method' => 'PUT',
				 	'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
					),
					'body' => json_encode(array(
				    	'email_address' => $email,			    	
				    	"merge_fields" => array("FNAME" => $fname, "LNAME" =>$lname, "NAME" => $name),
						'status'        => $status
					))
				);
				
				
				$response = wp_remote_post( $api_url . '/members/' . md5(strtolower($email)), $args );
				 
				$body = json_decode( $response['body'] );
				 
				if ( $response['response']['code'] == 200 && $body->status == $status ) {
					//echo 'The user has been successfully ' . $status . '.';
					$result = 'The user has been successfully ' . $status . '.';
				} else {
					//echo '<b>' . $response['response']['code'] . $body->title . ':</b> ' . $body->detail;
					$result = '<b>' . $response['response']['code'] . $body->title . ':</b> ' . $body->detail;
					//print_r($response);
				}
			
			// log error
			// NYI
			
		} // end foreach relation	
   } // end complete exam
}
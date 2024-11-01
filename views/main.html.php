<div class="wrap">
	<h1><?php _e('Watu to MailChimp Bridge', 'watuchimp')?></h1>
	
	<p><?php _e('This bridge lets you automatically subscribe users who take your exams into select list in MailChimp. You need to know your MailChimp API key.', 'watuchimp')?></p>
	
	<p><?php _e('Note that in order to have quiz taker subscribed you need their email address. So this will work the user is logged or (for non-logged in users) when you have selected "Send email to the user with their results" in the exam settings page.','watuchimp')?> </p>
	
	<p><b><?php _e('Subscribers will be added to your MailChimp mailing list only after they confirm their email!', 'watuchimp');?></b></p>
	
	<form method="post">
		<p><?php _e('Your MailChimp API Key:', 'watuchimp');?> <input type="text" name="api_key" value="<?php echo $api_key?>" size="60"> <br />
		<input type="checkbox" name="no_optin" value="1" <?php if(get_option('watuchimp_no_optin') == 1) echo 'checked'?>> <?php _e('Do not require email confirmation (Abusing this may cause your MailChimp account to be suspended.)', 'watuchimp');?><br><br>
		<input type="submit" name="set_key" value="<?php _e('Save Settings', 'watuchimp');?>" class="button button-primary"></p>
		<?php wp_nonce_field('watuchimp_settings');?>
	</form> 
	
	<?php if(empty($api_key)):?>
		<p><b><?php _e('You will not be able to add any rules until you enter your MailChimp API Key.', 'watuchimp');?></b></p>
	<?php return;
	endif;
	if(!empty($result->error) or empty($result->total)):?>
		<p><?php _e("We couldn't retrieve any mailing lists from your MailChimp account.", 'watuchimp');?></p>
		<?php if(!empty($result->error)):?>
			<p><?php _e('We got this error from MailChimp:', 'watuchimp');?> <b><?php echo $result->error;?></b></p>
		<?php endif;?>
		<p><a href="#" onclick="jQuery('#mailChimpResponse').toggle();return false;"><?php _e('Raw MailChimp response (debug)', 'watuchimp');?></a></p>
		<div style="display:none;" id="mailChimpResponse"><?php echo $json_result;?></div>
	<?php endif;?>
   
   <h2><?php _e('Add New Rule', 'watuchimp')?></h2>	  
	  
	 <form method="post">
	 	<div class="wrap">
	 			<?php _e('When user completes', 'watuchimp')?> <select name="exam_id" onchange="wcChangeQuiz(this.value, 'wbbGradeSelector');">
	 			<?php foreach($exams as $exam):?>
	 				<option value="<?php echo $exam->ID?>"><?php echo stripslashes($exam->name);?></option>
	 			<?php endforeach;?>
	 			</select> 
				
				<?php _e('achieving the following grade:', 'watuchimp')?>
				<span id="wbbGradeSelector">
					<select name="grade_id">
					   <option value="0"><?php _e('- Any grade -', 'watuchimp');?></option>
					   <?php foreach($exams[0]->grades as $grade):?>
					   	<option value="<?php echo $grade->ID?>"><?php echo stripslashes($grade->gtitle);?></option>
					   <?php endforeach;?>
					</select>
				</span>				
				
				 			
	 			<?php _e('subscribe them to mailing list','watuchimp')?> 
	 			<select name="list_id">
	 				<?php if(!empty($lists) and is_array($lists)):
	 					foreach($lists as $list):?>
	 					<option value="<?php echo $list->id?>"><?php echo stripslashes($list->name);?></option>
	 				<?php endforeach;
	 				endif;?>
	 			</select>
	 			<input type="submit" name="add" value="<?php _e('Add Rule', 'watuchimp')?>" class="button button-primary">
	 	</div>
	 	<?php wp_nonce_field('watuchimp_rule');?>
	 </form> 
	 
	 <?php if(count($relations)):?>
	 	<h2><?php _e('Manage Existing Rules', 'watuchimp')?></h2>
	 	
	 	<?php foreach($relations as $relation):?>
	 	<form method="post">
	 	<input type="hidden" name="id" value="<?php echo $relation->id?>">
	 	<input type="hidden" name="del" value="0">
	 	<div class="wrap">
	 			<?php _e('When user completes', 'watuchimp')?> <select name="exam_id" onchange="wcChangeQuiz(this.value, 'wbbGradeSelector<?php echo $relation->id?>');">
	 			<?php foreach($exams as $exam):
	 				$selected = ($exam->ID == $relation->exam_id) ? " selected" : "";?>
	 				<option value="<?php echo $exam->ID?>"<?php echo $selected?>><?php echo stripslashes($exam->name);?></option>
	 			<?php endforeach;?>
	 			</select> 
	 			
				<?php _e('achieving the following grade:', 'watuchimp')?>
				<span id="wbbGradeSelector<?php echo $relation->id?>">
					<select name="grade_id">
					   <option value="0"><?php _e('- Any grade -', 'watuchimp');?></option>
					   <?php foreach($relation->grades as $grade):
					   	$selected = ($grade->ID == $relation->grade_id) ? " selected" : "";?>
					   	<option value="<?php echo $grade->ID?>"<?php echo $selected?>><?php echo stripslashes($grade->gtitle);?></option>
					   <?php endforeach;?>
					</select>
				</span>			
					 			
	 			<?php _e('subscribe them to mailing list','watuchimp')?> 
	 			<select name="list_id">
	 				<?php	if(!empty($lists) and is_array($lists)): 
	 					foreach($lists as $list):
	 					$selected = ($list->id == $relation->list_id) ? " selected" : "";?>
	 					<option value="<?php echo $list->id?>"<?php echo $selected?>><?php echo stripslashes($list->name)?></option>
	 				<?php endforeach;
	 				endif;?>
	 			</select>
	 			<input type="submit" name="save" value="<?php _e('Save Rule', 'watuchimp')?>" class="button button-primary">
	 			<input type="button" value="<?php _e('Delete Rule', 'watuchimp')?>" onclick="WCConfirmDelete(this.form);" class="button">
	 	</div>
	 	<?php wp_nonce_field('watuchimp_rule');?>
	 </form> 
	 	<?php endforeach;?>
	 <?php endif;?>
</div>

<script type="text/javascript" >
function WCConfirmDelete(frm) {
		if(confirm("<?php _e('Are you sure?', 'watuchimp')?>")) {
			frm.del.value=1;
			frm.submit();
		}
}

function wcChangeQuiz(quizID, selectorID) {
	// array containing all grades by exams
	var grades = {<?php foreach($exams as $exam): echo $exam->ID.' : {';
			foreach($exam->grades as $grade):
				echo $grade->ID .' : "'.$grade->gtitle.'",';
			endforeach;
		echo '},';
	endforeach;?>};
	
	// construct the new HTML
	var newHTML = '<select name="grade_id">';
	newHTML += "<option value='0'><?php _e('- Any grade -', 'watuchimp');?></option>";
	jQuery.each(grades, function(i, obj){
		if(i == quizID) {
			jQuery.each(obj, function(j, grade) {
				newHTML += "<option value=" + j + ">" + grade + "</option>\n";
			}); // end each grade
		}
	});
	newHTML += '</select>'; 
	
	jQuery('#'+selectorID).html(newHTML);
}
</script>
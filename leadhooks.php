<?php

function stripslashes_deep($value) {
  $value = is_array($value) ?
    array_map('stripslashes_deep', $value) :
    stripslashes($value);

  return $value;
}

if (get_magic_quotes_gpc()) {
  $unescaped_post_data = stripslashes_deep($_POST);
} else {
  $unescaped_post_data = $_POST;
}
$form_data = json_decode($unescaped_post_data['data_json']);

/*******************************
	PARSE DATA FROM UNBOUNCE
*******************************/
$email_address 			= $form_data->email[0];
$page_id			= $_POST['page_id'];						//Unbounce Page ID
$page_url 			= $_POST['page_url'];						//Unbounce Page URL
$variant 			= $_POST['variant'];     					//Unbounce Page Variant   

/*******************************
	SALESFORCE WEB TO LEAD
*******************************/

//create array of data to be posted to Salesforce
$post_data['oid']			= '<INSERT YOUR ORG ID HERE>';				//Salesforce ORG ID
$post_data['<INSERT FIELD NAME>'] 	= $page_id;						//Unbounce Page Id
$post_data['<INSERT FIELD NAME>'] 	= $page_url;						//Unbounce URL
$post_data['<INSERT FIELD NAME>'] 	= $variant;						//Unbounce Variant

$post_data['email'] 			= $email_address;					//Salesforce Lead Email Address				
$post_data['lead_source'] 	  	= '<INSERT LEAD SOURCE'>;				//Salesforce Lead Source

//$post_data['Campaign_ID']		= '<INSERT CAMPAIGN ID>';				//Salesforce Campaign ID
//$post_data['member_status']		= 'Responded';			  			 //Salesforce Campaign Member Status

/*********************************
	SENDING DATA TO SALESFORCE
*********************************/
foreach ( $post_data as $key => $value) {
    $post_items[] = $key . '=' . $value;
}
 
$post_string = implode ('&', $post_items);
 
//Set CURL to Salesforce web-to-lead url
$curl_connection = curl_init('https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8');	
 
curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
 
$result = curl_exec($curl_connection);
 
curl_close($curl_connection);                  

?>
<?php

/* $client = new SoapClient(null, array('location' => "http://joomla.vanithadevel.platinum.local/service/server.php",
                                     'uri'      => "http://test-uri/"));
									 
 echo $client->getQuote("Ali");*/
 
try{
  $sClient = new SoapClient('http://sugar6.vanithadevel.platinum.local//service/v4_1/soap.php?wsdl');
  
$auth_array = array(
			'user_name' => 'admin',
			'password' => md5('loaded01'),
	);
  $response = $sClient->login($auth_array);
  
  echo '<pre>';
  
 
    $query = "contacts.first_name LIKE '%Way%'";
    $result = $sClient->get_entry_list(
        $response->id,
        'Contacts',
        $query,
        'last_name',
        0,
        array(
            'id',
            'first_name',
            'last_name',
            'email1',
            'phone_work',
        ),
        10,
        false
    );

  $set_entry_params = array(
		array('name'=>'first_name','value'=>'Test 1'),
		array('name'=>'last_name','value'=>'Test 2'),
		array('name'=>'status', 'value'=>'New'),
		array('name'=>'phone_work', 'value'=>'1234567890'),
		array('name'=>'phone_fax', 'value'=>'1234567890'),
		array('name'=>'account_name','value'=>'SL'),
		array('name'=>'lead_source','value'=>'Web Site'),
		array('name'=>'description','value'=>'TEST'),
		array('name'=>'assigned_user_id', 'value'=>1));
 
	$sd = $sClient->set_entry($response->id,'Leads',$set_entry_params);
  //var_export($sd);
  
} catch(SoapFault $e){
  var_dump($e);
}
 
?>


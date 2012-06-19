<?php
/**
* @version $Id: vbulletin.class.php 393 2005-12-15 13:37:52Z leonsio $
* @package com_connector
* @copyright Copyright (C) 2005 Leonid Kogan. All rights reserved.
*/


// Parameter for the Module
$params=array(	'prefix' => 'Cookie Prefix',
		'server' => 'SOAP Server URL',
		'key' => 'Key to access SOAP Server');

// Wrapped Extreme Rules
$rules = array(
		'search'=> array(),
		'replace' => array()
		);


class vbulletin extends mosMainFrame
{
	var $__res= null;
	var $__status= false;
	var $__data;
	var $__userdata=null;
	var $__sessionid=null;
	var $__cookies=null;
	var $__nologin=false;

	function vbulletin($module,$id='')
	{
		global $database, $my, $mainframe;
		$this->__data = $module;
		$this->__data->id=$id;

		$this->_session =& $mainframe->_session;

		// Verbindung aufbauen
		try
		{
			$connect= new SoapClient($this->__data->get('server'));
			// falls Benutzer exestiert und bereits eingelogt war, benutzen wir seine Cookies
			$userid=($my->id) ? $my->id : $this->_session->userid ;
			if($userid !='' AND  $userid!=0)
			{
				// Cookies sammeln
				$database->setQuery("SELECT connector_cookies FROM #__users WHERE id='$userid'");
            			if ($connector_cookies=$database->loadResult()) 
            			{       
					// find some cookies on user side
					if(is_array($_COOKIE))
					{
						foreach($_COOKIE AS $name => $value)
						{
							if(strpos($name, $this->__data->get('prefix')) !== false )
							{
								if($name != $this->__data->get('prefix').'sessionhash')
								{
									$cookies2[$name]=array($value, time()+1440, '');
								}
							}
						}
                                        }

                    			$cookies = unserialize($connector_cookies);
		
					// if we dont have user cookie in the DB, we are useing client cookies
					if(isset($cookies[$this->__data->id]))
					{
						$cookies_send=$cookies[$this->__data->id];
					}
					else
					{
						$cookies_send=$cookies2;
					}

					// Cookies fuer den CALL setzen
					$connect->_cookies=$cookies[$this->__data->id];

					$this->__nologin=true;
            			}
			}

			$this->__res=$connect;
			$this->__status=true;
		}
		catch( SoapFault $ex)
		{
			$this->__status=false;	
		}

		return $this->__status;
	}

	function login($username,$password)
	{
		if(is_null($this->__res))
		{
			return $this->__status;
		}
		
		if($this->__nologin)
		{
			return $this->__status;
		}	

		// Benutzer einlogen
		try
		{
			$result=$this->__res->doLogin($this->__data->get('key'), utf8_encode($password), utf8_encode($username));
			// Benutzer setzen
			$this->__userdata->userid=$result->userid;
			$this->__status=true;
		}
		catch( SoapFault $ex)
		{
			$this->__status=false;
		}
		return $this->__status;
		
	}

	function activate()
	{
                if(is_null($this->__res))
                {       
                        return $this->__status;
                }    

		// Benutzer aktivieren
		try
		{
			$result=$this->__res->doActivateSession($this->__data->get('key'),
                                utf8_encode($_SERVER['HTTP_USER_AGENT']),$_SERVER['REMOTE_ADDR']);
			foreach($this->__res->_cookies AS $name => $value)
			{
				if(strpos( $name, 'sessionhash'))
				{
					$this->__sessionid='?s='.$value[0];
					break;
				}
			}
			$this->__status=true;
		}
		catch ( SoapFault $ex )
		{
			$this->__status=false;
		}
	
		return $this->__status;

	}

	function logout()
	{
                if(is_null($this->__res))
                {
                        return $this->__status;
                }
                $lifetime = time() - 1800;
                // cookies setzen
		if(is_array($this->__res->_cookies))
		{
			foreach ( $this->__res->_cookies as $name => $value)
			{
				setcookie($name, $value[0], $lifetime, $value[1], $value[2]);
			}
		}
		// force logout
                setcookie( $this->__data->get('prefix')."userid", " ", $lifetime, "/" );
                setcookie( $this->__data->get('prefix')."password", " ", $lifetime, "/" );
                setcookie( $this->__data->get('prefix')."sessionhash", " ", $lifetime, "/" );
	}

	function userset($username, $password, $email)
	{
                if(is_null($this->__res))
                {       
                        return $this->__status;
                }    

		// Benutzer zu Vbulletin hinzufuegen
                try     
                {
			$result=$this->__res->setUser($this->__data->get('key'),utf8_encode($username), 
										utf8_encode($password), utf8_encode($email));
			$this->__userdata['userid']=$result;
			$this->__status=true;
		}
		catch ( SoapFault $ex )
		{
			$this->__status=false;
		}

		return $this->__status;
	}

	function userget($userid)
	{

                if(is_null($this->__res))
                {       
                        return $this->__status;
                }    

		// Benutzerinformationen sammeln
		try
		{
			$userdata=$this->__res->getUser($this->__data->get('key'),$userid);
			$this->__userdata=$userdata;
			$this->__userdata->name=utf8_decode($userdata->username);
			$this->__status=true;
		}
		catch ( SoapFault $ex )
		{
			$this->__status=false;
		}

		return $this->__status;
	}

	function userupd($old_name, $username='', $password='', $email='')
	{
                if(is_null($this->__res))
                {       
                        return $this->__status;
                }    

		try
		{
                        $result=$this->__res->updateUser($this->__data->get('key'),utf8_encode($old_name),'username', utf8_encode($username),
                                                                                utf8_encode($password), utf8_encode($email));
			$this->__status=true;
		}
		catch ( SoapFault $ex)
		{
			$this->__status=false;
		} 

		return $this->__status;
	}

        function userdel($username='')
        {
                if(is_null($this->__res))
                {
                        return $this->__status;
                }

                try
                {
                        $result=$this->__res->deleteUser($this->__data->get('key'), utf8_encode($username), 'username');
                        $this->__status=true;
                }
                catch ( SoapFault $ex)
                {
                        $this->__status=false;
                }

                return $this->__status;


        }

	function getcookies()
	{
		return $this->__res->_cookies;
	}

}


?>

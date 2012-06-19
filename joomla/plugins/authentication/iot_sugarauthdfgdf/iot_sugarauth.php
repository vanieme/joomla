<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');


class plgAuthenticationIot_sugarauth extends JPlugin {

  const NS = "IOT_SUGARAUTH";

  /** @var Accounts $account */
  protected static $account;
  protected $groups = false;
  
	/**
	 * Constructor
	 *
	 * @param JDispatcher $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
    $subject->attach($this);
	}


	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	array  username = Username for authentication,	password = Password for authentication
	 * @param	array	$options Array of extra options
	 * @param	JAuthenticationResponse	$response Authentication response object
	 * @return	boolean
	 * @since	1.5
	 */
	function onUserAuthenticate( $credentials, $options, &$response ) {
    jimport('joomla.user.helper');
    if (isset($credentials['username'])) Yii::trace('Username/Email: '.$credentials['username']);
    try {
      # are they logging in with a username or email address?
      if (preg_match('/[^@]+@[^\.]+\..+/',$credentials['username'])) {
        /** @var $list EmailAddresses[] */
        $list = EmailAddresses::model()->findAllByAttributes(array('email_address_caps'=>strtoupper($credentials['username'])));
        $result = array();
        foreach ($list as $emailaddress) {
          $result += $emailaddress->getAccounts();
        }
        $id = JUserHelper::getUserId($credentials['username']);
        if ($id > 0) {
          $instance = new JUser();
          $instance->load($id);
        }
      } else {
        /** @var $result Accounts[] */
        $result = Accounts::model()->with('mems_memberships')->findAll('mems_membership.user_name = :username', array(':username'=>$credentials['username']));
        if (count($result) == 0) {
          $id = JUserHelper::getUserId($credentials['username']);
          if ($id > 0) {
            $instance = new JUser();
            $instance->load($id);
            if (!isset($instance) || $this->getSugarGroup($instance) === false || array_search("Administrator", $this->getSugarGroup($instance)) === false) {
              $replaceUsername = false;
              foreach (array(
                "@tradingpursuits.com",
                "@platinumpursuits.com",
                "@tradingpursuits.com.au",
                "@platinumpursuits.com.au",
              ) as $domain) {
                if (strpos($instance->email, $domain) !== false) {
                  $replaceUsername = true;
                }
              }
              if ($replaceUsername) $credentials['username'] = $instance->email;
            }
            $list = EmailAddresses::model()->findAllByAttributes(array('email_address_caps'=>strtoupper($instance->email)));
            $result = array();
            foreach ($list as $emailaddress) {
              $result += $emailaddress->getAccounts();
            }
          } else {
            throw new Exception("No Client found: " . $credentials['username']);
          }
        } else if (!empty($result[0]->getPrimaryEmailAddress()->email_address)) {
            $credentials['username'] = $result[0]->getPrimaryEmailAddress()->email_address;
        }
      }
    } catch (Exception $e) {
      $response->status = JAuthentication::STATUS_FAILURE;
      $response->error_message = 'System error.';
      Yii::app()->setSystemMessage($response->error_message, 'error');
      Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
      return false;
    }
    if (count($result) == 0) {
      $response->status = JAuthentication::STATUS_FAILURE;
      $response->error_message = 'System error.';
      Yii::app()->setSystemMessage($response->error_message, 'error');
      return false;
    }
    /** This part is from old password hashing **/
    $missingEmailAddress = $loginWithToken = $valid = false;
    $sitename = Yii::app()->params["joomla"]["sitename"];
    /** @var $account Accounts */
    foreach ($result as $account) {
      if ($account->getPrimaryEmailAddress() != null && $account->getPrimaryEmailAddress()->email_address == "none@none.com") {
        $missingEmailAddress = true;
      }
      if (isset(Yii::app()->params["itontap"]["privatepassword"]) && Yii::app()->params["itontap"]["privatepassword"] == true) {
        if ($account->authenticateUserOnMembership($credentials['password'], $sitename)) {
          $response->fullname  = $account->accounts_cstm->first_name_c.' '.$account->accounts_cstm->last_name_c;
          $this->setEmailAddressAsUserName($account, $response, $credentials['username']);
          $response->email = $credentials['username'];
          $response->status = JAuthentication::STATUS_SUCCESS;
          $response->country = $account->billing_address_country;
          $response->password_clear = $credentials['password'];
          $valid = true;
          break;
        }
      }
      if ($account->authenticateUser($credentials['password'])) {
        $response->fullname  = $account->accounts_cstm->first_name_c.' '.$account->accounts_cstm->last_name_c;
        $this->setEmailAddressAsUserName($account, $response, $credentials['username']);
        $response->email = $credentials['username'];
        $response->status = JAuthentication::STATUS_SUCCESS;
        $response->country = $account->billing_address_country;
        $response->password_clear = $credentials['password'];
        $valid = true;
        break;
      }
    }
    if (!$valid) {
      $response->status = JAuthentication::STATUS_FAILURE;
      $response->error_message .= 'Invalid username and password.';
      Yii::app()->setSystemMessage($response->error_message, 'error');
      if ($missingEmailAddress) {
        Yii::app()->setSystemMessage(
          'The system does not have your email address, please contact our customer service to update your record.',
          'error'
        );
      }
      return false;
    }
    self::$account = $account;
    $groups = array();
    if (isset($instance)) $groups = $this->getSugarGroup($instance);
    if (!$account->hasValidMembership($sitename) && isset(Yii::app()->params["itontap"]["enablemembership"]) && Yii::app()->params["itontap"]["enablemembership"] == true) {
      $response->status = JAuthentication::STATUS_FAILURE;
      $response->error_message = 'Membership does not exist or has expired.';
      Yii::app()->setSystemMessage($response->error_message, 'error');
      Yii::log("Membership does not exist.", CLogger::LEVEL_WARNING);
      return false;
    }
    if ((count($groups) == 0) && isset(Yii::app()->params["itontap"]["enablegrouppermission"]) && Yii::app()->params["itontap"]["enablegrouppermission"] == true) {
      if (JFile::exists( JPATH_SITE.DS.'components'.DS.'com_juga'.DS.'juga.class.php' ) ) {
        $response->status = JAuthentication::STATUS_FAILURE;
        $response->error_message = 'User does not belong to any group.';
        Yii::app()->setSystemMessage($response->error_message, 'error');
        Yii::log("User does not belong to any group and JUGA is installed.", CLogger::LEVEL_WARNING);
        return false;
      }
    }
    $accountInfo = array_map('htmlentities', $account->getAttributes());
    JFactory::getSession()->set("sugar_account_info", $accountInfo, self::NS);
    return true;
	}
}
?>

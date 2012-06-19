<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
include_once(JPATH_ROOT.DS.'application/yii.php');

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
  * Get Sugar CRM Group assign to the user.
  * Return false is there is no valid membership, otherwise return the list of groups in array.
  *
  * @param $user JUser
  */
  function getSugarGroup($user) {
    if ($this->groups !== false) return $this->groups;
    if (empty(self::$account)) {
      $accountDetail = JFactory::getSession()->get("sugar_account_info", array(), self::NS);
      if (count($accountDetail) == 0) return false;
      self::$account = Accounts::model()->findByPk($accountDetail['id']);
    }
    $sitename = Yii::app()->params["joomla"]["sitename"];
    $groups = array_merge(
      self::$account->getEnrolmentTransactionUserAccessGroup($sitename),
      self::$account->getUserAccessGroup($sitename)
    );
    if (isset(Yii::app()->params['permissionOnQuiz']) || isset(Yii::app()->params['membershipOnQuiz'])) {
      $builder = Yii::app()->getDb()->getCommandBuilder();
      $c = new CDbCriteria();
      $c->addCondition("c_quiz_id = :qid AND c_student_id = :joomlaid and c_passed = 1");
      $command = $builder->createCountCommand(Yii::app()->params['joomla']['dbprefix'].'quiz_r_student_quiz', $c);
      if (isset(Yii::app()->params['permissionOnQuiz'])) {
        foreach (Yii::app()->params['permissionOnQuiz'] as $quizid => $permission) {
          if ($command->queryScalar(array(":qid"=>$quizid,':joomlaid'=>$user->id)) > 0) {
            $groups[] = UagUserAccessGroup::model()->findByPk($permission);
          }
        }
      }
      if (isset(Yii::app()->params['membershipOnQuiz'])) {
        foreach (Yii::app()->params['membershipOnQuiz'] as $quizid => $productid) {
          if ($command->queryScalar(array(":qid"=>$quizid,':joomlaid'=>$user->id)) > 0) {
            /** @var $item TrannItems */
            $item = TrannItems::model()->findByPk($productid);
            if ($item) {
              if (MemsMembership::model()->count(
                'products_id = :product and account_id = :account',
                array(':product'=>$item->getPrimaryKey(), ':account'=>self::$account->getPrimaryKey())
              ) == 0) {
                $membership = ObjectBuilder::buildProductItemMemsMembership($item, self::$account);
                $membership->note = "Membership given on Quiz Completion";
                $membership->save(false);
                $groups = array_merge($groups, $item->user_access_groups);
              }
            }
          }
        }
      }
    }
    $groups = ArrayHelper::uniqueModelList($groups);
    $this->groups = ArrayHelper::extractListOfValuesFromModels($groups, 'name');
    Yii::log("User belong to the following groups", CLogger::LEVEL_INFO, "application");
    Yii::log(implode(',', $this->groups), CLogger::LEVEL_INFO, "application");
    return $this->groups;
  }

  function _stripUsername($username) {
    return preg_replace ('/[^A-Za-z0-9\.]+/','', $username);
  }

  /**
   * Set email address as member username.
   * @param Accounts $account
   * @param JAuthenticationResponse $response
   */
  function setEmailAddressAsUserName($account, $response, $emailaddress) {
    $db =& JFactory::getDBO();
    $query = "
        SELECT
          `id`,
          `username`
        FROM
          `#__users`
        WHERE
          `email` = '{$emailaddress}'
        ";
    $db->setQuery($query);
    $result = $db->loadObject();
    foreach (array(
      "@tradingpursuits.com",
      "@platinumpursuits.com",
      "@tradingpursuits.com.au",
      "@platinumpursuits.com.au",
    ) as $domain) {
      if (strpos($emailaddress, $domain) !== false) {
        return true;
      }
    }
    if (isset($result->username) && $result->username != $emailaddress) {
      $query = "
          UPDATE
            `#__users`
          SET
            `username` = '{$emailaddress}'
          WHERE
            `id` = '{$result->id}'
          LIMIT 1
          ";
      $db->setQuery($query);
      $db->query();
    }
    $response->username = $emailaddress;
  }

  function onUserLogin($user, $options = array()) {
    jimport('joomla.user.helper');
    $instance = new JUser();
    if($id = intval(JUserHelper::getUserId($user['username']))) {
      $instance->load($id);
      if (self::$account) {
        $instance->setParam('accountid', self::$account->getPrimaryKey());
        $instance->save(true);
      } else if (($accountid = $instance->getParam("accountid", false)) != false) {
        self::$account = Accounts::model()->findByPk($accountid);
      } else return true;
      $accountcode = self::$account->account_code;
      /** @var $cs CClientScript */
      $cs = Yii::app()->clientScript;
      $js = <<<JS
        var _gaq = _gaq || [];
        _gaq.push(['_setCustomVar', 1, "Account Code", "$accountcode", 1]);
JS;
      $cs->registerScript("iot_sugarauth", $js, CClientScript::POS_HEAD);
      $groups = JAccess::getGroupsByUser($instance->id);
      $groupReset = true;
      foreach ($groups as $gid) {
        if ($gid >= 6 && $gid <= 8) {
          $groupReset = false;
        }
      }
      if ($groupReset) {
        try {
          $groups = $this->getSugarGroup($instance);
          if ($groups === false) return false;
          $db = JFactory::getDBO();
          $groups = array_merge($groups, array('Registered'));
          $query = "SELECT id, title "
                 . "FROM #__usergroups";
          $db->setQuery($query);
          $groupinSys = $db->loadAssocList('id','title');
          $addedGroup = array();
          $addedGroupId = array();
          foreach ($groups as $k=>$g) {
            if (! in_array($g, $groupinSys) || in_array($g, $addedGroup)) unset($groups[$k]);
            else {
              $groupid = array_search($g, $groupinSys);
              $addedGroupId[] = $groupid;
              $addedGroup[] = $g;
            }
          }
          Yii::trace("Groups ID" . var_export($addedGroupId, true));
          Yii::trace("Groups Name" . var_export($addedGroup, true));
          JUserHelper::setUserGroups($instance->id, $addedGroupId);
          return true;
        } catch (Exception $e) {
          Yii::trace($e->getMessage());
          return false;
        }
      }
    }
    return true;
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

<?php

/**
 * UniLogInController
 *
 * @author Elvinas Liutkevičius <elvinas@unisolutions.eu>
 * @license BSD http://silverstripe.org/BSD-license
 */
class UniLogInController extends Controller implements PermissionProvider {

	const ACL_CHECK_NONE               = 0; // should not be used
	const ACL_CHECK_ADMIN              = 1; // default
	const ACL_CHECK_PERMISSSIONS       = 2;
	const ACL_CHECK_ALLOWED_IP         = 4;
	const ACL_CHECK_ALLOWED_EMAIL_ADDR = 8;

	private static $access_control_policy = 'self::ACL_CHECK_ADMIN';

	private static $allowed_ip_list = array();

	private static $allowed_email_list = array();


	private static $allowed_actions = array(
		'as_member',
	);


	private function getConfiguredPolicy() {
		// TODO. eval() should not be used here?
		return eval($this->config()->access_control_policy);
	}

	private function isACLFlagSet($flag) {
		return (($this->getConfiguredPolicy() & $flag) == $flag);
	}

	private function access_allowed() {
		if ($this->getConfiguredPolicy() == self::ACL_CHECK_NONE) {
			return true;
		}

		$flag_set = false;
		$result = true;

		if ($this->isACLFlagSet(self::ACL_CHECK_ADMIN)) {
			$flag_set = true;
			$result = $result && Permission::check('ADMIN');
		}

		if ($this->isACLFlagSet(self::ACL_CHECK_PERMISSSIONS)) {
			$flag_set = true;
			$result = $result && Permission::check('UniLogIn');
		}

		if ($this->isACLFlagSet(self::ACL_CHECK_ALLOWED_IP)) {
			$flag_set = true;
			$result = $result && in_array($_SERVER['REMOTE_ADDR'], $this->config()->allowed_ip_list);
		}

		if ($this->isACLFlagSet(self::ACL_CHECK_ALLOWED_EMAIL_ADDR)) {
			$flag_set = true;
			$result = $result && in_array(Member::currentUser()->Email, $this->config()->allowed_email_list);
		}

		return $flag_set ? $result : false;
	}

	public function init() {
		if (!Member::currentUserID() || !$this->access_allowed()) {
			$this->httpError(404);
		}
		parent::init();
	}

	public function index() {
		Requirements::css(UNILOGIN_MODULE_DIR . '/css/style.css');
		// TODO. Add as composer requirement?
		Requirements::css('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css');

		if (class_exists('SiteTree')) {
			$tmpPage = new Page();
			$tmpPage->Title = _t('UniLogIn.Title', 'Choose member to login');
			$tmpPage->URLSegment = "unilogin";
			// Disable ID-based caching  of the log-in page by making it a random number
			$tmpPage->ID = -1 * rand(1, 10000000);

			$controller = Page_Controller::create($tmpPage);
			$controller->setDataModel($this->model);
			$controller->init();
		} else {
			$controller = $this;
		}

		$customisedController = $controller->customise(array(
			"Content" => _t('UniLogIn.Content', 'Choose member to login. Currently logged as {name}.', '', array('name' => Member::currentUser()->Title)),
			"Members" => Member::get()->exclude('ID', Member::currentUserID())->sort(array('FirstName' => 'asc', 'Surname' => 'asc')),
		));

		return $customisedController->renderWith(array('UniloginPage', 'Page'));
	}

	public function as_member() {
		if ($this->access_allowed()) {
			$member = Member::get()->byID($this->getRequest()->param('ID'));
			if ($member && $member->exists()) {
				$member->logIn();
			}
		}
		return $this->redirect('/');
	}

	public function providePermissions() {
		if ($this->isACLFlagSet(self::ACL_CHECK_PERMISSSIONS)) {
			return array(
				'UniLogIn' => array(
					'name'     => _t('UniLogIn.PermissionUniLogInName', 'Login as other user with UniLogIn'),
					'category' => _t('UniLogIn.PermissionUniLogInCategory', 'UniLogIn Access'),
					'help'     => _t('UniLogIn.PermissionUniLogInHelp', 'Allow for a user to login as other user without authentication process'),
				)
			);
		}
		return false;
	}

}

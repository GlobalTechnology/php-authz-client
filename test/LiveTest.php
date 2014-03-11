<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz.php');

class LiveTest extends PHPUnit_Framework_TestCase {
	private $_controller;
	private $_key;

	public function setUp() {
		$this->_controller = new \GCXAuthz\Controller();
		$this->_controller->setAuthzUri('https://dev.mygcx.org/authz');
		$this->_key = '';
	}

	public function testCheck() {
		$authz = $this->_controller;
		$authz->execute($authz->newCommands()
			->check('GUEST', 'resource')
		);
	}
}

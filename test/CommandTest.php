<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz/Constants.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Command.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Object.php');

class CommandTest extends PHPUnit_Framework_TestCase {
	public function testGenerateLoginKey() {
		// command utilizing custom user, ttl, and multiple namespaces
		$user = new \GCXAuthz\Object\User('GUEST');
		$namespaces = array(
			new \GCXAuthz\Object\Ns('ns1'),
			new \GCXAuthz\Object\Ns('ns2')
		);
		$ttl = 5;
		$cmd = new \GCXAuthz\Command\GenerateLoginKey($user, $namespaces, $ttl);
		$this->assertEquals(1, count($cmd->users()));
		$this->assertEquals($ttl, $cmd->ttl());
		$this->assertEquals(count($namespaces), count($cmd->namespaces()));
	}
}

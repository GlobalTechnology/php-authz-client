<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz.php');

class CommandsTest extends PHPUnit_Framework_TestCase {
	public function testCommands() {
		$key = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$ns = 'ns1';
		$user = 'GUEST';

		#generate some basic commands
		$cmds = new \GCXAuthz\Commands();
		$cmds
			->login($key)
			->listGroups($ns)
			->addUsers($user);

		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'commands'));
		$rawCmds = $cmds->commands();
		$this->assertEquals(3, count($rawCmds));
		# login command
		$cmd = $rawCmds[0];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\Login, "valid object");
		$this->assertEquals('login', $cmd->type());
		$keys = $cmd->keys();
		$this->assertEquals(1, count($keys));
		$this->assertTrue($keys[0]->equals(new \GCXAuthz\Object\Key($key)));
		# listGroups command
		$cmd = $rawCmds[1];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\Base, "valid object");
		$this->assertEquals('listGroups', $cmd->type());
		$nses = $cmd->namespaces();
		$this->assertEquals(1, count($nses));
		$this->assertTrue($nses[0]->equals(new \GCXAuthz\Object\Ns($ns)));
		# addUsers command
		$cmd = $rawCmds[2];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\Base, "valid object");
		$this->assertEquals('addUsers', $cmd->type());
		$users = $cmd->users();
		$this->assertEquals(1, count($users));
		$this->assertTrue($users[0]->equals(new \GCXAuthz\Object\User($user)));

		#test xml generation
		$actualDom = new DOMDocument();
		$actualDom->appendChild($cmds->toXml($actualDom));
		$this->assertEquals($expectedDom, $actualDom, 'valid commands xml');
		$actualDom2 = new DOMDocument();
		$actualDom2->appendChild(\GCXAuthz\Commands::newFromXml($cmds->toXml())->toXml($actualDom2));
		$this->assertEquals($actualDom, $actualDom2, 'newFromXml test');
	}
}

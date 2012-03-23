<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz.php');

class CommandsTest extends PHPUnit_Framework_TestCase {
	public function testCommands() {
		$key = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$ns = 'ns1';
		$user = 'GUEST';
		$target1 = 'testns:ns1|resource';
		$target2 = 'testns:ns2|resource';

		#generate some basic commands
		$cmds = new \GCXAuthz\Commands();
		$cmds
			->login($key)
			->listGroups($ns)
			->addUsers($user)
			->generateLoginKey()
			->generateLoginKey($user, null, 5)
			->check($user, array($target1, $target2))
			;

		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'commands'));
		$rawCmds = $cmds->commands();
		$this->assertEquals(6, count($rawCmds));
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
		# generateLoginKey command (no params)
		$cmd = $rawCmds[3];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\GenerateLoginKey, "valid object");
		$this->assertEquals('generateLoginKey', $cmd->type());
		$users = $cmd->users();
		$this->assertEquals(0, count($users));
		$this->assertEquals(0, $cmd->ttl());
		# generateLoginKey command (user & ttl)
		$cmd = $rawCmds[4];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\GenerateLoginKey, "valid object");
		$this->assertEquals('generateLoginKey', $cmd->type());
		$users = $cmd->users();
		$this->assertEquals(1, count($users));
		$this->assertTrue($users[0]->equals(new \GCXAuthz\Object\User($user)));
		$this->assertEquals(5, $cmd->ttl());
		// check($user, array($target1, $target2)) command
		$cmd = $rawCmds[5];
		$expectedNode->appendChild($cmd->toXml($expectedDom));
		$this->assertTrue($cmd instanceof \GCXAuthz\Command\Check, "valid object");
		$this->assertEquals('check', $cmd->type());
		$entities = $cmd->entities();
		$this->assertEquals(1, count($entities));
		$this->assertTrue($entities[0]->equals(new \GCXAuthz\Object\Entity($user)));
		$targets = $cmd->targets();
		$this->assertEquals(2, count($targets));
		$this->assertTrue($targets[0]->equals(new \GCXAuthz\Object\Target($target1)));
		$this->assertTrue($targets[1]->equals(new \GCXAuthz\Object\Target($target2)));

		#test xml generation
		$actualDom = new DOMDocument();
		$actualDom->appendChild($cmds->toXml($actualDom));
		$this->assertEquals($expectedDom, $actualDom, 'valid commands xml');
		$actualDom2 = new DOMDocument();
		$actualDom2->appendChild(\GCXAuthz\Commands::newFromXml($cmds->toXml())->toXml($actualDom2));
		$this->assertEquals($actualDom, $actualDom2, 'newFromXml test');
	}
}

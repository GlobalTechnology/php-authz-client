<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz/Constants.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Command.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Object.php');

class CommandTest extends PHPUnit_Framework_TestCase {
	public function testCheck() {
		$entity = new \GCXAuthz\Object\Entity('ns', 'group');
		$user = new \GCXAuthz\Object\User('GUEST');
		$group = new \GCXAuthz\Object\Group('ns', 'group');

		$targets = array(
			new \GCXAuthz\Object\Target('ns', 'role'),
			new \GCXAuthz\Object\Resource('ns', 'resource'),
			new \GCXAuthz\Object\Role('ns', 'role'),
		);

		# sample commands
		foreach(array($entity, $user, $group) as $ent) {
			$cmd = new \GCXAuthz\Command\Check($ent, $targets);
			$tmp = $cmd->entities();
			$this->assertTrue($ent->equals($tmp[0]));
			$this->assertEquals(count($targets), count($cmd->targets()));
			$tmp = $cmd->targets();
			$this->assertTrue($targets[0]->equals($tmp[0]));
			$this->assertTrue($targets[1]->equals($tmp[1]));
			$this->assertTrue($targets[2]->equals($tmp[2]));

			#xml generation
			$expectedDom = new DOMDocument();
			$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'command'));
			$expectedNode->setAttribute('type', 'check');
			$entityNode = $expectedNode->appendChild($ent->toXml($expectedDom));
			foreach($targets as $target) {
				$entityNode->appendChild($target->toXml($expectedDom));
			}
			$actualDom = new DOMDocument();
			$actualDom->appendChild($cmd->toXml($actualDom));
			$this->assertEquals($expectedDom, $actualDom, 'valid check xml');
		}
	}

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

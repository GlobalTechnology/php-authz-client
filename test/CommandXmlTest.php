<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz/Constants.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Command.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Object.php');

class CommandXmlTest extends PHPUnit_Framework_TestCase {
	public function testGenerateLoginKeyXml() {
		// simple command
		$cmd = new \GCXAuthz\Command\GenerateLoginKey();
		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'command'));
		$expectedNode->setAttribute('type', 'generateLoginKey');
		$actualDom = new DOMDocument();
		$actualNode = $actualDom->appendChild($cmd->toXml($actualDom));
		$this->assertEquals($expectedDom->saveXml(), $actualDom->saveXml(), 'valid simple generateLoginKey xml');
		$this->assertEqualXMLStructure($expectedNode, $actualNode, TRUE, 'valid simple generateLoginKey xml');

		// command utilizing custom user, ttl, and multiple namespaces
		$user = new \GCXAuthz\Object\User('GUEST');
		$namespaces = array(
			new \GCXAuthz\Object\Ns('ns1'),
			new \GCXAuthz\Object\Ns('ns2')
		);
		$ttl = 5;
		$cmd = new \GCXAuthz\Command\GenerateLoginKey($user, $namespaces, $ttl);
		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'command'));
		$expectedNode->setAttribute('type', 'generateLoginKey');
		$expectedNode->setAttribute('ttl', $ttl);
		$expectedNode->appendChild($user->toXml($expectedDom));
		$nsNode = $expectedNode->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'namespaces'));
		foreach($namespaces as $ns) {
			$nsNode->appendChild($ns->toXml($expectedDom));
		}
		$actualDom = new DOMDocument();
		$actualNode = $actualDom->appendChild($cmd->toXml($actualDom));
		$this->assertEquals($expectedDom->saveXml(), $actualDom->saveXml(), 'valid full generateLoginKey xml');
		$this->assertEqualXMLStructure($expectedNode, $actualNode, TRUE, 'valid full generateLoginKey xml');
	}
}

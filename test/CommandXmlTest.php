<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz/Constants.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Command.php');

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
	}
}

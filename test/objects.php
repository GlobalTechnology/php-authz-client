<?php

require_once(dirname(__FILE__) . '/../source/Constants.php');
require_once(dirname(__FILE__) . '/../source/Objects.php');

use \GCXAuthz\Object\Key;

class ObjectTest extends PHPUnit_Framework_TestCase {
	public function testKey() {
		# test generation of the key object
		$keyVal = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$key = new Key('abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq');
		$this->assertTrue($key instanceof Key, "valid object");
		$this->assertEquals($keyVal, $key->key(), 'key method returns correct value');
		$this->assertEquals($keyVal, $key . '', 'key stringification works');

		# test xml generation of key object
		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'key'));
		$expectedNode->setAttribute('key', $keyVal);
		$actualDom = new DOMDocument();
		$actualNode = $actualDom->appendChild($key->toXml($actualDom));
		$this->assertEquals($expectedDom->saveXml(), $actualDom->saveXml(), 'valid key xml');
		$this->assertEqualXMLStructure($expectedNode, $actualNode, TRUE, 'valid key xml');
	}

	public function testNamespace() {

	}
}

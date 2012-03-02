<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz/Constants.php');
require_once(dirname(__FILE__) . '/../source/GCXAuthz/Object.php');

class ObjectTest extends PHPUnit_Framework_TestCase {
	public function testKey() {
		# test generation of the key object
		$keyVal = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$key = new \GCXAuthz\Object\Key($keyVal);
		$this->assertTrue($key instanceof \GCXAuthz\Object\Key, "valid object");
		$this->assertEquals($keyVal, $key->key(), 'key method returns correct value');
		$this->assertEquals($keyVal, (string)$key, 'key stringification works');

		# test xml generation of key object
		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'key'));
		$expectedNode->setAttribute('key', $keyVal);
		$actualDom = new DOMDocument();
		$actualNode = $actualDom->appendChild($key->toXml($actualDom));
		$this->assertEquals($expectedDom->saveXml(), $actualDom->saveXml(), 'valid key xml');
		$this->assertEqualXMLStructure($expectedNode, $actualNode, TRUE, 'valid key xml');

		# test equals method
		$key1 = new \GCXAuthz\Object\Key($keyVal);
		$key2 = new \GCXAuthz\Object\Key($keyVal);
		$key3 = new \GCXAuthz\Object\Key($keyVal . 'abcd');
		$this->assertTrue($key1->equals($key2), 'keys equal');
		$this->assertFalse($key1->equals($key3), 'keys not equal');
	}

	public function testNamespace() {
		# test generation of the namespace object
		$nsVal = 'test:namespace';
		$ns = new \GCXAuthz\Object\Ns($nsVal);
		$this->assertTrue($ns instanceof \GCXAuthz\Object\Ns, "valid object");
		$this->assertEquals($nsVal, $ns->name(), 'name method returns correct value');
		$this->assertEquals($nsVal, (string)$ns, 'namespace stringification works');

		# test xml generation of the namespace object
		$expectedDom = new DOMDocument();
		$expectedNode = $expectedDom->appendChild($expectedDom->createElementNS(\GCXAuthz\XMLNS, 'namespace'));
		$expectedNode->setAttribute('name', $nsVal);
		$actualDom = new DOMDocument();
		$actualNode = $actualDom->appendChild($ns->toXml($actualDom));
		$this->assertEquals($expectedDom->saveXml(), $actualDom->saveXml(), 'valid namespace xml');
		$this->assertEqualXMLStructure($expectedNode, $actualNode, TRUE, 'valid namespace xml');
	}
}

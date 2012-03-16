<?php

require_once(dirname(__FILE__) . '/../source/GCXAuthz.php');

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
		$this->assertEquals($expectedDom, $actualDom, 'valid key xml');

		# test equals method
		$key1 = new \GCXAuthz\Object\Key($keyVal);
		$key2 = new \GCXAuthz\Object\Key($keyVal);
		$key3 = new \GCXAuthz\Object\Key($keyVal . 'abcd');
		$this->assertTrue($key1->equals($key2), 'keys equal');
		$this->assertFalse($key1->equals($key3), 'keys not equal');

		# test newFromXml
		$keyVal = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$key1 = new \GCXAuthz\Object\Key($keyVal);
		$key2 = \GCXAuthz\Object\Key::newFromXml($key1->toXml());
		$this->assertTrue($key1->equals($key2), 'newFromXml key is equal to original');
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
		$this->assertEquals($expectedDom, $actualDom, 'valid namespace xml');

		# test namespace comparison methods
		$ns1 = new \GCXAuthz\Object\Ns('');
		$ns2 = new \GCXAuthz\Object\Ns('parent');
		$ns3 = new \GCXAuthz\Object\Ns('parent:child1');
		$ns4 = new \GCXAuthz\Object\Ns('parent:child2');
		$ns5 = new \GCXAuthz\Object\Ns('parent:child2');
		$this->assertTrue($ns5->equals($ns4));
		$this->assertFalse($ns5->equals($ns3));
		foreach(array($ns2, $ns3, $ns4) as $ns) {
			$this->assertTrue($ns1->contains($ns));
		}
		$this->assertTrue($ns2->contains($ns3));
		$this->assertTrue($ns2->contains($ns4));
		foreach(array($ns1, $ns2, $ns3) as $ns) {
			$this->assertFalse($ns4->contains($ns));
		}

		# test invalid namespaces
		foreach(array('|', 'a|b|c') as $nsVal) {
			$ns = null;
			try {
				$ns = new \GCXAuthz\Object\Ns($nsVal);
				$this->fail('no exception thrown for invalid namespace');
			} catch(Exception $e) {
			}
			$this->assertFalse($ns instanceof \GCXAuthz\Object\Ns, "invalid object");
		}

		# test newFromXml
		$nsVal = 'test:namespace';
		$ns1 = new \GCXAuthz\Object\Ns($nsVal);
		$ns2 = \GCXAuthz\Object\Ns::newFromXml($ns1->toXml());
		$this->assertTrue($ns1->equals($ns2), 'newFromXml namespace is equal to original');
	}

	public function testTarget() {
		$nsVal = 'test:ns';
		$ns = new \GCXAuthz\Object\Ns($nsVal);
		$rootNs = new \GCXAuthz\Object\Ns('');
		foreach(array('a', 'b:c') as $name) {
			// test 2 param creation
			$target = new \GCXAuthz\Object\Target($ns, $name);
			$this->assertTrue($ns->equals($target->ns()));
			$this->assertEquals($name, $target->name());
			$this->assertEquals($nsVal . '|' . $name, (string) $target);

			// test 2 param creation with strings only
			$target = new \GCXAuthz\Object\Target($nsVal, $name);
			$this->assertTrue($ns->equals($target->ns()));
			$this->assertEquals($name, $target->name());
			$this->assertEquals($nsVal . '|' . $name, (string) $target);

			// test 2 param creation with root namespace
			$target = new \GCXAuthz\Object\Target($rootNs, $name);
			$this->assertTrue($rootNs->equals($target->ns()));
			$this->assertEquals($name, $target->name());
			$this->assertEquals($name, (string) $target);

			// test 1 param creation
			$target = new \GCXAuthz\Object\Target($nsVal . '|' . $name);
			$this->assertTrue($ns->equals($target->ns()));
			$this->assertEquals($name, $target->name());
			$this->assertEquals($nsVal . '|' . $name, (string) $target);

			// test 1 param creation with no namespace
			$target = new \GCXAuthz\Object\Target($name);
			$this->assertTrue($rootNs->equals($target->ns()));
			$this->assertEquals($name, $target->name());
			$this->assertEquals($name, (string) $target);
		}

		# test newFromXml
		$name = 'target';
		$target1 = new \GCXAuthz\Object\Target($ns, $name);
		$target2 = \GCXAuthz\Object\Target::newFromXml($target1->toXml());
		$this->assertTrue($target1->equals($target2), 'newFromXml target is equal to original');
	}
}

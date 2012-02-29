<?php

require_once(dirname(__FILE__) . '/../source/Objects.php');

class ObjectTest extends PHPUnit_Framework_TestCase {
	public function testKey() {
		$keyVal = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq';
		$key = new GCXAuthz_Key('abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq');
		$this->assertTrue($key instanceof GCXAuthz_Key, "valid object");
		$this->assertEquals($keyVal, $key->key(), 'key method returns correct value');
		$this->assertEquals($keyVal, $key . '', 'key stringification works');
	}
}

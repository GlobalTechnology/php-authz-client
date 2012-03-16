<?php
namespace GCXAuthz {
	interface Object {
		public function __toString();
		public function equals(Object $obj);
		public function toXml(\DOMDocument $doc);
	}
}

namespace GCXAuthz\Object {
	require_once(dirname(__FILE__) . '/../GCXAuthz.php');

	// Key object
	class Key implements \GCXAuthz\Object {
		private $_key;

		public function __construct($key) {
			$this->_key = (string)$key;
		}

		public static function newFromXml(\DOMElement $node) {
			$key = $node->getAttribute('key');
			return new Key($key);
		}

		public function key() {
			return $this->_key;
		}

		public function __toString() {
			return $this->key();
		}

		public function equals(\GCXAuthz\Object $obj) {
			return
				get_class($this) === get_class($obj) &&
				$this->key() === $obj->key();
		}

		public function toXml(\DOMDocument $doc = null) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'key');
			$node->setAttribute('key', $this->key());

			return $node;
		}
	}

	// Namespace object
	class Ns implements \GCXAuthz\Object {
		private $_name;

		// parsed namespace meta-data
		private $_parts = null;
		private $_length = 0;
		private $_substr = null;

		public function __construct($name) {
			$this->_name = (string)$name;

			if(!$this->isValid()) {
				throw new \Exception('invalid object');
			}

			$this->_parseNamespace();
		}

		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('name');
			return new Ns($ns);
		}

		protected function isValid() {
			return
				!preg_match('/\|/', $this->name());
		}

		// parse this namespace object into components for various namespace manipulation methods
		private function _parseNamespace() {
			if(is_null($this->_parts)) {
				$name = strtolower($this->name());
				$this->_parts = strlen($name) > 0 ? explode(':', $name) : array();
				$this->_length = count($this->_parts);

				// generate namespace substrings
				$tmpNs = '';
				$this->_substr = array($tmpNs);
				foreach($this->_parts as $part) {
					if(strlen($tmpNs) > 0) {
						$tmpNs .= ':';
					}
					$tmpNs .= $part;
					$this->_substr[] = $tmpNs;
				}
			}
		}

		// return a namespace substring
		private function _nsSubstr($length) {
			$length = (int)$length;
			if(array_key_exists($length, $this->_substr)) {
				return $this->_substr[$length];
			}

			return null;
		}

		public function name() {
			return $this->_name;
		}

		public function __toString() {
			return $this->name();
		}

		public function contains(Ns $obj) {
			$this->_length;
			return $this->_nsSubstr($this->_length) === $obj->_nsSubstr($this->_length);
		}

		public function equals(\GCXAuthz\Object $obj) {
			return
				get_class($this) === get_class($obj) &&
				strtolower($this->name()) === strtolower($obj->name());
		}

		public function toXml(\DOMDocument $doc = null) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'namespace');
			$node->setAttribute('name', $this->name());

			return $node;
		}
	}

	// base object
	abstract class Base implements \GCXAuthz\Object {
		private $_ns = null;

		private $_name = null;

		public function __construct($ns, $name = null) {
			// no name was provided, so parse the $ns value for the name
			if(is_null($name)) {
				$matches = array();
				if(preg_match('/^(.*)\|([^\|]*?)$/', $ns, $matches)) {
					$ns = $matches[1];
					$name = $matches[2];
				}
				// no namespace detected, use root namespace
				else {
					$name = $ns;
					$ns = '';
				}
			}

			// Make sure $ns is a Namespace object
			if(!($ns instanceof Ns)) {
				$ns = new Ns($ns);
			}

			$this->_ns = $ns;
			$this->_name = $name;

			if(!$this->isValid()) {
				throw new \Exception('invalid object');
			}
		}

		public function type() {
			return 'object';
		}

		public function ns() {
			return $this->_ns;
		}

		public function name() {
			return $this->_name;
		}

		protected function isValid() {
			return
				$this->ns() instanceof Ns &&
				!preg_match('/(?:^$|\|)/', $this->name());
		}

		public function __toString() {
			$ns = $this->ns();
			return
				(strlen($ns) > 0 ? $ns . '|' : '') .
				$this->name();
		}

		public function equals(\GCXAuthz\Object $obj) {
			return
				get_class($this) === get_class($obj) &&
				$this->ns()->equals($obj->ns()) &&
				strtolower($this->name()) === strtolower($obj->name());
		}

		public function toXml(\DOMDocument $doc = null) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			$node = $doc->createElementNS(\GCXAuthz\XMLNS, $this->type());
			$node->setAttribute('namespace', $this->ns());
			$node->setAttribute('name', $this->name());

			return $node;
		}
	}

	class Entity extends Base {
		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('namespace');
			$name = $node->getAttribute('name');
			return new Entity($ns, $name);
		}

		public function type() {
			return 'entity';
		}
	}

	class User extends Entity {
		public function __construct($name) {
			parent::__construct('', strtoupper($name));
		}

		public static function newFromXml(\DOMElement $node) {
			$name = $node->getAttribute('name');
			return new User($ns);
		}

		public function type() {
			return 'user';
		}

		protected function isValid() {
			$name = $this->name();
			return
				parent::isValid() &&
				(preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/', $name) || $name == 'SUPERUSER' || $name == 'GUEST' || $name == 'DEFAULT');
		}

		public function toXml(\DOMDocument $doc = null) {
			$node = parent::toXml($doc);
			$node->removeAttribute('namespace');
			return $node;
		}
	}

	class Group extends Entity {
		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('namespace');
			$name = $node->getAttribute('name');
			return new Group($ns, $name);
		}

		public function type() {
			return 'group';
		}
	}

	class Target extends Base {
		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('namespace');
			$name = $node->getAttribute('name');
			return new Target($ns, $name);
		}

		public function type() {
			return 'target';
		}
	}

	class Resource extends Target {
		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('namespace');
			$name = $node->getAttribute('name');
			return new Resource($ns, $name);
		}

		public function type() {
			return 'resource';
		}
	}

	class Role extends Target {
		public static function newFromXml(\DOMElement $node) {
			$ns = $node->getAttribute('namespace');
			$name = $node->getAttribute('name');
			return new Role($ns, $name);
		}

		public function type() {
			return 'role';
		}
	}
}

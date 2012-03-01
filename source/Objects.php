<?php
namespace GCXAuthz\Object {
	require_once(dirname(__FILE__) . '/Constants.php');

	// Key object
	class Key {
		private $_key;

		public function __construct(
			$key = ''
		) {
			$this->_key = $key;
		}

		public function key() {
			return $this->_key;
		}

		public function __toString() {
			return $this->key();
		}

		public function toXml(
			$doc = null
		) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'key');
			$node->setAttribute('key', $this->key());

			return $node;
		}
	}

	// Namespace object
	class Ns {
		private $_name;

		public function __construct(
			$name = ''
		) {
			$this->_name = $name . '';
		}

		public function name() {
			return $this->_name;
		}

		public function __toString() {
			return $this->name();
		}

		public function toXml(
			$doc = null
		) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'namespace');
			$node->setAttribute('name', $this->name());

			return $node;
		}
	}

	// base object
	class Base {
		private $_ns = null;

		private $_name = null;

		public function __construct(
			$ns = '',
			$name = null
		) {
			// no name was provided, so parse the $ns value for the name
			if(is_null($name)) {
				$matches = array();
				if(preg_match('/^(.*)\|([^\|]*?)$/', $ns, $matches)) {
					$ns = $matches[1];
					$name = $matches[2];
				}
			}

			// Make sure $ns is a Namespace object
			if(!($ns instanceof Ns)) {
				$ns = new Ns($ns);
			}

			$this->_ns = $ns;
			$this->_name = $name;

			if(!$this->isValid()) {
				throw new Exception('invalid object');
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
				(strlen($ns) ? $ns . '|' : '') .
				$this->name();
		}

		public function toXml(
			$doc = null
		) {
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
		public function type() {
			return 'entity';
		}
	}

	class User extends Entity {
		public function __construct(
			$name
		) {
			parent::__construct('', strtoupper($name));
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

		public function toXml(
			$doc = null
		) {
			$node = parent::toXml($doc);
			$node->removeAttribute('namespace');
			return $node;
		}
	}

	class Group extends Entity {
		public function type() {
			return 'group';
		}
	}

	class Target extends Base {
		public function type() {
			return 'target';
		}
	}

	class Resource extends Target {
		public function type() {
			return 'resource';
		}
	}

	class Role extends Target {
		public function type() {
			return 'role';
		}
	}
}

<?php

require_once(dirname(__FILE__) . '/Constants.php');

// Key object
class GCXAuthz_Key {
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
		if(!($doc instanceof DOMDocument)) {
			$doc = new DOMDocument();
		}

		$node = $doc->createElementNS(XMLNS_GCXAUTHZ, 'key');
		$node->setAttribute('key', $this->key());

		return $node;
	}
}

// Namespace object
class GCXAuthz_Namespace {
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
		if(!($doc instanceof DOMDocument)) {
			$doc = new DOMDocument();
		}

		$node = $doc->createElementNS(XMLNS_GCXAUTHZ, 'namespace');
		$node->setAttribute('name', $this->name());

		return $node;
	}
}

// base object
class GCXAuthz_BaseObject {
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
		if(!($ns instanceof GCXAuthz_Namespace)) {
			$ns = new GCXAuthz_Namespace($ns);
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
			$this->ns() instanceof GCXAuthz_Namespace &&
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
		if(!($doc instanceof DOMDocument)) {
			$doc = new DOMDocument();
		}

		$node = $doc->createElementNS(XMLNS_GCXAUTHZ, $this->type());
		$node->setAttribute('namespace', $this->ns());
		$node->setAttribute('name', $this->name());

		return $node;
	}
}

class GCXAuthz_Entity extends GCXAuthz_BaseObject {
	public function type() {
		return 'entity';
	}
}

class GCXAuthz_User extends GCXAuthz_Entity {
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

class GCXAuthz_Group extends GCXAuthz_Entity {
	public function type() {
		return 'group';
	}
}

class GCXAuthz_Target extends GCXAuthz_BaseObject {
	public function type() {
		return 'target';
	}
}

class GCXAuthz_Resource extends GCXAuthz_Target {
	public function type() {
		return 'resource';
	}
}

class GCXAuthz_Role extends GCXAuthz_Target {
	public function type() {
		return 'role';
	}
}

<?php
namespace GCXAuthz {
	interface Command {
		public function type();

		public function keys();
		public function namespaces();
		public function entities();
		public function users();
		public function groups();
		public function targets();
		public function resources();
		public function roles();

		public function toXml(\DOMDocument $doc);
	}
}

namespace GCXAuthz\Command {
	require_once(dirname(__FILE__) . '/Constants.php');
	require_once(dirname(__FILE__) . '/Object.php');

	class Base implements \GCXAuthz\Command {
		private $_type;

		private $_keys       = array();
		private $_namespaces = array();
		private $_entities   = array();
		private $_users      = array();
		private $_groups     = array();
		private $_targets    = array();
		private $_resources  = array();
		private $_roles      = array();

		private static function filterObjects($objs, $class) {
			if(!is_array($objs) && !is_null($objs)) {
				$objs = array($objs);
			}
			if(!is_array($objs)) {
				return array();
			}

			$resp = array();
			foreach($objs as $obj) {
				// sanitize the object
				if($obj instanceof \GCXAuthz\Object || is_null($obj)) {
					// do nothing
				}
				// convert to an actual authorization object
				elseif(is_array($obj)) {
					$obj = new $class($obj[0], $obj[1]);
				}
				else {
					$obj = new $class($obj);
				}

				// add any valid objects to the response
				if($obj instanceof $class) {
					$resp[] = $obj;
				}
			}

			return $resp;
		}

		public function __construct($type, array $args = array()) {
			// make sure this is a valid command type
			$this->_type = $type;
			if(!$this->_isValidType($this->_type)) {
				throw new Exception('invalid command type');
			}

			// set the root namespace as the default namespace when no namespaces are specified
			if(!array_key_exists('namespaces', $args) || is_null($args['namespaces'])) {
				$args['namespaces'] = new \GCXAuthz\Object\Ns();
			}

			// process authz objects
			if(array_key_exists('keys', $args)) {
				$this->_keys       = self::filterObjects($args['keys'],       '\GCXAuthz\Object\Key');
			}
			if(array_key_exists('namespaces', $args)) {
				$this->_namespaces = self::filterObjects($args['namespaces'], '\GCXAuthz\Object\Ns');
			}
			if(array_key_exists('entities', $args)) {
				$this->_entities   = self::filterObjects($args['entities'],   '\GCXAuthz\Object\Entity');
			}
			if(array_key_exists('users', $args)) {
				$this->_users      = self::filterObjects($args['users'],      '\GCXAuthz\Object\User');
			}
			if(array_key_exists('groups', $args)) {
				$this->_groups     = self::filterObjects($args['groups'],     '\GCXAuthz\Object\Group');
			}
			if(array_key_exists('targets', $args)) {
				$this->_targets    = self::filterObjects($args['targets'],    '\GCXAuthz\Object\Target');
			}
			if(array_key_exists('resources', $args)) {
				$this->_resources  = self::filterObjects($args['resources'],  '\GCXAuthz\Object\Resource');
			}
			if(array_key_exists('roles', $args)) {
				$this->_roles      = self::filterObjects($args['roles'],      '\GCXAuthz\Object\Role');
			}
		}

		protected function _isValidType($type) {
			return preg_match('/^(?:
				add(?:Users|Groups|Resources|Roles)|
				list(?:Groups|Resources|Roles)|
				remove(?:Groups|Resources|Roles)|
				removeAllObjects|

				add(?:ToGroups|ToRoles|Permissions)|
				list(?:ContainingGroups|GroupMembers|ContainingRoles|RoleTargets|Permissions|PermittedEntities)|
				remove(?:FromGroups|FromRoles|Permissions)|

				revokeLoginKeys|
				changeUser|restrictNamespaces|
				dumpExecutionContext
			)$/sx', $type) > 0;
		}

		public function type() {
			return $this->_type;
		}

		public function keys() {
			return $this->_keys;
		}

		public function namespaces() {
			return $this->_namespaces;
		}

		public function entities() {
			return $this->_entities;
		}

		public function users() {
			return $this->_users;
		}

		public function groups() {
			return $this->_groups;
		}

		public function targets() {
			return $this->_targets;
		}

		public function resources() {
			return $this->_resources;
		}

		public function roles() {
			return $this->_roles;
		}

		protected function _baseXml(\DOMDocument &$doc) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			// generate the base command xml node
			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'command');
			$node->setAttribute('type', $this->type());

			return $node;
		}

		protected function _attachNamespaceXml(\DOMElement $node, array $objs) {
			$rootNs = new \GCXAuthz\Object\Ns('');
			// no namespace xml is generated
			foreach($objs as $ns) {
				if($ns->contains($rootNs)) {
					return;
				}
			}
			$node->appendChild($this->_objectsToXml($node->ownerDocument, 'namespaces', $objs));
		}

		protected function _objectsToXml(\DOMDocument $doc, $type, array $objs) {
			$node = $doc->createElementNS(\GCXAuthz\XMLNS, $type);
			foreach($objs as $obj) {
				$node->appendChild($obj->toXml($doc));
			}
			return $node;
		}

		public function toXml(\DOMDocument $doc = null) {
			$node = $this->_baseXml($doc);

			return $node;
		}
	}

	class RenameBase extends Base {
		protected function _isValidType($type) {
			return preg_match('/^(?:
				rename(?:Group|Resource|Role|Namespace)
			)$/sx', $type) > 0;
		}

		public function toXml(\DOMDocument $doc = null) {
			// get the source and target object for this rename command
			switch ((string) $this->type()) {
				case 'renameGroup':
					$objs = $this->groups();
					break;
				case 'renameNamespace':
					$objs = $this->namespaces();
					break;
				case 'renameResource':
					$objs = $this->resources();
					break;
				case 'renameRole':
					$objs = $this->roles();
					break;
			}
			list($source, $target) = $objs;

			// generate the xml for this rename command
			$node = $this->_baseXml($doc);
			$node->appendChild($doc->createElementNS(\GCXAuthz\XMLNS, 'source'))->appendChild($source->toXml($doc));
			$node->appendChild($doc->createElementNS(\GCXAuthz\XMLNS, 'target'))->appendChild($target->toXml($doc));

			// return the generated xml element
			return $node;
		}
	}

	class Check extends Base {
		public function __construct($entity, $targets) {
			parent::__construct('check', array(
				'entities' => $entity,
				'targets'  => $targets,
			));
		}

		protected function _isValidType($type) {
			return $type === 'check';
		}

		public function toXml(\DOMDocument $doc = null) {
			$node = $this->_baseXml($doc);

			// generate the xml specific to a check command
			$entities = $this->entities();
			if(count($entities) > 0) {
				$entityXml = $node->appendChild($entities[0]->toXml($doc));
				foreach($this->targets() as $target) {
					$entityXml->appendChild($target->toXml($doc));
				}
			}

			return $node;
		}
	}

	class GenerateLoginKey extends Base {
		private $_ttl;

		public function __construct(
			$user = null,
			$namespaces = null,
			$ttl = 0
		) {
			parent::__construct('generateLoginKey', array(
				'users'      => $user,
				'namespaces' => $namespaces,
			));

			$this->_ttl = (int)$ttl;
		}

		protected function _isValidType($type) {
			return $type === 'generateLoginKey';
		}

		public function ttl() {
			return $this->_ttl;
		}

		public function toXml(\DOMDocument $doc = null) {
			$node = $this->_baseXml($doc);

			// set the ttl if specified
			$ttl = $this->ttl();
			if($ttl > 0) {
				$node->setAttribute('ttl', $ttl);
			}

			// set the user for this command if specified
			$users = $this->users();
			if(count($users) > 0) {
				$node->appendChild($users[0]->toXml($doc));
			}

			// generate xml for any attached namespaces
			$this->_attachNamespaceXml($node, $this->namespaces());

			return $node;
		}
	}

	class Login extends Base {
		public function __construct($key = null) {
			parent::__construct('login', array(
				'keys' => $key,
			));
		}

		protected function _isValidType($type) {
			return $type === 'login';
		}

		public function toXml(\DOMDocument $doc = null) {
			$node = $this->_baseXml($doc);

			// generate the xml specific to a login command
			$keys = $this->keys();
			if(count($keys) > 0) {
				$node->appendChild($keys[0]->toXml($doc));
			}

			return $node;
		}
	}
}

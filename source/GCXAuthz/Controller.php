<?php

namespace GCXAuthz {
	require_once(dirname(__FILE__) . '/../GCXAuthz.php');

	class Controller {
		private $_user;
		private $_namespaces;
		private $_processor;

		public function __construct() {
			$this->setContextUser();
			$this->setContextNamespaces('');
		}

		// methods to setup the execution context
		public function setContextUser($user = null) {
			$this->_user = $this->newUser('GUEST');
		}
		public function setContextNamespaces($namespace1/*[, $namespace2[, $...]]*/) {
			$args = func_get_args();
			$namespaces = array();
			foreach($args as $arg) {
				$namespaces[] = $this->newNamespace($arg);
			}
			$this->_namespaces = $namespaces;
		}
		public function setProcessor(Processor $processor) {
			$this->_processor = $processor;
		}
		public function setAuthzUri($uri) {
			$this->setProcessor(new RpcProcessor($uri));
		}

		// authz object creation methods
		public function newCommands() {
			return new \GCXAuthz\Commands();
		}
		public function newKey($key) {
			return new \GCXAuthz\Object\Key($key);
		}
		public function newNamespace($ns) {
			return new \GCXAuthz\Object\Ns($ns);
		}
		public function newEntity($ns, $name = null) {
			return new \GCXAuthz\Object\Entity($ns, $name);
		}
		public function newUser($user) {
			return new \GCXAuthz\Object\User($user);
		}
		public function newGroup($ns, $name = null) {
			return new \GCXAuthz\Object\Entity($ns, $name);
		}
		public function newTarget($ns, $name = null) {
			return new \GCXAuthz\Object\Target($ns, $name);
		}
		public function newResource($ns, $name = null) {
			return new \GCXAuthz\Object\Resource($ns, $name);
		}
		public function newRole($ns, $name = null) {
			return new \GCXAuthz\Object\Role($ns, $name);
		}

		// execute a set of authorization commands
		public function execute(\GCXAuthz\Commands $cmds) {
			// throw an exception if there is no processor for processing the authorization commands
			if(!($this->_processor instanceof \GCXAuthz\Processor)) {
				throw new Exception('No processor configured');
			}

			$this->_processor->process($this->_user, $this->_namespaces, $cmds->commands());

			return $cmds;
		}
	}
}

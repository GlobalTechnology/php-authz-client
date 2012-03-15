<?php
namespace GCXAuthz {
	require_once(dirname(__FILE__) . '/Constants.php');
	require_once(dirname(__FILE__) . '/Command.php');

	class Commands {
		private $_commands = array();
		private $_processed = false;

		public function addCommands(Command $command1/*[, Command $command2[, Command $...]]*/) {
			$cmds = func_get_args();
			foreach($cmds as $cmd) {
				if($cmd instanceof Command) {
					$this->_commands[] = $cmd;
				}
			}
		}

		public function commands() {
			return $this->_commands;
		}

		public function processed() {
			return $this->_processed;
		}

		public function toXml(
			\DOMDocument $doc = null,
			$response = null
		) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			// create the root commands xml node
			$node = $doc->createElementNS(XMLNS, 'commands');

			// generate xml for all commands
			foreach($this->commands() as $command) {
				$node->appendChild($command->toXml($doc));
			}

			return $node;
		}

		public function check($entity, $targets) {
			$this->addCommands(new Command\Check($entity, $targets));
			return $this;
		}

		public function generateLoginKey(
			$user = null,
			$namespaces = null,
			$ttl = 0
		) {
			$this->addCommands(new Command\GenerateLoginKey($user, $namespaces, $ttl));
			return $this;
		}

		public function login($key) {
			$this->addCommands(new Command\Login($key));
			return $this;
		}

		private static $_generic_methods = array(
			'addUsers'              => array('users'     ,             ),
			'addGroups'             => array('groups'    ,             ),
			'addResources'          => array('resources' ,             ),
			'addRoles'              => array('roles'     ,             ),
			'listGroups'            => array('namespaces',             ),
			'listResources'         => array('namespaces',             ),
			'listRoles'             => array('namespaces',             ),
			'removeGroups'          => array('groups'    ,             ),
			'removeResources'       => array('resources' ,             ),
			'removeRoles'           => array('roles'     ,             ),
			'removeAllObjects'      => array('namespaces',             ),
			'addToGroups'           => array('entities'  , 'groups'    ),
			'addToRoles'            => array('targets'   , 'roles'     ),
			'addPermissions'        => array('entities'  , 'targets'   ),
			'listContainingGroups'  => array('entities'  , 'namespaces'),
			'listGroupMembers'      => array('groups'    , 'namespaces'),
			'listContainingRoles'   => array('targets'   , 'namespaces'),
			'listRoleTargets'       => array('roles'     , 'namespaces'),
			'listPermissions'       => array('entities'  , 'namespaces'),
			'listPermittedEntities' => array('targets'   , 'namespaces'),
			'removeFromGroups'      => array('entities'  , 'groups'    ),
			'removeFromRoles'       => array('targets'   , 'roles'     ),
			'removePermissions'     => array('entities'  , 'targets'   ),
			'revokeLoginKeys'       => array('keys'      ,             ),
			'changeUser'            => array('users'     ,             ),
			'restrictNamespaces'    => array('namespaces',             ),
			'dumpExecutionContext'  => array(                          ),
		);
		private static $_generic_rename_methods = array(
			'renameGroup'     => 'groups'    ,
			'renameNamespace' => 'namespaces',
			'renameResource'  => 'resources' ,
			'renameRole'      => 'roles'     ,
		);

		public function __call($name, array $args) {
			if(array_key_exists($name, self::$_generic_methods)) {
				$argNames = self::$_generic_methods[$name];
				$this->addCommands(new Command\Base($name, array_combine($argNames, $args)));
			}
			elseif(array_key_exists($name, self::$_generic_rename_methods)) {
				$objType = self::$_generic_rename_methods[$name];
				$this->addCommands(new Command\RenameBase($name, array($objType => array_slice($args, 0, 2))));
			}
			else {
				throw new Exception('Invalid method called');
			}

			return $this;
		}
	}
}

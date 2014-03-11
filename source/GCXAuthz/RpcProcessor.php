<?php

namespace GCXAuthz {
	require_once(dirname(__FILE__) . '/../GCXAuthz.php');

	class RpcProcessor implements Processor {
		private $_authzUri;

		public function __construct($authzUri) {
			$this->_authzUri = $authzUri;
		}

		public function process(Object\User $user, $namespaces, $commands) {
			// create a temporary commands object to store the commands
			$cmds = new Commands();

			// determine if the context needs to be restricted
			$restrictContext = false;
			foreach ($commands as $cmd) {
				switch((string)$cmd->type()) {
					// check doesn't care about context, but subsequent commands might
					case 'check' :
						continue 2;
					// login commands completely change the context, so quit checking
					case 'login' :
						break 2;
					// all other commands should have the specified context before processing
					default :
						$restrictContext = true;
						break 2;
				}
			}

			// change the user and restrict namespaces because the context needs to be restricted
			if($restrictContext) {
				$cmds->changeUser($user)->restrictNamespaces($namespaces);
			}

			// add commands being processed to the commands object
			foreach ($commands as $cmd) {
				$cmds->addCommands($cmd);
			}

			// serialize the commands to xml
			$dom = new \DOMDocument('1.0', 'utf-8');
			$dom->appendChild($cmds->toXml($dom));

			// process the commands using cURL
			$ch = curl_init($this->_authzUri);
			curl_setopt_array($ch, array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $dom->saveXml(),
				CURLOPT_RETURNTRANSFER => true,
			));
			$response = curl_exec($ch);



			print $response;
		}
	}
}

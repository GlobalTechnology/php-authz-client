<?php

namespace GCXAuthz {
	require_once(dirname(__FILE__) . '/../GCXAuthz.php');

	class XmlUtils {
		// create an authz XPath processor for the specified document
		public static function getAuthzXPath(\DOMDocument $dom) {
			$xpath = new \DOMXPath($dom);
			$xpath->registerNamespace('authz', \GCXAuthz\XMLNS);
			return $xpath;
		}

		// processes the provided xml node into an authz object
		public static function processXmlNode(\DOMElement $node, \DOMXPath $xpath = null) {
			// make sure this is authorization xml
			if($node->namespaceURI == \GCXAuthz\XMLNS) {
				// make sure we have a valid xpath processor
				if(!($xpath instanceof \DOMXPath)) {
					$xpath = self::getAutzhXPath($node->ownerDocument);
				}

				// switch based on node being processed
				switch($node->localName) {
					// commands node
					case "commands":
						return Commands::newFromXml($node, $xpath);
					// command node
					case "command"   :
						$type = $node->getAttribute('type');
						switch($type) {
							case 'check':
								return Command\Check::newFromXml($node, $xpath);
							case 'login':
								return Command\Login::newFromXml($node, $xpath);
							case 'generateLoginKey':
								return Command\GenerateLoginKey::newFromXml($node, $xpath);
							case 'renameGroup':
							case 'renameNamespace':
							case 'renameResource':
							case 'renameRole':
								return Command\RenameBase::newFromXml($node, $xpath);
							default:
								return Command\Base::newFromXml($node, $xpath);
						}
						break;
					// object node
					case "key":
						return Object\Key::newFromXml($node);
					case "namespace":
						return Object\Ns::newFromXml($node);
					case "entity":
						return Object\Entity::newFromXml($node);
					case "user":
						return Object\User::newFromXml($node);
					case "group":
						return Object\Group::newFromXml($node);
					case "target":
						return Object\Target::newFromXml($node);
					case "resource":
						return Object\Resource::newFromXml($node);
					case "role":
						return Object\Role::newFromXml($node);
				}
			}

			// return null if this xml node was not recognized
			return null;
		}
	}
}

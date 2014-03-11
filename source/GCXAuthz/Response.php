<?php
namespace GCXAuthz {
	require_once(dirname(__FILE__) . '/../GCXAuthz.php');

	interface Response {
		public function code();
		public function command();
	}
}

namespace GCXAuthz\Response {
	class Base implements \GCXAuthz\Response {
		private $_command;
		private $_code;
		private $_objects;

		public function __construct(\GCXAuthz\Command $command, $code, $objs) {
			$this->_command = $command;
			$this->_code = $code;
			$this->_objects = $objs;
		}

		public function newFromXml($node, $xpath = null, \GCXAuthz\Command $command = null) {
			if(!($xpath instanceof \DOMXPath)) {
				$xpath = \GCXAuthz\XmlUtils::getAuthzXPath($node->ownerDocument);
			}

			$code = $node->getAttribute('code');
			$objs = array();
			foreach($xpath->query('authz:*', $node) as $obj) {
				$objs[] = \GCXAuthz\XmlUtils::processXmlNode($obj, $xpath);
			}

			return new Base($command, $code, $objs);
		}

		public function code() {
			return $this->_code;
		}

		public function command() {
			return $this->_command;
		}

		protected function _baseXml(\DOMDocument &$doc = null) {
			if(!($doc instanceof \DOMDocument)) {
				$doc = new \DOMDocument();
			}

			// generate the base command xml node
			$node = $doc->createElementNS(\GCXAuthz\XMLNS, 'command');
			$node->setAttribute('type', $this->type());

			return $node;
		}

//		public function toXml(
	}
}

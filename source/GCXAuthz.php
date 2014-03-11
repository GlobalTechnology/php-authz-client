<?php
require_once(dirname(__FILE__) . '/GCXAuthz/Constants.php');

// setup a class auto-loader
spl_autoload_register(function ($name) {
	switch($name) {
		case "GCXAuthz\Object":
		case "GCXAuthz\Object\Key":
		case "GCXAuthz\Object\Ns":
		case "GCXAuthz\Object\Base":
		case "GCXAuthz\Object\Entity":
		case "GCXAuthz\Object\User":
		case "GCXAuthz\Object\Group":
		case "GCXAuthz\Object\Target":
		case "GCXAuthz\Object\Resource":
		case "GCXAuthz\Object\Role":
			require_once(dirname(__FILE__) . '/GCXAuthz/Object.php');
			break;
		case "GCXAuthz\Command":
		case "GCXAuthz\Command\Base":
		case "GCXAuthz\Command\RenameBase":
		case "GCXAuthz\Command\Check":
		case "GCXAuthz\Command\GenerateLoginKey":
		case "GCXAuthz\Command\Login":
			require_once(dirname(__FILE__) . '/GCXAuthz/Command.php');
			break;
		case "GCXAuthz\Commands":
			require_once(dirname(__FILE__) . '/GCXAuthz/Commands.php');
			break;
		case "GCXAuthz\Controller":
			require_once(dirname(__FILE__) . '/GCXAuthz/Controller.php');
			break;
		case "GCXAuthz\Processor":
			require_once(dirname(__FILE__) . '/GCXAuthz/Processor.php');
			break;
		case "GCXAuthz\Response":
			require_once(dirname(__FILE__) . '/GCXAuthz/Response.php');
			break;
		case "GCXAuthz\RpcProcessor":
			require_once(dirname(__FILE__) . '/GCXAuthz/RpcProcessor.php');
			break;
		case "GCXAuthz\XmlUtils":
			require_once(dirname(__FILE__) . '/GCXAuthz/XmlUtils.php');
			break;
	}
});

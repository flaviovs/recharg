<?php

require_once __DIR__ . "/_exception.php";

spl_autoload_register(function ($class_name) {
		if (preg_match('/^Recharg\\\(.*)/', $class_name, $matches)) {
			include __DIR__ . '/' . strtr($matches[1], '\\', DIRECTORY_SEPARATOR) .'.php';
		}
	});

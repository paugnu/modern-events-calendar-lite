<?php

namespace MEC;

class Singleton {

	private static $instance;

	public static function getInstance() {

		$class_name = static::class;

		self::$instance[ $class_name ] ??= new $class_name();

		return self::$instance[ $class_name ];
	}
}

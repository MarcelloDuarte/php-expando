<?php

class AClassWithMagicGet {

	private $data = array('foo' => 'foo', 'bar' => 'bar');
	
	public function __get($arg)
	{
		return $this->data[$arg];
	}
}

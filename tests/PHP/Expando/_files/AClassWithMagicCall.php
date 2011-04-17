<?php

class AClassWithMagicCall {
	
	public function __call($m, $a)
	{
		return $m . "($a[0])";
	}

}
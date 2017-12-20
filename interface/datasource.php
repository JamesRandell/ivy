<?php

interface datasource
{
	public function insert ();
	public function select ();
	public function update ();
	public function delete ();
	
	public $data = array ();
}

?>
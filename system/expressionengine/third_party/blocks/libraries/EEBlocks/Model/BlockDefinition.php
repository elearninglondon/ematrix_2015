<?php

namespace EEBlocks\Model;

class BlockDefinition
{
	var $id;
	var $shortname;
	var $name;
	var $atoms;

	function __construct(
		$id = NULL,
		$shortname = NULL,
		$name = NULL,
		$instructions = NULL)
	{
		$this->id = $id;
		$this->shortname = $shortname;
		$this->name = $name;
		$this->instructions = $instructions;
		$this->atoms = array();
	}
}
<?php 

namespace DdlArtisan\Types;

class Index
{
	/** @var string|null */
	public $name;

	/** @var string[] */
	public $columns = [];

	/** @var string */
	public $type;

	public function __construct($name, array $columns, $type)
	{
		$this->name = $name;
		$this->columns = $columns;
		$this->type = strtoupper($type);
	}
}
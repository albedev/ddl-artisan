<?php 

namespace DdlArtisan\Types;

class Table {
	/** @var string */
	public $name;

	/** @var Column[] */
	public $columns = [];

	/** @var ForeignKey[] */
	public $foreignKeys = [];

	/** @var Index[] */
	public $indexes = [];

	/** @var string[] */
	public $queries = [];

	public function __construct(
		$name,
		array $columns = [],
		array $foreignKeys = [],
		array $indexes = [],
		array $queries = []
	) {
		$this->name = $name;
		$this->columns = $columns;
		$this->foreignKeys = $foreignKeys;
		$this->indexes = $indexes;
		$this->queries = $queries;
	}
}
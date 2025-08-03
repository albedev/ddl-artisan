<?php 

namespace DdlArtisan\Types;

class ForeignKey
{
	/** @var string|null */
	public $name;

	/** @var string[]|string */
	public $foreignColumns = [];

	/** @var string|null */
	public $referenceDatabase;

	/** @var string */
	public $referenceTable;

	/** @var string[] */
	public $referenceColumns = [];

	/** @var string[] */
	public $options = [];

	public function __construct(
		$name,
		array $foreignColumns,
		$referenceDatabase,
		$referenceTable,
		array $referenceColumns,
		array $options = []
	) {
		$this->name = $name;
		$this->foreignColumns = $foreignColumns;
		$this->referenceDatabase = $referenceDatabase;
		$this->referenceTable = $referenceTable;
		$this->referenceColumns = $referenceColumns;
		$this->options = $options;
	}
}
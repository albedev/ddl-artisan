<?php 

namespace DdlArtisan\Types;

class Column {
	/** @var string */
	public $name;

	/** @var string */
	public $type;

	// /** @var string[] */
	// public $parameters = [];

	/** @var string[] */
	public $modifiers = [];

	/** @var bool */
	public $nullable = false;

	/** @var bool */
	public $notNull = false;

	/** @var bool */
	public $autoIncrement = false;

	/** @var bool */
	public $unique = false;

	/** @var bool */
	public $primaryKey = false;

	/** @var string */
	public $default = '';

	/** @var bool */
	public $unsigned = false;

	/** @var bool */
	public $stored = false;

	/** @var bool */
	public $onUpdate = false;

	public function __construct(
		$name,
		$type,
		// array $parameters = [],
		array $modifiers = [],
		bool $nullable = false,
		bool $notNull = false,
		bool $autoIncrement = false,
		bool $unique = false,
		bool $primaryKey = false,
		string $default = '',
		bool $unsigned = false
	) {
		$this->name = $name;
		$this->type = $type;
		// $this->parameters = $parameters;
		$this->modifiers = $modifiers;
		$this->nullable = $nullable;
		$this->notNull = $notNull;
		$this->autoIncrement = $autoIncrement;
		$this->unique = $unique;
		$this->primaryKey = $primaryKey;
		$this->default = $default;
		$this->unsigned = $unsigned;
	}
}
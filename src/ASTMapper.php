<?php

namespace DdlArtisan;

use PhpMyAdmin\SqlParser\Components\CreateDefinition;
use PhpMyAdmin\SqlParser\Components\DataType;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\CreateStatement;
use DdlArtisan\Types\Table;
use DdlArtisan\Types\Column;
use DdlArtisan\Types\ForeignKey;
use DdlArtisan\Types\Index;

class ASTMapper
{
	/**
	 * Will hold the queries that are being processed
	 * during the table creation (the queries that ELOQUENT
	 * can't handle natively)
	 * @var array<string>
	 */
	protected array $queries = [];

	/** @var string */
	protected string $tableName = '';

	/**
	 * @param Parser $parser
	 * @return Table[]
	 */
	public function map(Parser $parser): array
	{
		$tables = [];

		foreach ($parser->statements as $statement) {
			if (!($statement instanceof CreateStatement)) {
				continue;
			}

			if (($statement->options->options[6] ?? '') !== 'TABLE') {
				continue;
			}

			$this->tableName = $statement->name->expr;
			$this->queries = []; 
			$columns = [];
			$foreignKeys = [];
			$indexes = [];

			foreach ($statement->fields as $field) {
				if ($field->name !== null && $field->isConstraint === null) {
					$column = new Column(
						str_replace('\'', "\\'", $field->name),
						$field->type->name,
						$field->type->parameters,
					);

					$scp = $this->setColumnProperty($column, $field);
					
					if(!$scp) $columns[] = $column;
				}

				if ($field->key !== null) {
					$key = $field->key;

					if (strtoupper($key->type) === 'FOREIGN KEY') {
						$reference = $field->references;
						$foreignKeys[] = new ForeignKey(
							$key->name,
							$this->parseColumns($key->columns),
							$reference->table->database,
							$reference->table->table,
							$reference->columns,
							$this->parseOptions($reference->options->options)
						);
					} else if(strtoupper($key->type) === 'INDEX' || str_contains(strtoupper($key->type), 'UNIQUE')) {
							$indexes[] = new Index(
								$key->name,
								$this->parseColumns($key->columns),
								$key->type
							);
					} else if ($key->type === 'FULLTEXT INDEX') {
						$indexes[] = new Index(
							$key->name,
							$this->parseColumns($key->columns),
							$key->type
						);
					} else if ($key->type === 'SPATIAL INDEX') {
						$indexes[] = new Index(
							$key->name,
							$this->parseColumns($key->columns),
							$key->type
						);
					}
				}
			}

			$tables[] = new Table($this->tableName, $columns, $foreignKeys, $indexes, $this->queries);
		}

		return $tables;
	}

	private function setColumnProperty(Column $column, CreateDefinition $field): bool
	{
		foreach($this->parseOptions($field->options->options) as $property) {
			if(is_string($property)) {
				$elaboratedProperty = $this->getColumnProperty($property);

				if ($elaboratedProperty == 1) {
					$csc = $this->checkSpecialColumn($field->name, $field->type, array_values($field->options->options));
					if ($csc) {
						return true;
					}
					continue;
				}

				$column->{$elaboratedProperty} = true;
			}
			elseif(is_array($property)) {
				$elaboratedProperty = $this->getColumnProperty($property[0]);
				$column->{$elaboratedProperty} = $property[1];
			}
			else {
				continue;
			}
		}

		foreach($this->parseOptions($field->type->options->options) as $property) {
			if(is_string($property)) {
				$elaboratedProperty = $this->getColumnProperty($property);

				if ($elaboratedProperty == 1) {
					$csc =$this->checkSpecialColumn($field->name, $field->type, array_values($field->type->options->options));
					if ($csc) {
						return true;
					}
					continue;
				}

				$column->{$elaboratedProperty} = true;
			}
			elseif(is_array($property)) {
				$elaboratedProperty = $this->getColumnProperty($property[0]);
				$column->{$elaboratedProperty} = $property[1];
			}
			else {
				continue; // Skip undesired properties
			}
		}

		return false;
	}

	private function getColumnProperty(string $attribute): string
	{
		$map = [
			'nullable' => 'nullable',
			'not null' => 'notNull',
			'auto_increment' => 'autoIncrement',
			'unique' => 'unique',
			'default' => 'default',
			'unsigned' => 'unsigned',
			'stored' => 'stored',
			'primary key' => 'primaryKey',
			'on update' => 'onUpdate',
		];

		return $map[strtolower($attribute)] ?? 1;
	}
	
	private function checkSpecialColumn(string $name, DataType $type, array $options): bool
	{
		$specialTypes = [
			'generated always',
		];

		$columnType = strtolower($options[0]);

		if (in_array($columnType, $specialTypes)) {
			switch ($columnType) {
				case 'generated always':
					if(count($type->parameters) == 0) {
						$this->queries[] = sprintf(
							'ALTER TABLE %s ADD COLUMN %s %s GENERATED ALWAYS AS (%s) STORED',
							$this->tableName,
							$name,
							$type->name,
							$options[1]['value']
						);
					}
					else {
						$this->queries[] = sprintf(
							'ALTER TABLE %s ADD COLUMN %s %s(%s) GENERATED ALWAYS AS (%s) STORED',
							$this->tableName,
							$name,
							$type->name,
							implode(', ', $type->parameters),
							$options[1]['value']
						);
					}
					return true;
			}
		}

		return false;
	}

	private function parseOptions(array $options): array
	{
		$parsed = [];

		foreach (array_values($options) as $opt) {
			is_string($opt) ? $parsed[] = $opt : (is_array($opt) ? $parsed[] = [$opt['name'], $opt['value']] : null);
		}

		return $parsed;
	}

	private function parseColumns(array $columns): array
	{
		$parsed = [];

		foreach ($columns as $column) {
			$parsed[] = is_string($column) ? $column : (is_array($column) ? $column['name'] : null);
		}

		return $parsed;
	}
}
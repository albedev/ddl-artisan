<?php 

namespace DdlArtisan\Helpers;

use DdlArtisan\Types\ForeignKey;

class Laravel 
{
    public const TAB = '    ';
    public const MIGRATION_HEAD = "Schema::create('%table%', function (Blueprint \$table) {\n";
    public const MIGRATION_FOOT = "        });";
    protected const LARAVEL_TYPES = [
        // Numbers
        'int' => 'integer',
        'integer' => 'integer',
        'bigint' => 'bigInteger',
        'smallint' => 'smallInteger',
        'tinyint' => 'tinyInteger',
        'mediumint' => 'mediumInteger',
        'float' => 'float',
        'double' => 'double',
        'decimal' => 'decimal',

        // Strings
        'char' => 'char',
        'varchar' => 'string',
        'tinytext' => 'text',
        'text' => 'text',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',

        // Date and Time
        'date' => 'date',
        'datetime' => 'dateTime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'year' => 'year',

        // JSON
        'json' => 'json',

        // Enum e Set
        'enum' => 'enum',
        'set' => 'set',

        // Binary/BLOB
        'binary' => 'binary',
        'varbinary' => 'binary',
        'tinyblob' => 'binary',
        'blob' => 'binary',
        'mediumblob' => 'binary',
        'longblob' => 'binary',

        // Boolean
        'bit' => 'boolean',
        'boolean' => 'boolean',

        // Indexes
        'index' => 'index',
        'unique key' => 'unique',
        'fulltext index' => 'fullText',
        'spatial index' => 'spatialIndex',
    ];

    /**
     * The entire table migration definition.
     * @var string
     */
    public string $table;
    /**
     * Table name
     * @var string
     */
    public string $tableName;

    public function __construct(string $tableName) {
        $this->tableName = $tableName;
    }

    /**
     * Generates the migration head for a Laravel migration.
     * @return Laravel
     */
    public function generateMigrationHead(): Laravel {
        $this->table = str_replace('%table%', $this->tableName, self::MIGRATION_HEAD);
        return $this;
    }

    /**
     * Generates the column definition for a Laravel migration.
     * @param \DdlArtisan\Types\Column[] $columns Array of Columns
     * @return Laravel
     */
    public function generateColumns(array $columns): Laravel {
        $columnDefs = [];
        foreach ($columns as $column) {
            $columnDefs[] = $this->generateColumnDefinition($column);
        }
        $this->table .= implode("\n", $columnDefs);
        return $this;
    }

    /**
     * Generates a single column definition for Laravel migration.
     * @param \DdlArtisan\Types\Column $column
     * @return string
     */
    public function generateColumnDefinition(\DdlArtisan\Types\Column $column): string {
        $type = self::LARAVEL_TYPES[strtolower($column->type)] ?? $column->type;
        
        switch(strtolower($type)) {
            case 'enum':
                $definition = str_repeat(self::TAB, 3) . "\$table->enum('{$column->name}', [" . implode(", ", $column->modifiers) . "])";
                break;
            case 'set':
                $definition = str_repeat(self::TAB, 3) . "\$table->set('{$column->name}', [" . implode(", ", $column->modifiers) . "])";
                break;
            case 'decimal':
                $definition = str_repeat(self::TAB, 3) . "\$table->decimal('{$column->name}', " . implode(", ", $column->modifiers) . ")";
                break;
            default:
                $definition = str_repeat(self::TAB, 3) . "\$table->{$type}('{$column->name}')";
                break;
        }

        if ($column->nullable) {
            $definition .= PHP_EOL . str_repeat(self::TAB, 3) . "->nullable()";
        }

        if ($column->default !== '') {
            $definition .= $this->checkDefaultValue($column->default);
        }

        if($column->autoIncrement) {
            switch(strtolower($column->type)) {
                case 'integer':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->integer('{$column->name}', true)";
                    break;
                case 'int':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->integer('{$column->name}', true)";
                    break;
                case 'bigint':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->bigInteger('{$column->name}', true)";
                    break;
                case 'tinyint':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->tinyInteger('{$column->name}', true)";
                    break;
                case 'smallint':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->smallInteger('{$column->name}', true)";
                    break;
                case 'mediumint':
                    $definition = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->mediumInteger('{$column->name}', true)";
                    break;
                default:
                    $definition .= PHP_EOL . str_repeat(self::TAB, 4) . "->autoIncrement()";
                    break;
            }
        }

        if ($column->unique) {
            $definition .= PHP_EOL . str_repeat(self::TAB, 4) . "->unique()";
        }

        if ($column->primaryKey) {
            $definition .= PHP_EOL . str_repeat(self::TAB, 4) . "->primary()";
        }

        $definition .= ";";
        return $definition;
    }

    /**
     * Check if the default value is a special value like CURRENT_TIMESTAMP, NULL, TRUE, or FALSE.
     * @param string $value
     * @return string
     */
    private function checkDefaultValue(string $value): string {
        switch (strtoupper($value)) {
            case 'CURRENT_TIMESTAMP':
                return PHP_EOL . str_repeat(self::TAB, 4) . "->useCurrent()";
            case 'NULL':
                return PHP_EOL . str_repeat(self::TAB, 4) . "->nullable()";
            case 'TRUE':
                return PHP_EOL . str_repeat(self::TAB, 4) . "->default(true)";
            case 'FALSE':
                return PHP_EOL . str_repeat(self::TAB, 4) . "->default(false)";
            default:
                if(is_numeric($value)) {
                    return PHP_EOL . str_repeat(self::TAB, 4) . "->default(" . (float)$value . ")";
                }
                else {
                    return PHP_EOL . str_repeat(self::TAB, 4) . "->default('" . str_replace('\'', "\\'",substr($value, 1, -1)) . "')";
                }
        }
    }

    /**
     * Append the query to the migration table.
     * @param array $queries
     * @return Laravel
     */
    public function appendQuery(array $queries): Laravel {
        foreach ($queries as $query) {
            $this->table .= PHP_EOL . str_repeat(self::TAB, 2) . "DB::statement('" . $query . "');";
        }
        return $this;
    }

    /**
     * Append indexes to the migration table.
     * @param array $indexes
     * @return Laravel
     */
    public function appendIndexes(array $indexes): Laravel {
        $pattern = PHP_EOL . str_repeat(self::TAB, 3) . "\$table->%index_type%(";
        foreach ($indexes as $index) {
            $this->table .= str_replace('%index_type%', Laravel::LARAVEL_TYPES[strtolower($index->type)], $pattern);
            if(count($index->columns) > 1) {
                $this->table .= "['" . implode("', '", $index->columns) . "']";
            } else {
                $this->table .= "'" . $index->columns[0] . "'";
            }

            if (isset($index->name) && $index->name !== null) {
                $this->table .= ", '" . $index->name . "');";
            }
            else {
                $this->table .= ");";
            }
        }
        return $this;
    }

    /**
     * Get table's foreign keys
     * @param array<ForeignKey> $foreignKeys
     * @return array
     */
    public static function getForeignKeys(string $tableName, array $foreignKeys): array {
        $foreignKeysRaw = '';
        $dropForeignKeysRaw = '';
        $pattern = Laravel::TAB . "DB::statement('ALTER TABLE `%table%` ";
        $i = 0;
        
        // Generate foreign keys queries
        /** @var \DdlArtisan\Types\ForeignKey $fk */
        foreach ($foreignKeys as $fk) {
            if (count($fk->foreignColumns) > 0) {
                $table = $fk->referenceDatabase != null ? $fk->referenceDatabase . '.' . $fk->referenceTable : $fk->referenceTable;
                $foreignKeysRaw .= 
                    ($i != 0 ? Laravel::TAB : '') . 
                    str_replace('%table%', $tableName, $pattern) .
                    "ADD CONSTRAINT `" . ($fk->name ?? ('fk_' . $fk->foreignColumns[0])) . "` FOREIGN KEY (" .
                    implode(', ', $fk->foreignColumns) .
                    ") REFERENCES `$table` (" .
                    implode(', ', $fk->referenceColumns) .
                    ")" . (
                        count($fk->options) > 0 ? 
                            (' ' . implode(' ', array_map(fn($a) => $a[0] . ' ' . $a[1], $fk->options)) . "');"  . PHP_EOL) 
                            : "');" . PHP_EOL
                    );

                $dropForeignKeysRaw .= ($i != 0 ? Laravel::TAB : '') . str_replace('%table%', $tableName, $pattern) .
                    "DROP FOREIGN KEY `" . ($fk->name ?? ('fk_' . $fk->foreignColumns[0])) . "`');" . PHP_EOL;
                $i++;
            }
        }

        return [
            'foreignKeys' => $foreignKeysRaw,
            'dropForeignKeys' => $dropForeignKeysRaw
        ];
    }

    /**
     * Close the migration definition.
     * @return Laravel
     */
    public function closeMigration(): Laravel {
        $this->table .= "\n" . self::MIGRATION_FOOT;
        return $this;   
    }

    /**
     * Get the migration name.
     * @return string
     */
    public static function getMigrationName(string $table): string {
        return date('Y_m_d_His') . '_' . $table . '_migration';
    }
}   
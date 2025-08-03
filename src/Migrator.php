<?php

namespace DdlArtisan;

use DdlArtisan\Helpers\Laravel;
use DdlArtisan\Laravel\Commands\GenerateFromDdl;
use DdlArtisan\Parser;
use DdlArtisan\Types\ForeignKey;
use Illuminate\Console\Command;

class Migrator
{
    /**
     * The parser instance that will handle the SQL parsing.
     * @var Parser
     */
    public Parser $parser;
    /**
     * The parsed SQL statements.
     * @var \PhpMyAdmin\SqlParser\Statement[]|null
     */
    protected array $statements;
    /**
     * The ASTMapper instance that will map the parsed SQL statements to DdlArtisan types.
     * @var ASTMapper
     */
    public ASTMapper $ATSMapper;

    /**
     * The array that temporarily holds the schema information
     * during the migration generation process.
     * @var array<string, \DdlArtisan\Helpers\Laravel>
     */
    protected array $schemas = [];

    /**
     * The array that holds all the foreign keys and indexes
     * @var array<string, array<string[]>>
     */
    protected array $foreignKeys = [];

    /**
     * Constructor to initialize the parser with the file path.
     * @param string $filePath The path to the .sql file to be parsed.
     */
    public function __construct(string $filePath)
    {
        $this->parser = new Parser();
        $this->ATSMapper = new ASTMapper();
        $this->parser->filePath = $filePath;
    }

    /**
     * This method is responsible for generating migration files based on the parsed SQL statements.
     * It will analyze the parsed statements and create migration files that can be used to apply
     * the changes to a database schema.
     * @return void
     */
    public function generate()
    {
        $this->statements = $this->parser->parse();
        $tables = $this->ATSMapper->map($this->parser->pmaParser);

        foreach ($tables as $table) {
            $laravel = new Laravel($table->name);
            $laravel->generateMigrationHead()
                ->generateColumns($table->columns)
                ->appendIndexes($table->indexes)
                ->closeMigration()
                ->appendQuery($table->queries);

            $this->schemas[$table->name] = $laravel->table;
            $this->foreignKeys[$table->name] = Laravel::getForeignKeys($table->name, $table->foreignKeys);
        }
    }

    /**
     * This method is responsible for creating the migration files based on the parsed SQL statements.
     * It will analyze the parsed statements and create migration files that can be used to apply
     * the changes to a database schema.
     * @return string
     */
    public function migrate(string $output, Command $command): void
    {
        $this->generate();

        $command->info("Generating migrations...");

        // Generate migration files for each table
        foreach ($this->schemas as $tableName => $schema) {
            $migrationContent = str_replace([
                '%schema%',
                '%table%'
            ],
            [
                $schema,
                $tableName
            ],
            file_get_contents(__DIR__ . '/Template/migration.php.txt'));
            file_put_contents($output . '/database/migrations/' . Laravel::getMigrationName($tableName) . ".php", $migrationContent);

            $command->info("Migration file created for table: $tableName");
        }

        // Generate migration files for foreign keys
        $foreignKeysRaw = '';
        $dropForeignKeysRaw = '';

        foreach($this->foreignKeys as $tableName => $foreignKeys) {
            if (empty($foreignKeys)) {
                continue;
            }

            $foreignKeysRaw .= $foreignKeys['foreignKeys'] ?? '';
            $dropForeignKeysRaw .= $foreignKeys['dropForeignKeys'] ?? '';
        }    

        $fkContent = str_replace([
            '%queries%',
            '%drop_queries%'
        ],
        [
            $foreignKeysRaw,
            $dropForeignKeysRaw
        ],
        file_get_contents(__DIR__ . '/Template/migration.keys.php.txt'));
        file_put_contents($output . '/database/migrations/9999_12_31_235959_fk.php', $fkContent);

        $command->info("Foreign key migration file created.");
    }
}
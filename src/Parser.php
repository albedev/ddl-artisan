<?php 

namespace DdlArtisan;

use PhpMyAdmin\SqlParser\Statement;

class Parser
{
    /**
     * The path to the .sql file to be parsed.
     * @var string
     */
    public string $filePath;
    /**
     * The parser instance that will handle the SQL parsing.
     * @var \PhpMyAdmin\SqlParser\Parser
     */
    public \PhpMyAdmin\SqlParser\Parser $pmaParser;

    /**
     * Get the .sql file content and parse it into statements.
     * @return Statement[]
     */
    public function parse(): array
    {
        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new \Exception("Could not read file: {$this->filePath}");
        }

        try {
            $this->pmaParser = new \PhpMyAdmin\SqlParser\Parser($content);
        } catch (\Exception $e) {
            throw new \Exception("Could not parse SQL: {$e->getMessage()}");
        }

        return $this->pmaParser->statements;
    }
}
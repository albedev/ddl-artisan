<?php

namespace DdlArtisan\Laravel\Commands;

use DdlArtisan\Migrator;
use Illuminate\Console\Command;

class GenerateFromDdl extends Command
{
	protected $signature = 'ddl:generate {ddl_path=ddl.sql}';
	protected $description = 'Generate Laravel migrations from a DDL file';

	public function handle(): int
	{
		$rootDir = base_path();
		$ddl = $rootDir . '/' . $this->argument('ddl_path');

		if (!file_exists($ddl)) {
			$this->error("DDL file not found: $ddl");
			return 1;
		}

		$this->info("Using DDL file: $ddl");

		$migrator = new Migrator($ddl);
		$migrator->migrate($rootDir, $this);

		return 0;
	}
}
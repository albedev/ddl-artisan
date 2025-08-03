<?php

namespace DdlArtisan\Laravel;

use Illuminate\Support\ServiceProvider;
use DdlArtisan\Laravel\Commands\GenerateFromDdl;

class LaravelServiceProvider extends ServiceProvider
{
	public function register(): void {}

	public function boot(): void
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				GenerateFromDdl::class,
			]);
		}
	}
}
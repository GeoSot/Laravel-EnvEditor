<?php

namespace lasselehtinen\MyPackage\Test;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
	/**
	 * Load package service provider
	 * @param  \Illuminate\Foundation\Application $app
	 * @return geo-s\env-editor\MyPackageServiceProvider
	 */
	protected function getPackageProviders($app)
	{
		return [ServiceProvider::class];
	}

	/**
	 * Load package alias
	 * @param  \Illuminate\Foundation\Application $app
	 * @return array
	 */
	protected function getPackageAliases($app)
	{
		return [
			'env-editor' => EnvEditor::class,
		];
	}
}
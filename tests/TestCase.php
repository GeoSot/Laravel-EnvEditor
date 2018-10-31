<?php

namespace lasselehtinen\MyPackage\Test;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
	/**
	 * Load package service provider
     * @return geo-s\env-editor\ServiceProvider
	 */
	protected function getPackageProviders()
    {
		return [ServiceProvider::class];
	}

	/**
	 * Load package alias
 * @return array
	 */
	protected function getPackageAliases()
    {
        return [
			'env-editor' => EnvEditor::class,
		];
	}
}
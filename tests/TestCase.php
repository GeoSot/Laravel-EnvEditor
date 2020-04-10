<?php

namespace GeoSot\EnvEditor\Tests;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase
 *
 * @package env-editor
 *
 */
class TestCase extends OrchestraTestCase
{
    private $tempDir;



    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app)
    {
        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getPackageAliases($app)
    {
        return [
            'env-editor' => EnvEditor::class,
        ];
    }
}

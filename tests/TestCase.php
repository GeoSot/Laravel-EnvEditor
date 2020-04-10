<?php

namespace GeoSot\EnvEditor\Tests;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase.
 */
class TestCase extends OrchestraTestCase
{
    private $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageAliases($app)
    {
        return [
            'env-editor' => EnvEditor::class,
        ];
    }
}

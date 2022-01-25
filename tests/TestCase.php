<?php

namespace GeoSot\EnvEditor\Tests;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\ServiceProvider;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $key = 'base64:'.base64_encode(
            Encrypter::generateKey('AES-256-CBC')
        );

        $app['config']->set('app.key', $key);
    }

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getPackageAliases($app)
    {
        return [
            'env-editor' => EnvEditor::class,
        ];
    }

    protected static function getTestPath(): string
    {
        return realpath(__DIR__.'/fixtures');
    }

    protected static function getTestFile(bool $fullPath = false): string
    {
        $file = '.env.example';

        return $fullPath ? static::getTestPath().DIRECTORY_SEPARATOR.$file : $file;
    }
}

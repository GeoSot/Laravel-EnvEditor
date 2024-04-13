<?php

namespace GeoSot\EnvEditor\Tests\Feature;

use GeoSot\EnvEditor\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

class ConfigurationTest extends TestCase
{
    #[Test]
    #[TestWith([false])]
    #[TestWith([true])]
    public function can_disable_routes(bool $enableRoutes): void
    {
        $this->app['config']->set('env-editor.route.enable', $enableRoutes);

        $routeNames = [
            '.index',
            '.key',
            '.clearConfigCache',
            '.files.getBackups',
            '.files.createBackup',
            '.files.restoreBackup',
            '.files.destroyBackup',
            '.files.download',
            '.files.upload',
        ];

        foreach ($routeNames as $name) {
            $routeName = $this->app['config']['env-editor.route.name'].$name;
            $this->assertFalse(Route::has($routeName));
        }
    }
}

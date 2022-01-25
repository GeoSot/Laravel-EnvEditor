<?php

namespace GeoSot\EnvEditor\Tests\Feature;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\Tests\TestCase;

class UiTest extends TestCase
{
    /**
     * @test
     */
    public function can_see_dashboard(): void
    {
        $response = $this->get($this->makeRoute('index'));
        $response->assertStatus(200)
            ->assertSee(trans('env-editor::env-editor.menuTitle'));
    }

    /**
     * @test
     */
    public function can_see_backups(): void
    {
        $response = $this->get($this->makeRoute('getBackups'));
        $response->assertStatus(200)
            ->assertSee(trans('env-editor::env-editor.views.backup.title'));
    }

    /**
     * @test
     */
    public function can_download(): void
    {
        EnvEditor::shouldReceive('getFilePath')->once()->with('fooBar')->andReturns(self::getTestFile(true));
        $response = $this->get($this->makeRoute('download', ['filename' => 'fooBar']));
        $response->assertStatus(200);
        $response->assertDownload(self::getTestFile());
    }

    /**
     * @param  string  $route
     * @param  array<string, string>  $parameters
     * @return string
     */
    protected function makeRoute(string $route, array $parameters = []): string
    {
        return route(config('env-editor.route.name').'.'.$route, $parameters);
    }
}

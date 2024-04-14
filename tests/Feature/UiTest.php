<?php

namespace GeoSot\EnvEditor\Tests\Feature;

use GeoSot\EnvEditor\Facades\EnvEditor;
use GeoSot\EnvEditor\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;

class UiTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app->useEnvironmentPath(self::getTestPath());
        $app->loadEnvironmentFrom(self::getTestFile());
        $app['config']->set('env-editor.route.enable', true);
    }

    #[Test]
    public function can_see_dashboard(): void
    {
        $response = $this->get($this->makeRoute('index'));
        $response->assertStatus(200)
            ->assertSee(trans('env-editor::env-editor.menuTitle'));
    }

    #[Test]
    public function get_json_results(): void
    {
        $response = $this->getJson($this->makeRoute('index'));
        $response->assertStatus(200);
        /** @var array<array<string, mixed>> $json */
        $json = $response->json('items');
        $jsonResponse = collect($json);
        $envData = EnvEditor::getEnvFileContent()->toJson();

        $this->assertEqualsCanonicalizing($envData, $jsonResponse);
    }

    #[Test]
    public function can_set_key_value(): void
    {
        $response = $this->postJson($this->makeRoute('key'), [
            'key' => 'FOO',
            'value' => 'bar',
        ]);
        $response->assertStatus(200);
        $this->assertSame('bar', EnvEditor::getKey('FOO'));
    }

    #[Test]
    public function can_edit_key_value(): void
    {
        $this->postJson($this->makeRoute('key'), [
            'key' => 'FOO',
            'value' => 'bar',
        ]);

        $response = $this->patchJson($this->makeRoute('key'), [
            'key' => 'FOO',
            'value' => 'foo-test',
        ]);
        $response->assertStatus(200);
        $this->assertSame('foo-test', EnvEditor::getKey('FOO'));
    }

    #[Test]
    public function can_delete_key_value(): void
    {
        $this->postJson($this->makeRoute('key'), [
            'key' => 'FOO',
            'value' => 'bar',
        ]);

        $response = $this->deleteJson($this->makeRoute('key'), [
            'key' => 'FOO',
        ]);
        $response->assertStatus(200);
        $this->assertFalse(EnvEditor::keyExists('FOO'));
    }

    #[Test]
    public function can_see_backups(): void
    {
        $response = $this->get($this->makeRoute('getBackups'));
        $response->assertStatus(200)
            ->assertSee(trans('env-editor::env-editor.views.backup.title'));
    }

    #[Test]
    public function can_get_json_backups(): void
    {
        $response = $this->getJson($this->makeRoute('getBackups'));
        $response->assertStatus(200);
        /** @var array<array<string, mixed>> $json */
        $json = $response->json('items');
        $jsonResponse = collect($json);
        $envData = EnvEditor::getAllBackUps()->toJson();

        $this->assertEqualsCanonicalizing($envData, $jsonResponse);
    }

    #[Test]
    public function can_create_backups(): void
    {
        $backupsDir = config('env-editor.paths.backupDirectory');
        File::deleteDirectory($backupsDir);
        $files = fn () => File::glob($backupsDir.'/env_*');
        $this->assertEmpty($files());
        $response = $this->postJson($this->makeRoute('createBackup'));
        $response->assertStatus(200);
        $this->assertCount(1, $files());
    }

    #[Test]
    public function can_restore_backups(): void
    {
        $backupsDir = config('env-editor.paths.backupDirectory');
        File::deleteDirectory($backupsDir);

        EnvEditor::addKey('FOO', 'bar');
        EnvEditor::backUpCurrent();
        EnvEditor::deleteKey('FOO');
        $this->assertNull(EnvEditor::getKey('FOO'));
        $file = EnvEditor::getAllBackUps()->first()->name;
        $this->postJson($this->makeRoute('restoreBackup').'/'.$file);
        $this->assertSame('bar', EnvEditor::getKey('FOO'));
    }

    #[Test]
    public function can_destroy_backups(): void
    {
        $backupsDir = config('env-editor.paths.backupDirectory');
        File::deleteDirectory($backupsDir);
        EnvEditor::backUpCurrent();

        $file = EnvEditor::getAllBackUps()->first()->name;
        $this->deleteJson($this->makeRoute('destroyBackup').'/'.$file);
        $this->assertCount(0, EnvEditor::getAllBackUps());
    }

    #[Test]
    public function can_download(): void
    {
        EnvEditor::shouldReceive('getFilePath')->once()->with('fooBar')->andReturns(self::getTestFile(true));
        $response = $this->get($this->makeRoute('download', ['filename' => 'fooBar']));
        $response->assertStatus(200);
        $response->assertDownload(self::getTestFile());
    }

    #[Test]
    public function can_upload_file(): void
    {
        $this->assertFalse(EnvEditor::keyExists('FOO'));
        $this->assertFalse(EnvEditor::keyExists('FOO2'));
        $fileContent = [
            'FOO=bar',
            'FOO2=bar2',
        ];
        $this->postJson($this->makeRoute('upload'), [
            'replace_current' => true,
            'file' => UploadedFile::fake()->createWithContent('test.txt', implode(PHP_EOL, $fileContent)),
        ]);
        $this->assertSame('bar', EnvEditor::getKey('FOO'));
        $this->assertSame('bar2', EnvEditor::getKey('FOO2'));
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function makeRoute(string $route, array $parameters = []): string
    {
        return route(config('env-editor.route.name').'.'.$route, $parameters);
    }
}

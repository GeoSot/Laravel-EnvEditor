<?php

namespace GeoSot\EnvEditor\Tests\Unit\Helpers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use GeoSot\EnvEditor\Helpers\EntryObj;
use GeoSot\EnvEditor\Helpers\EnvFileContentManager;
use GeoSot\EnvEditor\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;

class EnvFileContentManagerTest extends TestCase
{
    /**
     * @test
     */
    public function retrieves_file_contents(): void
    {
        $this->app['config']->set('env-editor.paths.backupDirectory', self::getTestPath());

        $manager = $this->getEnvFileContentManager();
        $content = $manager->getParsedFileContent(self::getTestFile());

        $separators = $content->filter(fn (EntryObj $obj) => $obj->isSeparator());
        $groups = $content->groupBy(fn (EntryObj $obj) => $obj->group);

        self::assertCount(5, $separators);
        self::assertCount(5, $groups);
        self::assertCount(17, $content);
    }

    /**
     * @test
     */
    public function wrong_file_throws_exception(): void
    {
        self::expectException(EnvException::class);
        $file = config('env-editor.paths.backupDirectory').DIRECTORY_SEPARATOR.'not-existed-file';
        self::expectExceptionMessage('File "'.$file.'" does not Exists !!!');
        \GeoSot\EnvEditor\Facades\EnvEditor::getFilePath('not-existed-file');
    }

    /**
     * @test
     */
    public function fail_to_retrieve_file_contents(): void
    {
        $manager = $this->getEnvFileContentManager();
        $file = $file = config('env-editor.paths.backupDirectory').DIRECTORY_SEPARATOR.'not-existed-file';

        self::expectException(EnvException::class);
        self::expectExceptionMessage('File "'.$file.'" does not Exists !!!');
        $manager->getParsedFileContent('not-existed-file');
    }

    /**
     * @test
     */
    public function saves_file_contents(): void
    {
        $testPath = self::getTestPath();
        $this->app['config']->set('env-editor.paths.backupDirectory', $testPath);
        $baseFile = self::getTestFile();
        $manager = $this->getEnvFileContentManager();
        $content = $manager->getParsedFileContent($baseFile);

        $backUpFile = 'test.tmp';
        $backUpFileFullPath = $testPath.DIRECTORY_SEPARATOR.$backUpFile;

        file_put_contents($backUpFileFullPath, '');
        $manager->save($content, $backUpFile);

        self::assertFileEquals(self::getTestFile(true), $backUpFileFullPath);
        self::assertEqualsCanonicalizing($content->toArray(), $manager->getParsedFileContent($backUpFile)->toArray());
        unlink($backUpFileFullPath);
    }

    protected function getEnvFileContentManager(): EnvFileContentManager
    {
        $envEditor = new EnvEditor(
            new Repository($this->app['config']->get('env-editor')),
            new Filesystem()
        );
        $this->app->singleton(EnvEditor::class, fn () => $envEditor);

        return $envEditor->getFileContentManager();
    }
}

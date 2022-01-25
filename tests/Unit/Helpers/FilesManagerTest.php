<?php

namespace GeoSot\EnvEditor\Tests\Unit\Helpers;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Helpers\EnvFilesManager;
use GeoSot\EnvEditor\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

/**
 * Class FilesManagerTest.
 *
 * @group helpers
 */
class FilesManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->cleanBackUpDir();
        parent::tearDown();
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function constructor_calls_makeBackupsDirectory_method(): void
    {
        $classname = EnvFilesManager::class;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('makeBackupsDirectory');

        // now call the constructor
        $reflectedClass = new \ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();

        $envEditorMock = \Mockery::mock(EnvEditor::class);
        $constructor->invoke($mock, $envEditorMock, $this->app->make(Filesystem::class));
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function backupDir_is_created(): void
    {
        $path = $this->getEnvFilesManager()->getBackupsDir();
        $this->createAndTestPath($path);
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function getEnvDir_exists(): void
    {
        $path = $this->getEnvFilesManager()->getEnvDir();
        $this->createAndTestPath($path);
    }

    /**
     * @test
     */
    public function getBackupsDir_can_return_file(): void
    {
        $path = $this->getEnvFilesManager()->getBackupsDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->getEnvFilesManager()->getBackupsDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    /**
     * @test
     */
    public function getEnvDir_can_return_file(): void
    {
        $path = $this->getEnvFilesManager()->getEnvDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->getEnvFilesManager()->getEnvDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    /**
     * @test
     */
    public function getAllBackUps_returns_all_files(): void
    {
        $manager = $this->getEnvFilesManager();
        $file1 = $manager->getBackupsDir('test.tmp');
        $file2 = $manager->getBackupsDir('test2.tmp');
        file_put_contents($file1, 'dummy');
        file_put_contents($file2, 'dummy');

        $backUps = $manager->getAllBackUps();
        $this->assertEquals(2, $backUps->count());

        unlink($file1);
        unlink($file2);
    }

    /**
     * @test
     */
    public function backUpCurrentEnv_works_and_returns_bool(): void
    {
        $fileName = 'test.tmp';
        $this->app['config']->set('env-editor.envFileName', $fileName);

        $content = time().'_dummy';
        $manager = $this->getEnvFilesManager();
        $file = $manager->getEnvDir($fileName);
        file_put_contents($file, $content);

        //Check CurrentEnv
        $currentEnv = $manager->getFilePath();

        $this->assertTrue(file_exists($currentEnv));
        $this->assertEquals(file_get_contents($currentEnv), $content);

        $result = $manager->backUpCurrentEnv();
        $this->assertTrue($result);

        $backUps = $manager->getAllBackUps();
        $this->assertEquals(1, $backUps->count());
        $this->assertEquals(Arr::get($backUps->first(), 'content'), $content);

        unlink($file);
    }

    /**
     * @test
     */
    public function restoreBackup_works_and_returns_bool(): void
    {
        $manager = $this->getEnvFilesManager();
        //place a dummy env file
        file_put_contents($manager->getEnvDir($this->app['config']->get('env-editor.envFileName')), '');

        $fileName = time().'_test.tmp';
        $content = time().'_dummy';
        $file = $manager->getBackupsDir($fileName);
        file_put_contents($file, $content);

        $result = $manager->restoreBackup($fileName);
        $this->assertTrue($result);

        $currentEnv = $manager->getFilePath();
        $this->assertEquals(file_get_contents($currentEnv), $content);

        unlink($file);
    }

    /**
     * @test
     */
    public function deleteBackup_works_and_returns_bool(): void
    {
        $fileName = time().'_test.tmp';
        $manager = $this->getEnvFilesManager();
        $file = $manager->getBackupsDir($fileName);
        file_put_contents($file, 'dummy');

        $result = $manager->deleteBackup($fileName);
        $this->assertTrue($result);

        $this->assertFalse(file_exists($file));
    }

    /**
     * @param  string  $path
     */
    private function createAndTestPath(string $path): void
    {
        $path = realpath($path);
        $this->assertNotFalse($path);
        $filename = tempnam($path, 'test');
        $this->assertEquals($filename, realpath($filename));
        unlink($filename);
    }

    private function cleanBackUpDir(): void
    {
        (new Filesystem())->cleanDirectory($this->getEnvFilesManager()->getBackupsDir());
    }

    /**
     * @param  array<string, mixed>  $config
     * @return EnvFilesManager
     */
    protected function getEnvFilesManager(array $config = []): EnvFilesManager
    {
        $envEditor = new EnvEditor(
            new Repository($config ?: $this->app['config']->get('env-editor')),
            new Filesystem()
        );

        return $envEditor->getFilesManager();
    }
}

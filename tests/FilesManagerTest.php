<?php

namespace GeoSot\EnvEditor\Tests;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Helpers\EnvFilesManager;
use Illuminate\Support\Arr;

/**
 * Class FilesManagerTest.
 *
 * @group helpers
 */
class FilesManagerTest extends TestCase
{
    /**
     * @var EnvFilesManager
     */
    protected $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app[EnvFilesManager::class];
    }

    protected function tearDown(): void
    {
        $this->cleanBackUpDir();
        parent::tearDown();
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function constructor_calls_makeBackupsDirectory_method()
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
        $constructor->invoke($mock, $this->app[EnvEditor::class]);
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function backupDir_is_created()
    {
        $path = $this->manager->getBackupsDir();
        $this->createAndTestPath($path);
    }

    /**
     * @test
     * Test makeBackupsDirectory method
     */
    public function getEnvDir_exists()
    {
        $path = $this->manager->getEnvDir();
        $this->createAndTestPath($path);
    }

    /**
     * @test
     */
    public function getBackupsDir_can_return_file()
    {
        $path = $this->manager->getBackupsDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->manager->getBackupsDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    /**
     * @test
     */
    public function getEnvDir_can_return_file()
    {
        $path = $this->manager->getEnvDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->manager->getEnvDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    /**
     * @test
     */
    public function getAllBackUps_returns_all_files()
    {
        $file1 = $this->manager->getBackupsDir('test.tmp');
        $file2 = $this->manager->getBackupsDir('test2.tmp');
        file_put_contents($file1, 'dummy');
        file_put_contents($file2, 'dummy');

        $backUps = $this->manager->getAllBackUps();
        $this->assertEquals(2, $backUps->count());

        unlink($file1);
        unlink($file2);
    }

    /**
     * @test
     */
    public function backUpCurrentEnv_works_and_returns_bool()
    {
        $fileName = 'test.tmp';
        $this->app['config']->set('env-editor.envFileName', $fileName);

        $content = time().'_dummy';
        $file = $this->manager->getEnvDir($fileName);
        file_put_contents($file, $content);

        //Check CurrentEnv
        $currentEnv = $this->manager->getFilePath();
        $this->assertTrue(file_exists($currentEnv));
        $this->assertEquals(file_get_contents($currentEnv), $content);

        $result = $this->manager->backUpCurrentEnv();
        $this->assertTrue($result);

        $backUps = $this->manager->getAllBackUps();
        $this->assertEquals(1, $backUps->count());
        $this->assertEquals(Arr::get($backUps->first(), 'content'), $content);

        unlink($file);
    }

    /**
     * @test
     */
    public function restoreBackup_works_and_returns_bool()
    {
        //place a dummy env file
        file_put_contents($this->manager->getEnvDir($this->app['config']->get('env-editor.envFileName')), '');

        $fileName = time().'_test.tmp';
        $content = time().'_dummy';
        $file = $this->manager->getBackupsDir($fileName);
        file_put_contents($file, $content);

        $result = $this->manager->restoreBackup($fileName);
        $this->assertTrue($result);

        $currentEnv = $this->manager->getFilePath();
        $this->assertEquals(file_get_contents($currentEnv), $content);

        unlink($file);
    }

    /**
     * @test
     */
    public function deleteBackup_works_and_returns_bool()
    {
        $fileName = time().'_test.tmp';

        $file = $this->manager->getBackupsDir($fileName);
        file_put_contents($file, 'dummy');

        $result = $this->manager->deleteBackup($fileName);
        $this->assertTrue($result);

        $this->assertFalse(file_exists($file));
    }

    /**
     * @param string $path
     */
    private function createAndTestPath(string $path): void
    {
        $path = realpath($path);
        $this->assertNotFalse($path);
        $filename = tempnam($path, 'test');
        $this->assertEquals($filename, realpath($filename));
        unlink($filename);
    }

    /**
     * @return mixed
     */
    private function cleanBackUpDir(): void
    {
        $files = glob($this->manager->getBackupsDir('*'));

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

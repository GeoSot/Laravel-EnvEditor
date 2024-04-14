<?php

namespace GeoSot\EnvEditor\Tests\Unit\Dto;

use GeoSot\EnvEditor\Dto\BackupObj;
use GeoSot\EnvEditor\Dto\EntryObj;
use GeoSot\EnvEditor\Tests\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class BackupObjTest extends TestCase
{
    #[Test]
    public function it_returns_data_as_array(): void
    {
        $this->app['config']->set('env-editor.timeFormat', 'd/m H:i');
        $this->freezeTime();
        $now = now();

        $data = [
            'name' => 'foo-file',
            'createdAt' => $now->clone()->subHour(),
            'modifiedAt' => $now->clone(),
            'rawContent' => 'dummy-content',
            'path' => 'foo-path',
            'entries' => new Collection([
                new EntryObj('key',
                    'value', 1, 1),
            ]),
        ];

        $dto = new BackupObj(...$data);

        $expected = [
            'name' => 'foo-file',
            'real_name' => 'foo-file',
            'created_at' => $now->clone()->subHour()->format('d/m H:i'),
            'modified_at' => $now->clone()->format('d/m H:i'),
            'raw_content' => 'dummy-content',
            'path' => 'foo-path',
            'entries' => $data['entries']->toArray(),
        ];
        $this->assertEquals($expected, $dto->jsonSerialize());
    }
}

<?php

namespace GeoSot\EnvEditor\Tests\Unit\Helpers;

use GeoSot\EnvEditor\Helpers\EntryObj;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntryObjTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider getDummyData
     */
    public function parses_env_lines(string $line, string $key, mixed $value, bool $isSeparator): void
    {
        $entry = EntryObj::parseEnvLine($line, 2, 8);

        self::assertSame($key, $entry->key);
        self::assertEquals($value, $entry->getValue());
        self::assertSame($isSeparator, $entry->isSeparator());
    }

    /**
     * @test
     */
    public function creates_key_separator(): void
    {
        $entry = EntryObj::makeKeysSeparator(1, 2);

        self::assertSame(2, $entry->index);
        self::assertSame(1, $entry->group);
        self::assertSame('', $entry->key);
        self::assertEquals('', $entry->getValue());
        self::assertTrue($entry->isSeparator());
    }

    /**
     * @test
     *
     * @dataProvider getDummyData
     */
    public function returns_env_lines(string $line, string $key, mixed $value, bool $isSeparator): void
    {
        $entry = EntryObj::parseEnvLine($line, 2, 8);

        self::assertSame($line, $entry->getAsEnvLine());
    }

    /**
     * @test
     *
     * @dataProvider getDummyData
     */
    public function returns_value_or_default(string $line, string $key, mixed $value, bool $isSeparator): void
    {
        $entry = EntryObj::parseEnvLine($line, 2, 8);
        self::assertEquals($value ?: 'foobar', $entry->getValue('foobar'));
    }

    /**
     * @return array{array{string, string, mixed, bool}}
     */
    public static function getDummyData(): array
    {
        return [
            ['test=1', 'test', 1, false],
            ['test="foo"', 'test', '"foo"', false],
            ['test=null', 'test', 'null', false],
            ['test="null"', 'test', '"null"', false],
            ['test=""', 'test', '""', false],
            ['test=', 'test', null, false],
            ['', '', '', true],
        ];
    }
}

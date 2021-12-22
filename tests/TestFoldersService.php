<?php

namespace PhotoCentralSynologyStorageServer\Tests;

trait TestFoldersService
{
    public static function getDataFolder(): string
    {
        return dirname(__DIR__) . '/tests/data';
    }
}
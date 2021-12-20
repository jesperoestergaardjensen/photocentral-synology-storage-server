<?php

namespace PhotoCentralSynologyStorageServer\Tests;

trait TestFoldersService
{
    public function getDataFolder(): string
    {
        return dirname(__DIR__) . '/tests/data';
    }
}
<?php

namespace PhotoCentralSynologyStorageServer\Tests;

trait TestService
{
    public static function getDataFolder(): string
    {
        return dirname(__DIR__) . '/tests/data';
    }

    public static function getPublicFolder(): string
    {
        return dirname(__DIR__) . '/public';
    }

    public static function getPhotoCollectionId(): string
    {
        return '2b613e8d-dd2a-4f1c-85a5-6e708290c200';
    }
}

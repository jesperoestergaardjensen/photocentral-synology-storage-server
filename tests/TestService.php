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

    public static function getExifSamplesPhotoCollectionId(): string
    {
        return '3b613e8d-dd2a-7f1c-85b5-6e708290c200';
    }

    public static function getDuplicatePhotosPhotoCollectionId(): string
    {
        return '9c111e8d-dd2a-7f1c-85b5-6e708290c200';
    }

    public static function getExifSamplesFolder(): string
    {
        return dirname(__DIR__) . '/vendor/jesperoestergaardjensen/exif-samples/jpg/';
    }

    public static function getDuplicatePhotosFolder(): string
    {
        return dirname(__DIR__) . '/tests/data/duplicate_photos/';
    }
}

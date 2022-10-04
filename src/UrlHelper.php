<?php

namespace PhotoCentralSynologyStorageServer;

class UrlHelper
{
    public function searchPath(): string
    {
        return 'Search.php';
    }

    public function listPhotoCollectionsPath(): string
    {
        return 'ListPhotoCollections.php';
    }

    public function getPhotoPath(): string
    {
        return 'GetPhoto.php';
    }

    public function displayPhotoPath(): string
    {
        return 'DisplayPhoto.php';
    }

    public function listPhotoQuantityByYearPath(): string
    {
        return 'ListPhotoQuantityByYear.php';
    }

    public function listPhotoQuantityByMonthPath(): string
    {
        return 'ListPhotoQuantityByMonth.php';
    }
    public function listPhotoQuantityByDayPath(): string
    {
        return 'ListPhotoQuantityByDay.php';
    }

    public function softDeletePath(): string
    {
        return 'SoftDeletePhoto.php';
    }

    public function undoSoftDeletePath(): string
    {
        return 'UndoSoftDeletePhoto.php';
    }

    public function listPhotosPath(): string
    {
        return 'ListPhotos.php';
    }
}

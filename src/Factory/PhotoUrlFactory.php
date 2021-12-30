<?php

namespace PhotoCentralSynologyStorageServer\Factory;

class PhotoUrlFactory
{
    private string $controller_public_base_url;

    public function __construct(string $controller_public_path)
    {
        $this->controller_public_base_url = $controller_public_path;
    }
    public function createPhotoUrl(string $photo_uuid, string $photo_collection_id): string
    {
        return $this->controller_public_base_url . "DisplayPhoto.php?photo_uuid={$photo_uuid}&photo_collection_id={$photo_collection_id}&image_dimensions_id=";
    }
}

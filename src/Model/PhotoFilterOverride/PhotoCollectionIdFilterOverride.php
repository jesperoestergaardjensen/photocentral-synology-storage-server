<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride;

use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;

class PhotoCollectionIdFilterOverride extends PhotoCollectionIdFilter implements PhotoFilterOverride
{
    public function getSql(): string
    {
        $photo_collection_id_list = implode("','", $this->getPhotoCollectionIdList());
        return PhotoDatabaseTable::ROW_PHOTO_COLLECTION_ID . " IN ('{$photo_collection_id_list}')";
    }
}
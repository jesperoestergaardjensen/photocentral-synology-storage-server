<?php

namespace PhotoCentralSynologyStorageServer\Model\DatabaseTables;

class PhotoDatabaseTable
{
    public const NAME = 'Photo';

    public const ROW_PHOTO_UUID            = 'photo_uuid';
    public const ROW_WIDTH                 = 'width';
    public const ROW_HEIGHT                = 'height';
    public const ROW_ORIENTATION           = 'orientation';
    public const ROW_EXIF_DATE_TIME        = 'exif_date_time';
    public const ROW_FILE_SYSTEM_DATE_TIME = 'file_system_date_time';
    public const ROW_OVERRIDE_DATE_TIME    = 'override_date_time';
    public const ROW_PHOTO_DATE_TIME       = 'photo_date_time';
    public const ROW_CAMERA_BRAND          = 'camera_brand';
    public const ROW_CAMERA_MODEL          = 'camera_model';
    public const ROW_PHOTO_ADDED_DATE_TIME = 'photo_added_date_time';
    public const ROW_PHOTO_COLLECTION_ID   = 'photo_collection_id';
}

<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Factory\PhotoUrlFactory;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

class PhotoRepository
{
    private DbEntity $database_table;
    private DatabaseConnection $database_connection;
    private PhotoUrlFactory $photo_url_factory;

    public function __construct(DatabaseConnection $database_connection, PhotoUrlFactory $photo_url_factory)
    {
        $this->database_connection = $database_connection;
        $this->photo_url_factory = $photo_url_factory;
    }

    public function connectToDb(): void
    {
        $link = DB::link([
            'host'     => $this->database_connection->getHost(),
            'username' => $this->database_connection->getUsername(),
            'password' => $this->database_connection->getPassword(),
            'dbname'   => $this->database_connection->getDatabaseName(),
        ]);

        $this->database_table = new DbEntity(PhotoDatabaseTable::NAME, $link);
    }

    /**
     * @param array $photo_uuid_list
     *
     * @return Photo[]
     */
    public function list(array $photo_uuid_list): array
    {
        $photo_uuid_list_str = implode("','", $photo_uuid_list);

        $photo_rows = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(PhotoDatabaseTable::ROW_PHOTO_UUID . " IN ('{$photo_uuid_list_str}')")
            ->get());

        $photos = [];

        foreach ($photo_rows as $photo_row) {
            $photo_row[Photo::PHOTO_URL] = $this->photo_url_factory->createPhotoUrl($photo_row[Photo::PHOTO_UUID], $photo_row[Photo::PHOTO_COLLECTION_ID]);
            $photo = Photo::fromArray($photo_row);
            $photos[$photo_row[PhotoDatabaseTable::ROW_PHOTO_UUID]] = $photo;
        }

        return $photos;
    }

    /**
     * @param array|null $photo_filters
     * @param array|null $sorting_parameter_list
     * @param int        $limit
     *
     * @return array
     */
    public function listWithFilters(?array $photo_filters = null, ?array $sorting_parameter_list = null, int $limit = 25): array
    {
        $photo_list = [];

        $base_sql = $this->database_table->reset()->setSelect('*');

        if ($photo_filters) {
            $first_filter = array_pop($photo_filters);
            $base_sql->setWhere($first_filter->getSql());
        }

        if ($sorting_parameter_list) {
            $first_sorting = array_pop($sorting_parameter_list);
            $base_sql->setOrderBy($first_sorting->getSql());
        }

        $base_sql->setLimit($limit);
        $photo_array_list = $base_sql->get();

        foreach ($photo_array_list as $key => $photo_array) {
            $photo_array[Photo::PHOTO_URL] = $this->photo_url_factory->createPhotoUrl($photo_array[Photo::PHOTO_UUID], $photo_array[Photo::PHOTO_COLLECTION_ID]);
            $photo_array_list[$key] = $photo_array;
        }

        return $photo_array_list;
/*
        foreach ($photo_rows as $photo_row) {
            $photo_list[] = Photo::fromArray($photo_row);
        }

        return $photo_list;
*/
    }

    public function add(Photo $new_photo): void
    {
        $photo_array = $new_photo->toArray();
        // We do not want to save the Photo url in the database
        unset($photo_array[Photo::PHOTO_URL]);
        $this->database_table->add($photo_array);
    }

    /**
     * @param Photo[] $photo_list
     *
     * @return void
     */
    public function bulkAdd(array $photo_list): void
    {
        if (count($photo_list) === 0) {
            return;
        }

        $table_name = PhotoDatabaseTable::NAME;
        $table_columns =
            PhotoDatabaseTable::ROW_PHOTO_UUID . ',' .
            PhotoDatabaseTable::ROW_WIDTH . ',' .
            PhotoDatabaseTable::ROW_HEIGHT . ',' .
            PhotoDatabaseTable::ROW_ORIENTATION . ',' .
            PhotoDatabaseTable::ROW_EXIF_DATE_TIME . ',' .
            PhotoDatabaseTable::ROW_FILE_SYSTEM_DATE_TIME . ',' .
            PhotoDatabaseTable::ROW_OVERRIDE_DATE_TIME . ',' .
            PhotoDatabaseTable::ROW_PHOTO_DATE_TIME . ',' .
            PhotoDatabaseTable::ROW_CAMERA_BRAND . ',' .
            PhotoDatabaseTable::ROW_CAMERA_MODEL . ',' .
            PhotoDatabaseTable::ROW_PHOTO_ADDED_DATE_TIME . ',' .
            PhotoDatabaseTable::ROW_PHOTO_COLLECTION_ID;

        $sql_values = '';
        $row_added_date_time = time();

        foreach ($photo_list as $new_photo) {
            $sql_values .= "(
                '{$new_photo->getPhotoUuid()}', 
                {$new_photo->getWidth()},
                {$new_photo->getHeight()},
                {$new_photo->getOrientation()},
                {$this->dbNULL($new_photo->getExifDateTime())},
                {$this->dbNULL($new_photo->getFileSystemDateTime())},
                {$this->dbNULL($new_photo->getOverrideDateTime())},
                {$this->dbNULL($new_photo->getPhotoDateTime())},
                '{$new_photo->getCameraBrand()}',
                '{$new_photo->getCameraModel()}',
                {$row_added_date_time},
                '{$new_photo->getPhotoCollectionId()}'
            ),";
        }

        $sql_values = rtrim($sql_values, ','); // strip last comma
        $this->database_table->runSQL("INSERT IGNORE INTO {$table_name} ({$table_columns}) VALUES {$sql_values};");
    }

    private function dbNULL($variable) {
        return $variable ?? 'NULL';
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    public function get($photo_uuid, $photo_collection_id): Photo
    {
        $photo_rows = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(PhotoDatabaseTable::ROW_PHOTO_UUID . " = '$photo_uuid'")
            ->addWhere(PhotoDatabaseTable::ROW_PHOTO_COLLECTION_ID . " = '$photo_collection_id'")
            ->get());

        if (isset($photo_rows[0])) {
            $photo_rows[0][Photo::PHOTO_URL] = $this->photo_url_factory->createPhotoUrl($photo_rows[0][Photo::PHOTO_UUID], $photo_rows[0][Photo::PHOTO_COLLECTION_ID]);
            return Photo::fromArray($photo_rows[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Photo with photo_uuid = $photo_uuid and photo collection id $photo_collection_id");
        }
    }
}

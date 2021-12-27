<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralStorage\Model\PhotoSorting\SortByCreatedTimestamp;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

class PhotoRepository
{
    private DbEntity $database_table;
    private DatabaseConnection $database_connection;

    public function __construct(DatabaseConnection $database_connection)
    {
        $this->database_connection = $database_connection;
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
            $photos[$photo_row[PhotoDatabaseTable::ROW_PHOTO_UUID]] = Photo::fromArray($photo_row);
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

        return $base_sql->get();
/*
        foreach ($photo_rows as $photo_row) {
            $photo_list[] = Photo::fromArray($photo_row);
        }

        return $photo_list;
*/
    }

    public function add(Photo $new_photo): void
    {
        $this->database_table->add($new_photo->toArray());
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
            return Photo::fromArray($photo_rows[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Photo with photo_uuid = $photo_uuid and photo collection id $photo_collection_id");
        }
    }
}

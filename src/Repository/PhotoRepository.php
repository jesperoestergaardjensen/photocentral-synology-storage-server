<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralStorage\Photo;
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
}

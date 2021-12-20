<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\SynologyPhotoCollectionDatabaseTable;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

class SynologyPhotoCollectionRepository
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

        $this->database_table = new DbEntity(SynologyPhotoCollectionDatabaseTable::NAME, $link);
    }

    public function add(SynologyPhotoCollection $synology_photo_collection): void
    {
        $this->database_table->add($synology_photo_collection->toArray());
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    public function get(string $id): SynologyPhotoCollection
    {
        $result_data_list = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(SynologyPhotoCollectionDatabaseTable::ROW_ID . " = '{$id}'")
            ->get()
        );

        if (count($result_data_list) == 0) {
            throw new PhotoCentralSynologyServerException("Could not find a Synology photo collection with id = {$id}");
        }

        $result_data = $result_data_list[0];
        return SynologyPhotoCollection::fromArray($result_data);
    }

    /**
     * @return SynologyPhotoCollection[]
     */
    public function list(): array
    {
        $return_list = null;

        $result_data_list = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->get()
        );

        if (count($result_data_list) == 0) {
            $return_list = [];
        } else {
            foreach ($result_data_list as $result_data) {
                $return_list[] = SynologyPhotoCollection::fromArray($result_data);
            }
        }

        return $return_list;
    }
}


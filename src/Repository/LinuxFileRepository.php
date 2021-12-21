<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\LinuxFileDatabaseTable;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

/**
 * @internal
 */
class LinuxFileRepository
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

        $this->database_table = new DbEntity(LinuxFileDatabaseTable::NAME, $link);
    }

    /**
     * @param string     $search_string
     * @param int        $limit
     * @param array|null $allowed_photo_source_uuids
     *
     * @return LinuxFile[]
     */
    public function search(string $search_string, int $limit, array $allowed_photo_source_uuids = null): array
    {
        $linux_files = [];

        if ($allowed_photo_source_uuids === null) {
            $sql = "SELECT * FROM LinuxFile WHERE MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";

        } else {
            $photo_source_uudids = implode("','", $allowed_photo_source_uuids);
            $sql = "SELECT * FROM LinuxFile WHERE " . LinuxFile::DB_ROW_PHOTO_SOURCE_UUID . " IN ('{$photo_source_uudids}') AND MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";
        }

        $linux_files_data = $this->database_table->runSQL($sql);

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    public function update(LinuxFile $linux_file): void
    {
    }

    public function delete(string $synology_photo_collection_id, int $inode_index): void
    {
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    public function get(int $inode_index, string $synlogy_photo_collection_id): LinuxFile
    {
        throw new PhotoCentralSynologyServerException("Cannot find Linux file with inode_index = $inode_index and photo collection id $synlogy_photo_collection_id");
    }

    /**
     * @param int $inode_index
     *
     * @return LinuxFile[]
     */
    public function list(int $inode_index): array
    {
        return[];
    }

    public function bulkAdd(array $linux_files_to_bulk_insert, int $bulk_size = 1000): void
    {

    }
}

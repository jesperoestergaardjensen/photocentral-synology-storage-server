<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityDay;
use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityMonth;
use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityYear;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

class PhotoQuantityRepository
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
     * @param ?array $photo_collection_id_list
     *
     * @return PhotoQuantityYear[]
     */
    public function listPhotoQuantityByYear(?array $photo_collection_id_list): array
    {
        if ($photo_collection_id_list !== null && count($photo_collection_id_list) === 0) {
            return [];
        }

        $applicable_date_time = PhotoDatabaseTable::ROW_PHOTO_DATE_TIME;
        $valid_timestamp = "$applicable_date_time > 0";
        $timestamp_is_not_future = "$applicable_date_time < " . time();

        $base_sql = $this->database_table
            ->reset()
            ->setSelect("FROM_UNIXTIME({$applicable_date_time}, \"%Y\") as year, COUNT(*) as totalPhotos")
            ->setWhere($valid_timestamp)
            ->addWhere($timestamp_is_not_future)
            ->setGroupBy('year')
            ->setOrderBy('year DESC')
        ;

        if ($photo_collection_id_list !== null) {
            $photo_collection_id_list_as_string = implode("','", $photo_collection_id_list);
            $base_sql = $this->database_table->addWhere(Photo::PHOTO_COLLECTION_ID . " IN ('{$photo_collection_id_list_as_string}')");
        }

        $year_rows = $base_sql->get();
        $year_array = [];

        foreach ($year_rows as $row) {
            $year_array[] = new PhotoQuantityYear($row['year'], $row['year'], $row['totalPhotos']);
        }

        return $year_array;
    }

    public function listPhotoQuantityByMonth(int $year, ?array $photo_collection_id_list): array
    {
        if ($photo_collection_id_list !== null && count($photo_collection_id_list) === 0) {
            return [];
        }

        $applicable_date_time = PhotoDatabaseTable::ROW_PHOTO_DATE_TIME;

        $base_sql = $this->database_table
            ->reset()
            ->setSelect("FROM_UNIXTIME({$applicable_date_time}, \"%c\")+0 as month, COUNT(*) as totalPhotos")
            ->setWhere("FROM_UNIXTIME({$applicable_date_time}, \"%Y\") = " . $year)
            ->setGroupBy('month')
            ->setOrderBy('month ASC')
        ;

        if ($photo_collection_id_list !== null) {
            $photo_collection_id_list_as_string = implode("','", $photo_collection_id_list);
            $base_sql->addWhere(Photo::PHOTO_COLLECTION_ID . " IN ('{$photo_collection_id_list_as_string}')");
        }

        $month_rows = $base_sql->get();
        $month_array = [];

        foreach ($month_rows as $row) {
            $month_array[] = new PhotoQuantityMonth($row['month'], $row['month'], $row['totalPhotos']);
        }

        return $month_array;
    }

    public function listPhotoQuantityByDay(int $year, int $month, ?array $photo_collection_id_list): array
    {
        if ($photo_collection_id_list !== null && count($photo_collection_id_list) === 0) {
            return [];
        }
        $applicable_date_time = PhotoDatabaseTable::ROW_PHOTO_DATE_TIME;

        $base_sql = $this->database_table
            ->reset()
            ->setSelect("FROM_UNIXTIME({$applicable_date_time}, \"%e\")+0 as day, COUNT(*) as totalPhotos")
            ->setWhere("FROM_UNIXTIME({$applicable_date_time}, \"%Y\") = " . $year)
            ->addWhere("FROM_UNIXTIME({$applicable_date_time}, \"%c\") = " . $month)
            ->setGroupBy('day')
            ->setOrderBy('day ASC')
        ;

        if ($photo_collection_id_list !== null) {
            $photo_collection_id_list_as_string = implode("','", $photo_collection_id_list);
            $base_sql->addWhere(Photo::PHOTO_COLLECTION_ID . " IN ('{$photo_collection_id_list_as_string}')");
        }

        $days_rows = $base_sql->get();
        $days_array = [];

        foreach ($days_rows as $row) {
            $days_array[] = new PhotoQuantityDay($row['day'], $row['day'], $row['totalPhotos']);
        }

        return $days_array;
    }
}

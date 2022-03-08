create table LinuxFile
(
    synology_photo_collection_id varchar(255) not null,
    inode_index         int          not null,
    last_modified_date  bigint       not null,
    file_name           varchar(244) not null,
    file_path           varchar(244) not null
);

create unique index LinuxFile_unique_entry
    on LinuxFile (synology_photo_collection_id, inode_index);

alter table LinuxFile
    add imported bool default null null;

alter table LinuxFile
    add import_date_time bigint default null null;

alter table LinuxFile
    add row_added_date_time bigint not null;

alter table LinuxFile
    add photo_uuid varchar(255) not null;

alter table LinuxFile
    add skipped_error varchar(1024) default null null;

create index LinuxFile_photo_uuid_index
    on LinuxFile (photo_uuid);

ALTER TABLE `LinuxFile`
    ADD `scheduled_for_deletion` TINYINT(1) NOT NULL DEFAULT '0' AFTER `skipped_error`;

ALTER TABLE `LinuxFile`
    ADD `file_uuid` VARCHAR(255) NULL DEFAULT NULL FIRST;

UPDATE `LinuxFile`
SET `file_uuid` = UUID()
where `file_uuid` is null;

ALTER TABLE `LinuxFile`
    ADD `skipped` bool default false not null AFTER `photo_uuid`;

ALTER TABLE `LinuxFile`
    ADD INDEX `combi-index` (`imported`, `skipped`, `scheduled_for_deletion`);

ALTER TABLE `LinuxFile`
    ADD FULLTEXT `text-search` (`file_name`, `file_path`);

create unique index LinuxFile__duplicate_index
    on LinuxFile (photo_uuid, synology_photo_collection_id, imported, skipped);

ALTER TABLE `LinuxFile`
    ADD `duplicate` bool default false not null AFTER `photo_uuid`;

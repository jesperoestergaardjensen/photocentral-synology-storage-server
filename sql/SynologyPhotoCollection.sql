create table SynologyPhotoCollection
(
    id                varchar(255) not null,
    name              varchar(255) not null,
    description       varchar(255) null,
    enabled           TINYINT(1) default 0,
    image_source_path varchar(255) not null,
    status_files_path varchar(255) not null
);
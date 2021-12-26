create table Photo
(
    photo_uuid varchar(255) not null,
    width int not null,
    height int not null,
    orientation int not null,
    exif_date_time bigint null,
    file_system_date_time bigint null,
    override_date_time bigint null,
    photo_date_time bigint null,
    camera_brand varchar(255) null,
    camera_model varchar(255) null,
    photo_added_date_time bigint not null,
    photo_collection_id varchar(255) not null
);

alter table Photo
    add constraint Photo_unique_key
        unique (photo_uuid, photo_collection_id);

create index Photo_photo_uuid_index
    on Photo (photo_uuid);



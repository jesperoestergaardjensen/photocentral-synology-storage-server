INSERT INTO `LinuxFile` (`file_uuid`, `photo_collection_id`, `inode_index`, `last_modified_date`, `file_name`, `file_path`, `imported`, `import_date_time`, `row_added_date_time`, `photo_uuid`, `skipped`, `skipped_error`, `scheduled_for_deletion`) VALUES
    ('2e869946-8be0-4193-a4a2-72ff6b0f5e93', '11efa610-5378-4964-b432-d891aef00eb9', 858, 1601071200, '20200926_171901.jpg', 'SamsungS6/SweetHomeAppUpload/2020-09-26/', 1, 1616774987, 1616774808, 'c3db925a9c3f19f6285f7038dcd9844e', 0, NULL, 0);

INSERT INTO `Photo` (photo_uuid,width,height,orientation,exif_date_time,file_system_date_time,override_date_time,photo_date_time,camera_brand,camera_model,photo_added_date_time,photo_collection_id) VALUES
    ('c3db925a9c3f19f6285f7038dcd9844e',1024,728,1,1601071200,NULL,NULL,1601071200,'Asus','Zenfone7',1601071200,'11efa610-5378-4964-b432-d891aef00eb9');
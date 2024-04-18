CREATE TABLE oc_consignee(
    consignee_id int(11) NOT NULL,
    consignee_name varchar(255) NOT NULL,
    master_id int(11) NOT NULL DEFAULT 0,
    Company_Name varchar(255) NOT NULL,
    location varchar(128) NOT NULL,
    image varchar(255) NOT NULL,
    status tinyint(1) NOT NULL DEFAULT 0,
    date_added datetime NOT NULL,
    date_modified datetime NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE oc_consignee_description (
    consignee_id int(11) NOT NULL,
    language_id int(11) NOT NULL,
    name varchar(255) NOT NULL,
    description text NOT NULL,
    tag text NOT NULL,
    meta_title varchar(255) NOT NULL,
    meta_description varchar(255) NOT NULL,
    meta_keyword varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE oc_consignee_image (
    consignee_image_id int(11) NOT NULL,
    consignee_id int(11) NOT NULL,
    image varchar(255) NOT NULL,
    sort_order int(3) NOT NULL DEFAULT 0
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

`
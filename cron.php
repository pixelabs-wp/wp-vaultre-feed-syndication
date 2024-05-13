<?php

define('WP_USE_THEMES', false);

define('ABSPATH', $_SERVER["DOCUMENT_ROOT"]."/");

require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');


require_once 'wb_realestate_child.php';

set_time_limit(0);

$data = @new_listing_ApiData(0);

header("content-type: application/json");

echo json_encode($data);

// echo ini_get('max_execution_time'); 

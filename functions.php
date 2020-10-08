<?php
//remove_action('rest_api_init', 'create_initial_rest_routes', 99); RETURN THIS AFTER ENDING

$dirbase = get_template_directory();
require_once $dirbase . '/endpoints/user_post.php';
require_once $dirbase . '/endpoints/user_get.php';

require_once $dirbase . '/endpoints/photo_post.php';
require_once $dirbase . '/endpoints/photo_get.php';
require_once $dirbase . '/endpoints/photo_delete.php';

require_once $dirbase . '/endpoints/comment_post.php';
require_once $dirbase . '/endpoints/comment_get.php';


// Crop Images to be lighter
update_option('medium_size_w', 300);
update_option('medium_size_h', 300);
update_option('medium_crop', 1);

update_option('large_size_w', 600);
update_option('large_size_h', 600);
update_option('large_crop', 1);

function change_api() {
  return 'json';
}
add_filter('rest_url_prefix', 'change_api');

//Exipere token after 24 hours (60*60*24 seconds)
function expire_token() {
  return time() + (60 * 60 * 24);
}
add_action('jwt_auth_expire', 'expire_token');

?>
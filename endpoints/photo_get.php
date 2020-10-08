<?php

function photo_data($post) {
  $post_meta = get_post_meta($post->ID);
  $src = wp_get_attachment_image_src($post_meta['img'][0], 'large')[0];
  $user = get_userdata($post->post_author);
  $total_comments = get_comments_number($post->ID);

  return [
    'id' => $post->ID,
    'author' => $user->user_login,
    'date' => $post->post_date,
    'src' => $src,
    'description' => $post_meta['description'][0],
    'category' => $post_meta['category'][0],    // IMPORTANT: all categories must be in one field separetade by semicolon.
    'tags' => $post_meta['tags'][0],            // IMPORTANT: all tags must be in one field separetade by semicolon.
    'clicks' => $post_meta['clicks'][0],
    'total_comments' => $total_comments,
  ];
}

function api_photo_get($request) {
  $post_id = $request['id'];
  $post = get_post($post_id);

  if (!isset($post) || empty($post_id)) {
    $response = new WP_Error('error', 'Page not found or deleted.', ['status' => 404]);
    return rest_ensure_response($response);
  }

  $photo = photo_data($post);
  $photo['clicks'] = (int) $photo['clicks'] + 1;
  update_post_meta($post_id, 'clicks', $photo['clicks']);

  $comments = get_comments([
    'post_id' => $post_id,
    'order' => 'ASC',
  ]);

  $response = [
    'photo' => $photo,
    'comments' => $comments,
  ];

  return rest_ensure_response($response);
}

function register_api_photo_get() {
  register_rest_route('wh-api-v001', '/photo/(?P<id>[0-9]+)', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_photo_get',
  ]);
}
add_action('rest_api_init', 'register_api_photo_get');

function api_photos_get($request) {
  $_total = sanitize_text_field($request['_total']) ?: 8;
  $_page = sanitize_text_field($request['_page']) ?: 1;
  $_user = sanitize_text_field($request['_user']) ?: 0;

  if (!is_numeric($_user)) {
    $user = get_user_by('login', $_user);   //If user not numeric && doesn't exist, it return all posts. 
    $_user = $user->ID;
  }

  $args = [
    'post_type' => 'post',
    'author' => $_user,
    'posts_per_page' => $_total,
    'paged' => $_page,
  ];

  $query = new WP_Query($args);
  $posts = $query->posts;

  $photos = [];
  if ($posts) {
    foreach ($posts as $post) {
      $photos[] = photo_data($post);
    }
  }

  return rest_ensure_response($photos);
}

function register_api_photos_get() {
  register_rest_route('wh-api-v001', '/photo', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_photos_get',
  ]);
}
add_action('rest_api_init', 'register_api_photos_get');

?>
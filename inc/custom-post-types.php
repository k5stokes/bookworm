<?php

/**
 * Books Post Type
 */
add_action('init', function () {
    $labels = [
        'name'                     => __('Books', 'bookworm'),
        'singular_name'            => __('Book', 'bookworm'),
        'menu_name'                => __('Books', 'bookworm'),
        'all_items'                => __('All Books', 'bookworm'),
        'add_new'                  => __('Add new', 'bookworm'),
        'add_new_item'             => __('Add new Book', 'bookworm'),
        'edit_item'                => __('Edit Book', 'bookworm'),
        'new_item'                 => __('New Book', 'bookworm'),
        'view_item'                => __('View Book', 'bookworm'),
        'view_items'               => __('View Books', 'bookworm'),
        'search_items'             => __('Search Books', 'bookworm'),
        'not_found'                => __('No Books found', 'bookworm'),
        'not_found_in_trash'       => __('No Books found in trash', 'bookworm'),
        'parent'                   => __('Parent Book:', 'bookworm'),
        'featured_image'           => __('Featured image for this Book', 'bookworm'),
        'set_featured_image'       => __('Set featured image for this Book', 'bookworm'),
        'remove_featured_image'    => __('Remove featured image for this Book', 'bookworm'),
        'use_featured_image'       => __('Use as featured image for this Book', 'bookworm'),
        'archives'                 => __('Book archives', 'bookworm'),
        'insert_into_item'         => __('Insert into Book', 'bookworm'),
        'uploaded_to_this_item'    => __('Upload to this Book', 'bookworm'),
        'filter_items_list'        => __('Filter Books list', 'bookworm'),
        'items_list_navigation'    => __('Books list navigation', 'bookworm'),
        'items_list'               => __('Books list', 'bookworm'),
        'attributes'               => __('Books attributes', 'bookworm'),
        'name_admin_bar'           => __('Book', 'bookworm'),
        'item_published'           => __('Book published', 'bookworm'),
        'item_published_privately' => __('Book published privately.', 'bookworm'),
        'item_reverted_to_draft'   => __('Book reverted to draft.', 'bookworm'),
        'item_scheduled'           => __('Book scheduled', 'bookworm'),
        'item_updated'             => __('Book updated.', 'bookworm'),
        'parent_item_colon'        => __('Parent Book:', 'bookworm'),
    ];

    $args = [
        'label'                 => __('Books', 'bookworm'),
        'labels'                => $labels,
        'description'           => '',
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_rest'          => true,
        'rest_base'             => '',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'has_archive'           => false,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'delete_with_user'      => false,
        'exclude_from_search'   => false,
        'Book_type'       => 'post',
        'map_meta_cap'          => true,
        'hierarchical'          => true,
        'rewrite'               => ['slug' => 'books', 'with_front' => true],
        'query_var'             => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-networking',
        'supports'              => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
    ];

    register_post_type('books', $args);
});
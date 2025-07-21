<?php

/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define('CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0');

/**
 * Enqueue styles
 */
function child_enqueue_styles()
{
	wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all');
}

add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);


function custom_post_type()
{
	$labels = array(
		'name'                => _x('Movies', 'Post Type General Name'),
		'singular_name'       => _x('Movie', 'Post Type Singular Name'),
		'menu_name'           => __('Movies'),
		'parent_item_colon'   => __('Parent Movie'),
		'all_items'           => __('All Movies'),
		'view_item'           => __('View Movie'),
		'add_new_item'        => __('Add New Movie'),
		'add_new'             => __('Add New'),
		'edit_item'           => __('Edit Movie'),
		'update_item'         => __('Update Movie'),
		'search_items'        => __('Search Movie'),
		'not_found'           => __('Not Found'),
		'not_found_in_trash'  => __('Not found in Trash'),
	);

	$args = array(
		'label'               => __('movies'),
		'labels'              => $labels,
		'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),
		'taxonomies'          => array('genres'),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 15,
		'menu_icon'           => 'dashicons-format-video',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'show_in_rest' => true,
	);

	register_post_type('movies', $args);
}


add_action('init', 'custom_post_type', 0);


function my_enqueue()
{
	wp_enqueue_script('my-ajax-script', get_stylesheet_directory_uri() . '/school.js', array('jquery'), null, true);
	wp_localize_script('my-ajax-script', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'my_enqueue');


add_action('wp_ajax_my_action', 'insert_data');

function insert_data()
{
	global $wpdb;

	$firstname = sanitize_text_field($_POST['fname']);
	$lastname  = sanitize_text_field($_POST['lname']);
	$gender    = sanitize_text_field($_POST['gender']);
	$email     = sanitize_email($_POST['email']);
	$school    = sanitize_text_field($_POST['school']);
	$hobbies   = isset($_POST['hobbies']) ? array_map('sanitize_text_field', $_POST['hobbies']) : [];

	$table = 'student_tbl';
	$existing_email = $wpdb->get_var($wpdb->prepare(
		"SELECT email FROM $table WHERE email = %s",
		$email
	));

	if ($existing_email) {
		wp_send_json_error(array('message' => 'Email already exists.'));
		wp_die();
	}

	$image_url = '';
	if (isset($_FILES['img']) && !empty($_FILES['img']['name'])) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		$uploaded_file = $_FILES['img'];
		$upload_overrides = array('test_form' => false);

		$movefile = wp_handle_upload($uploaded_file, $upload_overrides);

		if ($movefile && !isset($movefile['error'])) {
			$image_url = $movefile['url'];
		} else {
			wp_send_json_error(array('message' => 'File upload failed: ' . $movefile['error']));
			wp_die();
		}
	}

	$hobbies_str = !empty($hobbies) ? implode(', ', $hobbies) : '';

	$result = $wpdb->insert(
		$table,
		array(
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'gender'    => $gender,
			'email'     => $email,
			'school'    => $school,
			'hobbies'   => $hobbies_str,
			'image'     => $image_url,
		),
		array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
	);

	if ($result) {
		wp_send_json_success(array('message' => 'Data inserted successfully.'));
	} else {
		wp_send_json_error(array('message' => 'Failed to insert data.'));
	}

	wp_die();
}



function my_data_show()
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM student_tbl");

	foreach ($results as $row) {
		echo "<tr>
            <td>{$row->id}</td>
            <td>{$row->firstname}</td>
            <td>{$row->lastname}</td>
            <td>{$row->gender}</td>
            <td>{$row->email}</td>
            <td>{$row->school}</td>
            <td>{$row->hobbies}</td>
            <td><img src='{$row->image}' width='60'></td>
            <td class='d-flex gap-2'>
			<button class='btn btn-sm btn-primary edit-link' data-id='{$row->id}'>Edit</button>
			<button class='btn btn-sm btn-danger delete-link' data-id='{$row->id}'>Delete</button>
			</td>
        </tr>";
	}
	wp_die();
}

add_action('wp_ajax_show_table', 'my_data_show');

function delete_data()
{
	global $wpdb;
	$table = 'student_tbl';
	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$result = $wpdb->delete($table, array('id' => $id));
	wp_send_json_success($result);
}


add_action('wp_ajax_my_data_delete', 'delete_data');



function get_student_data_callback()
{
	global $wpdb;
	$id = intval($_POST['id']);
	$table = 'student_tbl';

	$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

	if ($row) {
		wp_send_json_success($row);
	} else {
		wp_send_json_error('Student not found');
	}
}

add_action('wp_ajax_get_student_data', 'get_student_data_callback');


function update_student_data()
{
	global $wpdb;
	$table = 'student_tbl';

	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	if ($id === 0) {
		wp_send_json_error('Invalid ID');
	}

	$fname = sanitize_text_field($_POST['fname']);
	$lname = sanitize_text_field($_POST['lname']);
	$gender = sanitize_text_field($_POST['gender']);
	$email = sanitize_email($_POST['email']);
	$school = sanitize_text_field($_POST['school']);
	$hobbies = isset($_POST['hobbies']) ? implode(', ', array_map('sanitize_text_field', $_POST['hobbies'])) : '';

	$image_url = '';
	if (!empty($_FILES['img']['name'])) {
		$upload = wp_handle_upload($_FILES['img'], array('test_form' => false));
		if (!isset($upload['error'])) {
			$image_url = esc_url($upload['url']);
		}
	} else {
		$current = $wpdb->get_var($wpdb->prepare("SELECT image FROM $table WHERE id = %d", $id));
		$image_url = $current;
	}

	$updated = $wpdb->update(
		$table,
		array(
			'firstname' => $fname,
			'lastname' => $lname,
			'gender' => $gender,
			'email' => $email,
			'school' => $school,
			'hobbies' => $hobbies,
			'image' => $image_url
		),
		array('id' => $id),
		array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
		array('%d')
	);

	if ($updated !== false || $updated === 0) {
		wp_send_json_success('Updated');
	} else {
		wp_send_json_error('Failed to update');
	}
}
add_action('wp_ajax_update_student_data', 'update_student_data');


function insert_school()
{
	global $wpdb;
	$table = 'school_tbl';

	$sclname = sanitize_text_field($_POST['school']);
	$scladdress = sanitize_text_field($_POST['address']);

	$result = $wpdb->insert(
		$table,
		array(
			'school' => $sclname,
			'address' => $scladdress
		),
		array('%s', '%s')
	);

	if ($result) {
		wp_send_json_success(array('message' => 'Data inserted successfully.'));
	} else {
		wp_send_json_error(array('message' => 'Failed to insert data.'));
	}

	wp_die();
}

add_action('wp_ajax_insert_my_school', 'insert_school');



function show_school()
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM school_tbl");

	foreach ($results as $row) {
		echo "<tr>
            <td>{$row->schoolid}</td>
            <td>{$row->school}</td>
            <td>{$row->address}</td>
            <td class='d-flex gap-2'>
			<button class='btn btn-sm btn-primary edit-school' data-id='{$row->schoolid}'>Edit</button>
			<button class='btn btn-sm btn-danger delete-school' data-id='{$row->schoolid}'>Delete</button>
			</td>
        </tr>";
	}
	wp_die();
}

add_action('wp_ajax_show_my_school', 'show_school');


function delete_school() {
    global $wpdb;

    $schoolid = intval($_POST['id']);
    $school = $wpdb->get_var($wpdb->prepare("SELECT school FROM school_tbl WHERE schoolid = %d", $schoolid));

    if (!$schoolid || !$school) {
        wp_send_json_error(['message' => 'Invalid school ID']);
    }

    $student_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM student_tbl WHERE school = %s", $school));

    if ($student_count > 0) {
        wp_send_json_error([
            'message' => "Cannot delete: $student_count student(s) are assigned to this school. Please remove them first."
        ]);
    }

    $deleted = $wpdb->delete('school_tbl', ['schoolid' => $schoolid]);

    if ($deleted) {
        wp_send_json_success('School deleted successfully.');
    } else {
        wp_send_json_error(['message' => 'Failed to delete the school.']);
    }
}


add_action('wp_ajax_delete_my_school', 'delete_school');


function get_school_data()
{
	global $wpdb;
	$id = intval($_POST['id']);
	$table = 'school_tbl';

	$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE schoolid = %d", $id));

	if ($row) {
		wp_send_json_success($row);
	} else {
		wp_send_json_error('school not found');
	}
}

add_action('wp_ajax_get_school_data', 'get_school_data');
add_action('wp_ajax_nopriv_get_school_data', 'get_school_data');

function update_school_data()
{
	global $wpdb;
	$table = 'school_tbl';

	$schoolid = isset($_POST['schoolid']) ? intval($_POST['schoolid']) : 0;
	if ($id === 0) {
		wp_send_json_error('Invalid ID');
	}

	$sclname = sanitize_text_field($_POST['school']);
	$scladdress = sanitize_text_field($_POST['address']);
	$updated = $wpdb->update(
		$table,
		array(
			'school' => $sclname,
			'address' => $scladdress,
		),
		array('schoolid' => $schoolid),
		array('%s', '%s'),
		array('%d')
	);

	if ($updated !== false || $updated === 0) {
		wp_send_json_success('Updated');
	} else {
		wp_send_json_error('Failed to update');
	}
	  wp_die();
}

add_action('wp_ajax_update_my_school_data', 'update_school_data');
// add_action('wp_ajax_nopriv_update_my_school_data', 'update_school_data');
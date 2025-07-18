<?php
/*
    Template Name: Movies
*/


$args = array('post_type' => 'movies', 'posts_per_page' => 10);
$the_query = new WP_Query($args);
?>

<div class="main-container">
    <?php if ($the_query->have_posts()) : ?>
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <h2><?php the_title(); ?></h2>
            <div class="entry-content">
                <?php the_content(); ?>
                <?php the_post_thumbnail('thumbnail'); ?><br>
            </div>
        <?php endwhile;
        wp_reset_postdata(); ?>
</div>
<?php else:  ?>
    <p><?php _e('Sorry, no movies available'); ?></p>
<?php endif; ?>

  <a href="#" class="delete-link btn btn-danger btn-sm" data-id="<?php echo $row->id; ?>">Delete</a>





<?php
// Check if 'id' is set in the URL for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
}
?>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">School Form</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form">
        <form id="myForm" method="post" enctype="multipart/form-data">
            <!-- Prefill the existing student data -->
            <input type="hidden" name="id" value="<?php echo $row->id; ?>" />

            <label class="form-label" for="fname">Firstname</label>
            <input type="text" name="fname" id="fname" value="<?php echo esc_attr($row->fname); ?>" placeholder="Enter your firstname">

            <label class="form-label" for="lname">Lastname</label>
            <input type="text" name="lname" id="lname" value="<?php echo esc_attr($row->lname); ?>" placeholder="Enter your lastname">

            <label class="form-check-label" for="gender">Gender</label><br>
            <label class="form-check-label" for="male">Male</label>
            <input class="form-check-input" type="radio" value="male" name="gender" id="male" <?php echo ($row->gender === 'male') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="female">Female</label>
            <input class="form-check-input" type="radio" value="female" name="gender" id="female" <?php echo ($row->gender === 'female') ? 'checked' : ''; ?>><br>

            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo esc_attr($row->email); ?>" placeholder="Enter your email">
            
            <label class="form-label" for="school">School Name</label>
            <select class="form-select" name="school" id="school">
                <option value="School1" <?php echo ($row->school === 'School1') ? 'selected' : ''; ?>>School</option>
            </select>

            <label class="form-check-label" for="hobbies">Hobbies</label><br>
            <label class="form-check-label" for="sports">Sports</label>
            <input class="form-check-input" id="sports" value="Sports" type="checkbox" name="hobbies[]" <?php echo (in_array('Sports', explode(', ', $row->hobbies))) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="music">Music</label>
            <input class="form-check-input" id="music" value="Music" type="checkbox" name="hobbies[]" <?php echo (in_array('Music', explode(', ', $row->hobbies))) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="reading">Reading</label>
            <input class="form-check-input" id="reading" value="Reading" type="checkbox" name="hobbies[]" <?php echo (in_array('Reading', explode(', ', $row->hobbies))) ? 'checked' : ''; ?>>

            <label class="form-label" for="img">Image</label>
            <input class="form-control" type="file" name="img" id="img">
            
            <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
        </div>
      </div>
      <div id="response"></div>
    </div>
  </div>
</div>



function update_data() {
    global $wpdb;
    $table = 'student_tbl';

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

        if (!$row) {
            wp_send_json_error('Student not found!');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
            $fname = sanitize_text_field($_POST['fname']);
            $lname = sanitize_text_field($_POST['lname']);
            $gender = sanitize_text_field($_POST['gender']);
            $email = sanitize_email($_POST['email']);
            $school = sanitize_text_field($_POST['school']);
            $hobbies = implode(', ', $_POST['hobbies']);
            $img = $_FILES['img'];

            if ($img['name']) {
                $uploaded = wp_handle_upload($img, array('test_form' => false));
                if ($uploaded && !isset($uploaded['error'])) {
                    $image_url = $uploaded['url'];
                }
            } else {
                $image_url = $row->image;
            }

            $wpdb->update(
                $table,
                array(
                    'fname' => $fname,
                    'lname' => $lname,
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

            // Return the updated student data
            $updated_student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
            wp_send_json_success($updated_student);
        }
    }
}

add_action('wp_ajax_my_data_update', 'update_data');







jQuery(document).on("click", ".edit-link", function () {
  var id = jQuery(this).data("id");

  // Fetch student data
  jQuery.ajax({
    type: "POST",
    url: MyAjax.ajaxurl,
    data: {
      action: "get_student_data",
      id: id,
    },
    success: function (response) {
      if (response.success) {
        var student = response.data;

        jQuery("#updateForm input[name='fname']").val(student.fname);
        jQuery("#updateForm input[name='lname']").val(student.lname);
        jQuery("#updateForm input[name='email']").val(student.email);
        jQuery("#updateForm input[name='school']").val(student.school);
        jQuery("#updateForm input[name='gender'][value='" + student.gender + "']").prop("checked", true);

        jQuery("#updateForm input[name='hobbies[]']").prop("checked", false);
        if (student.hobbies) {
          var hobbies = student.hobbies.split(", ");
          hobbies.forEach(function (hobby) {
            jQuery("#updateForm input[name='hobbies[]'][value='" + hobby + "']").prop("checked", true);
          });
        }

        jQuery("#updateForm input[name='id']").val(student.id);
        jQuery("#updateModal").modal("show");
      } else {
        alert("Failed to fetch student data.");
      }
    },
    error: function () {
      alert("Error occurred.");
    },
  });
});

// Handle form submission
jQuery("#updateForm").on("submit", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  formData.append("action", "update_student_data");

  jQuery.ajax({
    type: "POST",
    url: MyAjax.ajaxurl,
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      if (response.success) {
        alert("Student updated successfully!");
        jQuery("#updateModal").modal("hide");
        // Optionally refresh your list here
        showData();
      } else {
        alert("Update failed.");
      }
    },
    error: function () {
      alert("An error occurred during update.");
    },
  });
});

add_action('wp_ajax_get_student_data', 'get_student_data_callback');

function get_student_data_callback() {
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


add_action('wp_ajax_update_student_data', 'update_student_data_callback');

function update_student_data_callback() {
    global $wpdb;
    $table = 'student_tbl';

    $id = intval($_POST['id']);

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
            'fname' => $fname,
            'lname' => $lname,
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

    if ($updated !== false) {
        wp_send_json_success('Updated');
    } else {
        wp_send_json_error('Failed to update');
    }
}


<form id="updateForm" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id">

  <label>Firstname</label>
  <input type="text" name="fname">

  <label>Lastname</label>
  <input type="text" name="lname">

  <label>Gender</label><br>
  <label><input type="radio" name="gender" value="male"> Male</label>
  <label><input type="radio" name="gender" value="female"> Female</label>

  <label>Email</label>
  <input type="email" name="email">

  <label>School</label>
  <select name="school">
    <option value="School1">School1</option>
  </select>

  <label>Hobbies</label><br>
  <label><input type="checkbox" name="hobbies[]" value="Sports"> Sports</label>
  <label><input type="checkbox" name="hobbies[]" value="Music"> Music</label>
  <label><input type="checkbox" name="hobbies[]" value="Reading"> Reading</label>

  <label>Image</label>
  <input type="file" name="img" accept="image/*">

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">Update</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
  </div>
</form>













<!--  -->

function update_student_data_callback()
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
			'fname' => $fname,
			'lname' => $lname,
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
add_action('wp_ajax_update_student_data', 'update_student_data_callback');

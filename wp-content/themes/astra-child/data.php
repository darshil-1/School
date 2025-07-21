ALTER TABLE student_tbl
ADD CONSTRAINT school_name
FOREIGN KEY (schoolid)
REFERENCES school_tbl(schoolid);


SELECT 
    s.studentid,
    s.studentname,
    sch.schoolname
FROM 
    student s
JOIN 
    school sch ON s.schoolid = sch.schoolid;




    DESC student;
DESC school;





function refreshSchoolDropdown() {
  $.post(MyAjax.ajaxurl,
    { action: 'show_my_school' },
    function(response) {
      // Parse the returned table rows to get school names and IDs
      const temp = $('<div>').html(response);
      let options = '<option value="">Select School</option>';

      temp.find('tr').each(function() {
        const school = $(this).find('td:nth-child(2)').text();
        options += `<option value="${school}">${school}</option>`;
      });

      $('#myForm select[name="school"], #updateSchoolSelect').html(options);
    }
  );
}

$(document).ready(function() {
  refreshSchoolDropdown();
  showSchool(); // your function for table

  $('#schoolForm').on('submit', function(e){
    e.preventDefault();
    $.post(MyAjax.ajaxurl, new FormData(this), function(response){
      if(response.success){
        $('#schoolModal').modal('hide');
        showSchool();
        refreshSchoolDropdown();
        $('#schoolForm')[0].reset();
      }
      else console.error(response);
    });
  });

  $(document).on('click', '.delete-school', function() {
    if (!confirm('Are you sure?')) return;
    $.post(MyAjax.ajaxurl, { action: 'delete_my_school', id: $(this).data('id') }, function(response) {
      showSchool();
      refreshSchoolDropdown();
    });
  });

  // update-school similar...
});






function delete_school() {
    global $wpdb;
    $schoolid = intval($_POST['id']);
    $school = $wpdb->get_var($wpdb->prepare("SELECT school FROM school_tbl WHERE schoolid = %d", $schoolid));

    if (!$schoolid || !$school) {
        wp_send_json_error('Invalid school ID');
    }

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM student_tbl WHERE school = %s", $school));
    if ($exists > 0) {
        wp_send_json_error([
            'message' => 'Cannot delete: students are assigned. Remove them first.'
        ]);
    }

    $deleted = $wpdb->delete('school_tbl', ['schoolid' => $schoolid]);
    wp_send_json_success($deleted);
}


$(document).on('click', '.delete-school', function() {
  if (!confirm('Are you sure you want to delete this school?')) return;
  $.post(MyAjax.ajaxurl,
    { action: 'delete_my_school', id: $(this).data('id') },
    function(response) {
      if(response.success) {
        showSchool();
        refreshSchoolDropdown();
      } else {
        alert(response.data.message || 'Deletion failed');
      }
    }
  );
});


function showBootstrapAlert(message, type = 'danger') {
    // type can be 'success', 'danger', 'warning', etc.
    const $alert = $("#school-alert");
    $alert
        .removeClass('d-none alert-success alert-danger alert-warning alert-info')
        .addClass('alert-' + type)
        .text(message)
        .fadeIn();

    // Automatically hide after 5 seconds
    setTimeout(function () {
        $alert.fadeOut(function () {
            $alert.addClass('d-none').removeClass('alert-' + type);
        });
    }, 5000);
}

$(document).on("click", ".delete-school", function () {
    if (!confirm("Are you sure you want to delete this school?")) return;

    var id = $(this).data("id");

    $.post(
        MyAjax.ajaxurl,
        {
            action: "delete_my_school",
            id: id,
        },
        function (response) {
            if (response.success) {
                showBootstrapAlert("School deleted successfully.", "success");
                showSchool();
                refreshSchoolDropdown();
            } else {
                var errorMessage = response.data && response.data.message
                    ? response.data.message
                    : "An error occurred.";
                showBootstrapAlert(errorMessage, "danger");
            }
        }
    ).fail(function () {
        showBootstrapAlert("Request failed. Please try again.", "danger");
    });
});




SELECT student_tbl.id, student_tbl.firstname, student_tbl.lastname ,student_tbl.gender, student_tbl.email, student_tbl.hobbies, student_tbl.image, student_tbl.schoolid FROM student_tbl RIGHT JOIN school_tbl ON student_tbl.schoolid = school_tbl.schoolid;



$results = $wpdb->get_results("
    SELECT 
        student_tbl.id, 
        student_tbl.firstname, 
        student_tbl.lastname,
        student_tbl.gender, 
        student_tbl.email, 
        student_tbl.hobbies, 
        student_tbl.image, 
        student_tbl.schoolid,
        school_tbl.schoolname
    FROM student_tbl 
    LEFT JOIN school_tbl ON student_tbl.schoolid = school_tbl.schoolid
");











<!-- insert -->
// AJAX action for inserting data
add_action('wp_ajax_my_action', 'insert_data');

function insert_data() {
	global $wpdb;

	check_ajax_referer('my_nonce', 'security'); // Ensure request is secure

	$firstname = sanitize_text_field($_POST['fname']);
	$lastname  = sanitize_text_field($_POST['lname']);
	$gender    = sanitize_text_field($_POST['gender']);
	$email     = sanitize_email($_POST['email']);
	$schoolid  = intval($_POST['school']);
	$hobbies   = isset($_POST['hobbies']) ? array_map('sanitize_text_field', $_POST['hobbies']) : [];

	$table = $wpdb->prefix . 'student_tbl';

	// Check for existing email
	$existing_email = $wpdb->get_var($wpdb->prepare(
		"SELECT email FROM $table WHERE email = %s",
		$email
	));

	if ($existing_email) {
		wp_send_json_error(['message' => 'Email already exists.']);
		wp_die();
	}

	// Handle image upload
	$image_url = '';
	if (!empty($_FILES['img']['name'])) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$upload_overrides = ['test_form' => false];
		$movefile = wp_handle_upload($_FILES['img'], $upload_overrides);

		if ($movefile && !isset($movefile['error'])) {
			$image_url = esc_url_raw($movefile['url']);
		} else {
			wp_send_json_error(['message' => 'File upload failed: ' . esc_html($movefile['error'])]);
			wp_die();
		}
	}

	$hobbies_str = implode(', ', $hobbies);

	// Insert into database
	$result = $wpdb->insert(
		$table,
		[
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'gender'    => $gender,
			'email'     => $email,
			'hobbies'   => $hobbies_str,
			'image'     => $image_url,
			'schoolid'  => $schoolid,
		],
		['%s', '%s', '%s', '%s', '%s', '%s', '%d']
	);

	if ($result !== false) {
		wp_send_json_success(['message' => 'Data inserted successfully.']);
	} else {
		wp_send_json_error(['message' => 'Failed to insert data.']);
	}

	wp_die();
}

// AJAX action to show data table
add_action('wp_ajax_show_table', 'my_data_show');

function my_data_show() {
	global $wpdb;

	$table_student = $wpdb->prefix . 'student_tbl';
	$table_school  = $wpdb->prefix . 'school_tbl';

	$results = $wpdb->get_results("
		SELECT 
			s.id, 
			s.firstname, 
			s.lastname, 
			s.gender, 
			s.email, 
			s.hobbies, 
			s.image, 
			s.schoolid, 
			t.school 
		FROM $table_student s
		LEFT JOIN $table_school t ON s.schoolid = t.schoolid
	");

	foreach ($results as $row) {
		echo "<tr>
			<td>{$row->id}</td>
			<td>{$row->firstname}</td>
			<td>{$row->lastname}</td>
			<td>{$row->gender}</td>
			<td>{$row->email}</td>
			<td>{$row->hobbies}</td>
			<td><img src='" . esc_url($row->image) . "' width='60'></td>
			<td>{$row->school}</td>
			<td class='d-flex gap-2'>
				<button class='btn btn-sm btn-primary edit-link' data-id='{$row->id}'>Edit</button>
				<button class='btn btn-sm btn-danger delete-link' data-id='{$row->id}'>Delete</button>
			</td>
		</tr>";
	}

	wp_die();
}

<!-- update -->

add_action('wp_ajax_update_student_data', 'update_student_data');

function update_student_data() {
	global $wpdb;
	$table = $wpdb->prefix . 'student_tbl';

	$id = intval($_POST['id']);
	if ($id === 0) wp_send_json_error('Invalid ID');

	$fname = sanitize_text_field($_POST['fname']);
	$lname = sanitize_text_field($_POST['lname']);
	$gender = sanitize_text_field($_POST['gender']);
	$email = sanitize_email($_POST['email']);
	$schoolid = intval($_POST['school']);
	$hobbies = implode(', ', array_map('sanitize_text_field', $_POST['hobbies'] ?? []));

	if (!empty($_FILES['img']['name'])) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		$upload = wp_handle_upload($_FILES['img'], ['test_form' => false]);
		$image_url = isset($upload['url']) ? esc_url($upload['url']) : '';
	} else {
		$image_url = $wpdb->get_var($wpdb->prepare("SELECT image FROM $table WHERE id = %d", $id));
	}

	$updated = $wpdb->update(
		$table,
		[
			'firstname' => $fname,
			'lastname'  => $lname,
			'gender'    => $gender,
			'email'     => $email,
			'schoolid'  => $schoolid,
			'hobbies'   => $hobbies,
			'image'     => $image_url
		],
		['id' => $id],
		['%s', '%s', '%s', '%s', '%d', '%s', '%s'],
		['%d']
	);

	if ($updated !== false) {
		wp_send_json_success(['message' => 'Data updated successfully.']);
	} else {
		wp_send_json_error(['message' => 'Failed to update data.']);
	}
}

<!-- delete -->
 add_action('wp_ajax_my_data_delete', 'delete_data');

function delete_data() {
	global $wpdb;
	$id = intval($_POST['id']);
	$table = $wpdb->prefix . 'student_tbl';

	$deleted = $wpdb->delete($table, ['id' => $id]);

	if ($deleted) {
		wp_send_json_success(['message' => 'Student deleted successfully.']);
	} else {
		wp_send_json_error(['message' => 'Failed to delete student.']);
	}
	wp_die();
}



<select name="school" id="updateSchoolSelect" class="form-select">
  <option value="">Select School</option>
  <?php
  global $wpdb;
  
  // Fetch all schools
  $schools = $wpdb->get_results("SELECT * FROM school_tbl");
  
  // Get current student school (e.g., from $_GET or from DB if editing)
  $student_id = isset($_GET['id']) ? intval($_GET['id']) : 0; // or from a variable
  $student_school = '';

  if ($student_id) {
      $student_school = $wpdb->get_var($wpdb->prepare("SELECT school FROM student_tbl WHERE id = %d", $student_id));
  }

  if ($schools) {
    foreach ($schools as $school) {
      $selected = ($school->scoolid == $student_school) ? 'selected' : '';
      echo '<option value="' . esc_attr($school->scoolid) . '" ' . $selected . '>' . esc_html($school->school) . '</option>';
    }
  }
  ?>
</select>







<!-- school -->
 $student_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}student_tbl WHERE schoolid = %d", $schoolid));

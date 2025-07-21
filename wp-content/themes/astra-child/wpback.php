$(document).ready(function () {
  $("#myTable").DataTable();
  $("#mySchool").DataTable();

  showData();
  showSchool();

  $("#myForm").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "my_action");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success) {
          showData();
          $("#myForm")[0].reset();
          $("#exampleModal").modal("hide");
          showBootstrapAlert(response.data.message, "success");
        } else {
          showBootstrapAlert(response.data.message, "warning");
        }
      },
      error: function () {
        showBootstrapAlert("An error occurred during submission.", "danger");
      },
    });
  });

  function showData() {
    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: { action: "show_table" },
      success: function (response) {
        $("#studentTableBody").html(response);
      },
      error: function () {
        console.error("Failed to fetch student table.");
      },
    });
  }

  $(document).on("click", ".delete-link", function () {
    if (!confirm("Are you sure you want to delete this student?")) return;
    const id = $(this).data("id");

    $.post(MyAjax.ajaxurl, {
      action: "my_data_delete",
      id: id
    }, function (response) {
      showBootstrapAlert(response.data.message, "danger");
      showData();
    }).fail(function () {
      showBootstrapAlert("Failed to delete student.", "danger");
    });
  });

  $(document).on("click", ".edit-link", function () {
    const id = $(this).data("id");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: {
        action: "get_student_data",
        id: id,
      },
      success: function (response) {
        if (response.success) {
          const student = response.data;
          $("#updateForm input[name='fname']").val(student.firstname);
          $("#updateForm input[name='lname']").val(student.lastname);
          $("#updateForm input[name='email']").val(student.email);
          $("#updateSchoolSelect").val(student.school);
          $(`#updateForm input[name='gender'][value='${student.gender}']`).prop("checked", true);

          $("#updateForm input[name='hobbies[]']").prop("checked", false);
          if (student.hobbies) {
            student.hobbies.split(", ").forEach(function (hobby) {
              $(`#updateForm input[name='hobbies[]'][value='${hobby}']`).prop("checked", true);
            });
          }

          $("#updateForm input[name='id']").val(student.id);
          $("#updateModal").modal("show");
        } else {
          alert("Failed to fetch student data.");
        }
      },
      error: function () {
        alert("Error occurred while fetching student data.");
      },
    });
  });

  $("#updateForm").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "update_student_data");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success) {
          showBootstrapAlert(response.data.message, "success");
          $("#updateModal").modal("hide");
          showData();
        } else {
          showBootstrapAlert("Update failed.", "warning");
        }
      },
      error: function () {
        showBootstrapAlert("An error occurred while updating.", "danger");
      },
    });
  });

  $("#schoolForm").submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "insert_my_school");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success) {
          showAlert(response.data.message, "success");
        }
        $("#schoolModal").modal("hide");
        showSchool();
        refreshSchoolDropdown();
      },
      error: function () {
        showAlert("An error occurred while adding school.", "danger");
      },
    });
  });

  function showSchool() {
    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: { action: "show_my_school" },
      success: function (response) {
        $("#schoolTable").html(response);
      },
      error: function () {
        console.error("Failed to load school data.");
      },
    });
  }

  $(document).on("click", ".delete-school", function () {
    if (!confirm("Are you sure you want to delete this school?")) return;
    const id = $(this).data("id");

    $.post(MyAjax.ajaxurl, {
      action: "delete_my_school",
      id: id
    }, function (response) {
      if (response.success) {
        showAlert(response.data.message, "success");
        showSchool();
        refreshSchoolDropdown();
      } else {
        showAlert(response?.data?.message || "An error occurred.", "danger");
      }
    }).fail(function () {
      showAlert("Request failed. Please try again.", "danger");
    });
  });

  $(document).on("click", ".edit-school", function () {
    const id = $(this).data("id");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: {
        action: "get_school_data",
        id: id,
      },
      success: function (response) {
        if (response.success) {
          const school = response.data;
          $("#updateschoolForm input[name='schoolid']").val(school.schoolid);
          $("#updateschoolForm input[name='school']").val(school.school);
          $("#updateschoolForm textarea[name='address']").val(school.address);

          $("#updateschoolModal").modal("show");
        } else {
          alert("Failed to fetch school data.");
        }
      },
      error: function () {
        alert("Error occurred while fetching school data.");
      },
    });
  });

  $("#updateschoolForm").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "update_my_school_data");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success) {
          $("#updateschoolModal").modal("hide");
          showAlert(response.data.message, "success");
          showSchool();
          refreshSchoolDropdown();
        } else {
          showAlert(response.data.message, "warning");
        }
      },
      error: function () {
        showAlert("Failed to update school.", "warning");
      },
    });
  });

  function refreshSchoolDropdown() {
    $.post(MyAjax.ajaxurl, { action: "show_my_school" }, function (response) {
      const temp = $("<div>").html(response);
      let options = '<option value="">Select School</option>';

      temp.find("tr").each(function () {
        const schoolid = $(this).find("td:first").text().trim();
        const schoolName = $(this).find("td:nth-child(2)").text().trim();
        if (schoolid && schoolName) {
          options += `<option value="${schoolid}">${schoolName}</option>`;
        }
      });

      $('#myForm select[name="school"], #updateSchoolSelect').html(options);
    });
  }

  function showBootstrapAlert(message, type = "danger") {
    const $alert = $("#school-alert");
    $alert
      .removeClass("d-none alert-success alert-danger alert-warning alert-info")
      .addClass("alert-" + type)
      .text(message)
      .fadeIn();

    setTimeout(function () {
      $alert.fadeOut(function () {
        $alert.addClass("d-none").removeClass("alert-" + type);
      });
    }, 3000);
  }

  function showAlert(message, type = "danger") {
    const $alert = $("#alert");
    $alert
      .removeClass("d-none alert-success alert-danger alert-warning alert-info")
      .addClass("alert-" + type)
      .text(message)
      .fadeIn();

    setTimeout(function () {
      $alert.fadeOut(function () {
        $alert.addClass("d-none").removeClass("alert-" + type);
      });
    }, 3000);
  }
});





function update_student_data() {
	global $wpdb;
	$table = 'student_tbl';

	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	if ($id === 0) {
		wp_send_json_error(array('message' => 'Invalid student ID.'));
		wp_die();
	}

	$fname     = sanitize_text_field($_POST['fname']);
	$lname     = sanitize_text_field($_POST['lname']);
	$gender    = sanitize_text_field($_POST['gender']);
	$email     = sanitize_email($_POST['email']);
	$schoolid  = intval($_POST['school']);
	$hobbies   = isset($_POST['hobbies']) ? implode(', ', array_map('sanitize_text_field', $_POST['hobbies'])) : '';

	$image_url = '';
	if (!empty($_FILES['img']['name'])) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		$upload = wp_handle_upload($_FILES['img'], array('test_form' => false));
		if (!isset($upload['error'])) {
			$image_url = esc_url_raw($upload['url']);
		} else {
			wp_send_json_error(array('message' => 'Image upload failed: ' . $upload['error']));
			wp_die();
		}
	} else {
		// Retain old image if new one not uploaded
		$image_url = $wpdb->get_var($wpdb->prepare("SELECT image FROM $table WHERE id = %d", $id));
	}

	$updated = $wpdb->update(
		$table,
		array(
			'firstname' => $fname,
			'lastname'  => $lname,
			'gender'    => $gender,
			'email'     => $email,
			'hobbies'   => $hobbies,
			'image'     => $image_url,
			'schoolid'  => $schoolid,
		),
		array('id' => $id),
		array('%s', '%s', '%s', '%s', '%s', '%s', '%d'),
		array('%d')
	);

	if ($updated !== false) {
		wp_send_json_success(array('message' => 'Data Updated Successfully.'));
	} else {
		wp_send_json_error(array('message' => 'No changes made or update failed.'));
	}

	wp_die();
}














$(document).on('click', '.edit-link', function () {
    var studentId = $(this).data('id');

    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'get_student_data',
            id: studentId
        },
        success: function (response) {
            if (response.success) {
                var data = response.data;

                // Populate modal fields
                $('#updateForm input[name="id"]').val(data.id);
                $('#updateForm input[name="fname"]').val(data.firstname);
                $('#updateForm input[name="lname"]').val(data.lastname);
                $('#updateForm input[name="email"]').val(data.email);
                $('#updateForm input[name="gender"][value="' + data.gender + '"]').prop('checked', true);

                // Set selected school
                $('#updateSchoolSelect').val(data.schoolid);

                // Set hobbies
                $('#updateForm input[name="hobbies[]"]').prop('checked', false);
                if (data.hobbies) {
                    var hobbies = data.hobbies.split(', ');
                    hobbies.forEach(function (hobby) {
                        $('#updateForm input[name="hobbies[]"][value="' + hobby + '"]').prop('checked', true);
                    });
                }

                // Open the modal
                $('#updateModal').modal('show');
            } else {
                alert('Failed to fetch student data.');
            }
        }
    });
});





$("#myForm").on("submit", function (e) {
  e.preventDefault();

  // Validation logic
  const fname = $("input[name='fname']").val().trim();
  const lname = $("input[name='lname']").val().trim();
  const email = $("input[name='email']").val().trim();
  const gender = $("input[name='gender']:checked").val();
  const school = $("select[name='school']").val();

  if (!fname || !lname || !email || !gender || !school) {
    showBootstrapAlert("Please fill all required fields.", "warning");
    return;
  }

  // Optional: Basic email format check
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showBootstrapAlert("Please enter a valid email address.", "warning");
    return;
  }

  var formData = new FormData(this);
  formData.append("action", "my_action");

  // Your existing AJAX call continues here...
});



$("#updateForm").on("submit", function (e) {
  e.preventDefault();

  // Validation logic
  const fname = $("#updateForm input[name='fname']").val().trim();
  const lname = $("#updateForm input[name='lname']").val().trim();
  const email = $("#updateForm input[name='email']").val().trim();
  const gender = $("#updateForm input[name='gender']:checked").val();
  const school = $("#updateSchoolSelect").val();

  if (!fname || !lname || !email || !gender || !school) {
    showBootstrapAlert("Please fill all required fields.", "warning");
    return;
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showBootstrapAlert("Please enter a valid email address.", "warning");
    return;
  }

  var formData = new FormData(this);
  formData.append("action", "update_student_data");

  // Your existing AJAX call continues here...
});



$("#schoolForm").submit(function (e) {
  e.preventDefault();

  const schoolName = $("input[name='school']").val().trim();
  const address = $("textarea[name='address']").val().trim();

  if (!schoolName || !address) {
    showAlert("Please fill in both school name and address.", "warning");
    return;
  }

  var formData = new FormData(this);
  formData.append("action", "insert_my_school");

  // Continue with your AJAX...
});

$("#updateschoolForm").on("submit", function (e) {
  e.preventDefault();

  const schoolName = $("#updateschoolForm input[name='school']").val().trim();
  const address = $("#updateschoolForm textarea[name='address']").val().trim();

  if (!schoolName || !address) {
    showAlert("Please fill in both school name and address.", "warning");
    return;
  }

  let formData = new FormData(this);
  formData.append("action", "update_my_school_data");

  // Continue with your AJAX...
});

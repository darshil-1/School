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

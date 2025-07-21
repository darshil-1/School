$(document).ready(function () {
  $("#myTable").DataTable();
  $("#mySchool").DataTable();

  showData();

  $("#myForm").on("submit", function (e) {
    e.preventDefault();

    const fname = $("input[name='fname']").val().trim();
    const lname = $("input[name='lname']").val().trim();
    const email = $("input[name='email']").val().trim();
    const gender = $("input[name='gender']:checked").val();
    const school = $("#school").val();
    const hobby = $("input[name='hobbies[]']").val().trim();
    const img = $("input[name='img']").val();

    if (!fname || !lname || !email || !gender || !school) {
      showRequire("All Fields are required.", "warning");
      return;
    }

    if (!fname) {
      $("#fnameErr").html("Please Enter your firstname!");
      return;
    } else {
      $("#fnameErr").html("");
    }

    if (!lname) {
      $("#lnameErr").html("Please Enter your lastname!");
      return;
    } else {
      $("#lnameErr").html("");
    }

    if (!gender) {
      $("#genErr").html("Please select your gender!");
      return;
    } else {
      $("#genErr").html("");
    }

    if (!email) {
      $("#emailErr").html("Please Enter your email!");
      return;
    } else {
      $("#emailErr").html("");
    }

    if (!school) {
      $("#sclErr").html("Please select your school!");
      return;
    } else {
      $("#sclErr").html("");
    }

    if (!hobby) {
      $("#hobErr").html("Please select your hobbies!");
      return;
    } else {
      $("#hobErr").html();
    }

    if (!img) {
      $("#imgErr").html("Please Upload Image");
      return;
    } else {
      $("imgErr").html("");
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      showRequire("Please enter a valid email address.", "warning");
      return;
    }

    var formData = new FormData(this);
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
          showRequire(response.data.message, "warning");
        }
      },
      error: function (response) {
        console.error(response);
        showBootstrapAlert(response.data.message, "danger");
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
      error: function (response) {
        console.error(response);
      },
    });
  }

  $(document).on("click", ".delete-link", function () {
    if (!confirm("Are you sure you want to delete this student?")) return;

    var id = $(this).data("id");

    $.post(
      MyAjax.ajaxurl,
      {
        action: "my_data_delete",
        id: id,
      },
      function (response) {
        showBootstrapAlert(response.data.message, "danger");
        showData();
      }
    );
  });

  $(document).on("click", ".edit-link", function () {
    var id = $(this).data("id");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: {
        action: "get_student_data",
        id: id,
      },
      success: function (response) {
        if (response.success) {
          var student = response.data;

          $("#updateForm input[name='fname']").val(student.firstname);
          $("#updateForm input[name='lname']").val(student.lastname);
          $("#updateForm input[name='email']").val(student.email);
          $("#updateSchoolSelect").val(student.schoolid);
          // $('#updateSchoolSelect').val(data.schoolid);
          $(
            "#updateForm input[name='gender'][value='" + student.gender + "']"
          ).prop("checked", true);

          $("#updateForm input[name='hobbies[]']").prop("checked", false);
          if (student.hobbies) {
            var hobbies = student.hobbies.split(", ");
            hobbies.forEach(function (hobby) {
              $(
                "#updateForm input[name='hobbies[]'][value='" + hobby + "']"
              ).prop("checked", true);
            });
          }

          $("#updateForm input[name='id']").val(student.id);
          refreshSchoolDropdown();
          $("#updateModal").modal("show");
        } else {
          alert("Failed to fetch student data.");
        }
      },
      error: function () {
        alert("Error occurred.");
      },
    });
  });

  $("#updateForm").on("submit", function (e) {
    e.preventDefault();
    var formData = new FormData(this);
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
          alert("Update failed.");
        }
      },
      error: function () {
        showBootstrapAlert(response.data.message, "danger");
      },
    });
  });

  showSchool();
  $("#schoolForm").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);
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
      error: function (response) {
        console.error(response);
        showAlert(response.data.message, "danger");
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
        $("#schoolModal").modal("hide");
      },
      error: function (response) {
        console.error(response);
      },
    });
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
          showAlert(response.data.message, "success");
          showSchool();
          refreshSchoolDropdown();
        } else {
          var errorMessage =
            response.data && response.data.message
              ? response.data.message
              : "An error occurred.";
          showAlert(errorMessage, "danger");
        }
      }
    ).fail(function () {
      showAlert("Request failed. Please try again.", "danger");
    });
  });

  $(document).on("click", ".edit-school", function () {
    var id = $(this).data("id");
    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: {
        action: "get_school_data",
        id: id,
      },
      success: function (response) {
        if (response.success) {
          var schools = response.data;
          $("#updateschoolForm input[name='schoolid']").val(schools.schoolid);
          $("#updateschoolForm input[name='school']").val(schools.school);
          $("#updateschoolForm textarea[name='address']").val(schools.address);

          $("#updateschoolModal").modal("show");
        } else {
          alert("Failed to fetch school data.");
        }
      },
      error: function () {
        alert("Error occurred.");
      },
    });
  });

  $("#updateschoolForm").on("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);
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
        showAlert(response.data.message, "warning");
      },
    });
  });

  function refreshSchoolDropdown() {
    $.post(MyAjax.ajaxurl, { action: "show_my_school" }, function (response) {
      const temp = $("<div>").html(response);
      let options = '<option value="">Select School</option>';

      temp.find("tr").each(function () {
        const school = $(this).find("td:nth-child(2)").text();
        options += `<option value="${school}">${school}</option>`;
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

  function showRequire(message, type = "danger") {
    const $alert = $("#req_alert");
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

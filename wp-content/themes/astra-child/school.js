$(document).ready(function () {
  $("#myTable").DataTable();
  $("#mySchool").DataTable();

  showData();

  $("#myForm").on("submit", function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append("action", "my_action");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        showData();
        $("#myForm")[0].reset();
        $("#exampleModal").modal("hide");
      },
      error: function (response) {
        console.error(response);
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
          $("#updateSchoolSelect").val(student.school);
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
          alert("Student updated successfully!");
          $("#updateModal").modal("hide");

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
        $("#schoolModal").modal("hide");
        showSchool();
      },
      error: function (response) {
        console.error(response);
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
        showSchool();
      }
    );
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
          alert("School updated successfully!");
          $("#updateschoolModal").modal("hide");

          showSchool();
        } else {
          alert("Update failed.");
        }
      },
      error: function () {
        alert("An error occurred during update.");
      },
    });
  });
});

// $(document).ajaxStart(function () {
//   $("#loader").show();
// });

// $(document).ajaxStop(function () {
//   $("#loader").hide();
// });

$(document).ready(function () {
  $("#myTable").DataTable();
  $("#mySchool").DataTable();

  showData();

  $("#myForm").on("submit", function (e) {
    e.preventDefault();

    var isValid = false;

    const fname = $("input[name='fname']").val().trim();
    const lname = $("input[name='lname']").val().trim();
    const email = $("input[name='email']").val().trim();
    const gender = $("input[name='gender']:checked").val();
    const school = $("#school").val();
    const hobby = $("input[name='hobbies[]']").val().trim();
    const img = $("input[name='img']").val();

    // if (!fname || !lname || !email || !gender || !school) {
    //   showRequire("All Fields are required.", "warning");
    //   return;
    // }

    if (!fname) {
      $("#fnameErr").html("Please Enter your firstname!");
      isValid = false;
    } else {
      $("#fnameErr").html("");
      isValid = true;
    }

    if (!lname) {
      $("#lnameErr").html("Please Enter your lastname!");
      isValid = false;
    } else {
      $("#lnameErr").html("");
      isValid = true;
    }

    if (!gender) {
      $("#genErr").html("Please select your gender!");
      isValid = false;
    } else {
      $("#genErr").html("");
      isValid = true;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
      $("#emailErr").html("Please Enter your email!");
      isValid = false;
    } else if (!emailPattern.test(email)) {
      $("#emailErr").html("Enter a Valid Email");
      isValid = false;
    } else {
      $("#emailErr").html("");
      isValid = true;
    }

    if (!school) {
      $("#sclErr").html("Please select your school!");
      isValid = false;
    } else {
      $("#sclErr").html("");
      isValid = true;
    }

    // if (!hobby) {
    //   $("#hobErr").html("Please select your hobbies!");
    //
    // } else {
    //   $("#hobErr").html();
    // }

    // if (!img) {
    //   $("#imgErr").html("Please Upload Image");
    //
    // } else {
    //   $("imgErr").html("");
    // }

    if (!isValid) {
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
      beforeSend: function () {
        $("#loader").show();
      },

      success: function (response) {
        if (response.success) {
          showData();
          $("#myForm")[0].reset();

          $("#exampleModal").modal("hide");
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.data.message,
            timer: 3000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.data.message,
          });
        }
      },

      error: function (response) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.data
            ? response.data.message
            : "An unexpected error occurred.",
        });
      },

      complete: function () {
        $("#loader").hide();
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
    const id = $(this).data("id");

    Swal.fire({
      title: "Are you sure?",
      text: "You won’t be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: MyAjax.ajaxurl,
          data: {
            action: "my_data_delete",
            id: id,
            beforeSend: function () {
              $("#loader").show();
            },
          },

          success: function (response) {
            Swal.fire({
              icon: "success",
              title: "Deleted!",
              text: response.data.message,
              timer: 3000,
              showConfirmButton: false,
            });
            showData();
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Error!",
              text: "Failed to delete student.",
            });
          },
          complete: function () {
            $("#loader").hide();
          },
        });
      }
    });
  });

  $(document).on("click", ".edit-link", function () {
    var id = $(this).data("id");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: {
        action: "get_student_data",
        id: id,
        beforeSend: function () {
          $("#loader").show();
        },
      },
      success: function (response) {
        if (response.success) {
          var student = response.data;

          $("#updateForm input[name='fname']").val(student.firstname);
          $("#updateForm input[name='lname']").val(student.lastname);
          $("#updateForm input[name='email']").val(student.email);
          $("#updateSchoolSelect").val(student.schoolid);

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
      complete: function () {
        $("#loader").hide();
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
      beforeSend: function () {
        $("#loader").show();
      },
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.data.message,
            timer: 3000,
            showConfirmButton: false,
          });
          $("#updateModal").modal("hide");
          $("#updateForm")[0].reset();
          showSchool();
          showData();
        } else {
          Swal.fire({
            icon: "error",
            title: "Update Failed",
            text: "Update failed.",
          });
        }
      },
      error: function (xhr) {
        let msg = "An error occurred.";
        if (
          xhr.responseJSON &&
          xhr.responseJSON.data &&
          xhr.responseJSON.data.message
        ) {
          msg = xhr.responseJSON.data.message;
        }
        Swal.fire({
          icon: "error",
          title: "Error",
          text: msg,
        });
      },
      complete: function () {
        $("#loader").hide();
      },
    });
  });

  showSchool();
  $("#schoolForm").submit(function (e) {
    e.preventDefault();

    // $("#sclErr").html("");
    // $("#addrErr").html("");

    // const sclname = $("#school").val();
    // const scladdress = $("input[name='address']").val();

    // let isValid = false;

    // if (!sclname) {
    //   $("#sclErr").html("Please enter the school name.");
    //   isValid = false;
    // }

    // if (!scladdress) {
    //   $("#addrErr").html("Please enter the school address.");
    //   isValid = false;
    // }

    // if (!isValid) {
    //   return;
    // }

    const formData = new FormData(this);
    formData.append("action", "insert_my_school");

    $.ajax({
      type: "POST",
      url: MyAjax.ajaxurl,
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $("#loader").show();
      },
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.data.message,
            timer: 3000,
            showConfirmButton: false,
          });

          $("#schoolModal").modal("hide");
          $("#schoolForm")[0].reset();
          showSchool();
          showData();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.data?.message || "Failed to insert school.",
          });
        }
      },
      error: function (response) {
        console.error(response);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.responseJSON?.data?.message || "Something went wrong.",
        });
      },
      complete: function () {
        $("#loader").hide();
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
    const id = $(this).data("id");

    Swal.fire({
      title: "Are you sure?",
      text: "You won’t be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: MyAjax.ajaxurl,
          data: {
            action: "delete_my_school",
            id: id,
            beforeSend: function () {
              $("#loader").show();
            },
          },
          success: function (response) {
            if (response.success) {
              Swal.fire("Deleted!", response.data.message, "success");
              showSchool();
              refreshSchoolDropdown();
            } else {
              Swal.fire(
                "Error",
                response.data?.message || "Failed to delete.",
                "error"
              );
            }
          },
          error: function () {
            Swal.fire("Error", "Something went wrong.", "error");
          },
          complete: function () {
            $("#loader").hide();
          },
        });
      }
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
        beforeSend: function () {
          $("#loader").show();
        },
      },
      success: function (response) {
        if (response.success) {
          var schools = response.data;
          $("#updateschoolForm input[name='schoolid']").val(schools.schoolid);
          $("#updateschoolForm input[name='school']").val(schools.school);
          $("#updateschoolForm textarea[name='address']").val(schools.address);

          $("#updateschoolModal").modal("show");
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to fetch school data.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error occurred while fetching data.",
        });
      },
      complete: function () {
        $("#loader").hide();
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
      beforeSend: function () {
        $("#loader").show();
      },
      success: function (response) {
        if (response.success) {
          $("#updateschoolModal").modal("hide");
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.data.message,
            timer: 3000,
            showConfirmButton: false,
          });

          showSchool();
          showData();
          refreshSchoolDropdown();
        } else {
          Swal.fire({
            icon: "warning",
            title: "Warning",
            text: response.data.message,
          });
        }
      },
      error: function (xhr) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: xhr.responseJSON?.data?.message || "An error occurred.",
        });
      },
      complete: function () {
        $("#loader").hide();
      },
    });
  });

  function refreshSchoolDropdown() {
    $.post(MyAjax.ajaxurl, { action: "show_my_school" }, function (response) {
      let options = '<option value="">Select School</option>';

      if (response.success) {
        $.each(response.data, function (index, school) {
          options += `<option value="${school.id}">${school.school_name}</option>`;
        });

        $('#myForm select[name="school"], #updateSchoolSelect').html(options);
      } else {
        console.error("Failed to load schools");
      }
    }).fail(function () {
      console.error("Error fetching school data");
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

  let confirmCallback = null;

  function showConfirm(message, onConfirm) {
    $("#confirmMessage").text(message);
    confirmCallback = onConfirm;
    $("#confirmModal").modal("show");
  }

  $("#confirmYes").click(function () {
    if (typeof confirmCallback === "function") {
      confirmCallback();
      $("#confirmModal").modal("hide");
    }
  });
});

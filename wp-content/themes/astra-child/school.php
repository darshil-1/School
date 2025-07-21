<?php
/*
    Template Name: My School
*/


get_header();


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() . '/school.css' ?>">
  <title>School</title>
</head>

<body>
  <div id="school-alert" class="alert d-none" role="alert"></div>
  <div class="heading mt-3">
    <h2>Student Details</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Student</button>
  </div>
  <div class="showTable">
    <table id="myTable" class="display">
      <thead>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Gender</th>
        <th>Email</th>
        <th>School Name</th>
        <th>Hobbies</th>
        <th>Image</th>
        <th>Action</th>
      </thead>
      <tbody id="studentTableBody">
      </tbody>
    </table>
  </div>
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Student Form</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form">
            <form id="myForm" method="post">
              <label class="form-label" for="fname">Firstname</label>
              <input type="text" name="fname" id="fname" placeholder="Enter your firstname">

              <label class="form-label" for="lname">Lastname</label>
              <input type="text" name="lname" id="lname" placeholder="Enter your lastname">

              <label class="form-check-label" for="gender">Gender</label><br>
              <label class="form-check-label" for="male">Male</label>
              <input class="form-check-input" type="radio" value="male" name="gender" id="male">
              <label class="form-check-label" for="female">Female</label>
              <input class="form-check-input" type="radio" value="female" name="gender" id="female"><br>

              <label class="form-label" for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="Enter your email">

              <label class="form-label" for="school">School Name</label>
              <select name="school" id="">
                <option value="">Select School</option>
                <?php
                global $wpdb;
                $table = 'school_tbl';
                $schools = $wpdb->get_results("SELECT * FROM $table");

                if ($schools) {
                  foreach ($schools as $school) {
                    echo '<option value="' . esc_attr($school->school) . '">' . esc_html($school->school) . '</option>';
                  }
                }
                ?>
              </select>


              <label class="form-check-label" for="hobbies">Hobbies</label><br>
              <label class="form-check-label" for="sports">Sports</label>
              <input class="form-check-input" id="sports" value="Sports" type="checkbox" name="hobbies[]">
              <label class="form-check-label" for="music">Music</label>
              <input class="form-check-input" id="music" value="Music" type="checkbox" name="hobbies[]">
              <label class="form-check-label" for="reading">Reading</label>
              <input class="form-check-input" id="reading" value="Reading" type="checkbox" name="hobbies[]"><br>

              <label class="form-label" for="img">Image</label>
              <input class="form-control" type="file" name="img" id="img">

              <div class="modal-footer">
                <button name="add" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">School Form</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form">
            <form id="updateForm" method="post" enctype="multipart/form-data">
              <input type="hidden" name="id">

              <label class="form-label">Firstname</label>
              <input type="text" name="fname">

              <label class="form-label">Lastname</label>
              <input type="text" name="lname">

              <label class="form-label">Gender</label><br>
              <label><input type="radio" name="gender" value="male"> Male</label>
              <label><input type="radio" name="gender" value="female"> Female</label><br>

              <label class="form-label">Email</label>
              <input type="email" name="email">

              <label class="form-label">School</label>
              <select name="school" id="updateSchoolSelect" class="form-select">
                <option value="">Select School</option>
                <?php
                global $wpdb;
                $schools = $wpdb->get_results("SELECT * FROM school_tbl");

                if ($schools) {
                  foreach ($schools as $school) {
                    echo '<option value="' . esc_attr($school->school) . '">' . esc_html($school->school) . '</option>';
                  }
                }
                ?>
              </select>
              <label class="form-label">Hobbies</label><br>
              <label><input type="checkbox" name="hobbies[]" value="Sports"> Sports</label>
              <label><input type="checkbox" name="hobbies[]" value="Music"> Music</label>
              <label><input type="checkbox" name="hobbies[]" value="Reading"> Reading</label><br>

              <label class="form-label">Image</label>
              <input class="form-control" type="file" name="img" accept="image/*">

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="heading mt-3">
    <h2>School Details</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolModal">Add School</button>
  </div>

  <div class="showSchool">
    <table id="mySchool" class="display">
      <thead>
        <th>School ID</th>
        <th>School Name</th>
        <th>Address</th>
        <th>Action</th>
      </thead>
      <tbody id="schoolTable">

      </tbody>
    </table>
  </div>


  <div class="modal fade" id="schoolModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">School Form</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" id="schoolForm">

            <label for="school">School Name</label>
            <input type="text" id="school" name="school" placeholder="Enter your school name">

            <label for="address">School Address</label>
            <textarea type="text" id="address" name="address" placeholder="Enter your school address"></textarea>

            <div class="modal-footer">
              <button class="btn btn-primary">Add School</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

    <div class="modal fade" id="updateschoolModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">School Form</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" id="updateschoolForm">
            <input type="hidden" name="schoolid" value="">

            <label for="school">School Name</label>
            <input type="text" id="school" name="school" placeholder="Enter your school name">

            <label for="address">School Address</label>
            <textarea id="address" name="address" placeholder="Enter your school address"></textarea>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Update</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>

<?php get_footer(); ?>
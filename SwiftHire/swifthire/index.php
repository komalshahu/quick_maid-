
<!-- CSRF Token -->
<?php
session_start();
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Job Application Form</div>
                <div class="card-body">

    <div class="formbold-form-wrapper">
       <img src="images\human-resources.jpg" style="max-width: 100%; height: auto;">
      <form id="contactForm" action="process_form.php" method="POST" enctype="multipart/form-data">
        <div class="formbold-input-flex">
          <div>
            <label for="firstname" class="formbold-form-label"> First Name </label>
            <input
              type="text"
              name="firstname"
              id="firstname"
              placeholder="Your first name"
              class="formbold-form-input"
              required
            />
          </div>
  
          <div>
            <label for="lastname" class="formbold-form-label"> Last Name </label>
            <input
              type="text"
              name="lastname"
              id="lastname"
              placeholder="Your last name"
              class="formbold-form-input"
              required
            />
          </div>
        </div>
  
        <div class="formbold-input-flex">
          <div>
              <label for="email" class="formbold-form-label"> Email </label>
              <input
              type="email"
              name="email"
              id="email"
              placeholder="example@email.com"
              class="formbold-form-input"
              required
              />
          </div>
  
          <div>
              <label class="formbold-form-label">Gender</label>
  
              <select class="formbold-form-input" name="gender" id="gender" required>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="others">Others</option>
              </select>
          </div>
        </div>
  
        <div class="formbold-mb-3 formbold-input-wrapp">
          <label for="phone" class="formbold-form-label"> Phone </label>
  
          <div>
            <input
              type="text"
              name="areacode"
              id="areacode"
              placeholder="Area code"
              class="formbold-form-input formbold-w-45"
              required
            />
  
            <input
              type="text"
              name="phone"
              id="phone"
              placeholder="Phone number"
              class="formbold-form-input"
              required
            />
          </div>
        </div>
  
        <div class="formbold-mb-3">
          <label for="age" class="formbold-form-label"> Age </label>
          <input
            type="number"
            name="age"
            id="age"
            class="formbold-form-input"
            required
          />
        </div>
  
        <div class="formbold-mb-3">
          <label for="startdate" class="formbold-form-label"> When can you start? </label>
          <input type="date" name="startdate" id="startdate" class="formbold-form-input" required />
        </div>
  
        <div class="formbold-mb-3">
          <label for="address" class="formbold-form-label"> Address </label>
  
          <input
            type="text"
            name="address"
            id="address"
            placeholder="Street address"
            class="formbold-form-input formbold-mb-3"
            required
          />
          <input
            type="text"
            name="address2"
            id="address2"
            placeholder="Street address line 2"
            class="formbold-form-input"
          />
        </div>
  
        <div class="formbold-mb-3">
          <label for="message" class="formbold-form-label">
            Cover Letter
          </label>
          <textarea
            rows="6"
            name="message"
            id="message"
            class="formbold-form-input"
            required
          ></textarea>
        </div>
  
        <div class="formbold-form-file-flex">
          <label for="upload" class="formbold-form-label">
            Upload Resume
          </label>
          <input
            type="file"
            name="resume"
            id="resume"
            class="formbold-form-file"
            accept=".pdf,.doc,.docx" //Accept only PDF, DOC, and DOCX files
            required
          />
        </div>
        

        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        
        <!-- Google reCAPTCHA -->
        <div class="form-group">
            <div class="g-recaptcha" data-sitekey=""></div>
        </div>
        
        <button class="formbold-btn" type="submit" name="submit">Apply Now</button>
      </form>
    </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  
<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Custom JS -->
<script src="js/script.js"></script>
<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js"></script>
<script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>

</body>
</html>

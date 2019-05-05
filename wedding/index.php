<!DOCTYPE html>
<html>
<title>Chris and Clare</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="includes/style.css">

<body>
  <!-- Header / Home-->
  <header class="w3-display-container w3-wide bgimg w3-grayscale-min" id="home">
    <div class="w3-display-middle w3-text-white w3-center">
      <h1 class="w3-jumbo">Chris & Clare</h1>
      <h2>Are getting married</h2>
      <h2><b>17.07.2017</b></h2>
    </div>
  </header>

  <!-- Navbar (sticky bottom) -->
  <div class="w3-bottom w3-hide-small">
    <div class="w3-bar w3-white w3-center w3-padding w3-opacity-min w3-hover-opacity-off">
      <a href="#home" style="width:25%" class="w3-bar-item w3-button">Home</a>
      <a href="#us" style="width:25%" class="w3-bar-item w3-button">Clare & Chris</a>
      <a href="#wedding" style="width:25%" class="w3-bar-item w3-button">Wedding</a>
      <a href="#rsvp" style="width:25%" class="w3-bar-item w3-button w3-hover-black">RSVP</a>
    </div>
  </div>

  <!-- About / Jane And John -->
  <div class="w3-container w3-padding-64 w3-pale-red w3-grayscale-min" id="us">
    <div class="w3-content">
      <h1 class="w3-center w3-text-grey"><b>Clare & Chris</b></h1>
      <img class="w3-round w3-grayscale-min" src="/w3images/wedding_couple2.jpg" style="width:100%;margin:32px 0">
      <p><i>You all know us. And we all know you. We are getting married lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Excepteur sint
          occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
          laboris nisi ut aliquip ex ea commodo consequat.</i>
      </p><br>
      <p class="w3-center"><a href="#wedding" class="w3-button w3-black w3-round w3-padding-large w3-large">Wedding Details</a></p>
    </div>
  </div>

  <!-- Background photo -->
  <div class="w3-display-container bgimg2">
    <div class="w3-display-middle w3-text-white w3-center">
      <h1 class="w3-jumbo">You Are Invited</h1><br>
      <h2>Of course..</h2>
    </div>
  </div>

  <!-- Wedding information -->
  <div class="w3-container w3-padding-64 w3-pale-red w3-grayscale-min w3-center" id="wedding">
    <div class="w3-content">
      <h1 class="w3-text-grey"><b>THE WEDDING</b></h1>
      <img class="w3-round-large w3-grayscale-min" src="/w3images/wedding_location.jpg" style="width:100%;margin:64px 0">
      <div class="w3-row">
        <div class="w3-half">
          <h2>When</h2>
          <p>Wedding Ceremony - 2:00pm</p>
          <p>Reception & Dinner - 5:00pm</p>
        </div>
        <div class="w3-half">
          <h2>Where</h2>
          <p>Some place, an address</p>
          <p>Some where, some address</p>
        </div>
      </div>
    </div>
  </div>

  <!-- RSVP section -->
  <div class="w3-container w3-padding-64 w3-pale-red w3-center w3-wide" id="rsvp">
    <h1>HOPE YOU CAN MAKE IT!</h1>
    <p class="w3-large">Kindly Respond By January, 2017</p>
    <p class="w3-xlarge">
      <button onclick="document.getElementById('id01').style.display='block'" class="w3-button w3-round w3-red w3-opacity w3-hover-opacity-off" style="padding:8px 60px">RSVP</button>
    </p>
  </div>

  <!-- RSVP modal -->
  <div id="id01" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="padding:32px;max-width:600px">
      <div class="w3-container w3-white w3-center">
        <span onclick="document.getElementById('id01').style.display='none'" class="w3-button w3-hover-red w3-display-topright">&times;</span>
        <h1 class="w3-wide">CAN YOU COME?</h1>
        <p>We really hope you can make it.</p>
        <form id="contactForm" action="insert.php" method="post">
          <input class="w3-input w3-border" type="text" placeholder="Name(s)" name="name" required>
          <input class="w3-input w3-border" type="text" placeholder="Email" name="email" required>
          <p><i>Sincerely, Chris & Clare</i></p>
          <div class="w3-row">
            <div class="w3-half">
              <input type="submit" class="w3-button w3-block w3-green" value="Going">
            </div>
            <div class="w3-half">
              <button onclick="document.getElementById('id01').style.display='block'" type="button" class="w3-button w3-block w3-red">Can't come</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="w3-center w3-black w3-padding-16">
    <p>Get your own from: <a href="https://www.fagan-1.com" title="fagan-1.com" target="_blank" class="w3-hover-text-green">fagan-1.com</a></p>
  </footer>
  <div class="w3-hide-small" style="margin-bottom:32px"> </div>
  <?php
include 'includes/scripts.php';
?>
</body>

</html>

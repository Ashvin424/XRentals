window.onload = function() {
    window.location.hash = '';
  }

  // Get all navigation links
const navLinks = document.querySelectorAll('.nav-link');

// Add an event listener to each link
navLinks.forEach((link) => {
  link.addEventListener('click', () => {
    // Remove the :visited state after 1 second
    setTimeout(() => {
      link.style.color = ''; // reset the color to its original state
    }, 1000);
  });
});

function sendMessage(){
  var message = document.getElementById("message").value;
  var name =  document.getElementById("name").value;
  var email = document.getElementById("email").value;
  var mobileNo = document.getElementById("phone").value;

  if(message !== "" || name !== "" || email !== "" ||mobileNo !== ""){
    alert('Message Sent Succesfully! Please wait for our response.');
  }
}

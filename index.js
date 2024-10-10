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
// script.js — UI interactions only
// All auth, data, and storage is now handled server-side by PHP + MySQL

function toggleMenu() {
  const nav = document.getElementById("mainNav");
  if (nav) nav.classList.toggle("show");
}

// Close menu when a nav link is tapped (mobile)
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('#mainNav a').forEach(link => {
    link.addEventListener('click', () => {
      document.getElementById('mainNav')?.classList.remove('show');
    });
  });
});

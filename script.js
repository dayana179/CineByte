// =====================
// MENU
// =====================
function toggleMenu() {
  const nav = document.getElementById("mainNav");
  if (nav) nav.classList.toggle("show");
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('#mainNav a').forEach(link => {
    link.addEventListener('click', () => {
      document.getElementById('mainNav')?.classList.remove('show');
    });
  });
});

// =====================
// TMDB API SETUP
// =====================
const API_KEY = "ff3e44517aba3b3a85c940344177b06d";
const BASE_URL = "https://api.themoviedb.org/3";
const IMG_URL = "https://image.tmdb.org/t/p/w500";

// =====================
// AUTH NAV
// =====================
function getCurrentUser() {
  return JSON.parse(localStorage.getItem("cinebyteCurrentUser"));
}

function updateAuthNav() {
  const authNav = document.getElementById("authNav");
  const user = getCurrentUser();

  if (!authNav) return;

  if (user) {
    authNav.textContent = "Logout";
    authNav.href = "#";
    authNav.onclick = () => {
      localStorage.removeItem("cinebyteCurrentUser");
      location.reload();
    };
  } else {
    authNav.textContent = "Login";
    authNav.href = "auth.html";
  }
}

// =====================
// LOAD MOVIES (INDEX + FILMS)
// =====================
async function loadMovies() {
  const containers = document.querySelectorAll(".movie-container");
  if (!containers.length) return;

  try {
    const res = await fetch(
      `${BASE_URL}/movie/popular?api_key=${API_KEY}`
    );

    const data = await res.json();

    containers.forEach(container => {
      container.innerHTML = "";

      data.results.forEach(movie => {
        const card = document.createElement("div");
        card.classList.add("movie-card");

        card.innerHTML = `
          <a href="film-detail.html?id=${movie.id}">
            <img src="${IMG_URL + movie.poster_path}" alt="${movie.title}">
            <h3>${movie.title}</h3>
            <p>⭐ ${movie.vote_average}</p>
          </a>
        `;

        container.appendChild(card);
      });
    });

  } catch (err) {
    console.error("Error loading movies:", err);
  }
}

// =====================
// MOVIE DETAIL PAGE (API)
// =====================
async function loadMovieDetails() {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  if (!id) return;

  try {
    const res = await fetch(
      `${BASE_URL}/movie/${id}?api_key=${API_KEY}`
    );

    const movie = await res.json();

    if (document.getElementById("detailTitle")) {
      document.getElementById("detailTitle").textContent = movie.title;
      document.getElementById("detailGenre").textContent =
        movie.genres.map(g => g.name).join(", ");
      document.getElementById("detailPoster").src =
        IMG_URL + movie.poster_path;
      document.getElementById("detailDesc").textContent =
        movie.overview;
    }

  } catch (err) {
    console.error("Error loading movie details:", err);
  }
}

// =====================
// INIT
// =====================
updateAuthNav();
loadMovies();
loadMovieDetails();
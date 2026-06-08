// =====================
// MENU
// =====================
function toggleMenu() {
  const nav = document.getElementById("mainNav");
  if (nav) nav.classList.toggle("show");
}

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("#mainNav a").forEach((link) => {
    link.addEventListener("click", () => {
      document.getElementById("mainNav")?.classList.remove("show");
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
// MOVIE HELPERS
// =====================
function getPosterPath(movie) {
  if (!movie.poster_path) {
    return "https://via.placeholder.com/500x750?text=No+Poster";
  }

  return IMG_URL + movie.poster_path;
}

function getMovieYear(movie) {
  if (!movie.release_date) {
    return "Unknown";
  }

  return movie.release_date.split("-")[0];
}

function createMovieCard(movie) {
  const card = document.createElement("div");
  card.classList.add("movie-card");

  const rating = movie.vote_average ? movie.vote_average.toFixed(1) : "N/A";

  card.innerHTML = `
    <a href="film-detail.html?id=${movie.id}">
      <img src="${getPosterPath(movie)}" alt="${movie.title}">
      <h3>${movie.title}</h3>
      <p>${getMovieYear(movie)} • ⭐ ${rating}</p>
    </a>
  `;

  return card;
}

function createPosterCard(movie) {
  const card = document.createElement("div");
  card.classList.add("poster-card");

  const rating = movie.vote_average ? movie.vote_average.toFixed(1) : "N/A";

  card.innerHTML = `
    <a href="film-detail.html?id=${movie.id}">
      <img src="${getPosterPath(movie)}" alt="${movie.title}">
      <h3>${movie.title}</h3>
      <p>${getMovieYear(movie)} • ⭐ ${rating}</p>
    </a>
  `;

  return card;
}

// =====================
// LOAD MOVIES FOR INDEX
// =====================
async function loadMovies() {
  const containers = document.querySelectorAll(".movie-container");

  if (!containers.length) return;

  try {
    const res = await fetch(`${BASE_URL}/movie/popular?api_key=${API_KEY}&page=1`);
    const data = await res.json();

    containers.forEach((container) => {
      container.innerHTML = "";

      data.results.forEach((movie) => {
        container.appendChild(createMovieCard(movie));
      });
    });
  } catch (err) {
    console.error("Error loading movies:", err);
  }
}

// =====================
// HEADER SEARCH
// =====================
let headerSearchTimer = null;

function setupHeaderSearch() {
  const toggleBtn = document.getElementById("headerSearchToggle");
  const searchBox = document.getElementById("headerSearchBox");
  const searchInput = document.getElementById("headerSearchInput");
  const resultsBox = document.getElementById("headerSearchResults");

  if (!toggleBtn || !searchBox || !searchInput || !resultsBox) return;

  toggleBtn.addEventListener("click", function (event) {
    event.stopPropagation();
    searchBox.classList.toggle("show");

    if (searchBox.classList.contains("show")) {
      searchInput.focus();
    }
  });

  searchBox.addEventListener("click", function (event) {
    event.stopPropagation();
  });

  document.addEventListener("click", function () {
    searchBox.classList.remove("show");
  });

  searchInput.addEventListener("input", function () {
    const query = searchInput.value.trim();

    clearTimeout(headerSearchTimer);

    if (query.length < 2) {
      resultsBox.innerHTML = `<p class="header-search-empty">Type at least 2 letters.</p>`;
      return;
    }

    resultsBox.innerHTML = `<p class="header-search-empty">Searching...</p>`;

    headerSearchTimer = setTimeout(() => {
      searchHeaderMovies(query);
    }, 350);
  });

  searchInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
      event.preventDefault();

      const query = searchInput.value.trim();

      if (query.length > 0) {
        window.location.href = `films.html?search=${encodeURIComponent(query)}`;
      }
    }
  });
}

async function searchHeaderMovies(query) {
  const resultsBox = document.getElementById("headerSearchResults");

  if (!resultsBox) return;

  try {
    const res = await fetch(
      `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(
        query
      )}&include_adult=false&page=1`
    );

    const data = await res.json();
    const results = data.results ? data.results.slice(0, 6) : [];

    if (!results.length) {
      resultsBox.innerHTML = `<p class="header-search-empty">No films found.</p>`;
      return;
    }

    resultsBox.innerHTML = "";

    results.forEach((movie) => {
      const item = document.createElement("a");
      item.classList.add("header-result-item");
      item.href = `film-detail.html?id=${movie.id}`;

      item.innerHTML = `
        <img src="${getPosterPath(movie)}" alt="${movie.title}">
        <div class="header-result-info">
          <h4>${movie.title}</h4>
          <p>${getMovieYear(movie)}</p>
        </div>
      `;

      resultsBox.appendChild(item);
    });

    const viewAll = document.createElement("a");
    viewAll.classList.add("header-result-item");
    viewAll.href = `films.html?search=${encodeURIComponent(query)}`;
    viewAll.innerHTML = `
      <div class="header-result-info">
        <h4>View all results for "${query}"</h4>
        <p>Open full film search</p>
      </div>
    `;

    resultsBox.appendChild(viewAll);
  } catch (err) {
    console.error("Header search error:", err);
    resultsBox.innerHTML = `<p class="header-search-empty">Search failed. Try again.</p>`;
  }
}

// =====================
// FILMS PAGE: LETTERBOXD-LIKE BROWSER
// =====================
let filmPage = 1;
let filmTotalPages = 1;
let filmQuery = "";
let filmSearchMode = false;

async function loadGenres() {
  const genreSelect = document.getElementById("filmGenre");

  if (!genreSelect) return;

  try {
    const res = await fetch(`${BASE_URL}/genre/movie/list?api_key=${API_KEY}`);
    const data = await res.json();

    data.genres.forEach((genre) => {
      const option = document.createElement("option");
      option.value = genre.id;
      option.textContent = genre.name;
      genreSelect.appendChild(option);
    });
  } catch (err) {
    console.error("Error loading genres:", err);
  }
}

function getFilmsUrl() {
  const sort = document.getElementById("filmSort")?.value || "popularity.desc";
  const genre = document.getElementById("filmGenre")?.value || "";
  const year = document.getElementById("filmYear")?.value || "";

  if (filmSearchMode) {
    let searchUrl = `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(
      filmQuery
    )}&include_adult=false&page=${filmPage}`;

    if (year) {
      searchUrl += `&primary_release_year=${year}`;
    }

    return searchUrl;
  }

  let discoverUrl = `${BASE_URL}/discover/movie?api_key=${API_KEY}&sort_by=${sort}&include_adult=false&page=${filmPage}`;

  if (genre) {
    discoverUrl += `&with_genres=${genre}`;
  }

  if (year) {
    discoverUrl += `&primary_release_year=${year}`;
  }

  return discoverUrl;
}

async function loadFilmsPage(append = false) {
  const filmContainer = document.getElementById("filmContainer");
  const filmStatus = document.getElementById("filmStatus");
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const filmPageTitle = document.getElementById("filmPageTitle");

  if (!filmContainer) return;

  try {
    if (filmStatus) {
      filmStatus.textContent = "Loading films...";
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = true;
      loadMoreBtn.textContent = "Loading...";
    }

    const res = await fetch(getFilmsUrl());
    const data = await res.json();

    filmTotalPages = data.total_pages || 1;

    if (!append) {
      filmContainer.innerHTML = "";
    }

    if (!data.results || data.results.length === 0) {
      filmContainer.innerHTML = `<p class="muted-text">No films found.</p>`;

      if (filmStatus) {
        filmStatus.textContent = "";
      }

      if (loadMoreBtn) {
        loadMoreBtn.style.display = "none";
      }

      return;
    }

    data.results.forEach((movie) => {
      filmContainer.appendChild(createPosterCard(movie));
    });

    if (filmPageTitle) {
      filmPageTitle.textContent = filmSearchMode
        ? `Search: ${filmQuery}`
        : "Popular Films";
    }

    if (filmStatus) {
      filmStatus.textContent = filmSearchMode
        ? `Showing search results for "${filmQuery}"`
        : "Browse films by popularity, rating, year, or genre.";
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More";
      loadMoreBtn.style.display = filmPage >= filmTotalPages ? "none" : "inline-block";
    }
  } catch (err) {
    console.error("Error loading films:", err);

    if (filmStatus) {
      filmStatus.textContent = "Unable to load films. Please try again.";
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More";
    }
  }
}

function setupFilmBrowser() {
  const filmContainer = document.getElementById("filmContainer");
  const searchForm = document.getElementById("filmSearchForm");
  const searchInput = document.getElementById("filmSearchInput");
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const sortSelect = document.getElementById("filmSort");
  const genreSelect = document.getElementById("filmGenre");
  const yearInput = document.getElementById("filmYear");
  const clearBtn = document.getElementById("clearFilmFilters");

  if (!filmContainer) return;

  const params = new URLSearchParams(window.location.search);
  const searchFromHeader = params.get("search");

  if (searchFromHeader && searchInput) {
    filmQuery = searchFromHeader.trim();
    filmSearchMode = true;
    searchInput.value = filmQuery;
  }

  loadGenres();
  loadFilmsPage(false);

  if (searchForm && searchInput) {
    searchForm.addEventListener("submit", function (event) {
      event.preventDefault();

      filmQuery = searchInput.value.trim();
      filmSearchMode = filmQuery.length > 0;
      filmPage = 1;

      loadFilmsPage(false);
    });
  }

  [sortSelect, genreSelect].forEach((input) => {
    if (!input) return;

    input.addEventListener("change", function () {
      filmPage = 1;

      if (searchInput && searchInput.value.trim().length > 0) {
        filmQuery = searchInput.value.trim();
        filmSearchMode = true;
      } else {
        filmQuery = "";
        filmSearchMode = false;
      }

      loadFilmsPage(false);
    });
  });

  if (yearInput) {
    yearInput.addEventListener("change", function () {
      filmPage = 1;

      if (searchInput && searchInput.value.trim().length > 0) {
        filmQuery = searchInput.value.trim();
        filmSearchMode = true;
      } else {
        filmQuery = "";
        filmSearchMode = false;
      }

      loadFilmsPage(false);
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener("click", function () {
      if (searchInput) searchInput.value = "";
      if (sortSelect) sortSelect.value = "popularity.desc";
      if (genreSelect) genreSelect.value = "";
      if (yearInput) yearInput.value = "";

      filmQuery = "";
      filmSearchMode = false;
      filmPage = 1;

      loadFilmsPage(false);
    });
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", function () {
      filmPage++;
      loadFilmsPage(true);
    });
  }
}

// =====================
// MOVIE DETAIL PAGE API
// =====================
async function loadMovieDetails() {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  if (!id) return;

  try {
    const res = await fetch(`${BASE_URL}/movie/${id}?api_key=${API_KEY}`);
    const movie = await res.json();

    if (document.getElementById("detailTitle")) {
      document.getElementById("detailTitle").textContent = movie.title;

      document.getElementById("detailGenre").textContent = movie.genres
        ? movie.genres.map((g) => g.name).join(", ")
        : "No genre available";

      document.getElementById("detailPoster").src = getPosterPath(movie);

      document.getElementById("detailDesc").textContent =
        movie.overview || "No description available.";
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
setupHeaderSearch();
setupFilmBrowser();
loadMovieDetails();
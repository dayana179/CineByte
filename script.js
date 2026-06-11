// =====================
// MENU
// =====================
function toggleMenu() {
  const nav = document.getElementById("mainNav");
  const menuBtn = document.querySelector(".menu-toggle");

  if (!nav) return;

  nav.classList.toggle("show");

  if (menuBtn) {
    menuBtn.textContent = nav.classList.contains("show") ? "✕" : "☰";
  }
}

function setupMobileMenuClose() {
  const nav = document.getElementById("mainNav");
  const menuBtn = document.querySelector(".menu-toggle");

  if (!nav) return;

  document.querySelectorAll("#mainNav a").forEach((link) => {
    link.addEventListener("click", () => {
      nav.classList.remove("show");

      if (menuBtn) {
        menuBtn.textContent = "☰";
      }
    });
  });
}

// =====================
// TMDB API SETUP
// =====================
const API_KEY = "ff3e44517aba3b3a85c940344177b06d";
const BASE_URL = "https://api.themoviedb.org/3";
const IMG_URL = "https://image.tmdb.org/t/p/w500";

// =====================
// AUTH NAV WITH PHP SESSION
// =====================
async function updateAuthNav() {
  const authNav = document.getElementById("authNav");
  if (!authNav) return;

  try {
    const res = await fetch("auth_status.php", {
      cache: "no-store"
    });

    const data = await res.json();

    if (data.loggedIn) {
      authNav.textContent = "+ Journal";
      authNav.href = "#";
      authNav.classList.add("journal-open-btn");

      authNav.onclick = function (event) {
        event.preventDefault();
        openJournalModal();
      };
    } else {
      authNav.textContent = "Login";
      authNav.href = "login.html";
      authNav.classList.remove("journal-open-btn");
      authNav.onclick = null;
    }
  } catch (err) {
    console.error("Auth status error:", err);
    authNav.textContent = "Login";
    authNav.href = "login.html";
    authNav.classList.remove("journal-open-btn");
    authNav.onclick = null;
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

function openJournalModal() {
  const modal = document.getElementById("journalSearchModal");
  const input = document.getElementById("journalSearchInput");

  if (!modal) return;

  modal.classList.add("show");

  setTimeout(() => {
    if (input) input.focus();
  }, 100);
}

function closeJournalModal() {
  const modal = document.getElementById("journalSearchModal");
  const input = document.getElementById("journalSearchInput");
  const results = document.getElementById("journalSearchResults");

  if (!modal) return;

  modal.classList.remove("show");

  if (input) input.value = "";
  if (results) results.innerHTML = "";
}

let journalSearchTimer = null;

function setupJournalSearchModal() {
  const modal = document.getElementById("journalSearchModal");
  const closeBtn = document.getElementById("journalModalClose");
  const input = document.getElementById("journalSearchInput");
  const results = document.getElementById("journalSearchResults");

  if (!modal || !closeBtn || !input || !results) return;

  closeBtn.addEventListener("click", closeJournalModal);

  modal.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeJournalModal();
    }
  });

  input.addEventListener("input", function () {
    const query = input.value.trim();

    clearTimeout(journalSearchTimer);

    if (query.length < 2) {
      results.innerHTML = `<p class="journal-modal-empty">Type at least 2 letters.</p>`;
      return;
    }

    results.innerHTML = `<p class="journal-modal-empty">Searching...</p>`;

    journalSearchTimer = setTimeout(() => {
      searchJournalMovies(query);
    }, 350);
  });
}

async function searchJournalMovies(query) {
  const results = document.getElementById("journalSearchResults");
  if (!results) return;

  try {
    const res = await fetch(
      `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(query)}&include_adult=false&page=1`
    );

    const data = await res.json();
    const movies = data.results ? data.results.slice(0, 6) : [];

    if (!movies.length) {
      results.innerHTML = `<p class="journal-modal-empty">No films found.</p>`;
      return;
    }

    results.innerHTML = "";

    movies.forEach((movie) => {
      const item = document.createElement("a");
      item.classList.add("journal-modal-result");
      item.href =
        `journal.php?tmdb_id=${movie.id}` +
        `&title=${encodeURIComponent(movie.title)}` +
        `&poster=${encodeURIComponent(movie.poster_path || "")}`;

      item.innerHTML = `
        <img src="${getPosterPath(movie)}" alt="${movie.title}">
        <div>
          <h4>${movie.title}</h4>
          <p>${getMovieYear(movie)}</p>
        </div>
      `;

      results.appendChild(item);
    });
  } catch (err) {
    console.error("Journal search error:", err);
    results.innerHTML = `<p class="journal-modal-empty">Search failed. Try again.</p>`;
  }
}

// =====================
// FILMS PAGE
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
// WATCHLIST
// =====================
let currentDetailMovie = null;

async function addToWatchlist() {
  await fetchCurrentSessionUser();

  if (!getLoggedInUserId()) {
    alert("Please login first before adding to watchlist.");
    window.location.href = "login.html";
    return;
  }

  if (!currentDetailMovie) {
    alert("Movie details are still loading. Please try again.");
    return;
  }

  try {
    const res = await fetch("api/watchlist.php?action=add", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        tmdb_id: currentDetailMovie.id,
        title: currentDetailMovie.title,
        poster_path: currentDetailMovie.poster_path
      })
    });

    const data = await res.json();

    alert(data.message || "Watchlist updated.");
  } catch (err) {
    console.error("Add to watchlist error:", err);
    alert("Unable to add movie to watchlist.");
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
    currentDetailMovie = movie;

    if (document.getElementById("detailTitle")) {
      document.getElementById("detailTitle").textContent = movie.title;

      document.getElementById("detailGenre").textContent = movie.genres
        ? movie.genres.map((g) => g.name).join(", ")
        : "No genre available";

      document.getElementById("detailPoster").src = getPosterPath(movie);

      document.getElementById("detailDesc").textContent =
        movie.overview || "No description available.";

        const journalBtn = document.getElementById("journalBtn");

        if (journalBtn) {
          journalBtn.href =
            `journal.php?tmdb_id=${movie.id}&title=${encodeURIComponent(movie.title)}&poster=${encodeURIComponent(movie.poster_path || "")}`;
        }
    }
  } catch (err) {
    console.error("Error loading movie details:", err);
  }
}

// =====================
// LISTS PAGE: CATEGORIES + DATABASE USER LISTS
// =====================
let listCategoryPage = 1;
let currentCategoryType = "popular";
let currentGenreId = "";
let currentCategoryName = "Popular Movies";
let listCategoryTotalPages = 1;
let databaseUserLists = [];

let currentSessionUser = null;

async function fetchCurrentSessionUser() {
  try {
    const res = await fetch("auth_status.php", {
      cache: "no-store",
      credentials: "same-origin"
    });

    const data = await res.json();

    if (data.loggedIn) {
      currentSessionUser = data;
    } else {
      currentSessionUser = null;
    }
  } catch (err) {
    console.error("Session user error:", err);
    currentSessionUser = null;
  }
}

function getLoggedInUserId() {
  return currentSessionUser && currentSessionUser.user_id
    ? currentSessionUser.user_id
    : null;
}

function getCategoryUrl() {
  if (currentGenreId) {
    return `${BASE_URL}/discover/movie?api_key=${API_KEY}&with_genres=${currentGenreId}&sort_by=popularity.desc&page=${listCategoryPage}`;
  }

  return `${BASE_URL}/movie/${currentCategoryType}?api_key=${API_KEY}&page=${listCategoryPage}`;
}

async function fetchUserListsFromDatabase() {
  if (!getLoggedInUserId()) {
    databaseUserLists = [];
    renderUserLists();
    return;
  }

  try {
    const res = await fetch("api/lists.php?action=get", {
      cache: "no-store",
      credentials: "same-origin"
    });

    const data = await res.json();

    if (data.success) {
      databaseUserLists = data.lists;
    } else {
      databaseUserLists = [];
    }

    renderUserLists();
  } catch (err) {
    console.error("Error fetching user lists:", err);
    databaseUserLists = [];
    renderUserLists();
  }
}

async function loadCategoryMovies(append = false) {
  const grid = document.getElementById("categoryMovieGrid");
  const title = document.getElementById("categoryTitle");
  const status = document.getElementById("categoryStatus");
  const loadMoreBtn = document.getElementById("loadMoreCategoryBtn");

  if (!grid) return;

  try {
    if (title) {
      title.textContent = currentCategoryName;
    }

    if (status) {
      status.textContent = "Loading movies...";
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = true;
      loadMoreBtn.textContent = "Loading...";
    }

    const res = await fetch(getCategoryUrl());
    const data = await res.json();

    listCategoryTotalPages = data.total_pages || 1;

    if (!append) {
      grid.innerHTML = "";
    }

    if (!data.results || data.results.length === 0) {
      grid.innerHTML = `<p class="muted-text">No movies found for this category.</p>`;

      if (status) status.textContent = "";
      if (loadMoreBtn) loadMoreBtn.style.display = "none";

      return;
    }

    data.results.forEach((movie) => {
      grid.appendChild(createListMovieCard(movie));
    });

    if (status) {
      status.textContent = `Showing ${currentCategoryName.toLowerCase()}`;
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More";
      loadMoreBtn.style.display =
        listCategoryPage >= listCategoryTotalPages ? "none" : "inline-block";
    }
  } catch (err) {
    console.error("Error loading category movies:", err);

    if (status) {
      status.textContent = "Unable to load movies. Please try again.";
    }

    if (loadMoreBtn) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More";
    }
  }
}

function createListMovieCard(movie) {
  const card = document.createElement("div");
  card.classList.add("poster-card");

  const userId = getLoggedInUserId();
  const rating = movie.vote_average ? movie.vote_average.toFixed(1) : "N/A";

  let listOptions = `<option value="">Add to list...</option>`;

  databaseUserLists.forEach((list) => {
    listOptions += `<option value="${list.id}">${list.list_name}</option>`;
  });

  let addToListHTML = "";

  if (!userId) {
    addToListHTML = `
      <p class="login-required-note">
        Login to add this movie to a list.
      </p>
    `;
  } else if (databaseUserLists.length > 0) {
    addToListHTML = `
      <div class="add-to-list-area">
        <select class="movie-list-select">
          ${listOptions}
        </select>
        <button class="btn add-to-list-btn" type="button">Add</button>
      </div>
    `;
  } else {
    addToListHTML = `
      <p class="login-required-note">
        Create a list first.
      </p>
    `;
  }

  card.innerHTML = `
    <a href="film-detail.html?id=${movie.id}">
      <img src="${getPosterPath(movie)}" alt="${movie.title}">
      <h3>${movie.title}</h3>
      <p>${getMovieYear(movie)} • ⭐ ${rating}</p>
    </a>

    ${addToListHTML}
  `;

  const select = card.querySelector(".movie-list-select");
  const addBtn = card.querySelector(".add-to-list-btn");

  if (select && addBtn) {
    addBtn.addEventListener("click", function () {
      const selectedListId = select.value;

      if (!selectedListId) {
        alert("Please choose a list first.");
        return;
      }

      addMovieToDatabaseList(selectedListId, movie);
      select.value = "";
    });
  }

  return card;
}

async function createDatabaseList(listName) {
  await fetchCurrentSessionUser();

  if (!getLoggedInUserId()) {
    alert("Please login first before creating a list.");
    window.location.href = "login.html";
    return;
  }

  try {
    const res = await fetch("api/lists.php?action=create", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        list_name: listName
      })
    });

    const text = await res.text();
    console.log("Create list raw response:", text);

    let data;

    try {
      data = JSON.parse(text);
    } catch (parseError) {
      alert("PHP returned invalid JSON. Check Console for the raw response.");
      console.error("Invalid JSON from api/lists.php:", text);
      return;
    }

    if (data.success) {
      await fetchUserListsFromDatabase();
      await loadOverviewCategoryMovies();
    } else {
      alert(data.message || "Unable to create list.");
      console.error("Create list failed:", data);
    }
  } catch (err) {
    console.error("Error creating list:", err);
    alert("Unable to create list. Check Console for details.");
  }
}

async function addMovieToDatabaseList(listId, movie) {
  try {
    const res = await fetch("api/lists.php?action=add_movie", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        list_id: listId,
        tmdb_id: movie.id,
        title: movie.title,
        poster_path: movie.poster_path,
        release_date: movie.release_date,
        vote_average: movie.vote_average
      })
    });

    const data = await res.json();

    if (data.success) {
      await fetchUserListsFromDatabase();
      alert(`"${movie.title}" added to list.`);
    } else {
      alert(data.message || "Unable to add movie.");
    }
  } catch (err) {
    console.error("Error adding movie:", err);
    alert("Unable to add movie.");
  }
}

async function deleteDatabaseList(listId) {
  const confirmDelete = confirm("Delete this list?");

  if (!confirmDelete) return;

  try {
    const res = await fetch("api/lists.php?action=delete", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        list_id: listId
      })
    });

    const data = await res.json();

    if (data.success) {
      await fetchUserListsFromDatabase();
      await loadOverviewCategoryMovies();
    } else {
      alert(data.message || "Unable to delete list.");
    }
  } catch (err) {
    console.error("Error deleting list:", err);
    alert("Unable to delete list.");
  }
}

async function removeMovieFromDatabaseList(listId, tmdbId) {
  try {
    const res = await fetch("api/lists.php?action=remove_movie", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        list_id: listId,
        tmdb_id: tmdbId
      })
    });

    const data = await res.json();

    if (data.success) {
      await fetchUserListsFromDatabase();
    } else {
      alert(data.message || "Unable to remove movie.");
    }
  } catch (err) {
    console.error("Error removing movie:", err);
    alert("Unable to remove movie.");
  }
}

function renderUserLists() {
  const grid = document.getElementById("userListsGrid");

  if (!grid) return;

  const userId = getLoggedInUserId();

  if (!userId) {
    grid.innerHTML = `
      <p class="empty-list-message">
        Please login to create and view your movie lists.
      </p>
    `;
    return;
  }

  if (!databaseUserLists.length) {
    grid.innerHTML = `
      <p class="empty-list-message">
        No lists created yet. Create your first list from the form on the left.
      </p>
    `;
    return;
  }

  grid.innerHTML = "";

  databaseUserLists.forEach((list) => {
    const card = document.createElement("div");
    card.classList.add("user-list-card");

    const movieCount = list.movies ? list.movies.length : 0;

    let moviesHTML = "";

    if (movieCount > 0) {
      moviesHTML = `
        <div class="list-movie-preview-grid">
          ${list.movies
            .slice(0, 4)
            .map((movie) => {
              const poster = movie.poster_path
                ? IMG_URL + movie.poster_path
                : "https://via.placeholder.com/500x750?text=No+Poster";

              return `
                <div class="list-movie-preview">
                  <a href="film-detail.html?id=${movie.tmdb_id}">
                    <img src="${poster}" alt="${movie.title}">
                  </a>
                  <button
                    type="button"
                    class="remove-list-movie-btn"
                    data-list-id="${list.id}"
                    data-tmdb-id="${movie.tmdb_id}"
                  >
                    Remove
                  </button>
                </div>
              `;
            })
            .join("")}
        </div>
      `;
    } else {
      moviesHTML = `<p class="muted-text">No movies added yet.</p>`;
    }

    card.innerHTML = `
      <h3>${list.list_name}</h3>
      <div class="user-list-meta">${movieCount} movie${movieCount === 1 ? "" : "s"}</div>

      ${moviesHTML}

      <div class="list-card-actions">
        <a href="list-detail.php?id=${list.id}" class="small-link">View / Edit List</a>
        <button type="button" class="delete-list-btn">Delete List</button>
      </div>
    `;

    const deleteBtn = card.querySelector(".delete-list-btn");

    deleteBtn.addEventListener("click", function () {
      deleteDatabaseList(list.id);
    });

    card.querySelectorAll(".remove-list-movie-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const listId = button.dataset.listId;
        const tmdbId = button.dataset.tmdbId;

        removeMovieFromDatabaseList(listId, tmdbId);
      });
    });

    grid.appendChild(card);
  });
}

function setupCreateListForm() {
  const form = document.getElementById("createListForm");
  const titleInput = document.getElementById("listTitleInput");

  if (!form || !titleInput) return;

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const title = titleInput.value.trim();

    if (!title) return;

    createDatabaseList(title);

    titleInput.value = "";
  });
}

function setupCategoryButtons() {
  const buttons = document.querySelectorAll(".category-btn");
  const loadMoreBtn = document.getElementById("loadMoreCategoryBtn");

  if (!buttons.length) return;

  buttons.forEach((button) => {
    button.addEventListener("click", function () {
      buttons.forEach((btn) => btn.classList.remove("active"));
      button.classList.add("active");

      listCategoryPage = 1;
      currentGenreId = button.dataset.genre || "";
      currentCategoryType = button.dataset.type || "";
      currentCategoryName = button.textContent.trim() + " Movies";

      loadCategoryMovies(false);
    });
  });

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", function () {
      listCategoryPage++;
      loadCategoryMovies(true);
    });
  }
}

async function setupListsPage() {
  const listsPage = document.getElementById("categoryMovieGrid");

  if (!listsPage) return;

  await fetchCurrentSessionUser();

  setupCategoryButtons();
  setupCreateListForm();

  await fetchUserListsFromDatabase();
  await loadCategoryMovies(false);
}

// =====================
// LISTS OVERVIEW PAGE
// =====================
let overviewCategoryType = "popular";
let overviewCategoryName = "Top 20 Popular Movies";

function getOverviewCategoryUrl() {
  return `${BASE_URL}/movie/${overviewCategoryType}?api_key=${API_KEY}&page=1`;
}

async function loadOverviewCategoryMovies() {
  const grid = document.getElementById("categoryMovieGrid");
  const title = document.getElementById("categoryTitle");
  const status = document.getElementById("categoryStatus");

  if (!grid) return;

  try {
    if (title) title.textContent = overviewCategoryName;
    if (status) status.textContent = "Loading movies...";

    const res = await fetch(getOverviewCategoryUrl());
    const data = await res.json();

    grid.innerHTML = "";

    const movies = data.results ? data.results.slice(0, 20) : [];

    movies.forEach((movie) => {
      grid.appendChild(createPosterCard(movie));
    });

    if (status) {
      status.textContent = `Showing ${movies.length} films.`;
    }
  } catch (err) {
    console.error("Overview category error:", err);

    if (status) {
      status.textContent = "Unable to load movies.";
    }
  }
}

function setupListsOverviewPage() {
  const categoryCards = document.querySelectorAll(".list-category-card");

  if (!document.getElementById("categoryMovieGrid")) return;

  categoryCards.forEach((card) => {
    card.addEventListener("click", function () {
      categoryCards.forEach((item) => item.classList.remove("active"));
      card.classList.add("active");

      overviewCategoryType = card.dataset.category || "popular";
      overviewCategoryName = card.querySelector("h2").textContent.trim();

      loadOverviewCategoryMovies();
    });
  });

  loadOverviewCategoryMovies();
  fetchCurrentSessionUser().then(fetchUserListsFromDatabase);
}

// =====================
// CREATE LIST PAGE
// =====================
let selectedListMovies = [];

function renderSelectedMovies() {
  const grid = document.getElementById("selectedMoviesGrid");

  if (!grid) return;

  if (!selectedListMovies.length) {
    grid.innerHTML = `<p class="empty-list-message">No movies added yet.</p>`;
    return;
  }

  grid.innerHTML = "";

  selectedListMovies.forEach((movie) => {
    const item = document.createElement("div");
    item.classList.add("selected-movie-card");

    item.innerHTML = `
      <img src="${getPosterPath(movie)}" alt="${movie.title}">
      <div>
        <h4>${movie.title}</h4>
        <p>${getMovieYear(movie)}</p>
        <button type="button" class="remove-selected-movie-btn">Remove</button>
      </div>
    `;

    item.querySelector(".remove-selected-movie-btn").addEventListener("click", function () {
      selectedListMovies = selectedListMovies.filter((m) => m.id !== movie.id);
      renderSelectedMovies();
    });

    grid.appendChild(item);
  });
}

function setupCreateListPage() {
  const form = document.getElementById("newListForm");
  const titleInput = document.getElementById("newListTitle");
  const searchForm = document.getElementById("newListMovieSearchForm");
  const searchInput = document.getElementById("newListMovieSearchInput");
  const results = document.getElementById("newListSearchResults");
  const status = document.getElementById("newListSearchStatus");

  if (!form || !titleInput || !searchForm || !searchInput || !results) return;

  searchForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const query = searchInput.value.trim();

    if (!query) return;

    try {
      if (status) status.textContent = "Searching...";

      const res = await fetch(
        `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(query)}&include_adult=false&page=1`
      );

      const data = await res.json();
      const movies = data.results ? data.results.slice(0, 8) : [];

      results.innerHTML = "";

      if (!movies.length) {
        results.innerHTML = `<p class="muted-text">No movies found.</p>`;
        if (status) status.textContent = "";
        return;
      }

      movies.forEach((movie) => {
        const item = document.createElement("div");
        item.classList.add("list-search-result-card");

        item.innerHTML = `
          <img src="${getPosterPath(movie)}" alt="${movie.title}">
          <div>
            <h3>${movie.title}</h3>
            <p>${getMovieYear(movie)}</p>
            <button type="button" class="btn small-btn">Add to List</button>
          </div>
        `;

        item.querySelector("button").addEventListener("click", function () {
          const alreadyAdded = selectedListMovies.some((m) => m.id === movie.id);

          if (alreadyAdded) {
            alert("This movie is already in your list.");
            return;
          }

          selectedListMovies.push(movie);
          renderSelectedMovies();
        });

        results.appendChild(item);
      });

      if (status) status.textContent = `Showing results for "${query}".`;
    } catch (err) {
      console.error("Create list search error:", err);
      if (status) status.textContent = "Unable to search movies.";
    }
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const listName = titleInput.value.trim();

    if (!listName) {
      alert("Please enter a list name.");
      return;
    }

    if (!selectedListMovies.length) {
      alert("Please add at least one movie.");
      return;
    }

    try {
      const res = await fetch("api/lists.php?action=create_with_movies", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          list_name: listName,
          movies: selectedListMovies
        })
      });

      const text = await res.text();
      console.log("Create with movies response:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (err) {
        alert("PHP returned invalid JSON. Check Console.");
        console.error(text);
        return;
      }

      if (data.success) {
        alert("List created successfully.");
        window.location.href = "lists.html";
      } else {
        alert(data.message || "Unable to create list.");
      }
    } catch (err) {
      console.error("Save list error:", err);
      alert("Unable to save list.");
    }
  });

  renderSelectedMovies();
}

// =====================
// LIST DETAIL / EDIT PAGE
// =====================
function setupListDetailPage() {
  const listIdInput = document.getElementById("editListId");
  const nameForm = document.getElementById("editListNameForm");
  const nameInput = document.getElementById("editListNameInput");
  const searchForm = document.getElementById("editListMovieSearchForm");
  const searchInput = document.getElementById("editListMovieSearchInput");
  const results = document.getElementById("editListSearchResults");
  const status = document.getElementById("editListSearchStatus");

  if (!listIdInput) return;

  const listId = listIdInput.value;

  if (nameForm && nameInput) {
    nameForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const listName = nameInput.value.trim();

      if (!listName) {
        alert("Please enter a list name.");
        return;
      }

      try {
        const res = await fetch("api/lists.php?action=rename", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            list_id: listId,
            list_name: listName
          })
        });

        const data = await res.json();

        alert(data.message || "List updated.");

        if (data.success) {
          location.reload();
        }
      } catch (err) {
        console.error("Rename list error:", err);
        alert("Unable to rename list.");
      }
    });
  }

  if (searchForm && searchInput && results) {
    searchForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const query = searchInput.value.trim();

      if (!query) return;

      try {
        if (status) status.textContent = "Searching...";

        const res = await fetch(
          `${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(query)}&include_adult=false&page=1`
        );

        const data = await res.json();
        const movies = data.results ? data.results.slice(0, 8) : [];

        results.innerHTML = "";

        if (!movies.length) {
          results.innerHTML = `<p class="muted-text">No movies found.</p>`;
          if (status) status.textContent = "";
          return;
        }

        movies.forEach((movie) => {
          const item = document.createElement("div");
          item.classList.add("list-search-result-card");

          item.innerHTML = `
            <img src="${getPosterPath(movie)}" alt="${movie.title}">
            <div>
              <h3>${movie.title}</h3>
              <p>${getMovieYear(movie)}</p>
              <button type="button" class="btn small-btn">Add to List</button>
            </div>
          `;

          item.querySelector("button").addEventListener("click", async function () {
            try {
              const res = await fetch("api/lists.php?action=add_movie_to_owned_list", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({
                  list_id: listId,
                  tmdb_id: movie.id,
                  title: movie.title,
                  poster_path: movie.poster_path,
                  release_date: movie.release_date,
                  vote_average: movie.vote_average
                })
              });

              const data = await res.json();

              alert(data.message || "Movie added.");

              if (data.success) {
                location.reload();
              }
            } catch (err) {
              console.error("Add movie to list error:", err);
              alert("Unable to add movie.");
            }
          });

          results.appendChild(item);
        });

        if (status) {
          status.textContent = `Showing results for "${query}".`;
        }
      } catch (err) {
        console.error("List detail search error:", err);

        if (status) {
          status.textContent = "Unable to search movies.";
        }
      }
    });
  }

  document.querySelectorAll(".remove-detail-movie-btn").forEach((button) => {
    button.addEventListener("click", async function () {
      const confirmRemove = confirm("Remove this movie from the list?");

      if (!confirmRemove) return;

      try {
        const res = await fetch("api/lists.php?action=remove_movie", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            list_id: button.dataset.listId,
            tmdb_id: button.dataset.tmdbId
          })
        });

        const data = await res.json();

        alert(data.message || "Movie removed.");

        if (data.success) {
          location.reload();
        }
      } catch (err) {
        console.error("Remove movie error:", err);
        alert("Unable to remove movie.");
      }
    });
  });
}

// =====================
// PROFILE WATCHLIST REMOVE
// =====================
function setupProfileWatchlistRemove() {
  const buttons = document.querySelectorAll(".remove-watchlist-btn");

  if (!buttons.length) return;

  buttons.forEach((button) => {
    button.addEventListener("click", async function () {
      const confirmRemove = confirm("Remove this movie from your watchlist?");

      if (!confirmRemove) return;

      try {
        const res = await fetch("api/watchlist.php?action=remove", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            tmdb_id: button.dataset.tmdbId
          })
        });

        const data = await res.json();

        if (data.success) {
          button.closest(".profile-watchlist-card").remove();
        } else {
          alert(data.message || "Unable to remove movie.");
        }
      } catch (err) {
        console.error("Remove watchlist error:", err);
        alert("Unable to remove movie from watchlist.");
      }
    });
  });
}

document.addEventListener("click", function (event) {
  const nav = document.getElementById("mainNav");
  const menuBtn = document.querySelector(".menu-toggle");

  if (!nav || !menuBtn) return;

  const clickedInsideNav = nav.contains(event.target);
  const clickedMenuButton = menuBtn.contains(event.target);

  if (!clickedInsideNav && !clickedMenuButton && nav.classList.contains("show")) {
    nav.classList.remove("show");
    menuBtn.textContent = "☰";
  }
});

// =====================
// INIT
// =====================
document.addEventListener("DOMContentLoaded", function () {
  setupMobileMenuClose();
  updateAuthNav();
  loadMovies();
  setupHeaderSearch();
  setupFilmBrowser();
  loadMovieDetails();
  setupJournalSearchModal();
  setupListsOverviewPage();
  setupCreateListPage();
  setupListDetailPage();
  setupProfileWatchlistRemove();
});
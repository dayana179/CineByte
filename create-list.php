<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>CineByte | Create List</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <header class="site-header">
    <a href="index.html" class="logo">CineByte</a>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

    <nav id="mainNav">
      <a href="index.html">Home</a>
      <a href="films.html">Films</a>
      <a href="lists.html">Lists</a>
      <a href="profile.php">Profile</a>

      <div class="header-search">
        <button type="button" id="headerSearchToggle" class="header-search-btn" aria-label="Open search">
          <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M10.8 5.2a5.6 5.6 0 1 1 0 11.2a5.6 5.6 0 0 1 0-11.2Zm0-1.7a7.3 7.3 0 1 0 4.55 13.02l3.56 3.56a.9.9 0 0 0 1.27-1.27l-3.56-3.56A7.3 7.3 0 0 0 10.8 3.5Z"
            />
          </svg>
        </button>

        <div id="headerSearchBox" class="header-search-box">
          <input
            type="text"
            id="headerSearchInput"
            placeholder="Search films..."
            autocomplete="off"
          />
          <div id="headerSearchResults" class="header-search-results"></div>
        </div>
      </div>

      <a href="login.html" id="authNav">Login</a>
    </nav>
  </header>

  <main>
    <section class="page-header">
      <p class="tagline">New List</p>
      <h1>Create a Movie List</h1>
      <p>
        Give your list a title, search films, and add them before saving.
      </p>
    </section>

    <section class="content-section create-list-layout">
      <section class="box create-list-editor">
        <h2>List Details</h2>

        <form id="newListForm">
          <label for="newListTitle">List name</label>
          <input
            type="text"
            id="newListTitle"
            placeholder="e.g. My Top 10 Movies"
            required
          />

          <button type="submit" class="btn">Save List</button>
        </form>

        <div class="selected-movies-section">
          <h3>Movies in this list</h3>
          <p class="muted-text">Add movies from the search results.</p>

          <div id="selectedMoviesGrid" class="selected-movies-grid">
            <p class="empty-list-message">No movies added yet.</p>
          </div>
        </div>
      </section>

      <section class="box movie-search-panel">
        <h2>Add Movies</h2>

        <form id="newListMovieSearchForm" class="film-main-search">
          <input
            type="text"
            id="newListMovieSearchInput"
            placeholder="Search for a movie..."
            autocomplete="off"
          />

          <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <p id="newListSearchStatus" class="muted-text"></p>

        <div id="newListSearchResults" class="list-search-results">
          <!-- Search results appear here -->
        </div>
      </section>
    </section>
  </main>

  <footer>
    <p>&copy; 2026 CineByte. Web Design Project.</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
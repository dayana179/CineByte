<?php
require_once 'includes/init.php';

requireLogin();

$pageTitle = 'Create List';
?>

<?php include 'includes/header.php'; ?>

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
          autocomplete="off"
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

<?php include 'includes/footer.php'; ?>
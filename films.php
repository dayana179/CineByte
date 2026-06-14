<?php
require_once 'includes/init.php';

$pageTitle = 'Films';
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header films-page-header">
    <p class="tagline">CineByte Films</p>
    <h1>Browse Films</h1>
    <p>
      Search, filter, and discover films from the TMDB movie database.
    </p>
  </section>

  <section class="content-section film-browser-section">
    <div class="film-browser-top">
      <div>
        <h2 id="filmPageTitle">Popular Films</h2>
        <p id="filmStatus" class="muted-text">Loading films...</p>
      </div>

      <form id="filmSearchForm" class="film-main-search">
        <input
          type="text"
          id="filmSearchInput"
          placeholder="Search by film title..."
          autocomplete="off"
        />
        <button type="submit" class="btn">Search</button>
      </form>
    </div>

    <div class="film-toolbar">
      <div class="filter-group">
        <label for="filmSort">Sort by</label>
        <select id="filmSort">
          <option value="popularity.desc">Popularity</option>
          <option value="vote_average.desc">Highest Rated</option>
          <option value="primary_release_date.desc">Newest First</option>
          <option value="primary_release_date.asc">Oldest First</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="filmGenre">Genre</label>
        <select id="filmGenre">
          <option value="">All Genres</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="filmYear">Year</label>
        <input
          type="number"
          id="filmYear"
          placeholder="e.g. 2024"
          min="1900"
          max="2030"
        />
      </div>

      <button type="button" id="clearFilmFilters" class="btn btn-secondary">
        Clear
      </button>
    </div>

    <div id="filmContainer" class="poster-grid">
      <!-- Film cards load here from script.js -->
    </div>

    <div class="load-more-wrap">
      <button id="loadMoreBtn" class="btn btn-secondary" type="button">
        Load More
      </button>
    </div>
  </section>
</main>


<?php include 'includes/footer.php'; ?>
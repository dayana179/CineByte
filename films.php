<?php
require_once 'includes/init.php';

$pageTitle = 'Films';
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="style.css" />

<main>
  <section class="page-header films-page-header">
    <h1>Films</h1>
    <p>Browse popular movies, search for films, and filter by genre, year, or rating.</p>
  </section>

  <section class="content-section film-browser-section">
    <div class="film-browser-top">
      <div>
        <h2 id="filmPageTitle">Popular Films</h2>
        <p id="filmStatus" class="muted-text">
          Browse films by popularity, rating, year, or genre.
        </p>
      </div>

      <form id="filmSearchForm" class="film-main-search">
        <input
          type="text"
          id="filmSearchInput"
          placeholder="Search films..."
          autocomplete="off"
        />
        <button type="submit" class="btn">Search</button>
      </form>
    </div>

    <div class="film-toolbar">
      <div class="filter-group">
        <label for="filmSort">Sort by</label>
        <select id="filmSort">
          <option value="popularity.desc">Most Popular</option>
          <option value="vote_average.desc">Highest Rated</option>
          <option value="release_date.desc">Newest</option>
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
        <input type="number" id="filmYear" placeholder="e.g. 2026" />
      </div>

      <button type="button" id="clearFilmFilters" class="btn btn-secondary">
        Clear
      </button>
    </div>

    <div id="filmContainer" class="poster-grid"></div>

    <div class="load-more-wrap">
      <button id="loadMoreBtn" class="btn">Load More</button>
    </div>
  </section>
</main>

<div id="journalSearchModal" class="journal-modal">
  <div class="journal-modal-box">
    <div class="journal-modal-header">
      <h2>Add to your journal...</h2>
      <button id="journalModalClose" class="journal-modal-close" type="button">×</button>
    </div>

    <div class="journal-modal-body">
      <input
        type="text"
        id="journalSearchInput"
        placeholder="Search for film..."
        autocomplete="off"
      />

      <div id="journalSearchResults" class="journal-modal-results"></div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/init.php';

$pageTitle = 'Film Detail';
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="style.css" />

<main>
  <section class="detail-layout">
    <div>
      <img
        id="detailPoster"
        class="detail-poster"
        src=""
        alt="Film poster"
      />
    </div>

    <div class="detail-info">
      <h1 id="detailTitle">Loading...</h1>
      <p id="detailGenre">Loading genre...</p>
      <p id="detailDesc">Loading description...</p>

      <div class="hero-actions">
        <button class="btn" onclick="addToWatchlist()">Add to Watchlist</button>
        <a id="journalBtn" href="#" class="btn btn-secondary">Add Journal</a>
      </div>

      <div class="video-box" id="trailerBox"></div>
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
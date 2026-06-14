<?php
require_once 'includes/init.php';

$pageTitle = 'Lists';
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="style.css" />

<main>
  <section class="page-header lists-page-header">
    <h1>Lists</h1>
    <p>Create and explore movie lists based on popular films, top-rated films, upcoming releases, and your own saved collections.</p>

    <div class="hero-actions">
      <a href="create-list.php" class="btn">Create New List</a>
      <a href="#createdListsSection" class="btn btn-secondary">My Lists</a>
    </div>
  </section>

  <section class="content-section">
    <div class="lists-showcase-header">
      <h2>Explore Lists</h2>
    </div>

    <div class="lists-category-showcase">
      <article class="list-category-card active" data-category="popular">
        <h2>Popular Movies</h2>
        <p>Explore the current popular films from TMDB.</p>
      </article>

      <article class="list-category-card" data-category="top_rated">
        <h2>Featured Films</h2>
        <p>Highly rated films worth exploring.</p>
      </article>

      <article class="list-category-card" data-category="now_playing">
        <h2>Now Playing</h2>
        <p>Films currently active in cinemas.</p>
      </article>

      <article class="list-category-card" data-category="upcoming">
        <h2>Upcoming Releases</h2>
        <p>Films coming soon.</p>
      </article>

      <a href="#createdListsSection" class="list-category-card my-list-card">
        <h2>My List</h2>
        <p>Jump to your created movie lists.</p>
      </a>
    </div>
  </section>

  <section class="content-section">
    <div class="lists-section-header">
      <div>
        <h2 id="categoryTitle">Popular Movies</h2>
        <p id="categoryStatus" class="muted-text">Loading movies...</p>
      </div>
    </div>

    <div id="categoryMovieGrid" class="poster-grid"></div>
  </section>

  <section id="createdListsSection" class="content-section">
    <div class="lists-section-header">
      <div>
        <h2>My Created Lists</h2>
        <p class="muted-text">Lists you created from your CineByte account.</p>
      </div>

      <a href="create-list.php" class="btn">Create List</a>
    </div>

    <div id="userListsGrid" class="user-lists-grid"></div>
  </section>
</main>



<?php include 'includes/footer.php'; ?>
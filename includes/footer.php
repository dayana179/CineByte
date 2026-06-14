<footer>
  <p>&copy; <?= date('Y') ?> CineByte. Web Design Project.</p>
</footer>
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

  <div class="journal-video-link">
    <span>or</span>
    <a href="videos.php">log your YouTube videos</a>
  </div>

  <div id="journalSearchResults" class="journal-modal-results"></div>
</div>
  </div>
</div>

<script src="script.js?v=40"></script>

</body>
</html>

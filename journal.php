<?php
require_once 'includes/init.php';
requireLogin();

$pageTitle = 'Journal';

$db = getDB();
$msg = '';

$tmdb_id = (int)($_GET['tmdb_id'] ?? 0);
$movie = [];

if ($tmdb_id) {
    $movie = tmdbFetch("/movie/$tmdb_id");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $rating = (int)$_POST['rating'];
    $review = $_POST['review'];
    $tmdb_id = (int)$_POST['tmdb_id'];


    $stmt = $db->prepare(
        "INSERT INTO journals 
        (user_id, tmdb_id, title, rating, review)
        VALUES (?, ?, ?, ?, ?)"
    );


    $stmt->execute([
        $_SESSION['user_id'],
        $tmdb_id,
        $title,
        $rating,
        $review
    ]);


    $msg = "Journal added!";
}

?>

<?php include 'includes/header.php'; ?>


<main>

<section class="page-header">
<h1>Journal</h1>
<p>Record what you watched and write your personal viewing notes.</p>
</section>


<section class="content-section journal-layout">


<form method="POST" class="box">

<h2>Add Journal</h2>


<?php if (!empty($movie)): ?>


<img class="journal-poster"
src="<?= TMDB_IMAGE_BASE . $movie['poster_path'] ?>">


<h3><?= e($movie['title']) ?></h3>

<p>
⭐ <?= number_format($movie['vote_average'],1) ?>/10
</p>


<input type="hidden"
name="title"
value="<?= e($movie['title']) ?>">


<input type="hidden"
name="tmdb_id"
value="<?= $tmdb_id ?>">


<?php endif; ?>


<label>Your Rating (/10)</label>

<input 
type="number"
name="rating"
min="1"
max="10"
required>


<label>Review</label>

<textarea 
name="review"
rows="6"
required></textarea>


<button class="btn">
Save Journal
</button>


</form>



<div class="box">

<h2>Added Journal</h2>


<?php

$stmt = $db->prepare(
"SELECT * FROM journals WHERE user_id=? ORDER BY created_at DESC"
);

$stmt->execute([$_SESSION['user_id']]);

$journals = $stmt->fetchAll();


foreach ($journals as $j):

?>


<div class="review-box">

<h3><?= e($j['title']) ?></h3>

<p>⭐ <?= $j['rating'] ?>/10</p>

<p><?= e($j['review']) ?></p>

</div>


<?php endforeach; ?>


</div>


</section>


</main>


<?php include 'includes/footer.php'; ?>
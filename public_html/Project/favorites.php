<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in();

// Query user's favorites
$userFavorites = getUserFavorites(get_user_id());

// Fetch details of favorite songs from the SONGS table
$db = getDB();
$favoriteSongs = [];
foreach ($userFavorites as $songId) {
    $stmt = $db->prepare("SELECT * FROM SONGS WHERE label = ?");
    $stmt->execute([$songId]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($song) {
        $favoriteSongs[] = $song;
    }
}
?>

<div class="container-fluid">
    <h1>My Favorites</h1>
    <div class="row">
        <?php foreach ($favoriteSongs as $song) : ?>
            <div class="col-md-3"> <!-- Adjust the grid size -->
                <div class="card mb-4 shadow-sm">
                    <a href="search.php?song=<?php echo urlencode($song['title'] . ' ' . $song['artist']); ?>">
                        <img src="<?php echo htmlspecialchars($song['image']); ?>" class="card-img-top" style="max-width: 100%; max-height: 150px;" alt="Song Image"> <!-- Adjust the image size -->
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($song['title']); ?></h5>
                            <p class="card-text">Artist: <?php echo htmlspecialchars($song['artist']); ?></p>
                            <!-- Add any other details you want to display -->
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>
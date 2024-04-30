<?php
require(__DIR__ . "/../../partials/nav.php");
require(__DIR__ . "/../../partials/flash.php");
is_logged_in();

$userFavorites = getUserFavorites(get_user_id());
$db = getDB();

$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
    flash("Invalid limit value. Please enter a number between 1 and 100.", "danger");
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

$favoriteSongs = [];
$displayedItemsCount = 0; 
foreach ($userFavorites as $songId) {
    if ($displayedItemsCount >= $limit) {
        break;
    }

    $stmt = $db->prepare("SELECT * FROM SONGS WHERE label = ?");
    $stmt->execute([$songId]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($song) {
        $favoriteSongs[] = $song;
        $displayedItemsCount++; 
    }
}
$totalSongs = count($userFavorites); 

?>

<div class="container-fluid">
    <h1>My Favorites</h1>
    <div class="row">
        <div class="col-md-12">
            <p>Total favorites: <?php echo $totalSongs; ?></p>
            <p><?php echo $displayedItemsCount; ?> items displayed</p>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <form method="GET">
                <label for="limit">Display Limit:</label>
                <input type="number" id="limit" name="limit" value="<?php echo $limit; ?>" min="1" max="100">
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>
        </div>
    </div>

    <div class="row">
        <?php foreach ($favoriteSongs as $song) : ?>
            <div class="col-md-3">
                <div class="card mb-4 shadow-sm">
                    <a href="search.php?song=<?php echo urlencode($song['title'] . ' ' . $song['artist']); ?>">
                        <img src="<?php echo htmlspecialchars($song['image']); ?>" class="card-img-top" style="max-width: 100%; max-height: 150px;" alt="Song Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($song['title']); ?></h5>
                            <p class="card-text">Artist: <?php echo htmlspecialchars($song['artist']); ?></p>
                        </div>
                    </a>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo get_user_id(); ?>">
                        <input type="hidden" name="song_label" value="<?php echo $song['label']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this favorite?')">Remove Favorite</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form method="POST">
                <input type="hidden" name="remove_all" value="true">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove all favorites?')">Remove All Favorites</button>
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_POST['remove_all']) && $_POST['remove_all'] === "true") {
    removeAllFavorites(get_user_id());
    flash("All favorites removed successfully", "success");
    header("Refresh:0");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['song_label'])) {
    $userId = $_POST['user_id'];
    $songId = $_POST['song_label'];
    removeFromFavorites($userId, $songId);
    flash("Favorite removed successfully", "success");
    header("Refresh:0");
}
?>

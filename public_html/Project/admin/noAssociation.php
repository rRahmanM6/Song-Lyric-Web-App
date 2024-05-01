<?php
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../partials/flash.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    header("Location: $BASE_PATH" . "/search.php");
    exit;
}

$db = getDB();

$totalStmt = $db->query("SELECT COUNT(*) FROM SONGS WHERE label NOT IN (SELECT song_label FROM UserSongs)");
$totalNoAssociation = $totalStmt->fetchColumn();

$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
    flash("Invalid limit value. Please enter a number between 1 and 100.", "danger");
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $stmt = $db->prepare("SELECT * FROM SONGS WHERE (title LIKE CONCAT('%', :search, '%') OR artist LIKE CONCAT('%', :search, '%')) AND label NOT IN (SELECT song_label FROM UserSongs) LIMIT :limit");
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
} else {
    $stmt = $db->prepare("SELECT * FROM SONGS WHERE label NOT IN (SELECT song_label FROM UserSongs) LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
}

$stmt->execute();
$noAssociationSongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$displayedRecords = count($noAssociationSongs);

if (empty($noAssociationSongs)) {
    flash("No matching results found.", "warning");
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}
?>

<div class="container-fluid">
    <h1>No Association</h1>
    <div class="row">
        <div class="col-md-12">
            <p>Total records without association: <?php echo $totalNoAssociation; ?></p>
            <p><?php echo $displayedRecords; ?> records displayed</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form method="GET">
                <div class="form-group">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" value="<?php echo $search; ?>" class="form-control" placeholder="Enter title or artist">
                </div>
                <div class="form-group">
                    <label for="limit">Display Limit:</label>
                    <input type="number" id="limit" name="limit" value="<?php echo $limit; ?>" min="1" max="100" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>
        </div>
    </div>

    <?php if (!empty($noAssociationSongs)) : ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Artist</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($noAssociationSongs as $song) : ?>
                            <tr>
                                <td><a href="<?php echo $BASE_PATH ?>/search.php?song=<?php echo urlencode($song['title'] . ' ' . $song['artist']); ?>"><?php echo htmlspecialchars($song['title']); ?></a></td>
                                <td><?php echo htmlspecialchars($song['artist']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php //rr42 4/30/2024
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../partials/flash.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("search.php")));
}

function deleteFavorite($label)
{
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserSongs WHERE song_label = ?");
    $stmt->execute([$label]);
    flash("Favorite deleted successfully", "success");
}

if (isset($_POST['delete_label'])) {
    $label = $_POST['delete_label'];
    deleteFavorite($label);
    header("Location: allFavorites.php");
    exit;
}

if (isset($_POST['delete_username'])) {
    $username = $_POST['delete_username'];
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserSongs WHERE user_id IN (SELECT id FROM Users WHERE username LIKE ?)");
    $stmt->execute(["%$username%"]);
    flash("All records associated with the user '$username' deleted successfully", "success");
    header("Location: allFavorites.php");
    exit;
}

$perPage = 10;
if (isset($_GET['perPage']) && is_numeric($_GET['perPage'])) {
    $perPage = max(0, min(100, $_GET['perPage']));
}

$orderBy = "title";
if (isset($_GET['sort'])) {
    $sort = strtolower($_GET['sort']);
    if (in_array($sort, ['title', 'artist', 'total_users'])) {
        $orderBy = $sort;
    }
}

$db = getDB();
$partialUsername = isset($_GET['username']) ? $_GET['username'] : '';
$stmt = $db->prepare("SELECT SONGS.title, SONGS.artist, SONGS.label, COUNT(UserSongs.user_id) AS total_users, GROUP_CONCAT(Users.username) AS favorited_by 
                            FROM UserSongs 
                            JOIN SONGS ON UserSongs.song_label = SONGS.label 
                            JOIN Users ON UserSongs.user_id = Users.id
                            WHERE Users.username LIKE ?
                            GROUP BY SONGS.label 
                            ORDER BY $orderBy DESC 
                            LIMIT $perPage");
$stmt->execute(["%$partialUsername%"]);
$favoriteSongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Favorites</title>
</head>

<body> <!--rr42 4/30/2024!-->
    <div class="container-fluid">
        <h1>All Favorites</h1>

        <div class="row">
            <div class="col-md-6">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM UserSongs");
                $totalItems = $stmt->fetchColumn();
                ?>
                <p>Total number of items: <?php echo $totalItems; ?></p>
                <form method="GET" class="form-inline">
                    <label for="perPage">Records per page:</label>
                    <input type="number" id="perPage" name="perPage" value="<?php echo $perPage; ?>" min="0" max="100" class="form-control mr-2">
                    <label for="username">Filter by username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($partialUsername); ?>" class="form-control mr-2">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </form>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><a href="?sort=title">Title</a></th>
                            <th><a href="?sort=artist">Artist</a></th>
                            <th><a href="?sort=total_users">Total Users</a></th>
                            <th>Favorited By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($favoriteSongs) > 0) : ?>
                            <?php foreach ($favoriteSongs as $song) : ?>
                                <tr>
                                    <td><a href="<?php echo get_url('search.php') . '?song=' . urlencode($song['title'] . ' ' . $song['artist']); ?>"><?php echo htmlspecialchars($song['title']); ?></a></td>
                                    <td><?php echo htmlspecialchars($song['artist']); ?></td>
                                    <td><?php echo $song['total_users']; ?></td>
                                    <td><?php echo htmlspecialchars($song['favorited_by']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="delete_label" value="<?php echo $song['label']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this favorite?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">No matching records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if (count($favoriteSongs) > 0 && !empty($partialUsername)) : ?>
                    <form method="POST">
                        <input type="hidden" name="delete_username" value="<?php echo htmlspecialchars($partialUsername); ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all records associated with this user?')">Delete All Records Associated with User</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
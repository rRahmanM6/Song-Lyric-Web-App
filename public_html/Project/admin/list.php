<?php //rr42 4/18/2024
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../partials/flash.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/search.php"));
}
function deleteSong($id)
{
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM SONGS WHERE id = ?");
    $stmt->execute([$id]);
    flash("Song deleted successfully", "success");
}

if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    deleteSong($id);
    header("Location: list.php");
    exit;
}

$perPage = 10;
if (isset($_GET['perPage']) && is_numeric($_GET['perPage'])) {
    $perPage = max(0, min(100, $_GET['perPage']));
}
$orderBy = "title";
if (isset($_GET['sort'])) {
    $sort = strtolower($_GET['sort']);
    if (in_array($sort, ['title', 'artist', 'created'])) {
        $orderBy = $sort;
    }
}
$db = getDB();
$stmt = $db->prepare("SELECT id, title, artist, created FROM SONGS ORDER BY $orderBy LIMIT $perPage");
$stmt->execute();
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Songs</title>
</head>

<body>
    <h1>List Songs</h1>
    <form method="GET">
        <label for="perPage">Records per page:</label>
        <input type="number" id="perPage" name="perPage" value="<?php echo $perPage; ?>" min="0" max="100">
        <button type="submit">Apply</button>
    </form>
    <table border="1">
        <tr>
            <th><a href="?sort=title">Title</a></th>
            <th><a href="?sort=artist">Artist</a></th> 
            <th><a href="?sort=created">Created</a></th>
            <th>Actions</th>
        </tr>
        <?php foreach ($songs as $song) : ?>
            <tr>
                <td><a href="../search.php?song=<?php echo urlencode($song['title'] . ' ' . $song['artist']); ?>"><?php echo $song['title']; ?></a></td>
                <td><?php echo $song['artist']; ?></td>
                <td><?php echo $song['created']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $song['id']; ?>">Edit</a> |
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $song['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this song?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>
</body>

</html>
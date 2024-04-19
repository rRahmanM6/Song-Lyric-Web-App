<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if user has admin role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

// Check if song ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    flash("Invalid song ID", "danger");
    die(header("Location: $BASE_PATH" . "/list.php"));
}

$songId = $_GET['id'];

// Fetch song details from the database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM SONGS WHERE id = ?");
$stmt->execute([$songId]);
$song = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$song) {
    flash("Song not found", "danger");
    die(header("Location: $BASE_PATH" . "/list.php"));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = trim($_POST["title"]);
    $artist = trim($_POST["artist"]);
    $image = trim($_POST["image"]);
    $lyrics = trim($_POST["lyrics"]);

    // Update song record in the database
    $query = "UPDATE SONGS SET title = ?, artist = ?, image = ?, lyrics = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$title, $artist, $image, $lyrics, $songId]);

    flash("Song updated successfully", "success");
    header("Location: list.php");


}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Song</title>
</head>

<body>
    <h1>Edit Song</h1>
    <form method="POST">
        <div>
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required>
        </div>
        <div>
            <label>Artist</label>
            <input type="text" name="artist" value="<?php echo htmlspecialchars($song['artist']); ?>" required>
        </div>
        <div>
            <label>Image</label>
            <input type="text" name="image" value="<?php echo htmlspecialchars($song['image']); ?>" required>
        </div>
        <div>
            <label>Lyrics</label>
            <textarea name="lyrics" rows="5" required><?php echo htmlspecialchars($song['lyrics']); ?></textarea>
        </div>
        <button type="submit">Update Song</button>
    </form>
</body>

</html>

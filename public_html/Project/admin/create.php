<?php 
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("search.php")));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $artist = substr(trim($_POST["artist"]), 0, 40);
    $image = trim($_POST["image"]);
    $lyrics = trim($_POST["lyrics"]);
    $label = strtolower(str_replace(' ', '-', "$artist-$title-lyrics"));
    try {
        $db = getDB();
        $query = "INSERT INTO SONGS (label, title, artist, image, lyrics, is_api) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $db->prepare($query);
        $stmt->execute([$label, $title, $artist, $image, $lyrics]);
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Song</title>
</head>
<body>
    <h1>Create Song</h1>
    <?php if (isset($success) && $success) : ?>
        <div>Song created successfully!</div>
    <?php elseif (isset($error)) : ?>
        <div>Error: <?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div>
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div>
            <label>Artist</label>
            <input type="text" name="artist" required>
        </div>
        <div>
            <label>Image</label>
            <input type="text" name="image" required>
        </div>
        <div>
            <label>Lyrics</label>
            <input type="text" name="lyrics" required>
        </div>
        <button type="submit">Create Song</button>
    </form>
</body>
</html>
<?php //rr42 4/30/2024
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../partials/flash.php");
$db = getDB();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["users"]) && isset($_POST["songs"])) {
        $user_ids = $_POST["users"];
        $song_ids = $_POST["songs"];

        foreach ($user_ids as $user_id) {
            $stmt = $db->prepare("SELECT song_label FROM UserSongs WHERE user_id = :user_id");
            $stmt->execute([":user_id" => $user_id]);
            $existing_songs = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($song_ids as $song_id) {
                if (in_array($song_id, $existing_songs)) {
                    $stmt = $db->prepare("DELETE FROM UserSongs WHERE user_id = :user_id AND song_label = :song_id");
                    $stmt->execute([":user_id" => $user_id, ":song_id" => $song_id]);
                    flash("Removed song from user's favorites", "info");
                } else {
                    $stmt = $db->prepare("INSERT INTO UserSongs (user_id, song_label) VALUES (:user_id, :song_id)");
                    $stmt->execute([":user_id" => $user_id, ":song_id" => $song_id]);
                    flash("Added song to user's favorites", "success");
                }
            }
        }
    } else {
        flash("Both users and songs need to be selected", "warning");
    }
}

if (isset($_GET["search-query"])) {
    $search_query = "%" . $_GET["search-query"] . "%";
    $stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :search LIMIT 25");
    $stmt->execute([":search" => $search_query]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("SELECT label, title, artist FROM SONGS WHERE title LIKE :search OR artist LIKE :search LIMIT 25");
    $stmt->execute([":search" => $search_query]);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid"> <!--rr42 4/30/2024!-->
    <h1>New Association</h1>

    <form method="GET">
        <div class="form-group">
            <label for="user-search">Search Users:</label>
            <input type="text" id="user-search" name="user-search" class="form-control" placeholder="Enter username">
        </div>
        <div class="form-group">
            <label for="song-search">Search Songs:</label>
            <input type="text" id="song-search" name="song-search" class="form-control" placeholder="Enter title or artist">
        </div>

        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php
    if (isset($_GET["user-search"]) || isset($_GET["song-search"])) {
        if (isset($_GET["user-search"])) {
            $user_search = "%" . $_GET["user-search"] . "%";
            $stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :search LIMIT 25");
            $stmt->execute([":search" => $user_search]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (isset($_GET["song-search"])) {
            $song_search = "%" . $_GET["song-search"] . "%";
            $stmt = $db->prepare("SELECT label, title, artist FROM SONGS WHERE title LIKE :search OR artist LIKE :search LIMIT 25");
            $stmt->execute([":search" => $song_search]);
            $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    ?>
        <?php if (!empty($users)) : ?>
            <h3>Users</h3>
            <?php foreach ($users as $user) : ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="users[]" value="<?php echo $user['id']; ?>">
                    <label class="form-check-label" for="user-<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($songs)) : ?>
            <h3>Songs</h3>
            <?php foreach ($songs as $song) : ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="songs[]" value="<?php echo $song['label']; ?>">
                    <label class="form-check-label" for="song-<?php echo $song['label']; ?>">
                        <?php echo htmlspecialchars($song['title'] . ' - ' . $song['artist']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
    <?php endif;
    } ?>
    <button type="submit" class="btn btn-success">Apply Association</button>
    </form>
</div>

<?php
?>
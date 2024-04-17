<?php
require(__DIR__ . "/../../partials/nav.php");

if (isset($_GET["song"])) {
    $song = trim($_GET["song"]);

    if (empty($song)) {
        flash("Please enter a song name", "danger");
        die(header("Location: " . $_SERVER["PHP_SELF"]));
    }

    $data = ["query" => $song];
    $endpoint = "https://lyrics-api3.p.rapidapi.com/search.php";
    $isRapidAPI = true;
    $rapidAPIHost = "lyrics-api3.p.rapidapi.com";
    $result = get($endpoint, "SONG_API_KEY", $data, $isRapidAPI, $rapidAPIHost);

    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }

    if (!empty($result)) {
        $db = getDB();
        $firstSong = $result[0];
        $songName = $firstSong['name'];
        $artist = $firstSong['artist'];
        $image = $firstSong['image'];
        $songId = $firstSong['id'];

        $stmt = $db->prepare("SELECT * FROM SONGS WHERE label = ?");
        $stmt->execute([$songId]);
        $songExists = $stmt->fetch();

        if (!$songExists) {
            $query = "INSERT INTO SONGS (label, title, artist, image, lyrics) VALUES (?, ?, ?, ?, '')"; // Provide a default value or allow NULL values for 'lyrics'
            $stmt = $db->prepare($query);
            $stmt->execute([$songId, $songName, $artist, $image]);

            $lyricsEndpoint = "https://lyrics-api3.p.rapidapi.com/lyrics.php?id=$songId";
            $lyricsResult = get($lyricsEndpoint, "SONG_API_KEY", [], true, "lyrics-api3.p.rapidapi.com");

            if (se($lyricsResult, "status", 400, false) == 200 && isset($lyricsResult["response"])) {
                $lyricsData = json_decode($lyricsResult["response"], true);
                if (isset($lyricsData['lyrics'])) {
                    $lyrics = $lyricsData['lyrics'];
                    $query = "UPDATE SONGS SET lyrics = ? WHERE label = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$lyrics, $songId]);
                }
            }
        }
    }
}
?>

<div class="container-fluid">
    <h1>Song Lyrics</h1>
    <form>
        <div>
            <label>Song</label>
            <input name="song" />
            <input type="submit" value="Fetch Song" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result) && !empty($result)) : ?>
            <?php
            $firstSong = $result[0];
            $songName = $firstSong['name'];
            $artist = $firstSong['artist'];
            $image = $firstSong['image'];
            $firstSongId = $firstSong['id'];

            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM SONGS WHERE label = ?");
            $stmt->execute([$firstSongId]);
            $song = $stmt->fetch();

            if ($song) {
                $lyrics = $song['lyrics'];
            } else {
                $lyricsEndpoint = "https://lyrics-api3.p.rapidapi.com/lyrics.php?id=$firstSongId";
                $lyricsResult = get($lyricsEndpoint, "SONG_API_KEY", [], true, "lyrics-api3.p.rapidapi.com");

                if (se($lyricsResult, "status", 400, false) == 200 && isset($lyricsResult["response"])) {
                    $lyricsData = json_decode($lyricsResult["response"], true);
                    if (isset($lyricsData['lyrics'])) {
                        $lyrics = $lyricsData['lyrics'];
                        $query = "INSERT INTO SONGS (label, title, artist, image, lyrics) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$firstSongId, $songName, $artist, $image, $lyrics]);
                    } else {
                        $lyrics = "Lyrics not available.";
                    }
                } else {
                    $lyrics = "Failed to fetch lyrics.";
                }
            }

            echo "<h2>$songName</h2>";
            echo "<p>Artist: $artist</p>";
            echo "<img src='$image' alt='Song Image' style='max-width: 400px; max-height: 400px;'>";
            echo "<pre>$lyrics</pre>";
            ?>
        <?php endif; ?>
    </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>

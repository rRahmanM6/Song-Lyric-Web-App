<?php
// Function to add a song to favorites
function addToFavorites($userId, $songId)
{
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO UserSongs (user_id, song_label) VALUES (?, ?)");
    $stmt->execute([$userId, $songId]);
    // Optionally, you can add a success message or redirect the user to a different page
}

// Function to remove a song from favorites
function removeFromFavorites($userId, $songId)
{
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserSongs WHERE user_id = ? AND song_label = ?");
    $stmt->execute([$userId, $songId]);
    // Optionally, you can add a success message or redirect the user to a different page
}

// Function to get user favorites
function getUserFavorites($userId)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT song_label FROM UserSongs WHERE user_id = ?");
     $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $favorites;
}
?>
<?php 
function addToFavorites($userId, $songId){
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO UserSongs (user_id, song_label) VALUES (?, ?)");
    $stmt->execute([$userId, $songId]);
}

function removeFromFavorites($userId, $songId){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserSongs WHERE user_id = ? AND song_label = ?");
    $stmt->execute([$userId, $songId]);
}

function getUserFavorites($userId){
    $db = getDB();
    $stmt = $db->prepare("SELECT song_label FROM UserSongs WHERE user_id = ?");
     $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $favorites;
}

function removeAllFavorites($userId){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserSongs WHERE user_id = ?");
    $stmt->execute([$userId]);
}
?>
<?php
// Load match data (array of matches)
$data = json_decode(file_get_contents("data/2012-13.json"), true);  


$teams = [];

foreach ($data as $match) {
    if (!in_array($match['home_team'], $teams)) {
        $teams[] = $match['home_team'];
    }
    if (!in_array($match['away_team'], $teams)) {
        $teams[] = $match['away_team'];
    }
}

sort($teams); // Optional: sort alphabetically

// Output as JSON for frontend use (if needed)
header('Content-Type: application/json');
echo json_encode($teams);
?>

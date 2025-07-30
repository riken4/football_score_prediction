<?php
function get_available_seasons() {
    $seasons = [];
    $files = glob('data/*.json');
    
    foreach ($files as $file) {
        // Extract season from filename (e.g., "2012-13.json" becomes "2012-13")
        $season = basename($file, '.json');
        if (preg_match('/^\d{4}-\d{2}$/', $season)) {  // Validate format YYYY-YY
            $seasons[] = $season;
        }
    }
    
    // Sort seasons in descending order (most recent first)
    rsort($seasons);
    return $seasons;
}

// If called directly, return JSON
if (basename($_SERVER['PHP_SELF']) == 'get_seasons.php') {
    header('Content-Type: application/json');
    echo json_encode(get_available_seasons());
}
?> 
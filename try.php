<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Football Match Prediction</h2>";

// Test if shell_exec works at all
echo "<p><strong>Testing shell_exec...</strong><br>";
$test_output = shell_exec("echo Hello from PHP");
echo "Test output: " . ($test_output ? $test_output : "NULL") . "</p>";

// Use the new Python script
$python = "python"; // Use python from PATH
$script = "C:\\xampp2\\htdocs\\6workingsem-with-py\\predict_match_new.py";

$team1 = "Man City";
$team2 = "Chelsea";

$command = "\"$python\" \"$script\" \"$team1\" \"$team2\" 2>&1";
echo "<p><strong>Command:</strong> " . $command . "</p>";

$output = shell_exec($command);

echo "<p><strong>Raw Output:</strong> " . ($output ? $output : "NULL") . "</p>";

// Parse JSON output if available
if ($output) {
    $json_data = json_decode($output, true);
    if ($json_data && !isset($json_data['error'])) {
        echo "<div style='background-color: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3 style='color: #2c3e50; text-align: center;'>🎯 PREDICTION RESULTS</h3>";
        echo "<div style='text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0;'>";
        echo "{$json_data['home_team']} vs {$json_data['away_team']}";
        echo "</div>";
        
        echo "<div style='display: flex; justify-content: space-around; margin: 20px 0;'>";
        echo "<div style='text-align: center; background-color: #e8f5e8; padding: 15px; border-radius: 8px; flex: 1; margin: 0 10px;'>";
        echo "<h4 style='color: #27ae60; margin: 0;'>🏠 HOME WIN</h4>";
        echo "<div style='font-size: 32px; font-weight: bold; color: #27ae60;'>{$json_data['prediction']['home_win']}%</div>";
        echo "</div>";
        
        echo "<div style='text-align: center; background-color: #fff3cd; padding: 15px; border-radius: 8px; flex: 1; margin: 0 10px;'>";
        echo "<h4 style='color: #f39c12; margin: 0;'>🤝 DRAW</h4>";
        echo "<div style='font-size: 32px; font-weight: bold; color: #f39c12;'>{$json_data['prediction']['draw']}%</div>";
        echo "</div>";
        
        echo "<div style='text-align: center; background-color: #f8d7da; padding: 15px; border-radius: 8px; flex: 1; margin: 0 10px;'>";
        echo "<h4 style='color: #e74c3c; margin: 0;'>✈️ AWAY WIN</h4>";
        echo "<div style='font-size: 32px; font-weight: bold; color: #e74c3c;'>{$json_data['prediction']['away_win']}%</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<div style='text-align: center; background-color: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4 style='color: #1976d2; margin: 0;'>⚽ EXPECTED GOALS</h4>";
        echo "<div style='font-size: 28px; font-weight: bold; color: #1976d2;'>";
        echo "{$json_data['prediction']['expected_home_goals']} - {$json_data['prediction']['expected_away_goals']}";
        echo "</div>";
        echo "</div>";
        
        // Model information
        echo "<div style='background-color: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4 style='color: #555; margin: 0 0 10px 0;'>📊 MODEL INFORMATION</h4>";
        echo "<p><strong>Total Matches Analyzed:</strong> {$json_data['model_info']['total_matches']}</p>";
        echo "<p><strong>Training Matches:</strong> {$json_data['model_info']['training_matches']}</p>";
        echo "<p><strong>Teams in Database:</strong> {$json_data['model_info']['available_teams']}</p>";
        echo "</div>";
        
        // Prediction summary
        $max_prob = max($json_data['prediction']['home_win'], $json_data['prediction']['draw'], $json_data['prediction']['away_win']);
        $predicted_result = "";
        if ($max_prob == $json_data['prediction']['home_win']) {
            $predicted_result = "HOME WIN for {$json_data['home_team']}";
        } elseif ($max_prob == $json_data['prediction']['away_win']) {
            $predicted_result = "AWAY WIN for {$json_data['away_team']}";
        } else {
            $predicted_result = "DRAW";
        }
        
        echo "<div style='background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>";
        echo "<h3 style='color: #155724; margin: 0;'>🎯 FINAL PREDICTION</h3>";
        echo "<div style='font-size: 24px; font-weight: bold; color: #155724; margin: 10px 0;'>$predicted_result</div>";
        echo "<div style='font-size: 16px; color: #155724;'>Confidence: $max_prob%</div>";
        echo "</div>";
        
        echo "</div>";
        
    } else if ($json_data && isset($json_data['error'])) {
        echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4 style='color: #721c24; margin: 0;'>❌ ERROR</h4>";
        echo "<p style='color: #721c24;'>{$json_data['error']}</p>";
        echo "</div>";
    }
}
?>

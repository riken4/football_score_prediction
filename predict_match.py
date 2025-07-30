import pandas as pd
import numpy as np
import os
import sys
from scipy.stats import poisson
from sklearn.model_selection import train_test_split
import json

def load_data(data_folder):
    all_files = [os.path.join(data_folder, f) for f in os.listdir(data_folder) if f.endswith(".json")]
    df_list = []
    for file in all_files:
        try:
            with open(file, 'r', encoding='utf-8') as f:
                data = json.load(f)
            # Convert JSON data to DataFrame - assuming it has match data
            if isinstance(data, list):
                df = pd.DataFrame(data)
            else:
                # If it's a nested structure, we might need to flatten it
                df = pd.json_normalize(data)
            df_list.append(df)
        except Exception as e:
            print(f"Error loading {file}: {e}")
            continue
    if df_list:
    combined_df = pd.concat(df_list, ignore_index=True)
    return combined_df
    else:
        return None

def clean_data(df):
    print(f"Original data shape: {df.shape}")
    print(f"Original columns: {list(df.columns)}")
    
    df_clean = df.copy()
    # Use explicit columns if they exist
    if all(col in df_clean.columns for col in ['home_goals', 'away_goals', 'home_team', 'away_team']):
    df_clean['home_goals'] = pd.to_numeric(df_clean['home_goals'], errors='coerce')
    df_clean['away_goals'] = pd.to_numeric(df_clean['away_goals'], errors='coerce')
        df_clean['home_team'] = df_clean['home_team']
        df_clean['away_team'] = df_clean['away_team']
    else:
        print("Required columns not found, using fallback logic.")
        # Fallback: try to find by substring
        goal_columns = []
        for col in df_clean.columns:
            if 'goal' in col.lower() or 'score' in col.lower():
                goal_columns.append(col)
        print(f"Found goal columns: {goal_columns}")
        if len(goal_columns) >= 2:
            df_clean['home_goals'] = pd.to_numeric(df_clean[goal_columns[0]], errors='coerce')
            df_clean['away_goals'] = pd.to_numeric(df_clean[goal_columns[1]], errors='coerce')
        else:
            print("No goal columns found, using dummy data")
            df_clean['home_goals'] = np.random.randint(0, 4, len(df_clean))
            df_clean['away_goals'] = np.random.randint(0, 4, len(df_clean))
        team_columns = []
        for col in df_clean.columns:
            if 'team' in col.lower() or 'home' in col.lower() or 'away' in col.lower():
                team_columns.append(col)
        print(f"Found team columns: {team_columns}")
        if len(team_columns) >= 2:
            df_clean['home_team'] = df_clean[team_columns[0]]
            df_clean['away_team'] = df_clean[team_columns[1]]
        else:
            print("No team columns found, using dummy data")
            teams = ['Manchester City', 'Chelsea', 'Arsenal', 'Liverpool', 'Manchester United', 'Tottenham']
            df_clean['home_team'] = np.random.choice(teams, len(df_clean))
            df_clean['away_team'] = np.random.choice(teams, len(df_clean))
    print(f"Before dropna: {len(df_clean)} rows")
    df_clean = df_clean.dropna(subset=['home_team', 'away_team', 'home_goals', 'away_goals'])
    print(f"After dropna: {len(df_clean)} rows")
    return df_clean

def calculate_attack_defense_strength(df):
    teams = pd.concat([df['home_team'], df['away_team']]).unique()
    team_stats = {team: {'home_goals': 0, 'away_goals': 0, 'home_games': 0, 'away_games': 0} for team in teams}
    for _, row in df.iterrows():
        ht, at = row['home_team'], row['away_team']
        hg, ag = row['home_goals'], row['away_goals']
        team_stats[ht]['home_goals'] += hg
        team_stats[ht]['home_games'] += 1
        team_stats[at]['away_goals'] += ag
        team_stats[at]['away_games'] += 1
    total_home_goals = df['home_goals'].sum()
    total_away_goals = df['away_goals'].sum()
    total_matches = len(df)
    avg_home_goals = total_home_goals / total_matches
    avg_away_goals = total_away_goals / total_matches
    team_strength = {}
    for team in teams:
        home_attack = team_stats[team]['home_goals'] / team_stats[team]['home_games'] if team_stats[team]['home_games'] > 0 else 0
        away_attack = team_stats[team]['away_goals'] / team_stats[team]['away_games'] if team_stats[team]['away_games'] > 0 else 0
        home_defense = team_stats[team]['away_goals'] / team_stats[team]['away_games'] if team_stats[team]['away_games'] > 0 else 0
        away_defense = team_stats[team]['home_goals'] / team_stats[team]['home_games'] if team_stats[team]['home_games'] > 0 else 0
        team_strength[team] = {
            'home_attack': home_attack / avg_home_goals,
            'away_attack': away_attack / avg_away_goals,
            'home_defense': home_defense / avg_away_goals,
            'away_defense': away_defense / avg_home_goals
        }
    return team_strength, avg_home_goals, avg_away_goals

def predict_result_poisson(home_team, away_team, team_strength, avg_home_goals, avg_away_goals, max_goals=6):
    if home_team not in team_strength or away_team not in team_strength:
        return None
    h = team_strength[home_team]
    a = team_strength[away_team]
    home_exp = avg_home_goals * h['home_attack'] * a['away_defense']
    away_exp = avg_away_goals * a['away_attack'] * h['home_defense']
    prob_matrix = np.zeros((max_goals+1, max_goals+1))
    for i in range(max_goals+1):
        for j in range(max_goals+1):
            prob_matrix[i][j] = poisson.pmf(i, home_exp) * poisson.pmf(j, away_exp)
    home_win_prob = np.sum(np.tril(prob_matrix, -1))
    draw_prob = np.sum(np.diag(prob_matrix))
    away_win_prob = np.sum(np.triu(prob_matrix, 1))
    return {
        "home_win": round(home_win_prob * 100, 2),
        "draw": round(draw_prob * 100, 2),
        "away_win": round(away_win_prob * 100, 2)
    }

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Please provide home and away team names"}))
        sys.exit(1)

    home_team = sys.argv[1]
    away_team = sys.argv[2]

    # Updated data folder path to current workspace
    data_folder = r'C:\xampp2\htdocs\6workingsem-with-py\data'
    
    df = load_data(data_folder)
    if df is None or len(df) == 0:
        print(json.dumps({"error": "No data files found or could not load data"}))
        sys.exit(1)
    
    df_clean = clean_data(df)
    if len(df_clean) == 0:
        print(json.dumps({"error": "No valid data after cleaning"}))
        sys.exit(1)
    
    train_df, _ = train_test_split(df_clean, test_size=0.2, random_state=42)
    team_strength, avg_home_goals, avg_away_goals = calculate_attack_defense_strength(train_df)
    result = predict_result_poisson(home_team, away_team, team_strength, avg_home_goals, avg_away_goals)

    if result is None:
        print(json.dumps({"error": "Team not found in dataset"}))
    else:
        print(json.dumps({
            "home_team": home_team,
            "away_team": away_team,
            "prediction": result
        }))

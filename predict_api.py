from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import os
import json
from scipy.stats import poisson

app = Flask(__name__)
CORS(app)

def load_data(season, data_dir='data'):
    file = os.path.join(data_dir, f"{season}.json")
    if not os.path.exists(file):
        return None
    with open(file, 'r', encoding='utf-8') as f:
        data = json.load(f)
    return pd.DataFrame(data)

def clean_data(df):
    for col in ['home_goals', 'away_goals', 'yellow_cards_home_team', 'yellow_cards_away_team', 'red_cards_home_team', 'red_cards_away_team']:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)
        else:
            df[col] = 0
    return df

def calculate_team_stats(df, team):
    matches = df[(df['home_team'] == team) | (df['away_team'] == team)]
    total_matches = len(matches)
    if total_matches == 0:
        return None
    goals_for = goals_against = yellow = red = 0
    for _, row in matches.iterrows():
        if row['home_team'] == team:
            goals_for += row['home_goals']
            goals_against += row['away_goals']
            yellow += row['yellow_cards_home_team']
            red += row['red_cards_home_team']
        else:
            goals_for += row['away_goals']
            goals_against += row['home_goals']
            yellow += row['yellow_cards_away_team']
            red += row['red_cards_away_team']
    return {
        'attack': goals_for / total_matches,
        'defense': goals_against / total_matches,
        'yellow_cards': yellow / total_matches,
        'red_cards': red / total_matches
    }

def adjust_strength(attack, defense, yellow, red, yellow_weight=0.01, red_weight=0.05, min_strength=0.6):
    penalty = 1 - (yellow_weight * yellow + red_weight * red)
    penalty = max(min_strength, penalty)
    return {
        'attack': max(min_strength, attack * penalty),
        'defense': max(min_strength, defense * penalty)
    }

def predict_scores(stats_home, stats_away, home_advantage=1.05, max_goals=6):
    teamA = adjust_strength(stats_home['attack'], stats_home['defense'], stats_home['yellow_cards'], stats_home['red_cards'])
    teamB = adjust_strength(stats_away['attack'], stats_away['defense'], stats_away['yellow_cards'], stats_away['red_cards'])
    lambda_home = teamA['attack'] / teamB['defense'] * home_advantage
    lambda_away = teamB['attack'] / teamA['defense']
    results = np.zeros((max_goals+1, max_goals+1))
    score_probabilities = {}
    
    for i in range(max_goals+1):
        for j in range(max_goals+1):
            prob = poisson.pmf(i, lambda_home) * poisson.pmf(j, lambda_away)
            results[i, j] = prob
            score_probabilities[f"{i}-{j}"] = prob

    home_win = np.sum(np.tril(results, -1))
    draw = np.sum(np.diag(results))
    away_win = np.sum(np.triu(results, 1))
    
    return {
        'home_win': round(home_win * 100, 2),
        'draw': round(draw * 100, 2),
        'away_win': round(away_win * 100, 2),
        'expected_home_goals': round(lambda_home, 2),
        'expected_away_goals': round(lambda_away, 2),
        'score_probabilities': score_probabilities
    }

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    home_team = data.get('home_team')
    away_team = data.get('away_team')
    season = data.get('season')
    
    if not home_team or not away_team or not season:
        return jsonify({'error': 'Missing parameters'}), 400
    
    df = load_data(season)
    if df is None:
        return jsonify({'error': f'Season {season} data not found.'}), 404
    
    df = clean_data(df)
    stats_home = calculate_team_stats(df, home_team)
    stats_away = calculate_team_stats(df, away_team)

    if not stats_home or not stats_away:
        return jsonify({'error': 'Team stats not found'}), 400
    
    prediction = predict_scores(stats_home, stats_away)
    return jsonify({
        'home_team': home_team,
        'away_team': away_team,
        'season': season,
        'prediction': prediction,
        'stats_home': stats_home,
        'stats_away': stats_away
    })

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)

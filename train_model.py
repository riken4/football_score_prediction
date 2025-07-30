import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier
import json
import glob
import os
import joblib
import sys
import matplotlib.pyplot as plt
import seaborn as sns

# Force flush for print statements
def print_flush(*args, **kwargs):
    print(*args, **kwargs, flush=True)

def load_data(data_path=None):
    """Load data from either JSON or CSV file."""
    if data_path is None:
        # Use the default path provided
        data_path = r'C:\xampp\htdocs\6sem\premier-league-data\2015-16.csv'
    
    print_flush(f"\nLoading data from: {os.path.abspath(data_path)}")
    
    try:
        if data_path.endswith('.json'):
            df = pd.read_json(data_path)
        elif data_path.endswith('.csv'):
            df = pd.read_csv(data_path)
        else:
            raise ValueError("Unsupported file format. Please use JSON or CSV files.")
        
        print_flush(f"Successfully loaded {len(df)} matches")
        print_flush("\nAvailable columns in the dataset:")
        print_flush(df.columns.tolist())
        
        return df
    except Exception as e:
        print_flush(f"Error loading data: {str(e)}")
        raise

def investigate_data(df):
    """Analyze the dataset for missing values and data quality."""
    print_flush("\n=== Data Investigation ===")
    
    # Check for missing values
    missing_values = df.isnull().sum()
    print_flush("\nMissing values per column:")
    print_flush(missing_values[missing_values > 0])
    
    # Basic statistics for numeric columns
    numeric_columns = [
        'home_goals', 'away_goals',
        'yellow_cards_home_team', 'yellow_cards_away_team',
        'red_cards_home_team', 'red_cards_away_team'
    ]
    
    # Filter to only include columns that exist in the dataset
    numeric_columns = [col for col in numeric_columns if col in df.columns]
    
    if numeric_columns:
        print_flush("\nBasic statistics for numeric columns:")
        print_flush(df[numeric_columns].describe())
    
    # Save data quality report
    os.makedirs('reports', exist_ok=True)
    with open('reports/data_quality_report.txt', 'w') as f:
        f.write("=== Data Quality Report ===\n\n")
        f.write("Available Columns:\n")
        f.write(str(df.columns.tolist()))
        f.write("\n\nMissing Values:\n")
        f.write(str(missing_values[missing_values > 0]))
        if numeric_columns:
            f.write("\n\nBasic Statistics:\n")
            f.write(str(df[numeric_columns].describe()))
    
    return missing_values

def clean_data(df):
    """Clean and preprocess the data for machine learning."""
    print_flush("\n=== Data Cleaning ===")
    
    # Make a copy to avoid modifying the original data
    df_clean = df.copy()
    
    # Define numeric columns that we want to use for the model
    numeric_columns = [
        'home_goals', 'away_goals',                    # Goals
        'yellow_cards_home_team', 'yellow_cards_away_team',  # Yellow cards
        'red_cards_home_team', 'red_cards_away_team'   # Red cards
    ]
    
    # Filter to only include columns that exist in the dataset
    numeric_columns = [col for col in numeric_columns if col in df_clean.columns]
    
    if not numeric_columns:
        raise ValueError("No matching columns found in the dataset. Please check the column names.")
    
    # Select only the numeric columns we want to use
    df_clean = df_clean[numeric_columns].copy()
    
    # Convert all columns to numeric, coercing errors to NaN
    for col in numeric_columns:
        df_clean[col] = pd.to_numeric(df_clean[col], errors='coerce')
    
    # Handle missing values by filling with column means
    df_clean = df_clean.fillna(df_clean.mean())
    
    # Remove any remaining rows with missing values
    df_clean = df_clean.dropna()
    
    print_flush(f"Cleaned data shape: {df_clean.shape}")
    return df_clean

def create_features(df):
    """Create and select features for machine learning."""
    print_flush("\n=== Feature Engineering ===")
    
    # Select features for the model
    feature_columns = [
        'yellow_cards_home_team', 'yellow_cards_away_team',  # Yellow cards
        'red_cards_home_team', 'red_cards_away_team'   # Red cards
    ]
    
    # Filter to only include columns that exist in the dataset
    feature_columns = [col for col in feature_columns if col in df.columns]
    
    if not feature_columns:
        raise ValueError("No matching feature columns found in the dataset. Please check the column names.")
    
    # Create feature matrix
    X = df[feature_columns].values
    
    # Create target variable (0: away win, 1: draw, 2: home win)
    y = np.where(df['home_goals'] > df['away_goals'], 2,
                np.where(df['home_goals'] < df['away_goals'], 0, 1))
    
    print_flush(f"Feature matrix shape: {X.shape}")
    print_flush(f"Target vector shape: {y.shape}")
    
    return X, y

def train_model(data_path=None):
    print_flush("\n=== Starting Model Training ===")
    
    try:
        # Step 1: Load data
        print_flush("\nStep 1: Loading match data...")
        df = load_data(data_path)
        
        # Step 2: Investigate data
        print_flush("\nStep 2: Investigating data quality...")
        missing_values = investigate_data(df)
        
        # Step 3: Clean data
        print_flush("\nStep 3: Cleaning data...")
        df_clean = clean_data(df)
        
        # Step 4: Create features
        print_flush("\nStep 4: Creating features...")
        X, y = create_features(df_clean)
        
        # Step 5: Split and scale data
        print_flush("\nStep 5: Preparing data for training...")
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        # Step 6: Train model
        print_flush("\nStep 6: Training Random Forest model...")
        model = RandomForestClassifier(n_estimators=100, random_state=42)
        model.fit(X_train_scaled, y_train)
        
        # Step 7: Evaluate model
        train_score = model.score(X_train_scaled, y_train)
        test_score = model.score(X_test_scaled, y_test)
        
        print_flush("\nModel Performance:")
        print_flush(f"Training accuracy: {train_score:.2%}")
        print_flush(f"Testing accuracy: {test_score:.2%}")
        
        # Step 8: Save model and scaler
        print_flush("\nStep 8: Saving model and scaler...")
        os.makedirs('models', exist_ok=True)
        joblib.dump(model, 'models/match_predictor.joblib')
        joblib.dump(scaler, 'models/scaler.joblib')
        
        print_flush("\nTraining complete! Model and scaler saved in 'models' directory.")
        
    except Exception as e:
        print_flush("\nError during training: " + str(e))
        raise

if __name__ == "__main__":
    # Use the specific dataset path
    data_path = r'C:\xampp\htdocs\6sem\premier-league-data\2015-16.csv'
    train_model(data_path) 
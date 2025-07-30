import json
import os
import glob

def test_json_files():
    data_dir = os.path.join(os.getcwd(), 'data')
    print(f"Looking for JSON files in: {data_dir}")
    
    json_files = glob.glob(os.path.join(data_dir, '*.json'))
    print(f"Found {len(json_files)} JSON files:")
    
    for json_file in json_files:
        print(f"\nTesting file: {os.path.basename(json_file)}")
        try:
            with open(json_file, 'r', encoding='utf-8') as f:
                data = json.load(f)
                print(f"Successfully loaded {len(data)} matches")
                
                # Print first match as sample
                if data:
                    print("\nSample match data:")
                    for key, value in data[0].items():
                        print(f"{key}: {value}")
                
        except Exception as e:
            print(f"Error loading file: {str(e)}")

if __name__ == "__main__":
    test_json_files() 
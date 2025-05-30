"""
Insert API Articles Script for Newspress CMS Migration

This script traverses a directory structure containing JSON article files
and inserts them into the Newspress CMS database.

Usage:
    python insert_api_articles.py [--path /custom/path/to/json]
"""
import os
import sys
import argparse
from pathlib import Path

# Add parent directory to path to allow imports
sys.path.append(str(Path(__file__).parent.parent))

from config.settings import ARTICLE_JSON_PATH, TABLES_TO_CLEAR
from models.article import ArticleManager
from utils.file_utils import traverse_directory

def parse_arguments():
    """Parse command line arguments"""
    parser = argparse.ArgumentParser(description='Insert API articles into Newspress CMS database')
    parser.add_argument('--path', type=str, default=ARTICLE_JSON_PATH,
                        help=f'Path to article JSON files (default: {ARTICLE_JSON_PATH})')
    parser.add_argument('--clear', action='store_true',
                        help='Clear tables before insertion')
    return parser.parse_args()

def main():
    """Main function to insert API articles"""
    # Parse command line arguments
    args = parse_arguments()
    
    # Set unlimited memory
    # Note: This is a Python script, so memory management is different from PHP
    # but we'll ensure efficient processing
    
    print(f"Starting article insertion from {args.path}")
    
    # Initialize article manager
    article_manager = ArticleManager()
    
    # Clear tables if requested
    if args.clear:
        print("Clearing tables before insertion...")
        article_manager.clear_tables(TABLES_TO_CLEAR)
    
    # Traverse directory structure and process each JSON file
    article_count = 0
    error_count = 0
    
    try:
        # Walk through the directory structure
        for year_dir in os.listdir(args.path):
            year_path = os.path.join(args.path, year_dir)
            if not os.path.isdir(year_path):
                continue
                
            print(f"Processing year: {year_dir}")
            
            for month_dir in os.listdir(year_path):
                month_path = os.path.join(year_path, month_dir)
                if not os.path.isdir(month_path):
                    continue
                    
                print(f"Processing year: {year_dir}, month: {month_dir}")
                
                for day_dir in os.listdir(month_path):
                    day_path = os.path.join(month_path, day_dir)
                    if not os.path.isdir(day_path):
                        continue
                    
                    # Process all JSON files in the day directory
                    for article_file in os.listdir(day_path):
                        if not article_file.endswith('.json'):
                            continue
                            
                        article_path = os.path.join(day_path, article_file)
                        
                        try:
                            success = article_manager.process_article_json(article_path)
                            if success:
                                article_count += 1
                            else:
                                error_count += 1
                        except Exception as e:
                            print(f"Error processing article {article_path}: {e}")
                            error_count += 1
    
    except Exception as e:
        print(f"Error traversing directory structure: {e}")
    
    finally:
        # Close database connection
        article_manager.close()
    
    print(f"Article insertion complete. Processed {article_count} articles successfully with {error_count} errors.")

if __name__ == "__main__":
    main()

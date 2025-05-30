"""
Script to clear database tables for Newspress CMS Migration

This script clears specified tables in the Newspress CMS database.

Usage:
    python clear_tables.py [--tables table1,table2,...]
"""
import sys
import argparse
from pathlib import Path

# Add parent directory to path to allow imports
sys.path.append(str(Path(__file__).parent.parent))

from config.settings import TABLES_TO_CLEAR
from models.article import ArticleManager

def parse_arguments():
    """Parse command line arguments"""
    parser = argparse.ArgumentParser(description='Clear tables in Newspress CMS database')
    parser.add_argument('--tables', type=str, default=None,
                        help='Comma-separated list of tables to clear (default: all tables in settings)')
    return parser.parse_args()

def main():
    """Main function to clear database tables"""
    # Parse command line arguments
    args = parse_arguments()
    
    # Determine tables to clear
    tables_to_clear = args.tables.split(',') if args.tables else TABLES_TO_CLEAR
    
    print(f"Preparing to clear the following tables: {', '.join(tables_to_clear)}")
    
    # Initialize article manager
    article_manager = ArticleManager()
    
    try:
        # Clear tables
        article_manager.clear_tables(tables_to_clear)
        print("Tables cleared successfully")
    
    except Exception as e:
        print(f"Error clearing tables: {e}")
    
    finally:
        # Close database connection
        article_manager.close()

if __name__ == "__main__":
    main()

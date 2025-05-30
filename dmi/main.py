"""
Main entry point for Newspress CMS Migration

This script provides a command-line interface for all migration operations.

Usage:
    python main.py [command] [options]

Commands:
    insert  - Insert articles from JSON files
    update  - Update image paths
    clear   - Clear database tables
"""
import sys
import argparse
from pathlib import Path

# Add scripts directory to path
scripts_dir = Path(__file__).parent / 'scripts'
sys.path.append(str(scripts_dir))

def parse_arguments():
    """Parse command line arguments"""
    parser = argparse.ArgumentParser(description='Newspress CMS Migration Tool')
    subparsers = parser.add_subparsers(dest='command', help='Command to execute')
    
    # Insert command
    insert_parser = subparsers.add_parser('insert', help='Insert articles from JSON files')
    insert_parser.add_argument('--path', type=str, help='Path to article JSON files')
    insert_parser.add_argument('--clear', action='store_true', help='Clear tables before insertion')
    
    # Update command
    update_parser = subparsers.add_parser('update', help='Update image paths')
    
    # Clear command
    clear_parser = subparsers.add_parser('clear', help='Clear database tables')
    clear_parser.add_argument('--tables', type=str, help='Comma-separated list of tables to clear')
    
    return parser.parse_args()

def main():
    """Main function to handle command routing"""
    args = parse_arguments()
    
    if args.command == 'insert':
        # Import and run insert_api_articles
        from scripts.insert_api_articles import main as insert_main
        sys.argv = [sys.argv[0]]
        if args.path:
            sys.argv.extend(['--path', args.path])
        if args.clear:
            sys.argv.append('--clear')
        insert_main()
    
    elif args.command == 'update':
        # Import and run update_image_path
        from scripts.update_image_path import main as update_main
        update_main()
    
    elif args.command == 'clear':
        # Import and run clear_tables
        from scripts.clear_tables import main as clear_main
        sys.argv = [sys.argv[0]]
        if args.tables:
            sys.argv.extend(['--tables', args.tables])
        clear_main()
    
    else:
        print("Please specify a command: insert, update, or clear")
        print("Use --help for more information")

if __name__ == "__main__":
    main()

"""
Update Image Path Script for Newspress CMS Migration

This script updates image paths in the database based on publish date and image ID.

Usage:
    python update_image_path.py
"""
import sys
from pathlib import Path

# Add parent directory to path to allow imports
sys.path.append(str(Path(__file__).parent.parent))

from models.article import ArticleManager

def main():
    """Main function to update image paths"""
    print("Starting image path update process")
    
    # Initialize article manager
    article_manager = ArticleManager()
    
    try:
        # Update image paths
        article_manager.update_image_paths()
        print("Image path update completed successfully")
    
    except Exception as e:
        print(f"Error updating image paths: {e}")
    
    finally:
        # Close database connection
        article_manager.close()

if __name__ == "__main__":
    main()

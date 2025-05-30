"""
Configuration settings for Newspress CMS Migration
"""

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'newspress',
    'raise_on_warnings': True
}

# File paths
ARTICLE_JSON_PATH = '/var/polopoly-import/article-json/'

# Image paths
IMAGE_BASE_PATH = 'albayan/uploads/archives/images/'

# Tables to clear on clean start
TABLES_TO_CLEAR = [
    'article_archive',
    'article_multi_section_archive',
    'image_archive',
    'article_archive_tags'
]

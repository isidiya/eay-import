# Newspress CMS Migration - User Guide

## Overview

This Python package provides tools to migrate content from JSON files to the Newspress CMS database. It includes scripts for:

1. Inserting articles from JSON files
2. Updating image paths in the database
3. Clearing database tables when needed

## Installation

1. Extract the `newspress_migration.zip` file
2. Install the required dependency:
   ```
   pip install mysql-connector-python
   ```

## Configuration

Edit the `config/settings.py` file to configure:

- Database connection details
- File paths for JSON articles
- Tables to clear on clean start

```python
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
```

## Usage

The package provides a main CLI script with three commands:

### 1. Insert Articles

```bash
python main.py insert [--path /path/to/json/files] [--clear]
```

Options:
- `--path`: Custom path to JSON article files (default: path from settings)
- `--clear`: Clear tables before insertion

### 2. Update Image Paths

```bash
python main.py update
```

This command updates image paths in the database based on publish date and image ID.

### 3. Clear Tables

```bash
python main.py clear [--tables table1,table2,...]
```

Options:
- `--tables`: Comma-separated list of tables to clear (default: all tables in settings)

## Individual Scripts

You can also run the individual scripts directly:

```bash
# Insert articles
python scripts/insert_api_articles.py [--path /path/to/json/files] [--clear]

# Update image paths
python scripts/update_image_path.py

# Clear tables
python scripts/clear_tables.py [--tables table1,table2,...]
```

## Project Structure

```
newspress_migration/
├── config/
│   ├── __init__.py
│   └── settings.py         # Configuration settings
├── utils/
│   ├── __init__.py
│   ├── db.py               # Database connection and operations
│   ├── file_utils.py       # File handling utilities
│   └── date_utils.py       # Date manipulation utilities
├── models/
│   ├── __init__.py
│   └── article.py          # Article data models and operations
├── scripts/
│   ├── __init__.py
│   ├── insert_api_articles.py  # Main script for inserting articles
│   ├── update_image_path.py    # Main script for updating image paths
│   └── clear_tables.py         # Script to clear database tables
└── main.py                 # Entry point with command-line interface
```

## Comparison with Original Laravel Commands

### InsertApiArticles

The Python version replicates the functionality of the Laravel command:
- Traverses directory structure by year/month/day
- Processes JSON article files
- Inserts data into multiple tables
- Handles sections, tags, and images

### UpdateImagePath

The Python version replicates the functionality of the Laravel command:
- Retrieves article and image data from database
- Constructs new image paths based on publish date and image ID
- Updates image paths in the database

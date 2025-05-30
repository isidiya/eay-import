"""
File utility functions for Newspress CMS Migration
"""
import os
import json
import glob
from pathlib import Path

def read_json_file(file_path):
    """Read and parse JSON file"""
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            return json.load(file)
    except Exception as e:
        print(f"Error reading JSON file {file_path}: {e}")
        return None

def get_file_extension(file_path):
    """Get file extension from path"""
    return os.path.splitext(file_path)[1].lstrip('.')

def traverse_directory(base_dir, pattern="*"):
    """Recursively traverse directory and yield file paths matching pattern"""
    for path in Path(base_dir).rglob(pattern):
        if path.is_file():
            yield str(path)

def get_nested_directories(base_dir):
    """Get nested directories by year/month/day structure"""
    result = {}
    
    # Get all year directories
    year_dirs = glob.glob(os.path.join(base_dir, '*'))
    for year_dir in year_dirs:
        if os.path.isdir(year_dir):
            year = os.path.basename(year_dir)
            result[year] = {}
            
            # Get all month directories
            month_dirs = glob.glob(os.path.join(year_dir, '*'))
            for month_dir in month_dirs:
                if os.path.isdir(month_dir):
                    month = os.path.basename(month_dir)
                    result[year][month] = {}
                    
                    # Get all day directories
                    day_dirs = glob.glob(os.path.join(month_dir, '*'))
                    for day_dir in day_dirs:
                        if os.path.isdir(day_dir):
                            day = os.path.basename(day_dir)
                            result[year][month][day] = day_dir
    
    return result

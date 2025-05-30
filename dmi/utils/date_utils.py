"""
Date utility functions for Newspress CMS Migration
"""
from datetime import datetime, timedelta

def parse_datetime(date_string):
    """Parse datetime string to datetime object"""
    if not date_string:
        return None
    
    try:
        return datetime.fromisoformat(date_string.replace('Z', '+00:00'))
    except ValueError:
        # Try different formats if ISO format fails
        formats = [
            '%Y-%m-%dT%H:%M:%S',
            '%Y-%m-%d %H:%M:%S',
            '%Y-%m-%d'
        ]
        
        for fmt in formats:
            try:
                return datetime.strptime(date_string, fmt)
            except ValueError:
                continue
        
        raise ValueError(f"Unable to parse date string: {date_string}")

def add_hours(dt, hours):
    """Add hours to datetime object"""
    if not dt:
        return None
    
    return dt + timedelta(hours=hours)

def format_date(dt, format_string):
    """Format datetime object to string"""
    if not dt:
        return None
    
    return dt.strftime(format_string)

def get_date_components(dt):
    """Get year, month, day components from datetime"""
    if not dt:
        return None, None, None
    
    return dt.year, dt.month, dt.day

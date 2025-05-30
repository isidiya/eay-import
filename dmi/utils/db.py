"""
Database utility functions for Newspress CMS Migration
"""
import mysql.connector
from mysql.connector import Error
from config.settings import DB_CONFIG

class DatabaseManager:
    def __init__(self):
        self.connection = None
        self.connect()
    
    def connect(self):
        """Establish database connection"""
        try:
            self.connection = mysql.connector.connect(**DB_CONFIG)
            print("MySQL connection established")
        except Error as e:
            print(f"Error connecting to MySQL: {e}")
            raise
    
    def execute_query(self, query, params=None, commit=False):
        """Execute a query with optional parameters"""
        cursor = self.connection.cursor(dictionary=True)
        try:
            cursor.execute(query, params or ())
            if commit:
                self.connection.commit()
            return cursor
        except Error as e:
            print(f"Error executing query: {e}")
            print(f"Query: {query}")
            print(f"Params: {params}")
            raise
    
    def fetch_all(self, query, params=None):
        """Execute query and fetch all results"""
        cursor = self.execute_query(query, params)
        result = cursor.fetchall()
        cursor.close()
        return result
    
    def fetch_one(self, query, params=None):
        """Execute query and fetch one result"""
        cursor = self.execute_query(query, params)
        result = cursor.fetchone()
        cursor.close()
        return result
    
    def insert(self, table, data):
        """Insert data into table and return last insert ID"""
        columns = ', '.join(data.keys())
        placeholders = ', '.join(['%s'] * len(data))
        query = f"INSERT INTO {table} ({columns}) VALUES ({placeholders})"
        
        cursor = self.execute_query(query, list(data.values()), commit=True)
        last_id = cursor.lastrowid
        cursor.close()
        return last_id
    
    def update(self, table, data, condition, condition_params):
        """Update table with data based on condition"""
        set_clause = ', '.join([f"{key} = %s" for key in data.keys()])
        query = f"UPDATE {table} SET {set_clause} WHERE {condition}"
        
        params = list(data.values()) + condition_params
        cursor = self.execute_query(query, params, commit=True)
        affected_rows = cursor.rowcount
        cursor.close()
        return affected_rows
    
    def clear_table(self, table):
        """Clear all data from a table"""
        query = f"TRUNCATE TABLE {table}"
        cursor = self.execute_query(query, commit=True)
        cursor.close()
    
    def close(self):
        """Close the database connection"""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            print("MySQL connection closed")

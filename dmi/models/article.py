"""
Article data models and operations for Newspress CMS Migration
"""
import json
from utils.db import DatabaseManager
from utils.date_utils import parse_datetime, add_hours, format_date
from utils.file_utils import read_json_file, get_file_extension

class ArticleManager:
    def __init__(self):
        self.db = DatabaseManager()
    
    def article_exists(self, permalink):
        """Check if article already exists in database"""
        query = "SELECT cms_article_id FROM article_archive WHERE permalink = %s LIMIT 1"
        result = self.db.fetch_one(query, [permalink])
        return result is not None
    
    def insert_article(self, article_data):
        """Insert article data into database"""
        # Insert main article record
        article_id = self.db.insert('article_archive', article_data)
        
        return article_id
    
    def insert_article_sections(self, article_id, sections):
        """Insert article sections"""
        for i, section in enumerate(sections):
            if i == 0 or (i < len(sections) - 1):
                section_data = {
                    'ams_article_id': article_id,
                    'section_name': section,
                    'sub_section_name': sections[i+1] if i+1 < len(sections) else '',
                    'ams_order': i + 1
                }
                self.db.insert('article_multi_section_archive', section_data)
    
    def insert_article_tags(self, article_id, tags):
        """Insert article tags"""
        for tag in tags:
            tag_data = {
                'cms_article_id': article_id,
                'tag': tag.strip()
            }
            self.db.insert('article_archive_tags', tag_data)
    
    def insert_article_images(self, article_id, images, publish_time):
        """Insert article images"""
        for image in images:
            image_data = {
                'np_image_id': 0,
                'np_related_article_id': article_id,
                'image_caption': image.get('byline', ''),
                'image_description': image.get('desc', ''),
                'image_path': image.get('link', ''),
                'media_type': 0 if image.get('imageType') == 'image' else 1,
                'image_custom_fields': json.dumps({k: v for k, v in image.items() 
                                                if k not in ['title', 'desc', 'link']})
            }
            self.db.insert('image_archive', image_data)
    
    def process_article_json(self, article_path):
        """Process article JSON file and insert into database"""
        # Read JSON file
        article_archive = read_json_file(article_path)
        if not article_archive:
            print(f"Failed to read or parse article JSON: {article_path}")
            return False
        
        print(f"Processing article ID: {article_archive.get('_id', 'Unknown')}")
        
        # Extract permalink and check if article exists
        permalink = article_archive.get('link', '').replace('https://www.emaratalyoum.com/', '')
        if self.article_exists(permalink):
            print(f"Article already exists with permalink: {permalink}")
            return True
        
        # Prepare article data
        article = {
            'old_article_id': article_archive.get('_id', ''),
            'np_article_id': 0,
            'article_title': article_archive.get('title', ''),
            'article_subtitle': article_archive.get('subTitle', ''),
            'seo_meta_description': article_archive.get('lead', ''),
            'article_headline': article_archive.get('shortTitle', ''),
            'permalink': permalink,
            'article_byline': article_archive.get('authorName', '')
        }
        
        # Handle author name
        if 'authors' in article_archive and article_archive['authors'] and 'title' in article_archive['authors'][0]:
            article['author_name'] = article_archive['authors'][0]['title']
        elif 'authorName' in article_archive:
            article['author_name'] = article_archive['authorName']
        else:
            article['author_name'] = ''
        
        # Handle tags
        if 'tags' in article_archive:
            if isinstance(article_archive['tags'], list):
                article['article_tags'] = ','.join(article_archive['tags'])
            else:
                article['article_tags'] = article_archive['tags']
        else:
            article['article_tags'] = ''
        
        # Handle dates
        article['publish_time'] = parse_datetime(article_archive.get('published', '')).isoformat() if article_archive.get('published') else None
        article['alt_publish_time'] = parse_datetime(article_archive.get('created', '')).isoformat() if article_archive.get('created') else None
        article['last_edited'] = parse_datetime(article_archive.get('modified', '')).isoformat() if article_archive.get('modified') else None
        
        # Handle article body
        article['article_body'] = article_archive.get('body', '')
        
        # Initialize section fields
        article['section_name'] = ''
        article['sub_section_name'] = ''
        article['image_path'] = ''
        
        # Handle custom fields
        article_custom_field = article_archive.copy()
        for field in ['title', 'subTitle', 'body', 'link', 'authorName', 'published', 
                     'created', 'modified', 'images', 'tags', 'shortTitle', 'lead', 'authors']:
            if field in article_custom_field:
                del article_custom_field[field]
        
        article['article_custom_fields'] = json.dumps(article_custom_field)
        
        # Process sections from permalink
        article_multi_sections = []
        permalink_array = permalink.split('/')
        
        # Query for section mappings
        if len(permalink_array) > 3:
            section_query = "SELECT * FROM section_mapping WHERE section = %s LIMIT 1"
            section = self.db.fetch_one(section_query, [permalink_array[3]])
            if section:
                article['section_name'] = section['arabic_name']
                article_multi_sections.append(section['arabic_name'])
        
        if len(permalink_array) > 4:
            subsection_query = "SELECT * FROM section_mapping WHERE sub_section = %s LIMIT 1"
            sub_section = self.db.fetch_one(subsection_query, [permalink_array[4]])
            if sub_section:
                article['sub_section_name'] = sub_section['arabic_name']
                article_multi_sections.append(sub_section['arabic_name'])
        
        if len(permalink_array) > 5:
            subsection2_query = "SELECT * FROM section_mapping WHERE sub_sub_section = %s LIMIT 1"
            sub_section_2 = self.db.fetch_one(subsection2_query, [permalink_array[5]])
            if sub_section_2:
                article['sub_section_name'] = sub_section_2['arabic_name']
                article_multi_sections.append(sub_section_2['arabic_name'])
        
        if len(permalink_array) > 6:
            subsection3_query = "SELECT * FROM section_mapping WHERE sub_sub_sub_section = %s LIMIT 1"
            sub_section_3 = self.db.fetch_one(subsection3_query, [permalink_array[6]])
            if sub_section_3:
                article['sub_section_name'] = sub_section_3['arabic_name']
                article_multi_sections.append(sub_section_3['arabic_name'])
        
        # Update permalink to match the original format
        article['permalink'] = article_archive.get('link', '').replace('https://www.albayan.ae/', '')
        
        # Process image path
        if 'topImages' in article_archive and article_archive['topImages']:
            image_path = {}
            
            if article_archive['topImages'][0]['imageType'] == 'image':
                image_path['media_type'] = 0
            
            if 'title' in article_archive['topImages'][0]:
                image_path['title'] = article_archive['topImages'][0]['title']
            
            if 'desc' in article_archive['topImages'][0]:
                image_path['desc'] = article_archive['topImages'][0]['desc']
            
            # Process image date and path
            image_date = parse_datetime(article_archive.get('published', ''))
            image_date = add_hours(image_date, 4)
            
            image_extension = get_file_extension(article_archive['topImages'][0]['link'])
            article_image_path = f"albayan/uploads/archives/images/{format_date(image_date, '%Y')}/{format_date(image_date, '%m')}/{format_date(image_date, '%d')}/{article_archive['topImages'][0]['id'].replace('1.', '')}.{image_extension}"
            
            image_path['image_path'] = article_image_path
            article['image_path'] = json.dumps(image_path)
            
        elif 'images' in article_archive and article_archive['images']:
            image_path = {}
            
            if article_archive['images'][0]['imageType'] == 'image':
                image_path['media_type'] = 0
            
            if 'title' in article_archive['images'][0]:
                image_path['title'] = article_archive['images'][0]['title']
            
            if 'desc' in article_archive['images'][0]:
                image_path['desc'] = article_archive['images'][0]['desc']
            
            # Process image date and path
            image_date = parse_datetime(article_archive.get('published', ''))
            image_date = add_hours(image_date, 4)
            
            image_extension = get_file_extension(article_archive['images'][0]['link'])
            article_image_path = f"albayan/uploads/archives/images/{format_date(image_date, '%Y')}/{format_date(image_date, '%m')}/{format_date(image_date, '%d')}/{article_archive['images'][0]['id'].replace('1.', '')}.{image_extension}"
            
            image_path['image_path'] = article_image_path
            article['image_path'] = json.dumps(image_path)
        
        # Insert article and get ID
        cms_article_id = self.insert_article(article)
        
        # Insert article sections
        self.insert_article_sections(cms_article_id, article_multi_sections)
        
        # Insert article images
        if 'images' in article_archive and article_archive['images']:
            self.insert_article_images(cms_article_id, article_archive['images'], article['publish_time'])
        
        # Insert article tags
        if 'tags' in article_archive and isinstance(article_archive['tags'], list):
            self.insert_article_tags(cms_article_id, article_archive['tags'])
        
        return True
    
    def update_image_paths(self):
        """Update image paths in database"""
        query = """
        SELECT 
            article_archive.cms_article_id,
            article_archive.old_article_id,
            article_archive.publish_time,
            article_archive.image_path as article_image_path,
            article_archive.article_custom_fields,
            image_archive.cms_image_id,
            image_archive.np_related_article_id,
            image_archive.image_path,
            image_archive.image_custom_fields
        FROM article_archive
        JOIN image_archive ON article_archive.cms_article_id = image_archive.np_related_article_id
        """
        
        results = self.db.fetch_all(query)
        
        for record in results:
            if record['image_custom_fields']:
                try:
                    image_custom_fields = json.loads(record['image_custom_fields'])
                    
                    # Parse and adjust publish time
                    publish_time = parse_datetime(record['publish_time'])
                    adjusted_time = add_hours(publish_time, 4)
                    
                    # Get file extension
                    extension = get_file_extension(record['image_path'])
                    
                    # Construct new path
                    year = format_date(adjusted_time, '%Y')
                    month = format_date(adjusted_time, '%m')
                    day = format_date(adjusted_time, '%d')
                    
                    image_id = str(image_custom_fields.get('id', '')).replace('1.', '')
                    
                    new_path = f"albayan/uploads/archives/images/{year}/{month}/{day}/{image_id}.{extension}"
                    
                    # Update database
                    self.db.update(
                        'image_archive',
                        {'image_path': new_path},
                        'cms_image_id = %s',
                        [record['cms_image_id']]
                    )
                    
                    print(f"Updated image path for ID {record['cms_image_id']}")
                    
                except Exception as e:
                    print(f"Error updating image path for ID {record['cms_image_id']}: {e}")
    
    def clear_tables(self, tables):
        """Clear specified tables"""
        for table in tables:
            try:
                self.db.clear_table(table)
                print(f"Cleared table: {table}")
            except Exception as e:
                print(f"Error clearing table {table}: {e}")
    
    def close(self):
        """Close database connection"""
        self.db.close()

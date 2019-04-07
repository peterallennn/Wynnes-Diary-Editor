# The idea behind this script is to migrate the old Wynne's Diary HTML and media over to Wordpress with ease.

import json, os, codecs, shutil, pyperclip

from bs4 import BeautifulSoup
from pathlib import Path   

def read_html(file, open=True):
	if open:
		f = codecs.open(file)
		html = f.read()
	else:
		html = file

	return BeautifulSoup(html, 'html.parser')

def strip_tags(html):
	if type(html) is not str:
		# If the variable is already a BeautifulSoup instance, then convert to a string
		# This is happening as I haven't been able to find away to strip the tags that were used as part of the initial 'FindAll' parameter
		# By recreating an instance of BeautifulSoup, we are able to remove all the tags necessary.
		# I'm sure there's a better way to do this...
		html = str(html)

	# Create an instance of BeautifulSoup
	html = read_html(html, False)

	invalid_tags = ['font', 'tr', 'td', 'i', 'table', 'br', 'b', 'div']

	for tag in invalid_tags:
		for match in html.findAll(tag):
			#print(match)
			#print('!' * 20)
			match.replaceWithChildren()

	return html

def move_media(old_file, old_dir, new_dir):
	file_src = str(old_file['src'])

	old_file_path = str(os.path.abspath(old_dir + file_src)).replace('\\', '/').replace('%20', ' ')

	print(old_file_path)
	print(file_src)

	file_name = Path(old_file_path).name
	
	if '../' in file_src:
		# The absolute positioning suggests a generic file that is used across the website
		file_src = file_src.replace('../', '').replace('%20', ' ')
		new_dir = MEDIA_DIR + '/' + file_src.replace(file_name, '')
	
	new_file_path = new_dir + '/' + file_name

	# Create new year/month directories within MEDIA_DIR if doesn't exit
	try:
	    os.makedirs(new_dir)
	except FileExistsError:
	    # Directory already exists
	    pass

	# Copy the image into our media folder
	shutil.copyfile(old_file_path, new_file_path)

	return new_file_path

OLD = 'C:/wamp64/www/Wynne\'s Diary/Old Wynne\'s Diary/'
MEDIA_DIR = os.path.dirname(os.path.abspath(__file__)) + '/media'
#MONTHS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december']
MONTHS = ['december']

# We will store the relevant data in this dict variable
data = {}

# Scan the OLD directory
for file in os.listdir(OLD):
	# Find all year directories with naming convention of "{year}_pages", these are the directories that will need to be migrated
	## Store the year in the JSON 
	#if file.endswith('_pages') and 'section' not in file and 'blurb' not in file and '.' not in file:
	if '1895' in file:
		year = file.split('_')[0]
		year_dir = OLD + year + '_pages'
		data[year] = {}

		# Then loop through all the directories within the year
		## Store the month within the JSON beneath the parent year
		for month in os.listdir(year_dir):
			# Only get the directories that are months
			if any(s in month for s in MONTHS) and '.' not in month:
				data[year][month] = {}
				data[year][month]['viewers'] = {}
				month_dir = year_dir + '/' + month

				new_file_dir = MEDIA_DIR + '/' + year + '/' + month
				old_file_dir = OLD + year + '_pages' + '/' + month + '/'

				for file in os.listdir(month_dir):
					# Parse the 'strip.html' file, this is the timeline view for that year
					if 'strip.html' in file:
						strip_html = read_html(month_dir + '/' + file)
						
						# Get all of tables with a width of 230, these tables contain the summary data for each viewer
						strip = strip_html.find_all('table', {'width': '230'})
						
						v = 0
						for viewer in strip:		
							#print(viewer)	
							#print('!' * 20)		

							# Delete all uneccessary tags to make cleaner
							viewer_cleaned = strip_tags(viewer)

							if v == 0:
								# The first viewer is actually the description for the month
								data[year][month]['description'] = str(viewer_cleaned)
							else:
								viewer_id = 'viewer_' + str(v)

								data[year][month]['viewers'][viewer_id] = {}
								viewer_dict = data[year][month]['viewers'][viewer_id]

								if viewer_cleaned.find('img'):
									# The viewer actually uses a featured image, store this instead
									img_src = viewer_cleaned.find('img')
									new_img_path = move_media(img_src, old_file_dir, new_file_dir)

									viewer_dict['featured_image'] = new_img_path
								else:
									# Get the post title
									post_title = viewer.select('td > p')[1].get_text(strip=True)
									viewer_dict['title'] = str(post_title)

									# Store the excerpt data
									viewer_dict['excerpt'] = str(viewer_cleaned)

								# Now open the full viewer html
								viewer_html = read_html(old_file_dir + viewer_id + '.html')

								# Remove all the relevant tags from within the <td> with class of viewerbody
								# This will become our description in WordPress
								description = viewer_html.find('td', {'class': 'viewerbody'})

								# Get all the images within the description
								for img in description.findAll('img'):
									new_img_path = move_media(img, old_file_dir, new_file_dir)
									print('!' * 20)

								# Get the audio and convert from flash to HTML5
								
								viewer_dict['description'] = str(strip_tags(description))

							# Increment index count
							v += 1

							#print(viewer)
							#print('!' * 20)

					

					# Iterate through the first table element with class of "centre_strip"

					# Get the month description html

					# Get each individual post's html
					## Distinguish whether it has an image or not, if so use it as a featured image
					## Get the post title
					## Get the exceprt

					# Loop through the month's viewer_*.html files 

					# Iterate through the viewer file and look for the 'viewerbody' td, use all of this data as the description
					## Need to find the images and change the url structure to something that works with Wordpress,

json_data = json.dumps(data)
#print(json_data)
pyperclip.copy(json_data)
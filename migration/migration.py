# The idea behind this script is to migrate the old Wynne's Diary HTML and media over to Wordpress with ease.

# TODO
# Augut 1916, the month description isn't being found correctly. Seems to be an issue in html, also other months in same year
# Finish the mp3 migration (need to convert to html5)

import json, os, codecs, shutil, pyperclip, urllib

from bs4 import BeautifulSoup
from pathlib import Path   
from htmllaundry import sanitize

def read_html(file, open=True):
	if open:
		f = codecs.open(file)
		html = f.read()
	else:
		html = file

	return BeautifulSoup(html, 'html.parser')

def strip_tags(html, remove_empty=False):
	if type(html) is not str:
		# If the variable is already a BeautifulSoup instance, then convert to a string
		# This is happening as I haven't been able to find away to strip the tags that were used as part of the initial 'FindAll' parameter
		# By recreating an instance of BeautifulSoup, we are able to remove all the tags necessary.
		# I'm sure there's a better way to do this...
		html = str(html)

	# Create an instance of BeautifulSoup
	html = read_html(html, False)

	invalid_tags = ['font', 'tr', 'td', 'i', 'table', 'br', 'b', 'div', 'strong']

	for tag in invalid_tags:
		for match in html.findAll(tag):
			#print(match)
			#print('!' * 20)
			match.replaceWithChildren()

	# Remove all empty tags
	if remove_empty:
		for p in html.findAll('p'):
			if str(p) == '<p> </p>':
				p.decompose()
	return html

def move_media(old_file, old_dir, new_dir):
	if type(old_file) is not str:
		file_src = str(old_file['src'])
	else:
		file_src = old_file

	old_file_path = urllib.parse.unquote(str(os.path.abspath(old_dir + file_src)).replace('\\', '/'))

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
	try:
		shutil.copyfile(old_file_path, new_file_path)

		new_url = new_file_path.replace(MEDIA_DIR, WEB_DIR)

		return {
			'new_path': new_file_path,
			'new_url': new_url
		}
	except FileNotFoundError as e:
		print(e)
		return {
			'error': e
		}


OLD = 'C:/wamp64/www/Wynne\'s Diary/Old Wynne\'s Diary/'
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MEDIA_DIR = BASE_DIR + '/media'
WEB_DIR = '//wynnesdiary.com/wp-content/uploads/migration'
#MONTHS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december']
MONTHS = ['august']

# We will store the relevant data in this dict variable
data = {}

# Scan the OLD directory
for file in os.listdir(OLD):
	# Find all year directories with naming convention of "{year}_pages", these are the directories that will need to be migrated
	## Store the year in the JSON 
	#if file.endswith('_pages') and 'section' not in file and 'blurb' not in file and '.' not in file:
	if '1916' in file:
		year = file.split('_')[0]
		year_dir = OLD + year + '_pages'
		data[year] = {}

		# Then loop through all the directories within the year
		## Store the month within the JSON beneath the parent year
		for month in os.listdir(year_dir):
			# Only get the directories that are months
			if any(s in month for s in MONTHS) and '.' not in month:
				new_file_dir = MEDIA_DIR + '/' + year + '/' + month
				old_file_dir = OLD + year + '_pages' + '/' + month + '/'

				data[year][month] = {}
				data[year][month]['viewers'] = {}
				data[year][month]['errors'] = []
				month_dir = year_dir + '/' + month

				# Some months have been created as placeholders, i.e. there's no actual content in them
				# Pete has seemingly set up the templates for the future.
				# The common theme is when a month has only been setup as a placeholder, there's no imagery.
				has_images = False

				for file in os.listdir(old_file_dir):
					if file.endswith('.jpg') or file.endswith('.png'):
						has_images = True

				if has_images == False:
					data[year][month]['errors'].append('Seems to be a placeholder with no content.')
					data[year][month]['placeholder'] = True
					continue

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
								# Clean the year/month headings
								for tag in viewer_cleaned.findAll():
									text = str(tag.get_text).lower()

									if year in text or month in text:
										tag.decompose()

								print(viewer_cleaned)
								data[year][month]['description'] = str(strip_tags(viewer_cleaned, True))
							else:
								viewer_id = 'viewer_' + str(v)

								data[year][month]['viewers'][viewer_id] = {}
								viewer_dict = data[year][month]['viewers'][viewer_id]

								viewer_dict['order'] = v
 
								if viewer_cleaned.find('img'):
									# The viewer actually uses a featured image, store this instead
									img_src = viewer_cleaned.find('img')
									new_img_path = move_media(img_src, old_file_dir, new_file_dir)

									if('error' in new_img_path):
										data[year][month]['errors'].append('Featured image for ' + viewer_id + ' not found.')
									else:
										viewer_dict['featured_image'] = new_img_path['new_url']
									
									viewer_dict['title'] = 'Viewer ' + str(v) # Just set a default title for the moment
								else:
									# Get the post title
									title = viewer.select('td > p')
									
									for paragraph in title:
										if str(paragraph) != '<p> </p>':
											# Skip all of the empty paragraph tags
											# The first instance of no empty tags will be the post title.
											post_title = strip_tags(paragraph)

											break;

									viewer_dict['title'] = str(post_title)

									# Store the excerpt data
									viewer_dict['excerpt'] = str(viewer_cleaned)

								# Now open the full viewer html
								try:
									viewer_html = read_html(old_file_dir + viewer_id + '.html')
								except FileNotFoundError as e:
									# There have been instances where the _ isn't included, double check this
									viewer_dict['error'] = 'Could not find viewer html.'
									continue									

								# Remove all the relevant tags from within the <td> with class of viewerbody
								# This will become our description in WordPress
								description = viewer_html.find('td', {'class': 'viewerbody'})

								# Get all the images within the description
								for img in description.findAll('img'):
									new_img_path = move_media(img, old_file_dir, new_file_dir)

									if('error' in new_img_path):
										data[year][month]['errors'].append('Image for ' + viewer_id + ' not found.')
									else:
										img['src'] = new_img_path['new_url']

								# Find the anchor tag
								# There are some instances where there is a hover effect applied to an image using MM_swapImg.
								# This contains an image within the function that also needs to be copied across.
								anchors = description.findAll('a', {'onmouseout': 'MM_swapImgRestore()'})

								if anchors:
									for anchor in anchors:
										print(anchor['onmouseover'])
										# Example: <a href="viewer_3.html" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image3','','Brookfield_mod2_ext.jpg',1)">
										anchor_mouseover = anchor['onmouseover'].split("'','")
										anchor_mouseover_img = str(anchor_mouseover[1]).split("'")
										img = anchor_mouseover_img[0]
										
										new_image_path = move_media(img, old_file_dir, new_file_dir)

										if('error' in new_image_path):
											data[year][month]['errors'].append('Mouseover image for ' + viewer_id + ' not found.')
										else:
											# Now update the attribute within the anchor
											anchor_mouseover_img[0] = new_image_path['new_url']
											anchor_mouseover[1] = anchor_mouseover_img[0] + '\'' + anchor_mouseover_img[1]

											anchor['onmouseover'] = "'','".join(anchor_mouseover)

								# Get the audio and convert from flash to HTML5
								audio_objects = description.findAll('object')

								if audio_objects:
									for audio_object in audio_objects:
										# Example: <object data="../../mp3/zplayer.swf?mp3=../../mp3/YSO2_Parade.mp3&amp;c1=420000" height="20" type="application/x-shockwave-flash" width="200"></object>
										mp3_split = str(audio_object).split('?mp3=')
										mp3 = str(mp3_split[1]).split('&')[0]

										new_mp3_path = move_media(mp3, old_file_dir, new_file_dir)

										audio_object.replace_with('<audio control><source src="{}" type="audio/mpeg"></audio>'.format(new_mp3_path['new_url']))
										
								
								description = str(strip_tags(description, True))

								viewer_dict['description'] = description

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

with open('data.json', 'w') as outfile:  
    json.dump(data, outfile)

#print(json_data)
pyperclip.copy(json_data)
# The idea behind this script is to migrate the old Wynne's Diary HTML and media over to Wordpress with ease.



# TODO

# Improve the headings on the strip and add spacing

# Implement the search functionality

# Change how the reordering changes the viewer page title

# Maybe add a custom field to indicate it was a migrated post?

# Fix the bottom of the content for a post

# Implement the side links

# Add the additional pages



import json, os, re, codecs, glob, shutil, pyperclip, urllib



from bs4 import BeautifulSoup, Comment

from pathlib import Path   

from htmllaundry import sanitize



def read_html(file, open=True):

	if open:

		f = codecs.open(file)

		html = f.read()

	else:

		html = file



	return BeautifulSoup(html, 'lxml')



def strip_tags(html, remove_empty=False, remove_br=False, remove_table=False, remove_bold=False, remove_italic=False):

	if type(html) is not str:

		# If the variable is already a BeautifulSoup instance, then convert to a string

		# This is happening as I haven't been able to find away to strip the tags that were used as part of the initial 'FindAll' parameter

		# By recreating an instance of BeautifulSoup, we are able to remove all the tags necessary.

		# I'm sure there's a better way to do this...

		html = str(html)



	# Create an instance of BeautifulSoup

	html = read_html(html, False)



	invalid_tags = ['html', 'body', 'font', 'div']



	if remove_table:

		invalid_tags.append(['table', 'tr', 'td'])



	if remove_br:

		invalid_tags.append(['br'])



	if remove_bold:

		invalid_tags.append(['b', 'strong'])



	if remove_italic:

		invalid_tags.append(['i'])



	for tag in invalid_tags:

		for match in html.findAll(tag):

			#print(match)

			#print('!' * 20)

			match.replaceWithChildren()



	# Remove comments

	comments = html.findAll(text=lambda text:isinstance(text, Comment))

	[comment.extract() for comment in comments]



	# Remove all empty tags

	if remove_empty:

		# Also remove classes & alignments

		for tag in html.findAll(True):

			if 'class' in tag.attrs: 

				del tag.attrs['class']



			if 'align' in tag.attrs: 

				del tag.attrs['align']



		for p in html.findAll('p'):

			if str(p) == '<p> </p>' or str(p) == '<p>&nbsp;</p>' or str(p) == '<p></p>':

				p.decompose()



		empty_tags = html.findAll(lambda tag: tag.name == 'p' and not tag.contents and (tag.string is None or not tag.string.strip()))

		[empty_tag.extract() for empty_tag in empty_tags]



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

MONTHS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december']

#MONTHS = ['december']



# We will store the relevant data in this dict variable

data = {}



# Scan the OLD directory

for file in os.listdir(OLD):

	# Find all year directories with naming convention of "{year}_pages", these are the directories that will need to be migrated

	## Store the year in the JSON 

	if file.endswith('_pages') and 'section' not in file and 'blurb' not in file and '.' not in file:

	#if '1896' in file:

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

				viewer_count = 0

				viewer_default_count = 0



				#print(month + ' ' + year)



				for file in os.listdir(old_file_dir):

					if file.endswith('.jpg') or file.endswith('.png'):

						has_images = True

					else:

						# This suggests that it's a placeholder month

						# there are some cases where this is wrong so to be sure, let's loop through each viewer file and double check

						if 'viewer' in file:

							viewer_count += 1



							viewer = read_html(month_dir + '/' + file)

							content = viewer.find('td', {'class': 'viewerbody'})



							if 'Page Viewer Content' in str(content.get_text()):

								viewer_default_count += 1



				#print('viewer_count: ' + str(viewer_count))

				#print('viewer_default_count: ' + str(viewer_default_count))



				if has_images == False and viewer_default_count == viewer_count:

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

								if viewer_cleaned.find('span', text=re.compile(month.upper())):

									viewer_cleaned.find('span', text=re.compile(month.upper())).decompose()

								elif viewer_cleaned.find('p', text=re.compile(month.upper())):

									viewer_cleaned.find('p', text=re.compile(month.upper())).decompose()



								if viewer_cleaned.findAll('p', {'class': 'red'}):

									for p in viewer_cleaned.findAll('p', {'class': 'red'}):

										p.decompose()



								viewer_cleaned = str(viewer_cleaned).replace(year, '')



								data[year][month]['description'] = str(strip_tags(viewer_cleaned, True, True, True, True, True))

							else:

								viewer_id = 'viewer_' + str(v)



								data[year][month]['viewers'][viewer_id] = {}

								viewer_dict = data[year][month]['viewers'][viewer_id]



								viewer_dict['order'] = v



								viewer_dict['title'] = 'Viewer ' + str(v) # Just set a default title for the post title

 

								if viewer_cleaned.find('img'):

									# The viewer actually uses a featured image, store this instead

									img_src = viewer_cleaned.find('img')

									new_img_path = move_media(img_src, old_file_dir, new_file_dir)



									if('error' in new_img_path):

										data[year][month]['errors'].append('Featured image for ' + viewer_id + ' not found.')

									else:

										viewer_dict['featured_image'] = new_img_path['new_url']

									

									

								else:

									# Get the post title

									#title = viewer.select('td > p')

									

									#for paragraph in title:

										#if str(paragraph) != '<p> </p>':

											# Skip all of the empty paragraph tags

											# The first instance of no empty tags will be the post title.

											#title = strip_tags(paragraph)



											#break;



									#viewer_dict['title'] = str(title)



									# Store the excerpt data

									viewer_dict['excerpt'] = str(strip_tags(viewer_cleaned, True, True, True, True, True))



								# Now open the full viewer html

								try:

									viewer_html = read_html(old_file_dir + viewer_id + '.html')

								except FileNotFoundError as e:

									# There have been instances where the _ isn't included, double check this

									data[year][month]['errors'].append('Could not find viewer html.')

									continue									



								# Remove all the relevant tags from within the <td> with class of viewerbody

								# This will become our description in WordPress



								description = viewer_html.find('td', {'class': 'viewerbody'})



								description = str(description)

								description = description.replace(year, '', 1)

								description = description.replace(month.upper(), '', 1)

								description = read_html(description, False)





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



								# Remove all 'param' tags

								for param in description.findAll('param'):

									param.decompose()





								description = str(strip_tags(description, False))



								if audio_objects:

									for audio_object in audio_objects:

										# Example: <object data="../../mp3/zplayer.swf?mp3=../../mp3/YSO2_Parade.mp3&amp;c1=420000" height="20" type="application/x-shockwave-flash" width="200"></object>

										mp3_split = str(audio_object).split('?mp3=')

										mp3 = str(mp3_split[1]).split('&')[0]



										new_mp3_path = move_media(mp3, old_file_dir, new_file_dir)



										if('error' in new_image_path):

											data[year][month]['errors'].append('MP3 for ' + viewer_id + ' not found.')

										else:



											new_audio_object = '<audio controls><source src="{}" type="audio/mpeg"></audio>'.format(new_mp3_path['new_url'])

											

											description = description.replace(str(audio_object), new_audio_object)

																		



								viewer_dict['description'] = str(strip_tags(description, True, False))



							# Increment index count

							v += 1





						# I have come across situations whereby the strip doesn't have properly formatted TD elements

						# i.e. doesn't contain the crucial table width=230 element that almost all have.

						# To detect this sitation, create a count of the viewer-X.html files in the month directory and confirm with the for count

						# Minus 1 to remove strip.html

						dir_viewer_count = tifCounter = int(len(glob.glob1(old_file_dir, "*.html")) - 1)



						# Minus 1 to exclude the month description

						v = (v - 1)



						if(dir_viewer_count > v):

							data[year][month]['errors'].append('There are more viewers available (' + str(v) + ' of ' + str(dir_viewer_count) + ' found). The migration script cannot migrate all of these likely due to an inconsistency in the old HTML.')



						if data[year][month]['errors']:

							print(month + ', ' + year)

							print('')

							print('')



							for error in data[year][month]['errors']:

								print(error)

								print('')



							print('!' * 20)

							print('')

							print('')



json_data = json.dumps(data)



with open('data.json', 'w') as outfile:  

    json.dump(data, outfile)



#print(json_data)

pyperclip.copy(json_data)
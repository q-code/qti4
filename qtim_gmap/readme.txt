This folder contains a config_gmap.php file and images

-------------------
A) About config_gmap.php
-------------------

This file contains the preferences (permission and symbol) for each section.

--> The file must be writeable (chmod777).


-------------------
B) About images
-------------------

The images are the symbol markers displayed on top of the Google map.
You can add other images (or change existing images) in this folders.

--> The images must be in png format and of size 32x32 pixels.
--> The image filenames must be in lowercase.

To have a shadow under the markers, add in this folder a shadow image.

Example:
pushpin_blue.png

-------------------
C) About PRINT images (gif)
-------------------

It's known that browsers cannot print png transparancy correctly.
That's why Google offers to use alternate printable image.
This image is not mandatory, but if the file exists, it will be use while printing.

--> You can include in this folder image (gif) using the same filename.
--> The size of the gif image must be the same as the png image (32x32 pixels)

Example:
pushpin_blue.png
pushpin_blue.gif

# Dorm-Cafeteria-Booking-Webapp

This project is a web app for booking meals. It also processes the pdf of a specific type of menu, and outputs a json with all the dish data in the menu, and uploads it onto the website's database.

There are 2 main folders and the SQL file for the database.

### Recommended to set up a mariadb or mysql database first with the provided .sql file

### The website folder contains all of the website code that you can throw into whatever web server app you want to use

The vendor folder is necessary for generating pdfs using tcpdf

### The processing folder is where all of the Python code is located. Going in order of operations

- The menu pdfs can be split into png using the preprocessing.py
- Put the pngs in the menu folder with the correct year
- Run main.py and look in the output folder for the data.json file
- Use loader.py to parse and insert the dishes in the menu onto the database

The main.ipynb file is there for debugging or to help with making improvements to the OCR. <br/>
The ttf font file is used for visualizing what has been read from the menu. <br/>
The sampledata.json is what an ideal output would look like, from main.py (but currently, main.py is unable to do that)

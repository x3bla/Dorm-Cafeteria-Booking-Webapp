# Dorm-Cafeteria-Booking-Webapp

There are 2 main folders, and the sql file for the database.

### Recommended to set up a mariadb or mysql database first with the provided .sql file

### The website folder is all the php code that you can throw into whatever web server app you want to use

### The processing folder is where all of the python code is located. Going in order of operations

- The menu pdfs can be split into png using the preprocessing.py
- Put the pngs in the menu folder with the correct year
- Run main.py and look in the output folder for the data.json file
- Use loader.py to parse and insert the dishes in the menu onto the database

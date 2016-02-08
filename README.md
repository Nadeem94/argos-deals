# Argos Graduate Exercise - Nadeem Ahmed
I have implemented this webpage using Codeigniter, a PHP framework which uses MVC principles. The web page fetches the ten most hottest products from Argos using the Hot UK Deals API, and compares them with prices fetched from Amazon of a similar product. The deals are then re ordered from 1-10 by best price difference between Argos and Amazon. This is done using a JSON API, with Ajax calls and jQuery to fetch and display the data dynamically.
## How to execute the project:
I used XAMPP to test and work with my project locally: https://www.apachefriends.org/index.html

To run this web page, XAMPP needs to be installed. The codeigniter files in this repository needs to be pasted into a folder called 'codeigniter' in the file path: C:\xampp\htdocs. After this, open up XAMPP Control Panel and Start the 'Apache' module from it.

The web page can now be accessed by entering following url in a browser (preferably chrome/firefox):
##### localhost/codeigniter/index.php/argos_controller/homePage

The page may take up to 3-5 seconds to initially load the data. If the page does not load it may be because port 80 is in use. If this is the case, the config files needs to be changed in the XAMPP Control Panel. This brief video helps in explaining how to do this: https://www.youtube.com/watch?v=AWtL1tSQVMk

If the config files have been changed using the video linked above, the web page can be accessed using:
##### localhost:8080/codeigniter/index.php/argos_controller/homePage

### Additional features:
  - Amazon price and Link added to available argos deals.
  - Bootstrap used for design.
  - Pagination added, to navigate through each deal.
  - Refresh button added to reload deal data.
  - Dynamic display of deals through drop down and pagination.

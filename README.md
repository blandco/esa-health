# ESA Health :love_letter:

### A simple web page to monitor IronPort ESA health statistics. 

The HTML was built using the Bootstrap3 framework. The PHP code uses a cURL session to query the ESA ASyncOS API for live and historical appliance statistics.

> ![Screenshot](https://github.com/blandco/esa-health/blob/master/esa-dashboard-gif.gif)

* [Screenshot - Live Stats](https://github.com/blandco/esa-health/blob/master/esa-dashboard-live.png)

* [Screenshot - Historical Stats](https://github.com/blandco/esa-health/blob/master/esa-dashboard-hist.png)

### Requirements

* Web server running PHP
* ESA API access

### Instructions

1. Copy esa.php to your webserver.
2. Edit the variables in esa.php with your ESA hostnames and credentials.
3. Open http://www.yourserver.com/esa.php in your browser.

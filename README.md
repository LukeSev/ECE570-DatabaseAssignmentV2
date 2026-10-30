# ECE570-DatabaseAssignmentV2

Code for all webpages implemented/utilized in Part 2 of the assignment are all in public_html.

## Quick Key:
- sensor_display_data.php - Main page that displays sensor data (tables, chart, stats)
- upload_sensor_data.php - Responsible for taking in Base64-encoded queries, decoding them, and uploading them to the sensor_data database (if everything is formatted properly)
- Chartjs/app.js - The script to display the chart of sensor data
- Chartjs/data.php - Loading/Processing the data from the database, for use by app.js to create the chart later

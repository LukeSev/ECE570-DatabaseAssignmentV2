-- Table to register sensors
CREATE TABLE sensor_register (
    node_name VARCHAR(10) PRIMARY KEY,
    manufacturer VARCHAR(10),
    longitude DECIMAL(15, 12), -- Max longitude can have 15 digits, with 3 before decimal (180 degrees)
    latitude DECIMAL(15, 12) -- Max latitude can have 15 digits, with 3 before decimal (180 degrees)
);

-- Table for Sensor Data
CREATE TABLE sensor_data (
    node_name VARCHAR(10) NOT NULL,
    time_received DATETIME NOT NULL,
    temperature DECIMAL(6, 3) CHECK (temperature >= -10 AND temperature <= 100),
    humidity DECIMAL(6,3) CHECK (humidity >= 0 AND humidity <= 100),
    FOREIGN KEY (node_name) REFERENCES sensor_register(node_name) -- Only allow sensor data from sensors registered in sensor_register table
);



-- Registering sensors
INSERT INTO sensor_register (node_name, manufacturer, longitude, latitude) 
VALUES 
('node_1','Siemens', 38.547249, -122.816780),
('node_2','Exxelia', 10.823099, 106.629662),
('node_3','Bosch', 37.774929, -122.419418),
('node_4','Siemens', 38.941631, -119.977219),
('node_5','Exxelia', 11.580230, 108.074181);



-- Creating Sensor Data
INSERT INTO sensor_data (node_name, time_received, temperature, humidity) 
VALUES 
-- Node 1 (Windsor, CA)
('node_1','2022-10-01 11:00:00',78,45),
('node_1','2022-10-01 11:30:00',83,50),
('node_1','2022-10-01 12:00:00',85,48),
('node_1','2022-10-01 12:30:00',87,55),
('node_1','2022-10-01 01:00:00',92,53),

-- Node 2 (Ho Chi Minh City, Vietnam)
('node_2','2022-10-01 11:00:00',85,85),
('node_2','2022-10-01 11:30:00',88,88),
('node_2','2022-10-01 12:00:00',91,82),
('node_2','2022-10-01 12:30:00',94,87),
('node_2','2022-10-01 01:00:00',95,90),

-- Node 3 (San Francisco, CA)
('node_3','2022-10-01 11:00:00',40,58),
('node_3','2022-10-01 11:30:00',43,66),
('node_3','2022-10-01 12:00:00',46,67),
('node_3','2022-10-01 12:30:00',48,59),
('node_3','2022-10-01 01:00:00',52,62),

-- Node 4 (South Lake Tahoe, CA)
('node_4','2022-10-01 11:00:00',55,33),
('node_4','2022-10-01 11:30:00',56,36),
('node_4','2022-10-01 12:00:00',59,34),
('node_4','2022-10-01 12:30:00',61,38),
('node_4','2022-10-01 01:00:00',62,32),

-- Node 5 (Di Linh, Vietnam)
('node_5','2022-10-01 11:00:00',80,89),
('node_5','2022-10-01 11:30:00',80,88),
('node_5','2022-10-01 12:00:00',78,90),
('node_5','2022-10-01 12:30:00',77,92),
('node_5','2022-10-01 01:00:00',78,87);



-- Create combined view with all sensor information next to every reading
CREATE VIEW sensor_combined AS SELECT
  sensor_register.node_name,
  sensor_register.manufacturer,
  sensor_register.longitude,
  sensor_register.latitude,
  sensor_data.time_received,
  sensor_data.temperature,
  sensor_data.humidity
FROM
  sensor_register,
  sensor_data
WHERE
  sensor_register.node_name = sensor_data.node_name;



-- Try to insert sensor data for un-registered sensor (fails)
INSERT INTO sensor_data(node_name, time_received, temperature, humidity)
VALUES
('node_6', '2022-10-02', 56, 59);

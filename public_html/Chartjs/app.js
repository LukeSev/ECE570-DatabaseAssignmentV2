$(document).ready(function(){
  $.ajax({
    url: "https://lucasloughner.site/Chartjs/data.php", // location of the datafile
    method: "GET",
    success: function(data) {
      console.log(data);
      var x_axis = {}; 
      var y_axis = {};
      var humidities = {};

      for(var i in data) {
        if(Object.hasOwn(x_axis, data[i].node_name)) {
            x_axis[data[i].node_name].push(data[i].time_received);
        } else {
            x_axis[data[i].node_name] = [data[i].time_received];
        }
        if(Object.hasOwn(y_axis, data[i].node_name)) {
            y_axis[data[i].node_name].push(data[i].temperature);
        } else {
            y_axis[data[i].node_name] = [data[i].temperature];
        }
        if(Object.hasOwn(humidities, data[i].node_name)) {
            humidities[data[i].node_name].push(data[i].humidity);
        } else {
            humidities[data[i].node_name]= [data[i].humidity];
        }
      }

      var chartdata = {
        // Initially display node_1 as the default
        labels: x_axis['node_1'],
        datasets : [
          {
            label: 'node_1', //Title
            // Change colors: https://www.w3schools.com/css/tryit.asp?filename=trycss3_color_rgba 
            backgroundColor: 'rgba(36, 239, 39, 0.8)', 
            borderColor: 'rgba(36, 239, 39, 0.8)', 
            hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
            hoverBorderColor: 'rgba(200, 200, 200, 1)',
            data: y_axis['node_1']
          }
        ]
      };

      var ctx = $("#myChart");

      var barGraph = new Chart(ctx, {
        type: 'line',   // Change back to 'bar' for bar graph
        data: chartdata,
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Time",
                        color: '#333',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: "Temperature",
                        color: '#333',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        }
      });
      
      const dropdown = document.getElementById('nodeSelect');
      dropdown.addEventListener('change', function() {
          const selectedValue = this.value;
          const selectedText = this.options[this.selectedIndex].text;
          
          const temps = y_axis[selectedValue];
          const hums = humidities[selectedValue];
          console.log(temps);
          console.log(hums);
          
          barGraph.data.labels = x_axis[selectedValue];
          barGraph.data.datasets[0].data = y_axis[selectedValue];
          barGraph.data.datasets[0].label = selectedText;
          
          barGraph.update();
          
          // Calculate stats for each node, then update accordingly
          // Average Temp
          let sum = 0;
          for(let i = 0; i < temps.length; i++) {
              sum += Number(temps[i]);
          }
          let avgTemp = sum/temps.length;
          console.log(avgTemp);
          
          sum = 0;
          for(let i = 0; i < hums.length; i++) {
              sum += Number(hums[i]);
          }
          let avgHum = sum/hums.length;
          console.log(avgHum);
          
          document.getElementById("avgTemp").textContent = 'Average Temperature: ' + avgTemp.toFixed(2);
          document.getElementById("avgHum").textContent = 'Average Humidity: '+ avgHum.toFixed(2) + '%';
          document.getElementById("maxTemp").textContent = 'Maximum Temperature: ' + Math.max(...temps);
          document.getElementById("minHum").textContent = 'Minimum Humidity: ' + Math.min(...hums) + '%';
      });
      
    },
    error: function(data) {
      console.log(data);
    }
  });
});

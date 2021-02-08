<?php
$mysql = new mysqli("localhost", "raphi", "Luusbueb#02", "CO2");
$mysql->set_charset("utf8");

$stmt = $mysql->prepare("SELECT `time`, `temp`, `hum`, `co2` FROM `sensor_data` WHERE `dev_id` = ? ORDER BY `time` ASC");

$dev_id = htmlentities(@$_GET['id'], ENT_QUOTES);
$stmt->bind_param("s", $dev_id);
$stmt->bind_result($time, $temp, $hum, $co2);
$stmt->execute();

//Save data into array
while ($stmt->fetch()) {
    $sensorData[] = array("time" => date('d M H:i', strtotime($time)), "temp" => $temp, "hum" => $hum, "co2" => $co2);
}
if (!isset($sensorData)) {
    $sensorData[] = array("time" => "noData", "temp" => "noData", "hum" => "noData", "co2" => "noData");
}

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="icon" type="image/png" href="/favicon.png" sizes="32x32">
    <title>CO2</title>
    <link href="css/co2.css?<?Php echo time(); ?>" rel="stylesheet">
</head>

<body class="text-center">

<div class="d-flex h-100 p-3 mx-auto flex-column">
    <header class="masthead mb-auto">
        <div class="inner">
            <a href="/devices" class="btn back masthead-brand" style="color: white">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                          d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </a>
            <h3 class="masthead-brand">CO2</h3>
        </div>
    </header>
    <main role="main" class="inner">
        <h2 class="cover-heading" onclick="showAirQualityChart()"><?Php echo end($sensorData)['time'] ?></h2>
        <canvas id="chart" class="chart"></canvas>
        <canvas id="chartAirQuality" class="airQualityChart"></canvas>
        <div class="card-group" id="cardGroup">
            <div class="card cardShort rounded-3
            <?Php
            if (end($sensorData)['co2'] > 1000)
                echo "bg-danger";
            elseif (end($sensorData)['co2'] > 800)
                echo "bg-warning";
            else echo "bg-success"
            ?>" onclick="showChart('ppm', 'CO2', 'gradient', false)">
                <div class="card-body">
                    <h5 class="card-title">Co2</h5>
                    <p class="card-text"><?Php echo end($sensorData)['co2'] ?> ppm</p>
                </div>
            </div>
            <div class="card cardShort rounded-3 bg-secondary"
                 onclick="showChart('°C', 'Temperature', 'rgb(108,117,125)', false)">
                <div class="card-body">
                    <h5 class="card-title">Temperature</h5>
                    <p class="card-text"><?Php echo end($sensorData)['temp'] ?> °C</p>
                </div>
            </div>
            <div class="card cardShort rounded-3 bg-info"
                 onclick="showChart('%', 'Humidity', 'rgb(13,202,240)', false)">
                <div class="card-body">
                    <h5 class="card-title">Humidity</h5>
                    <p class="card-text"><?Php echo end($sensorData)['hum'] ?> %</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p>Raphael Furrer</p>
        </div>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"
        integrity="sha512-SuxO9djzjML6b9w9/I07IWnLnQhgyYVSpHZx0JV97kGBfTIsUYlWflyuW4ypnvhBrslz1yJ3R+S14fdCWmSmSA=="
        crossorigin="anonymous"></script>
<script type="text/javascript">
    let landscape = false;
    if (screen.availHeight > screen.availWidth) {
        alert("For better diagrams use Landscape and reload page");
    } else {
        landscape = true;
    }

    let mobile = false;
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        mobile = true;
    }

    let sensorData = <?Php echo json_encode($sensorData); ?>;

    //chart
    let gradient = null;
    let width = null;
    let height = null;
    //chart options
    const cfg = {
        type: 'line',
        data: {
            labels: Array.from(sensorData, v => v.time),
            datasets: [{
                label: 'CO2',
                data: Array.from(sensorData, v => v.co2),
                fill: false
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            animation: {
                duration: 2000
            },
            scales: {
                xAxes: [{
                    type: 'time',
                    scaleLabel: {
                        display: !mobile,
                        labelString: 'Time'
                    },
                    gridLines: {
                        display: false
                    }
                }],
                yAxes: [{
                    type: 'linear',
                    gridLines: {
                        drawBorder: false
                    },
                    ticks: {
                        min: 0,
                        max: 4000,
                        stepSize: 200
                    },
                    scaleLabel: {
                        display: !mobile,
                        labelString: 'ppm'
                    }
                }]
            },
            tooltips: {
                position: 'nearest',
                mode: 'index',
                intersect: false,
                displayColors: false
            }
        }
    };

    const cfgAirQuality = {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Air Quality',
                data: [{
                    x: sensorData[sensorData.length - 1].temp,
                    y: sensorData[sensorData.length - 1].hum
                }],
                backgroundColor: 'rgb(0,0,0)'
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            animation: {
                duration: 2000
            },
            scales: {
                xAxes: [{
                    type: 'linear',
                    position: 'bottom',
                    scaleLabel: {
                        display: true,
                        labelString: 'Temperature'
                    },
                    ticks: {
                        min: 15,
                        max: 31,
                        stepSize: 1
                    }
                }],
                yAxes: [{
                    type: 'linear',
                    ticks: {
                        min: 0,
                        max: 100,
                        stepSize: 10
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Humidity'
                    }
                }]
            },
            tooltips: {
                position: 'nearest',
                mode: 'index',
                intersect: false,
                displayColors: false
            }
        }
    };

    //First load of chart and immediately remove it (chart needs to be loaded on page load, else it wont work)
    window.onload = function () {
        window.chartAirQuality = new Chart('chartAirQuality', cfgAirQuality)

        window.chart = new Chart('chart', cfg);


        document.getElementById('chart').style.height = "0";
        document.getElementById('chartAirQuality').style.height = "0";
        //That the first loaded chart instant disappears and not with animation
        setTimeout(function () {
            document.getElementById('chart').style.transition = "height 1s";
            document.getElementById('chartAirQuality').style.transition = "height 1s";
        }, 10);
    }

    //change chart data
    function showChart(type, title, color) {
        let outputData = sensorData.slice(sensorData.length - 101, sensorData.length - 1); //Max 100
        document.getElementById('chart').style.height = mobile ? landscape ? "100vh" : "100vw" : "81vh"
        //Generate array with data
        const data = Array.from(outputData, v => type === 'ppm' ? v.co2 : type === '%' ? v.hum : v.temp);

        const dataset = window.chart.config.data.datasets[0];

        //set labels
        window.chart.config.data.labels = Array.from(outputData, v => v.time)

        //set data / title
        dataset.data = data;
        dataset.label = title;

        //Background color
        if (color === 'gradient') {

            //create gradient for co2 good/bad values
            dataset.borderColor = function (context) {
                const chartArea = context.chart.chartArea;

                if (!chartArea) {
                    // This case happens on initial chart load
                    return null;
                }

                const chartWidth = chartArea.right - chartArea.left;
                const chartHeight = chartArea.bottom - chartArea.top;
                if (gradient === null || width !== chartWidth || height !== chartHeight) {
                    // Create the gradient because this is either the first render
                    // or the size of the chart has changed
                    width = chartWidth;
                    height = chartHeight;
                    const ctx = context.chart.ctx;
                    gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0.19, 'rgb(40,167,69)');
                    gradient.addColorStop(0.25, 'rgb(255,165,0)');
                    gradient.addColorStop(0.27, 'rgb(255,69,0)');
                    gradient.addColorStop(0.3, 'rgb(255,0,0)');
                    gradient.addColorStop(1, 'rgb(176,2,2)');
                }

                return gradient;
            };
            dataset.backgroundColor = function (context) {
                const chartArea = context.chart.chartArea;

                if (!chartArea) {
                    // This case happens on initial chart load
                    return null;
                }

                const chartWidth = chartArea.right - chartArea.left;
                const chartHeight = chartArea.bottom - chartArea.top;
                if (gradient === null || width !== chartWidth || height !== chartHeight) {
                    // Create the gradient because this is either the first render
                    // or the size of the chart has changed
                    width = chartWidth;
                    height = chartHeight;
                    const ctx = context.chart.ctx;
                    gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0.19, 'rgb(40,167,69)');
                    gradient.addColorStop(0.25, 'rgb(255,165,0)');
                    gradient.addColorStop(0.27, 'rgb(255,69,0)');
                    gradient.addColorStop(0.3, 'rgb(255,0,0)');
                    gradient.addColorStop(1, 'rgb(176,2,2)');
                }

                return gradient;
            };
        } else {
            //set normal colors
            dataset.borderColor = color;
            dataset.backgroundColor = color;
        }

        //Set y axes
        const y = window.chart.config.options.scales.yAxes[0];
        y.scaleLabel.labelString = type;
        if (type === 'ppm') {
            y.ticks.min = 0;
            y.ticks.max = 4000;
            y.ticks.stepSize = 200;
        } else if (type === '%') {
            y.ticks.min = 0;
            y.ticks.max = 100;
            y.ticks.stepSize = 5;
        } else {
            y.ticks.min = 15;
            y.ticks.max = 31;
            y.ticks.stepSize = 1;
        }
        chart.update();
    }

    function showAirQualityChart() {
        document.getElementById('chartAirQuality').style.height = mobile ? landscape ? "100vh" : "100vw" : "81vh"
    }


    //remove chart on scroll
    window.addEventListener("scroll", function () {
        if (!mobile || window.pageYOffset > 200) {
            document.getElementById('chart').style.height = "0"
            document.getElementById('chartAirQuality').style.height = "0"
            setTimeout(function () {
                window.scrollTo(0, 0)
            }, 1100);
        }
    })

</script>
</body>
</html>

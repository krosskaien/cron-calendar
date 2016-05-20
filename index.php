<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CronEntry.php';
require_once __DIR__ . '/CronTable.php';
date_default_timezone_set('Europe/Warsaw');
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));

// $repo = CronEntryCalendarRepository::make('primary');
$cron = CronTable::make();
$cron->loadFromFile('cron.dump.ultramin');

$today = new DateTime('now');
$begin = $today->format('Y-m-d 00:00:01');
$end = $today->format('Y-m-d 23:59:59');
$schedule = $cron->findEntriesStartedBetween($end, $begin);

$json = array();
foreach($schedule as $timestamp => $events){
	$dt = new DateTime($timestamp);
	$day = intval($dt->format('d'));
	$hour = intval($dt->format('H'));
	$minute = intval($dt->format('i'));
	foreach($events as $event){
		if($day == intval($today->format('d'))){
			$json[] = array("timestamp" => $timestamp, "summary" => $event->getSummary(), "day" => $day, "hour" => $hour, "minute" => $minute);
		}
	}
}
$json = json_encode($json);
?>

<!DOCTYPE html>
<meta charset="utf-8">
<html>
	<head>
		<style>
			body{
				font-family: sans-serif;
				font-size: 8pt;
			}
			rect.bordered {
				stroke: #E6E6E6;
				stroke-width:2px;   
			}

		</style>
		<script src="http://d3js.org/d3.v3.js"></script>
	</head>
	<body>
		<div id="chart"></div>
		<script type="text/javascript">
			var margin = { top: 50, right: 0, bottom: 100, left: 30 },
					width = 960 - margin.left - margin.right,
					height = 960 - margin.top - margin.bottom,
					gridSize = Math.floor(width / 24);

			var svg = d3.select("#chart").append("svg")
					.attr("width", width + margin.left + margin.right)
					.attr("height", height + margin.top + margin.bottom)
					.append("g")
					.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

			var dayoffset = -19;
			var hours = [ "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00", "24:00"];

			var hourLabels = svg.selectAll(".hourLabel")
          .data(hours)
          .enter().append("g");

      hourLabels.append("rect")
      			.attr("x", 0)
            .attr("y", function(d, i) { return i * gridSize; })
            .attr("class", "bordered")
            .attr("width", gridSize)
						.attr("height", gridSize)
						.style("fill", "#ffffd9");

      hourLabels.append("text")
            .text(function(d) { return d; })
            .attr("x", 0)
            .attr("y", function(d, i) { return i * gridSize; })
            .style("text-anchor", "middle");

			var heatmapChart = function() {
					data = JSON.parse('<?php echo $json; ?>');
/*
					var cards = svg.selectAll(".hour")
							.data(data)
							.enter().append("rect")
							.attr("x", function(d) { return (dayoffset + d.day) * gridSize; })
							.attr("y", function(d) { return d.hour * gridSize; })
							.attr("class", "hour bordered")
							.attr("data-summary", function(d) {return d.summary})
							.attr("data-timestamp", function(d) {return d.timestamp})
							.attr("width", gridSize)
							.attr("height", gridSize)
							.style("fill", "#ffffd9");
							*/
			};

			heatmapChart();
		</script>
	</body>
</html>
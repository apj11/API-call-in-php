<html>
	<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<title>
			Fetch data
		</title>
	</head>
	<body>
	<div class="container-fluid">
	<table class="table table-striped">
			<thead>
				<tr>
					<th>Year</th>
					<th>Product </th>
					<th>Min</th>
					<th>Max </th>
					<th>Avg</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$con=mysqli_connect('localhost','root','','petrol');
					if($con === false){
						die("ERROR: Could not connect. " . mysqli_connect_error());
					}
					else{
						echo 'connected successfully';
					}
					
					//creating database and table from phpmyadmin mysql

					// insert api data to database
					$url = "https://raw.githubusercontent.com/younginnovations/internship-challenges/master/programming/petroleum-report/data.json";
					$handle = curl_init();
					curl_setopt($handle, CURLOPT_URL, $url);
					// Set the result output to be a string.
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
					 
					$output = curl_exec($handle);
					$data = json_decode($output, true);
					foreach($data as $data){
					$sql = "INSERT INTO petrol_report (year, petroleum_product, sale)VALUES ('{$data['year']}','{$data['petroleum_product']}','{$data['sale']}')";
						if ($con->query($sql) === TRUE) {
							echo "New record created successfully";
						} else {
							echo "Error: " . $sql . "<br>" . $con->error;
						}
					}
					
					// insert api data to database

					// Min and max value is taken from database
					$minYearQueryString = mysqli_query($con, "Select min(year) as min_year from petrol_report;");
					$maxYearQueryString = mysqli_query($con, "Select max(year) as max_year from petrol_report;");
					$productNameQueryString = mysqli_query($con, "Select distinct petroleum_product from petrol_report;");
					$minYear = mysqli_fetch_array($minYearQueryString)["min_year"];
					$maxYear = mysqli_fetch_array($maxYearQueryString)["max_year"];
					$productName = mysqli_fetch_array($productNameQueryString);

					$minPossibleYear = (int)($minYear / 5) * 5;
					$maxPossibleYear = ((int)($maxYear / 5) + 1) * 5;



					if ($maxPossibleYear == $maxYear) {
						$maxPossibleYear += 4;
					}

					$start = $minPossibleYear;
					$intervalArray = array();

					while ($start < $maxPossibleYear) {
						array_push($intervalArray, array($start, $start + 4));
						$start += 5;
					}


					while($data = mysqli_fetch_array($productNameQueryString)) {
						$productName = $data[0];
						foreach ($intervalArray as $columnName => $columnData) {
							$minRange = $columnData[0];
							$maxRange = $columnData[1];

							$productWithRangeAvgQuery = mysqli_query($con, "select avg(sale) as p_avg from petrol_report where petroleum_product = '$productName' and year between $minRange and $maxRange and sale != 0" );
							$productWithRangeMinQuery = mysqli_query($con, "select min(sale) as p_min from petrol_report where petroleum_product = '$productName' and year between $minRange and $maxRange and sale != 0" );
							$productWithRangeMaxQuery = mysqli_query($con, "select max(sale) as p_max from petrol_report where petroleum_product = '$productName' and year between $minRange and $maxRange and sale != 0" );

							$productYear = "$minRange - $maxRange";
							$productMin = mysqli_fetch_array($productWithRangeMinQuery)['p_min'];
							$productMax = mysqli_fetch_array($productWithRangeMaxQuery)['p_max'];
							$productAvg = mysqli_fetch_array($productWithRangeAvgQuery)['p_avg'];
							
							// Min and max value is taken from database

							// PRDOUCT DATA IS ADDED TO VIEW
							if($productName && $productMin && $productMax && $productAvg){
								echo("<tr><td>$productYear</td><td>$productName</td><td>$productMin</td><td>$productMax</td><td>$productAvg</td></tr>");
							}
							// PRDOUCT DATA IS ADDED TO VIEW

						}
					}
				?>
			</tbody>
		</table>
		</div>
	</body>
</html>

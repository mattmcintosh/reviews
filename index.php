<?php 

// ENABLE ALL ERROR AND WARNING DETAIL SHOWING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// simple sanitize input from page number for pagination 
// drop everything that is not in my white list.. (Numbers only)
function sanitize($input, $pattern = "/[^0-9]/") {
	$filtered = preg_replace($pattern, "", $input);
	return $filtered;
}

/******************************************
*******************************************
Read JSON files with company information
and display list of reviews.
*******************************************
*******************************************/

$jsonraw = file_get_contents('http://test.localfeedbackloop.com/api?apiKey=61067f81f8cf7e4a1f673cd230216112&noOfReviews=10&internal=1&yelp=1&google=1&offset=50&threshold=1');
$jsonobj = json_decode($jsonraw);

// setup array with review sources (and thanks for the hint)
$review_source=array(0 => 'internal', 1 => 'yelp', 2 => 'google');

// create biz and reviews arrays to make them easy access
$biz=$jsonobj->business_info;
$rev=$jsonobj->reviews;
// break out total rating details
$trating=$biz->total_rating;

// reset page information if page it not provided.. 
if (!empty($_GET['page'])) $page=sanitize($_GET['page']);
	else $page=1;
// records per page
$maxperpage=3;

// calulate records you need for loop later.
$lowrecord=($page-1)*$maxperpage;
$highrecord=($page*$maxperpage)-1;

?>
<head>
<link rel="stylesheet" type="text/css" href="css/reviews.css">
</head>
<div style="business_reviews"> 
<div class="business">
	<div class="business_name"><?= $biz->business_name ?></div>
	<div class="business_address"><?= $biz->business_address ?></div>
	<div class="business_phone"><?= $biz->business_phone ?></div>
</div>
<div class="summary">
	<div class="sitestatbox noreviews">
		<div class="totalreviews"><?= $trating->total_no_of_reviews ?></div>
		<div class="title">Total</div>
	</div>
	<div class="sitestatbox averating">
		<div class="totalaverating"><?= $trating->total_avg_rating ?></div>
		<div class="reviewstars">
			<?php $r=$trating->total_avg_rating; 
				while ($r>0) { $r--; ?>
			<img class="stars" src="images/1star.png"> 
			<?php } ?>
		</div>
		<div class="title">Average Rating</div>
	</div>
</div>
<div class="reviews">
<?php
	// loop through each of the reviews
	$recno=0;
	foreach ($rev as $v) {
		if ($recno<$lowrecord || $recno>$highrecord) {
			$recno++; 
			continue;

		}
		$recno++; 
		$r=$v->rating;
		if (!empty($v->customer_last_name)) {
			$name=$v->customer_name. ", ".substr($v->customer_last_name,0,1);
		} else {
			$name=$v->customer_name;

		}
		$rf=$v->review_from;
		$rstext=$review_source[$rf];
?>
	<div class="review">
		<div class="reviewperson">
			<img src="images/person.png"><br>
			<?= $name ?><br>
			<?php while ($r>0) { $r--; ?>
			<img class="stars" src="images/1star.png"> 
			<?php } ?>
		</div>
		<div class="reviewdetails">
			<div class="reviewdate"><b>Review Date:</b> <?php echo substr($v->date_of_submission,0,10) ?></div>
			<div class="reviewsource"><?= $rstext ?></div>
			<div style="clear:both;"></div>
			<div class="reviewdescription"><?= $v->description ?></div>
		</div>
		
	</div>
<?php
	}
	$maxpage=ceil($recno/$maxperpage);
	$nextpage=$page+1;
	if ($nextpage>$maxpage) $nextpage=$maxpage;
	$lastpage=$page-1;
	if ($lastpage<1) $lastpage=1;
	//echo "-- lowrecord: {$lowrecord} highrecord: {$highrecord} recno: {$recno} maxpage={$maxpage} nextpage: {$nextpage} lastpage: {$lastpage} page: {$page} --";
	
?>
	<a href="<?= $_SERVER["PHP_SELF"] ?>?page=1">First</a>
	<a href="<?= $_SERVER["PHP_SELF"] ?>?page=<?= $lastpage ?>">Previous</a>
	<a href="<?= $_SERVER["PHP_SELF"] ?>?page=<?= $nextpage ?>">Next</a>
	<a href="<?= $_SERVER["PHP_SELF"] ?>?page=<?= $maxpage ?>">Last</a>
</div>
</div>
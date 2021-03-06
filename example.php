<?php
/* PHPSyncer v0.2.0 <github.com/fiedlr/PHPSyncer> | (c) 2016 Adam Fiedler | @license <opensource.org/licenses/MIT> */

require_once "PHPSyncer.class.php";

$source = "./newfiles";
$target = "./oldfiles";

try 
{
	$startingTime = microtime();

	$sync = new PHPSyncer('/if\s*\(\s*function_exists\(\'magicFunction\'\)\s*\)\s*/', new RecursiveDirectoryIterator($source), new RecursiveDirectoryIterator($target));

	echo "<p>Generating map...</p>";

	// Extract & save to an external source
	$sync->extract()->saveMap("classyMap.json");

	echo "<p>Applying changes...</p>";

	$r = $sync->apply();

	if (!empty($r)) 
	{
		$endingTime = microtime();
		
		echo "<p>Finished in ".($endingTime - $startingTime)."ms</p><p>Log:</p>";

		// Show the changes
		var_dump($r);
	} else {
		echo "<p>No matches.</p>";
	}
}
catch (Exception $e) 
{
	echo $e->getMessage();	
}
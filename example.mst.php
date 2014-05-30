<?php
/***************************************************************
*      Copyright notice
*  
*      (c) 2010-2011 Chi Hoang (info@chihoang.de)
*      All rights reserved
*
*	The above copyright notice and this permission notice shall be included
*	in all copies or substantial portions of the Software.
*	
*	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
*	OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
*	THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
*	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
*	DEALINGS IN THE SOFTWARE.
*
*      Permission is hereby granted, free of charge, to any person obtaining a
*      copy of this software and associated documentation files (the "Software"),
*      to deal in the Software without restriction, including without limitation
*      the rights to use, copy, modify, merge, publish, distribute, sublicense,
*      and/or sell copies of the Software, and to permit persons to whom the
*	Software is furnished to do so, subject to the following conditions:
*
*      Free for non-commercial use
****************************************************************/

require_once ( "mst.php" );

$example_3 = Array (   1 => Array ( 1, 2, 3, 8, 7 ),
					  2 => Array ( 1, 3  ),
					  3 => Array ( 1, 2, 4, 7 ),
					  4 => Array ( 3, 7, 9, 5 ),
					  5 => Array ( 4, 9 ),
					  6 => Array ( 7, 9 ),
					  7 => Array ( 1, 3, 4, 6, 8, 9 ),
					  8 => Array ( 1, 7 ),
					  9 => Array ( 4, 5, 6, 7 ),
				  );
				  
$example_2 = Array ( 1 => Array ( 2, 3 ),
					2 => Array ( 1,3,4,5 ),
					3 => Array ( 1,2,4,5 ), 
					4 => Array ( 2,3,5 ),
					5 => Array ( 2,3,4 )
				);

$example_1 = Array (
					1 => Array ( 2, 3, 4, 5 ),
					2 => Array ( 1,4 ),
					3 => Array ( 1,4,5, 6 ), 
					4 => Array ( 1,2,3, 6 ),
					5 => Array ( 1, 3 ),
					6 => Array ( 3, 4 )
				);
				
$test = new mst ( );
$test->example ( );
$test->prim ( );
$test->pickPrimTree ( );	
$test->makePMTree ( $test->mst );
$test->getWayStr ( 0 );	
$pm = $test->pmatch ( $test->wayStr );	
$test->addmatch ( $test->mst, $pm [ 1 ] );	
$start = $test->eulerCircuit ( $test->mst );
$path = $test->FindEulerCircuit ( $start );
$path = $test->HamiltonianPath ( $path );
$sum = $test->sumPath ( $path );	
echo "2.0x MST: " . $doubleMST = $test->total * 2 . " km \n";
echo "1.5x MST: " . $sum . " km \n";
echo "Error in percent: "  .  substr ( ( ( $sum - $doubleMST ) / $sum )  * 100 , 0, 2 )." %\n"; 
echo "Travelsalesman route: " . implode (", ", $path ) ."\n";
?>
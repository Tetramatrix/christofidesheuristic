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
*
***************************************************************/

define ( "INFINITY", 1000000 );

class mst {

	var $cost = Array ( );      // cost of assigning house i to person j  
	var $curMatchings = Array ( );   // house i matched to person curMatchings[i]  
	var $delta = Array ( );   // the delta/cost graph  
	var $mother = Array ( );   // used for backtracking the cycle  
	var $cycle = Array ( );         // path of the cycle  
	var $totalLen;         // length of the cycle 
	
		/* The number of nodes in the graph */
	var $mapWidth;
	
		// $points is an Array in the following format: ( router1, router2, distance-between-them)
	var	$points1 = Array (
						Array ( 0, 1, 40 ),
						Array ( 0, 2, 2 ),
						Array ( 1, 2, 5 ),
						Array ( 1, 3, 5 ),
						Array ( 2, 3, 5 ),
						Array ( 3, 4, 5 ),
						Array ( 4, 5, 5 ),
						Array ( 2, 10, 30 ),
						Array ( 2, 11, 40 ),
						Array ( 5, 19, 20 ),
						Array ( 10, 11, 20 ),
						Array ( 12, 13, 20 ),
		);
		
		 	//~ 		Lo	LA	NY	Pa	Pe	To	Br	Os
			//~ Lo	0	69	43	2	66	81	58	80
			//~ LA	69	0	27	71	66	51	64	54
			//~ NY	43	27	0	44	91	77	53	81
			//~ Pa	2	71	44	0	65	79	57	78
			//~ Pe	66	66	91	65	0	15	105	13
			//~ To	81	51	77	79	15	0	109	3
			//~ Br	58	64	53	57	105	109	0	105
			//~ Os	80	54	81	78	13	3	105	0

	var $points = Array ( 
						Array ( 1, 0, 69 ),
						Array ( 2, 0, 43 ),
						Array ( 3, 0, 2 ),
						Array ( 4, 0, 66 ),
						Array ( 5, 0, 81 ),
						Array ( 6, 0, 58 ),
						Array ( 7, 0, 80 ),
						Array ( 2, 1, 27 ),
						Array ( 3, 1, 71 ),
						Array ( 4, 1, 66 ),
						Array ( 5, 1, 51 ),
						Array ( 6, 1, 64 ),
						Array ( 7, 1, 54 ),
						Array ( 3, 2, 44 ),
						Array ( 4, 2, 91 ),
						Array ( 5, 2, 77 ),
						Array ( 6, 2, 53 ),
						Array ( 7, 2, 77 ),
						Array ( 4, 3, 65 ),
						Array ( 5, 3, 79 ),
						Array ( 6, 3, 57 ),
						Array ( 7, 3, 78 ),
						Array ( 5, 4, 15 ),
						Array ( 6, 4,  105 ),
						Array ( 7, 4, 13 ),
						Array ( 6, 5, 109 ),
						Array ( 7, 5, 3 ),
						Array ( 7, 6, 105 ),
					);
					
		// minimum spanning tree
	var $mst = Array ( );
	var $doubleWhoTo = Array ( );
	var $eulerMap = Array ( );
	var $perfectMatching = Array ( );
	var $pmEdges = Array ( );
	var $wayStr = Array ( );
	var $pmPoints = Array ( );
	var 	$path = Array ( );
	
		// shortest Paath
	var $ourShortestPath = Array ( );
	
		// result text
	var $foo;
		
		// total distance
	var $total;
	
		/* weight [ i ][ j ] is the distance between node i and node j; if there is no path between i and j, weight [ i ][ j ] should be 0  */
	var $weight = Array ( ); 

		/* $inTree [ $i ] is 1 if the node i is already in the minimum spanning tree; 0 otherwise*/
	var $inTree = Array ( ); 

		/* $d[$i] is the distance between node i and the minimum spanning
			tree; this is initially infinity (100000); if i is already in
			the tree, then d[i] is undefined;
			this is just a temporary variable. It's not necessary but speeds
			up execution considerably (by a factor of n) */
	var $d = Array ( ); 
			
		/* $whoTo[i] holds the index of the node i would have to be linked to in order to get a distance of d[i] */
	var $whoTo = Array ( ); 

		/*	should be called immediately after target is added to the tree;
			updates d so that the values are correct (goes through target's
			neighbours making sure that the distances between them and the tree
			are indeed minimum)
		*/
	function updateDistances ( $target ) {
		for ( $i = 0; $i < $this->mapWidth ; ++$i ) {
			if ( ! $this->inTree [ $i  ] && $this->weight [ $target ] [ $i ] != 0 &&  $this->d [  $i  ] > $this->weight [  $target  ] [ $i ] ) {
				$this->d [ $i ] = $this->weight [ $target ] [ $i ];
				$this->whoTo [ $i ] = $target;
			}
		}
	}

	function example ( ) {
		
			// Size of the matrix
		$this->mapWidth = 20;

		for ( $i = 0; $i < $this->mapWidth; ++$i ) {
				/* Initialise d with infinity */
			$this->d [ $i  ] = INFINITY;
				/* Mark all nodes as NOT beeing in the minimum spanning tree */
			$this->inTree [ $i  ] = 0;
				/* Initialise minimum spanning tree with infinity */
			$this->whoTo [ $i  ] = 0;
			for ( $j=0; $j < $this->mapWidth; ++$j ) {
				$this->weight [ $i ] [ $j ] = 0;
			}
		}

			// Read in the points and push them into the map
		for ( $i = 0, $end = count ( $this->points ); $i < $end; ++$i  ) {
			$x = $this->points [ $i ] [ 0 ];
			$y = $this->points [ $i ] [ 1 ];
			$c = $this->points [ $i ] [ 2 ];
			$this->weight [ $x ] [ $y ] = $this->weight [ $y ] [ $x ] = $c;
		}
	}
	
	function init_points ( $points, $mapWidth ) {
		
			// Size of the matrix
		$this->mapWidth = $mapWidth;

		for ( $i = 0; $i < $this->mapWidth; ++$i ) {
				/* Initialise d with infinity */
			$this->d [ $i ] = INFINITY;
				/* Mark all nodes as NOT beeing in the minimum spanning tree */
			$this->inTree [ $i ] = 0;
				/* Initialise minimum spanning tree with infinity */
			$this->whoTo [ $i ] = 0;
			
			for ( $j=0; $j < $this->mapWidth; ++$j ) {
				$this->weight [ $i ] [ $j ] = 0;
			}
		}

			// Read in the points and push them into the map
		for ( $i = 0, $end = count ( $points ); $i < $end; ++$i  ) {
			$x = $points [ $i ] [ 0 ];
			$y = $points [ $i ] [ 1 ];
			$c = $points [ $i ] [ 2 ];
			$this->weight [ $x ] [ $y ] = $this->weight [ $y ] [ $x ] = $c;
		}
	}
	
	function init ( $map ) {
	
			// Size of the matrix
		$this->mapWidth = count ( $map );

		for (	$i = 0; $i < $this->mapWidth; ++$i  ) {
				/* Initialise d with infinity */
			$this->d [ $i ] = INFINITY;
				/* Mark all nodes as NOT beeing in the minimum spanning tree */
			$this->inTree [ $i ] = 0;
				/* Initialise minimum spanning tree with infinity */
			$this->whoTo [ $i ] = 0;
		}
		
		$this->weight = $map;
	}
	
	function prim ( )  {		

			/* Add the first node to the tree */
		$this->foo = sprintf ( "Adding node %c\n", 0 + ord ('A') );
		
		$this->inTree [ 0 ] = 1;
		$this->updateDistances ( 0 );
		$this->total = 0;
		
		for ( $treeSize = 1; $treeSize < $this->mapWidth; ++$treeSize ) {
				/* Find the node with the smallest distance to the tree */
			$min = -1;
			for ( $i = 0; $i < $this->mapWidth ; ++$i ) {			
				if ( ! $this->inTree[ $i ] ) { 				
					if ( $min == -1 || $this->d [ $min ] > $this->d [ $i ] ) {
						$min = $i;
					}
				}
			}
				/* And add it */
			$this->foo .= sprintf ( "Adding edge %c-%c\n", $this->whoTo[ $min ]  + ord( 'A' ), $min + ord( 'A' ) );
			
			$this->inTree [  $min ]  = 1;
			if ( $this->d [ $min ] != INFINITY ) {
				$this->total += $this->d [ $min ];
			}
			$this->updateDistances ( $min );
		}

		$this->foo .= sprintf ( "\nTotal distance: %d\n\nOur shortest path is:\n\n", $this->total );
	}
	
	function pickPrimTree ( ) {
	
		$this->ourShortestPath = Array ( );
		$startnode = "0";
		
                for ($i = 0; $i < $this->mapWidth ; $i++) {
                        $endNode = null;
                        $currNode = $i;
                        $this->ourShortestPath [ $i ] [ ] = $i;
                        while ( $endNode === null || $endNode != $startnode ) {
                                $this->ourShortestPath [ $i ] [ ] = $this->whoTo [ $currNode ];
                                $endNode =  $currNode = $this->whoTo [ $currNode ];
                        }
                        $this->ourShortestPath [ $i ] = Array_reverse ( $this->ourShortestPath [ $i ] );
			if ( $this->d [ $i ] >= INFINITY ) {
				$this->foo .= sprintf ("No route from %d to %d. \n", $startnode, $i);
				unset ( $this->ourShortestPath [ $i ] );
			} else {
				$found = false;
				foreach ( $this->ourShortestPath as $k => $v ) {
					$match = 0;
					$end = count ( $this->ourShortestPath [ $k ] ) ; 
					if  ( $this->ourShortestPath [ $k ] [ 0 ] !== "delete" ) {
						for ( $j = 0; $end2 = count ( $this->ourShortestPath [ $k ] ), $j < $end2; ++$j  ) {
							if  (  $end <= count ( $this->ourShortestPath [ $i ] ) && $this->ourShortestPath [ $k ] [ $j ] == $this->ourShortestPath [ $i ] [ $j ] ) {
								++$match;
							}
							if  ( $match == $end && $k != $i ) {
								$found = true;
								break;
							}
						}
						if ( $found ) break;
					}
				}
				$this->foo .= sprintf ( '%d => %d = %d [%d]: (%s).'."\n" ,
							$startnode, $i , $this->d [ $i ],
							count ( $this->ourShortestPath [ $i  ]),
							implode ( '-', $this->ourShortestPath [ $i  ] ) );
				$this->mst [ $i ]  = $this->ourShortestPath [ $i ];
				if ( $found )  {
					$this->ourShortestPath [ $k ] [ 0 ] = "delete";
					unset ( $this->mst [ $k ] );
				} 
			}
			$this->foo .= str_repeat ( '-', 20 ) . "\n";
		}
		
		$this->mst = Array_values ( $this->mst );
	}
	
	function doubleWhoTo ( $tree )  {
		for (	$i = 0; $i < $this->mapWidth; ++$i ) {
			$this->doubleWhoTo [ $i  ] = $tree [ $i  ];
			$this->doubleWhoTo [ $tree [ $i ] ]  = $i;
		}
	}

	function sumMST ( $tree ) {
	
		$sum = 0;
		$queue = Array ( );

		foreach ( $tree as $key  => $depth ) {
			$sum += $this->weight [ $key ] [ $depth ];
		}
		return $sum;
	}
	
	function sumPath ( $tree ) {
		$sum = 0;
		$queue = Array ( );
		
		foreach ( $tree as $k => $v ) {
			if ( count ( $queue ) != 2 ) {
				$queue [ ] = $v;
			} else {
				$sum += $this->weight [ $queue [ 0 ]  ] [ $queue [ 1 ] ];
				$waste = array_shift ( $queue );
				$queue [ ] = $v;
			}
		}
		$sum += $this->weight [ $queue [ 0 ]  ] [ $queue [ 1 ] ];
		$waste = array_shift ( $queue );
		$sum += $this->weight [ $queue [ 0 ]  ] [ $tree [ 0 ] ];
		return $sum;
	}
	
	function eulerCircuit ( $tree ) {
	
		$odd = Array ( );
		$empty = true;
		
		foreach ( $tree as $k => $v ) {
			if ( count ( $v ) % 2 != 0 ) {
				$odd [ ] = $v;
				$empty = false;
			}
		}
		
		if ( $empty ) {
			$myStack = 0;
		} else if ( count ( $odd ) < 3 ) {
			//~ $myStack = $odd [ 0 ] [ 0 ];
			$myStack = 0;
		} else {
			die ( "No Euler-Path" );
		}
		
			// prerequisuits
		for (	$i = 0; $i < $this->mapWidth; ++$i  ) {
			for ( $j=0; $j < $this->mapWidth; ++$j ) {
				$this->eulerMap [ $i ] [ $j ] = 0;
			}
		}
		
			// Read in the points and push them into the map		
		foreach ( $tree as $k => $v ) {
			foreach ( $v as $node => $leaf ) {
				$this->eulerMap [ $k ] [ $leaf ] = $this->eulerMap [ $leaf ] [ $k ] = 1;
			}
		}
		return $myStack;
	}
	
	function CanGoBack ( $x, $y ) {
		$Queue = $Free = Array ( );		
		$this->eulerMap [ $x ] [ $y ] = $this->eulerMap [ $y ] [ $x ] = 0; 

		for ( $i = 0; $i < $this->mapWidth; ++$i ) {
			$Free [ $i ]  = 1;
		}
		
		$Free [ $y ] = 0;
		$Queue [  ] = $y;

		do {
			$u = Array_shift ( $Queue ); 
			for ( $i = 0; $i < $this->mapWidth; ++$i  ) {
				if ( $Free [ $i ] && ( $this->eulerMap [ $u ] [ $i ] > 0) ) {
					$Queue  [ ] = $i;
					$Free [ $i  ] = 0;
					if  ( $Free [ $x ] ) Break;
				}
			}
		} while ( ! empty ( $Queue [ 0 ] )  );
		
		$this->eulerMap [ $x ] [ $y ] = $this->eulerMap [ $y ] [ $x ] = 1;
		return  ( $Free [ $x ] );
	}
	
	function FindEulerCircuit ( $Current ) {
		$Next = -1;
		for ( $v = 0; $v < $this->mapWidth; ++$v ) {
			if ( $this->eulerMap [ $Current ] [ $v ] > 0 ) {
				$Next = $v;
				if ( ! $this->CanGoBack ( $Current, $Next ) ) {
					break;
				}
			} 
		}
		if ( $Next != -1 ) {
				// echo circuit 
			$this->path [ ]  = $Next;
				// deleting
			$this->eulerMap [ $Current ] [ $Next ] = $this->eulerMap [ $Next ] [ $Current ] = 0;
			$this->FindEulerCircuit ( $Next );
			
		}  else {
			
			for ( $i = 0; $i < $this->mapWidth; ++$i  ) {
				for ( $j = 0; $j < $this->mapWidth; ++$j  ) {
					if ( $this->eulerMap [ $i ] [ $j ] > 0 ) {
						$this->FindEulerCircuit ( $i );
					}
				}
			}
		}
		return $this->path;
	}
	
	function HamiltonianPath ( $path ) {
		$clean = Array ( );
		foreach ( $path as $k => $v ) {
			if ( ! in_array ( $v, $clean ) ) {
				$clean [ ] = $v;
			}
		}
		return $clean;
	}
	
	function makePMTree ( $tree ) {
		foreach ( $tree as $k => $v ) {
			if ( count ( $v ) % 2 != 0 ) {
				$odd [ ] = $v;
			}
		}
		if ( ! empty ( $odd [ 0 ] ) ) {
			$c = 0;
			$this->perfectMatching = $odd;
			foreach ( $odd as $k => $v ) {
				foreach ( $v as $edge => $leaf ) {
					if ( ! in_array ( ++$leaf, $this->pmEdges ) ) {
						$this->pmEdges [  ++$c ] = $leaf;
					}
				}
			}
		}
	}
	
	//~ 1,2,3,4,5
	//~ 2,1,X,X,X
	//~ 3,X,1,X,X
	//~ 4,X,X,1,X
	//~ 5,X,X,X,1

	function getWayStr ( $curr ) {
		$nextAbove = -1;
		for (  $i = ++$curr; $end = count ( $this->pmEdges ), $i <= $end; ++$i  ) {
			if ( $nextAbove == -1) {
				$nextAbove = $i;
			} else {
				$this->wayStr [ ] = $this->pmEdges [ $i  ] - 1;
				$this->wayStr [ ] = $this->pmEdges [ $curr  ] - 1;
			}
		}
		if ( $nextAbove != -1 ) {
			if ( $nextAbove != $curr ) {
				$this->wayStr [ ] = $this->pmEdges [ $nextAbove ] - 1;
				$this->wayStr [ ] = $this->pmEdges [ $curr ] - 1;
			}
			$this->getWayStr ( $nextAbove );
		}
	} 
	
	function makePMTree2 ( $arr ) {
		$halfNum = count ( $arr );
		for ( $i=0; $i < $halfNum; ++$i ) {
			$this->pmPoints [ ] = Array ( $arr [ $i  ], $arr [ $i+1 ], $this->weight [ $arr [ $i  ] ] [ $arr [ $i+1 ] ] );
			++$i;
		}
	}
  
	function randomMatch ( $nodes, $nodesNum ) {
		$halfNum = $nodesNum / 2;
		for ( $i=0; $i < $nodesNum; $done [ $i++ ] = 0 );
		for ( $i=0; $i < $halfNum; ++$i ) {
			$num0 = $i;
			while ( $done [ $num0 ] !== 1 ) {
				++$num0;
				$done [ $num0 ] = 1;
				$num = rand ( 0, $nodesNum - 1);
				while ( ( $done [ $num ] !== 1 ) || ( $num == $nodesNum - 1 ) ) {
					$num = rand ( 0, $nodesNum - 1 );
					$newsum = $this->weight [ $nodes [ $num0 ] ] [ $nodes [ $num ] ];
					if ( $done [ $num ] !== 1 && $newsum !== 0 ) {
						$done [ $num ] = 1;
						$match [  ]  = Array ( "node1" => $nodes [ $num0 ], "node2" => $nodes [ $num ],  "distance" =>  $newsum );
					} 
				}
			}
		}
		return $match; 
	}

	function improve ( $numOfMatches, &$match ) {
		
		$weight=0;
		for ( $i =0; $i < $numOfMatches; ++$i  ) {
			for ( $j = ++$i; $j < $numOfMatches; ++$j ) {
			
				$chose1=$chose2=false;
				$sum = $match [ $i  ] [ "distance" ] + $match [ $j ] [ "distance" ];
				
				$i1 = $match [ $i  ] [ "node1" ];
				$i2 = $match [ $i  ] [ "node2" ];
				$j1 = $match [ $j  ] [ "node1" ];
				$j2 = $match [ $j  ] [ "node2" ];
				
				$newsum1 = $this->weight [ $i1 ] [ $j1 ] + $this->weight [ $i2 ] [ $j2 ];
				$newsum2 = $this->weight [ $i1 ] [ $j2 ] + $this->weight [ $i2 ] [ $j1 ];

				if ( $newsum1 < $sum && $newsum1 != 0 ) {
					if ( $newsum2 < $newsum1 && $newsum2 != 0 ) {
						$chose2=true;
					} else {
						$chose1=true; 
					}
				} else if ( $newsum2 < $sum && $newsum2 != 0 ) {
					$chose2=true;
				}
				
				if ( $chose1 ) {
					$match [ $i ] [  "node2" ] = $j1;
					$match [ $i ] [  "distance" ] = $this->weight [ $i1 ] [ $j1 ];
					$match [ $j ] [  "node1" ]  = $i2;
					$match [ $j ] [  "distance" ] = $this->weight [ $i2 ] [ $j2 ]; 
				} else if ( $chose2 ) {
					$match [ $i ] [  "node2" ]  = $j2;
					$match [ $i ] [  "distance" ] = $this->weight [ $i1 ] [ $j2 ];
					$match [ $j ] [  "node2" ]  = $i2;
					$match [ $j ] [  "distance" ] = $this->weight [ $i2 ] [ $j1 ];
				}
			}
		}	
		for ( $i =0; $i < $numOfMatches; ++$i ) {
			$weight += $match [ $i ] [ "distance" ];
		}
		return $weight;
	}
	
	function pmatch ( $odd )  {
		$oddNum = count ( $odd );		
		$Match = $this->randomMatch ( $odd, $oddNum );
		$matchNum = count ( $Match );
		$matchWeight = $this->improve ( $matchNum, $Match );
		while ( $matchWeight  != ( $sum=$this->improve( $matchNum, $Match ) ) ) {
			$matchWeight = $sum;
		}
		return array ( $matchWeight, $Match );
	}
	
	function addmatch ( &$tree, $match ) {
		foreach ( $tree as $k => $v ) {
			if ( count ( $v ) % 2 != 0 ) {
				$odd [  $k ] = $v;
			}
		}
		if ( count ( $odd ) > 1 ) {
			//~ $c = 0;
			//~ $merge = Array ( );
			//~ foreach ( $odd as $k => $v ) {
				//~ foreach ( $v as $i => $j ) {
					//~ if ( ! in_array ( $j, $merge ) ) {
						//~ $merge [ ] = $j;
					//~ }
				//~ }
			//~ }
			//~ foreach ( $match as $i => $j ) {
				//~ if ( $j [ "distance" ] != 0 ) {
					//~ if ( ! in_array ( $j [ "node1" ], $merge ) ) {
						//~ $merge [ ] = $j [ "node1" ];
					//~ } else if ( ! in_array ( $j [ "node2" ], $merge ) ) {
						//~ $merge [ ] = $jd [ "node2" ];
					//~ }
				//~ }
			//~ }
			
			foreach ( $match as $m => $n ) {
				foreach ( $tree as $k => $v ) {
					foreach ( $v as $i => $j ) {
						if (  $n [ "node1" ] == $j  && ! in_array (  $n [ "node2" ], $tree [ $k ] ) ) {
							$tree [ $k ] [ ] = $n [ "node2" ];
						}
						if (  $n [ "node2" ] == $j  && ! in_array (  $n [ "node1" ], $tree [ $k ] ) ) {
							$tree [ $k ] [ ] = $n [ "node1" ];
						}
					}
				}
			}
		}
		
		$odd = Array ( );
		foreach ( $tree as $k => $v ) {
			if ( count ( $v ) % 2 != 0 ) {
				$odd [  $k ] = $v;
			}
		}
		$merge = Array ( );
		foreach ( $odd as $k => $v ) {
			foreach ( $v as $i => $j ) {
				if ( ! in_array ( $j, $merge ) ) {
					$merge [ ] = $j;
				}
			}
		}
			
		foreach ( $odd as $k => $v ) {
			unset ( $tree [ $k ] );
		}
		
		$tree [ ] = $merge;
	}
}
?>
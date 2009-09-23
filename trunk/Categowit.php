#!/usr/bin/php
<?php
function get_line(&$file)
{
  $buff = eregi_replace("	", " ", fgets($file));
  $buff = eregi_replace(" +", " ", $buff);
  $buff = eregi_replace("\n", "", $buff);
  if ($buff != "")
    return $buff;
  return 0;
}

function get_fd($filename)
{
  global $cmd;
  $fd = @fopen($filename, "r");
  if ($fd)
    return $fd;
  else
    die("$cmd: $filename not found.\n");
}

function get_fd_trunc($filename)
{
  global $cmd;
  $fd = @fopen($filename, "w");
  if ($fd)
    return $fd;
  else
    die("$cmd: $filename not found.\n");
}

function usage()
{
  global $cmd;
  echo "$cmd: Usage: $cmd <filename>\n";
}


function write_data_gnuplot($tab)
{
  global $filelist;

  // $tab[]  est un tableau contenant l'ensemble des points ainsi que les parametres de sortie gnuplot
  //legende
  $filelist[$tab["file"]]["name"] = $tab["name"];
  //type de trace
  $filelist[$tab["file"]]["draw"] = $tab["draw"];
 
  $fd = get_fd_trunc($tab["file"]);
  foreach($tab as $k => $v)
    {
      if (is_a_point($k))
		{
			$buff = $k." ".$v;
			fwrite($fd, $buff."\n");
		}
    }
  fclose($fd);
}

// Generation du fichier commande gnuplot
function generate_gnuplot_file($tab)
{
  global	$filelist;

  $fd = get_fd_trunc("./temp.gnuplot");
  $cmd = "plot ";
  $i = 0;
  foreach ($filelist as $k => $v)
    {
      if ($i > 0)
	$cmd .= ", ";
      $cmd .= "\"".$k."\" title \"".$v["name"]."\" with ".$v["draw"];
      ++$i;
    }
  fwrite($fd, $cmd);
  fclose($fd);
}

function	make_tabs($fd, &$nb_nuages, &$point_list, &$orph_points)
{
	$nb_nuages = get_line($fd);
	for ($i = 0; ($temp = get_line($fd)) != "#"; ++$i)
	{
		$temp2 = explode(" ", $temp);
		$point_list[$i][x] = $temp2[0];
		$point_list[$i][y] = $temp2[1];
	}
	for ($i = 0; $temp = get_line($fd); ++$i)
	{
		$temp2 = explode(" ", $temp);
		$orph_points[$i][x] = $temp2[0];
		$orph_points[$i][y] = $temp2[1];
	}
}

function	barycentre(&$barry_c, $point_list, $orph_points)
{
	for ($i = 0, $barry_c[x] = 0, $barry_c[y] = 0; isset($point_list[$i]); ++$i)
	{
		$barry_c[x] += $point_list[$i][x];
		$barry_c[y] += $point_list[$i][y];
	}
	for ($j = 0; isset($orph_points[$j]); ++$j)
	{
		$barry_c[x] += $point_list[$j][x];
		$barry_c[y] += $point_list[$j][y];	
	}
	$barry_c[x] = $barry_c[x] / ($i + $j + 2);
	$barry_c[y] = $barry_c[y] / ($i + $j + 2);
}

function	categowit($fd)
{
	make_tabs($fd, $nb_nuages, $point_list, $orph_points);
	barycentre($barry_c, $point_list, $orph_points);
	print_r($barry_c);
}


function main($argc, $argv)
{
  global $cmd;
  if ($argc == 2)
    {
      $file = get_fd($argv[1]);
	  //Notre commande de traitement du fichier et calculs
	  categowit($file);
	  //Generer le fichier de config gnuplot

	  //Ligne pour lancer gnuplot avec le fichier commande qui contient les parametres pour l'affichage gnuplot ( voir "function generate_gnuplot_file()"
	 // exec("gnuplot temp.gnuplot", $output);
	}
  else
    usage();
}

$filelist = array();
$cmd = $argv[0];
main($argc, $argv)
?>

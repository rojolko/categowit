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

function main($argc, $argv)
{
  global $cmd;
  if ($argc == 2)
    {
      $file = get_fd($argv[1]);
	  //Notre commande de traitement du fichier et calculs
	  
	  //Generer le fichier de config gnuplot

	  //Ligne pour lancer gnuplot avec le fichier commande qui contient les parametres pour l'affichage gnuplot ( voir "function generate_gnuplot_file()"
	  exec("gnuplot temp.gnuplot", $output);
	}
  else
    usage();
}

$filelist = array();
$cmd = $argv[0];
main($argc, $argv)
?>

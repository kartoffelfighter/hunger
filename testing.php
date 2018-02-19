<?php

include 'vendor/autoload.php';
// Parse pdf file and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();

$keywords = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");

$names_to_pdfids = array(
    1 => "Naser",
    2 => "Dees"
);




// link zu dees (der nicht immer gleich ist)
$weeknumber = preg_replace("/\\b0*/","",date("W"));

// Montag der Woche:
$wochentag=strftime("%w",mktime(0,0,0,date("m"),date("d"),date("Y")))-1; 
if($wochentag==-1) $wochentag=6; 
$monday =  date("d.m",mktime(0,0,0,date("m"),date("d")-$wochentag,date("Y"))); 
$friday = substr($monday, 0, 2)+5;
$friday .= date(".m.Y");
$wochenstring = date("Y-");

$deesstring = "http://www.metzgerei-dees.de/wp-content/themes/MetzgereiDees/uploads/Menüplan-für-die-Woche-KW-";
$deesstring .= $weeknumber;
$deesstring .= date("-Y-");
$deesstring .= $monday;
$deesstring .= "-";
$deesstring .= $friday;
$deesstring .= "-neu.pdf";
//echo "<br> deesstring: <b> $deesstring </b>";
//$links_to_pdfs[2] = $deesstring;
////////////////////////////////////

$iii = 1;
while (1) {
    $try_for_404 = get_headers($deesstring, 1);
    if($try_for_404[0] != "HTTP/1.1 404 Not Found") {
        break;
    }
    if($try_for_404[0] == "HTTP/1.1 404 Not Found") {
        $deesstring = "http://www.metzgerei-dees.de/wp-content/themes/MetzgereiDees/uploads/Menüplan-für-die-Woche-KW-";
        $deesstring .= $weeknumber;
        $deesstring .= date("-Y-");
        $deesstring .= $monday;
        $deesstring .= "-";
        $deesstring .= $friday;
        $deesstring .= "-neu-";
        $deesstring .= $iii;
        $deesstring .= ".pdf";
}
    $iii++;
}

$links_to_pdfs = array(
    1 => "http://www.wurstnaser.de/Speiseplan1.pdf",
    2 =>  $deesstring           //"http://www.metzgerei-dees.de/wp-content/themes/MetzgereiDees/uploads/Menüplan-für-die-Woche-KW-7-2018-12.02-17.02.2018-neu.pdf"
);

$allAuthors = array();
$allMenues = array();
for ($currentPdf = 1; $currentPdf <= count($links_to_pdfs); $currentPdf++) {
    $pdf    = $parser->parseFile($links_to_pdfs[$currentPdf]);

// details
    $allAuthors[$currentPdf]  = $pdf->getDetails()["Author"];
 
// Loop over each property to extract values (string or array).
   
/*foreach ($details as $property => $value) {
        if (is_array($value)) {
           $value = implode(', ', $value);
        }
        echo $property . ' => ' . $value . "<br>";
    }
*/
// text

    $allMenues[$currentPdf] = $pdf->getText();
//print_r($text);
}


//var_dump($allMenues[2]);
//echo "<br>";

$parsedMenu = array();
// $parsedMenu[Tag][Hersteller][Item] = {Inhalt}

// regex für dees: 
array_push($keywords, "geni");
for($ii = 0; $ii <= count($keywords) - 2; $ii++) {
    echo "<b>$keywords[$ii]:</b><br>";
preg_match("/".$keywords[0]."(.+)geni/isU", $allMenues[2], $output_array);
preg_match("/".$keywords[$ii]."(.+)".$keywords[$ii+1]."/isU", $output_array[0], $output_array1);
preg_match("/".$keywords[$ii]."(.+)€/isU", $output_array1[0], $output_array2);
preg_match("/€(.+)€/isU", $output_array1[0], $output_array3);
echo($output_array2[1]);
$parsedMenu[$keywords[$ii]]["2"]["1"] = $output_array2[1];
echo "<br>";
echo($output_array3[1]);
$parsedMenu[$keywords[$ii]]["2"]["2"] = $output_array3[1];
echo "<br>";
}


/*
// regex für Naser:
//var_dump($allMenues[1]);
echo "<br>";
array_pop($keywords);
array_push($keywords, "essen");

for($ii = 0; $ii <= count($keywords) - 2; $ii++) {
    echo "<b>$keywords[$ii]:</b><br>";
preg_match("/Tagesessen(.+)rungen/isU", $allMenues[1], $output_array);
preg_match("/".$keywords[$ii]."(.+)".$keywords[$ii+1]."/isU", $output_array[0], $output_array1);
//var_dump($output_array1);

// Tagesgericht 1
preg_match("/1(.+)2/isU", $output_array1[0], $output_array2);

// Tagesgericht 2
preg_match("/2(.+)\b/ism", $output_array1[0], $output_array3);
preg_match("/2(.+)\b/ism", $output_array3[1], $output_array4);
preg_match("/2(.+)".$keywords[$ii+1]."/ism", $output_array4[1], $output_array5);
preg_match("/2(.+)".$keywords[$ii+1]."/ism", $output_array5[1], $output_array6);


echo($output_array2[1]);
$parsedMenu[$keywords[$ii]]["1"]["1"] = $output_array2[1];

echo "<br>";
if(!empty($output_array6)) {
    echo($output_array6[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array6[1];
}
elseif(!empty($output_array5)){
    preg_match("/2(.+)".$keywords[$ii+1]."/ism", $output_array4[1], $output_array5);
    echo($output_array5[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array5[1];
}
else {
    preg_match("/2(.+)".$keywords[$ii+1]."/ism", $output_array3[1], $output_array4);
    echo($output_array4[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array4[1];
}
echo "<br>";
}

//var_dump($parsedMenu);
*/
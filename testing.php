<?php

include 'vendor/autoload.php';
// Parse pdf file and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();

$keywords = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
$regex_keywords = array("M\s*o\s*n\s*t\s*a\s*g","D\s*i\s*e\s*n\s*s\s*t\s*a\s*g","M\s*i\s*t\s*t\s*w\s*o\s*c\s*h","D\s*o\s*n\s*n\s*e\s*r\s*s\s*t\s*a\s*g","F\s*r\s*e\s*i\s*t\s*a\s*g");

$names_to_pdfids = array(
    1 => "Naser",
    2 => "Dees"
);


// new dees pdf crawler
$data = file_get_contents("http://www.metzgerei-dees.de/"); // get source from site
$hyperfile = htmlspecialchars($data);   
preg_match("/metzgerei-dees.de\/wp-content\/themes\/metzgereidees\/uploads\/m(.+)pdf/isU", $hyperfile, $deeslink);  // regex to search for menu[].pdf
////echo $deeslink[0];  //
$deesstring = "http://www."; // build link string
$deesstring .= $deeslink[0];



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
        //echo $property . ' => ' . $value . "<br>";
    }
*/
// text

    $allMenues[$currentPdf] = $pdf->getText();
//print_r($text);
}


//var_dump($allMenues[2]);
////echo "<br>";

$parsedMenu = array();
// $parsedMenu[Tag][Hersteller][Item] = {Inhalt}

// regex für dees: 
array_push($regex_keywords, "g\s*e\s*n\s*i");
for($ii = 0; $ii <= count($regex_keywords) - 2; $ii++) {
    //echo "<b>$keywords[$ii]:</b><br>";
    preg_match("/".$regex_keywords[0]."(.+)g\s*e\s*n\s*i/isU", $allMenues[2], $output_array);
    preg_match("/".$regex_keywords[$ii]."(.+)".$regex_keywords[$ii+1]."/isU", $output_array[0], $output_array1);
    preg_match("/".$regex_keywords[$ii]."(.+)€/isU", $output_array1[0], $output_array2);
    preg_match("/€(.+)€/isU", $output_array1[0], $output_array3);
    //echo($output_array2[1]);
    $parsedMenu[$keywords[$ii]]["2"]["1"] = $output_array2[1];
    //echo "<br>";
    //echo($output_array3[1]);
    $parsedMenu[$keywords[$ii]]["2"]["2"] = $output_array3[1];
    //echo "<br>";
}



// regex für Naser:
//var_dump($allMenues[1]);
//echo "<br>";
array_pop($regex_keywords);
array_push($regex_keywords, "essen");

for($ii = 0; $ii <= count($regex_keywords) - 2; $ii++) {
    //echo "<b>$keywords[$ii]:</b><br>";
preg_match("/Tagesessen(.+)rungen/isU", $allMenues[1], $output_array);
preg_match("/".$regex_keywords[$ii]."(.+)".$regex_keywords[$ii+1]."/isU", $output_array[0], $output_array1);
//var_dump($output_array1);

// Tagesgericht 1
preg_match("/1(.+)2/isU", $output_array1[0], $output_array2);

// Tagesgericht 2
preg_match("/2(.+)\b/ism", $output_array1[0], $output_array3);
preg_match("/2(.+)\b/ism", $output_array3[1], $output_array4);
preg_match("/2(.+)".$regex_keywords[$ii+1]."/ism", $output_array4[1], $output_array5);
preg_match("/2(.+)".$regex_keywords[$ii+1]."/ism", $output_array5[1], $output_array6);


//echo($output_array2[1]);
$parsedMenu[$regex_keywords[$ii]]["1"]["1"] = $output_array2[1];

//echo "<br>";
if(!empty($output_array6)) {
    ////echo($output_array6[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array6[1];
}
elseif(!empty($output_array5)){
    preg_match("/2(.+)".$regex_keywords[$ii+1]."/ism", $output_array4[1], $output_array5);
    ////echo($output_array5[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array5[1];
}
else {
    preg_match("/2(.+)".$regex_keywords[$ii+1]."/ism", $output_array3[1], $output_array4);
    ////echo($output_array4[1]);
    $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array4[1];
}
//echo "<br>";
}

//var_dump($parsedMenu);

<?php

// pdf analizer for Mittagspausen
// (CC) Marc Fischer

// autoload from composer
include 'vendor/autoload.php';
// Parse pdf file and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();

// keywords um pdfs zu durchsuchen
$keywords = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag");
$regex_keywords = array("M\s*o\s*n\s*t\s*a\s*g", "D\s*i\s*e\s*n\s*s\s*t\s*a\s*g", "M\s*i\s*t\s*t\s*w\s*o\s*c\s*h", "D\s*o\s*n\s*n\s*e\s*r\s*s\s*t\s*a\s*g", "F\s*r\s*e\s*i\s*t\s*a\s*g");

// herstellernamen
$names_to_pdfids = array(
  1 => "Naser",
  2 => "Dees"
);

// new dees pdf crawler
$data = file_get_contents("http://www.metzgerei-dees.de/"); // get source from site
$hyperfile = htmlspecialchars($data);
preg_match("/metzgerei-dees.de\/wp-content\/themes\/metzgereidees\/uploads\/m(.+)pdf/isU", $hyperfile, $deeslink);  // regex to search for menu[].pdf
//echo $deeslink[0];  //
$deesstring = "http://www."; // build link string
$deesstring .= $deeslink[0];



$links_to_pdfs = array(
  1 => "http://www.wurstnaser.de/Speiseplan1.pdf",
  2 => $deesstring           //"http://www.metzgerei-dees.de/wp-content/themes/MetzgereiDees/uploads/Menüplan-für-die-Woche-KW-7-2018-12.02-17.02.2018-neu.pdf"
);

if (strlen($deesstring) >= 200) {
  array_pop($links_to_pdfs);
  $error = 1;         // throw error, if dees didn't upload a correct pdf (f.e. .docx instead)
}


// init arrays
$allAuthors = array();
$allMenues = array();

// pdf dateeien parsen
for ($currentPdf = 1; $currentPdf <= count($links_to_pdfs); $currentPdf++) {
  $pdf = $parser->parseFile($links_to_pdfs[$currentPdf]);
// details
  $allAuthors[$currentPdf] = $pdf->getDetails()["Author"];
// text
  $allMenues[$currentPdf] = $pdf->getText();
}

//var_dump($allMenues[2]);
//echo "<br>";

// init multi-dimensionales array mit allen mittagessen
$parsedMenu = array();
// $parsedMenu[Tag][Hersteller][Item] = {Inhalt}

$parsedMenu = array();
// $parsedMenu[Tag][Hersteller][Item] = {Inhalt}

// regex für dees: 
array_push($regex_keywords, "g\s*e\s*n\s*i");
for ($ii = 0; $ii <= count($regex_keywords) - 2; $ii++) {
    //echo "<b>$keywords[$ii]:</b><br>";
  preg_match("/" . $regex_keywords[0] . "(.+)g\s*e\s*n\s*i/isU", $allMenues[2], $output_array);
  preg_match("/" . $regex_keywords[$ii] . "(.+)" . $regex_keywords[$ii + 1] . "/isU", $output_array[0], $output_array1);
  preg_match("/" . $regex_keywords[$ii] . "(.+)€/isU", $output_array1[0], $output_array2);
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

// new v1.2 naser pdf analyzer
// get monday of current week

$wochentag = strftime("%w", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) - 1;
if ($wochentag == -1) $wochentag = 6;
$monday = date("d.m", mktime(0, 0, 0, date("m"), date("d") - $wochentag, date("Y")));


array_pop($regex_keywords);
array_push($regex_keywords, "essen");

for ($ii = 0; $ii <= count($regex_keywords) - 2; $ii++) {
    //echo "<b>$keywords[$ii]:</b><br>";
  preg_match("/Tagesessen(.+)rungen/isU", $allMenues[1], $output_array);
  preg_match("/" . $regex_keywords[$ii] . "(.+)" . $regex_keywords[$ii + 1] . "/isU", $output_array[0], $output_array1);
//var_dump($output_array1);





// Tagesgericht 1
  preg_match("/1(.+)2/isU", $output_array1[0], $output_array2);

// Tagesgericht 2
  preg_match("/1\s*8\s*2(.+)\b/ism", $output_array1[1], $output_array3);        // Durchsuche nach "1 8 2", egal was dazwischen steht (Jahreszahl '18, Gericht 2), funktioniert ggf. nicht am 18.02. oder an Tagen, an denen die kombo vorkommt
  preg_match("/2(.+)" . $regex_keywords[$ii + 1] . "/ism", $output_array3[2], $output_array4);      // Trimmt den Wochentag weg

  $parsedMenu[$keywords[$ii]]["1"]["1"] = $output_array2[1];
  $parsedMenu[$keywords[$ii]]["1"]["2"] = $output_array3[1];

}
array_pop($regex_keywords);       // letztes word wieder aus den keywords löschen
//var_dump($parsedMenu);


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="./favicon.ico">

    <title>ich habe huuuuuuuunger!!</title>

    <!-- Bootstrap core CSS -->
    <link href="./dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./dist/offcanvas.css" rel="stylesheet">
  </head>

  <body class="bg-light">

    <nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark">
      <a class="navbar-brand" href="#">Such dir was aus, du Hungerhaken!</a>
      <button class="navbar-toggler p-0 border-0" type="button" data-toggle="offcanvas">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="navbar-collapse offcanvas-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            
          </li>
          </ul>
      </div>
    </nav>

    <div class="nav-scroller bg-white box-shadow">
      <nav class="nav nav-underline">
        <a class="nav-link current" href="#">Analysierte pdfs:</a>
        <?php 
        foreach ($links_to_pdfs as $id => $value) {
          ?>
            <a class="nav-link" href="<?php echo $value; ?>"><?php echo $names_to_pdfids[$id]; // analysierte pdf-dateien ausgeben (quellen)?></a>       
        <?php

      };
      ?>
      <?php if($error > 0){
        ?>
        <a class="nav-link text-danger" href="#">Keine gültige PDF von "dees"</a>
        <?php
      }
      ?>
      </nav>
    </div>

    <main role="main" class="container">
      <div class="d-flex align-items-center p-3 my-3 text-white-50 bg-purple rounded box-shadow">
        <img class="mr-3" src="https://getbootstrap.com/assets/brand/bootstrap-outline.svg" alt="" width="48" height="48">
        <div class="lh-100">
          <h6 class="mb-0 text-white lh-100">Aktueller Speißeplan:</h6>
          <small>KW <?php echo date("W"); ?>, heute ist der <?php echo date("d.m.Y"); ?></small>
        </div>
      </div>


<?php for ($i = 0; $i <= count($keywords) - 1; $i++) {     // Menüliste ausgeben
  ?>


      <div class="my-3 p-3 bg-white rounded box-shadow">
        <h6 class="border-bottom border-gray pb-2 mb-0"><?php echo $keywords[$i]; ?></h6>
        <div class="media text-muted pt-3">
          <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
            <strong class="d-block text-gray-dark">Naser</strong>
            Essen 1: <?php echo $parsedMenu[$keywords[$i]]["1"]["1"]; ?> 5,40€<br>
            Essen 2: <?php echo $parsedMenu[$keywords[$i]]["1"]["2"]; ?> 4,50€
          </p>
        </div>
        <?php if($error < 1) { ?>
        <div class="media text-muted pt-3">
          <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
            <strong class="d-block text-gray-dark">Dees</strong>
            Essen 1: <?php echo $parsedMenu[$keywords[$i]]["2"]["1"]; ?><br>
            Essen 2: <?php echo $parsedMenu[$keywords[$i]]["2"]["2"]; ?>
          </p>
        </div>
        <?php 
        }
        ?>
      </div>
    <?php 
  }; ?>




      <div class="d-flex align-items-center text-light bg-secondary p-3 my-3 rounded box-shadow">
        <div class="lh-100">
          <h6 class="mb-0 lh-100">Analysierte PDFs:</h6>
          <?php 
          foreach ($links_to_pdfs as $id => $value) {
            ?>
            <small><?php echo $names_to_pdfids[$id]; ?>, erstellt von <?php echo $allAuthors[$id]; ?> <a class="text-info" href="<?php echo $value; ?>">Link</a></small><br>
        <?php

      }; // analysierte pdf-dateien und deren autoren
      ?>
        </div>
      </div>
      
      <div class="d-flex align-items-center text-light bg-secondary p-3 my-3 rounded box-shadow">
        <div class="lh-100">
          <h6 class="mb-0 lh-100">Disclaimer:</h6>
          <br>
          <p>Ich stehe in keiner Verbindung zu den o.g. Restaurants/Metzgereien/Bistros etc. <br> Ich beziehe durch diese Seite keine Vorzüge. <br> Diese Seite dient ledeglich der Information.<br>Alle Angaben ohne Gewähr</p>
          <br>
          <a class="text-info" href="https://github.com/kartoffelfighter/hunger">get the source code</a><br>
          <small class="text-light">Impressum: Verantwortlich für den Inhalt dieser Seite (sofern nicht automatisch durch algorithmen ausgewerterte Informationen): Marc Fischer (Kontakt: marc{a}marcfischer.org)</small>
        </div>
      </div>
    </main>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="./assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="./assets/js/vendor/popper.min.js"></script>
    <script src="./dist/js/bootstrap.min.js"></script>
    <script src="./assets/js/vendor/holder.min.js"></script>
    <script src="./dist/offcanvas.js"></script>
  </body>
</html>

<?php

#CST:xmelic17

//Pole s retezcem napovedy
$help = "--input=fileordir: 
Zadany vstupni soubor nebo adresar se zdrojovym kodem v jazyce C. Soubory v 
kodovani ISO-8859-2. Zadanim konkretniho souboru je analyzovan pouze tento 
soubor. Jeli zadan adresar, tak jsou analyzovany vsechny soubory s priponou 
(.c, .h) v tomto adresari a vsech jeho podadresarich. Pokud nebude tento
parametr zadan, tak se analyzuji soubory (s priponou .c a .h) z aktualniho 
adresare a vsech jeho podadresaru. Je-li zadan adresar a nektery z analyzovanych
souboru nelze cist, ukonci se skript s chybovym hlasenim a navratovym kodem 21.

--nosubdir: 
Prohledavani bude provadeno pouze v zadanem adresari bez souboru v 
podadresarich. Parametr se nesmi kombinovat se zadanim konkretniho souboru 
pomoci parametru --input.

--output=filename: 
Zadany textovy vystupni soubor v kodovani ISO-8859-2.

-k: 
Vypise pocet vsech vyskytu klicovych slov (mimo poznamky a retezce) v kazdem 
zdrojovem souboru a celkem (pro vsechny analyzovane soubory).

-o: 
Vypise pocet vyskytu jednoduchych operatoru (nikoliv oddelovacu apod.) mimo 
poznamky, znakove literaly a retezce v kazdem zdrojovem souboru a celkem (pro 
vsechny analyzovane soubory). Jednoduchy operator definujme jako danou dopredu 
znamou a fixni posloupnost nepismennych znaku.

-i:
Vypise pocet vyskytu identifikatoru (mimo poznamky, znak. literaly, retezce)
v kazdem zdrojovem souboru a celkem (pro vsechny analyzovane soubory).
Nezahrnuje klicova slova. 

w=pattern:
Vyhleda textovy retezec pattern ve vsech zdrojovych kodech a vypise pocet 
neprekryvajicich se vyskytu na soubor i celkem. Jelikoz se nejedna o 
identifikator ale retezec, hleda se i v poznamkach, makrech, znakovych 
literalech a retezcich!

-c:
Vypise celkovy pocet znaku komentaru vcetne uvozujicich znaku komentaru 
(//, /* a */) na soubor a celkem (pro vsechny analyzovane soubory). Komentare 
pocitame vcetne znaku konce radku uvnitr blokoveho komentare a v pripade 
radkoveho komentare take znaku konce radku.

-p:
V kombinaci s predchozimi (az na --help) zpusobi, ze soubory se budou vypisovat
bez (absolutni) cesty k souboru (samotna jmena souboru, radi se pouze podle 
jmena souboru).";


$ridici_parametry = array("-k", "-o", "-i", "-c");
$nosubdir = FALSE;// Promenna nastavujici se podle parametru --nosubdir
$input_zadano = FALSE;// Promenna nastavujici se pri zadani parametru --input
$parametr_p = FALSE;// Promenna nastavujici se pri zadani parametru -p
$zadan_parametr = FALSE;// Promenna pro kontrolu opakovani parametru
$output_zadano = FALSE;// Promenna pro kontrolu opakovani parametru

$file_out = fopen('php://stdout', 'w');

foreach($argv as $hodnota){
// Podminka urcujici parametr napoveda
  if($hodnota == "--help"){
    if($argc > 2){
      print_err(1);
      exit(1);
      }
    printf("%s\n", $help);
    exit(0);
  }

// Podminka urcujici parametr vstupniho souboru nebo adresare
  elseif(preg_match("/^--input=.*$/", $hodnota)){
    if($input_zadano == TRUE){
      print_err(1);
      exit(1);
    }
    $in_file_dir = explode("=", $hodnota); //Vytvari pole
    $input_zadano = TRUE;
  }

// Podminka urcujici nazev vystupniho souboru
  elseif(preg_match("/--output=.*$/", $hodnota)){
    if($output_zadano == TRUE){
      print_err(1);
      exit(1);
    }
    
    $out_file = explode("=", $hodnota);//Vytvari pole

    // Podminka pro urceni zda byl zadan soubor a ne adresar
    if(!preg_match("/^.*\..*$/", $out_file[1])){
      print_err(3);
      exit(3);
    }
        
    /* Silena podminka overujici zda soubor existuje, je zapisovatelny a jde otevrit nebo zda soubor *
     * neexistuje a jde vytvorit. 
     */
    if((file_exists($out_file[1]) && is_writable($out_file[1]) && ($file_out = fopen($out_file[1], "w"))) || 
    ((file_exists($out_file[1]) === false) && ($file_out = fopen($out_file[1], "w"))))
      $output_zadano = true;
      
    else{
      print_err(3);
      exit(3);
    }
  }
 
// Podminka urcujici parametr -w pro vyhledavani retezce 
  elseif(preg_match("/^-w=.*$/", $hodnota)){
    if($zadan_parametr == TRUE){
      print_err(1);
      exit(1);
    }
    
    $hledany_retezec = explode ("=", $hodnota);// Vytvari pole
    $ridici_parametr = $hledany_retezec[0];
    $zadan_parametr = TRUE;
  }
  
// Podminka pro kontrolu parametru -k, -i, -c, -o
  elseif(in_array($hodnota, $ridici_parametry)){
  if($zadan_parametr == TRUE){
      print_err(1);
      exit(1);
    }
    $ridici_parametr = $hodnota;
    $zadan_parametr = TRUE;
  } 
 
// Podminka pro urceni parametru --nosubdir 
  elseif($hodnota == "--nosubdir"){
    if($nosubdir == TRUE){
      print_err(1);
      exit(1);
    }
    $nosubdir = TRUE;
  }

// Podminka pro urceni parametru -p    
  elseif($hodnota == "-p"){
    if($parametr_p == TRUE){
      print_err(1);
      exit(1);
    }
    $parametr_p = TRUE;
  }
   
  /* Podminka osetrujici prvni parametr (nazev souboru) a zaroven *
   * spravnost parametru.                                         */ 
  else{
    if($argv[0] != $hodnota){
      print_err(1);
      exit(1);
    }
  }
}


// Pokud nebyl zadan parametr --input
  if($input_zadano == FALSE)
    $in_file_dir[1] = ".";

  // Overeni zda byl do parametru --input zadan soubor nebo adresar
  if($dir = (is_dir($in_file_dir[1]))){
    $seznam_souboru = obsah_adresare($in_file_dir[1], $nosubdir);
    
  }
  else
    $seznam_souboru[0] = $in_file_dir[1];
  
  
  /* Do promenne $file_in se ulozi jmeno souboru, z toho jak se postupne prochazi pole. *
   * Do promenne $otevreny_vstup_soubor se ulozi ukazatel na otevreny soubor.           */
  foreach($seznam_souboru as $file_in){
  // Byl zadan adresar a nektery soubor nelze otevrit pro cteni
    if(($dir == TRUE) and (!($otevreny_vstup_soubor = fopen($file_in, "r")))){
      print_err(4);
      exit(21);
    }
    // Byl zadan vstupni soubor a doslo k chybe pri otevreni
    elseif(($dir == FALSE) and (($otevreny_vstup_soubor = open_file($in_file_dir[1])) == 2)){
      print_err(2);
      exit(2);
    }

    // Pokud byl zadan parametr -p, vypise se vysledek jen s nazvy souboru
    if($parametr_p == TRUE){
      $info_soubor = pathinfo($file_in);
      if($ridici_parametr != "-w"){
        $vysledne_pole[$info_soubor["basename"]] = analyza($otevreny_vstup_soubor, $ridici_parametr);
        }
      else
        $vysledne_pole[$info_soubor["basename"]] = hledani_retezce($file_in, $hledany_retezec[1]);
    }
    else{
      $cesta_souboru = realpath($file_in);
      if($ridici_parametr != "-w"){
        $vysledne_pole[$cesta_souboru] = analyza($otevreny_vstup_soubor, $ridici_parametr);
        }
      else
        $vysledne_pole[$cesta_souboru] = hledani_retezce($file_in, $hledany_retezec[1]);
    }        
  }
  
  // Osetreni adresare, ktery neobsahuje zadne soubory
  if(count($seznam_souboru) == 0)
    $vysledne_pole[] = NULL;
  
  formatovany_vypis($vysledne_pole, $file_out);



//----------------------------------------------------------------------------
// Pomocne funkce
/* Funkce pro vypis chybovych stavu. Jako parametr funkce ocekava cislo identifikujici chybu. */
function print_err($cislo)
{
  --$cislo;
  $chyba_vypis = array("Zadan neplatny parametr nebo jejich kombinace.",
                       "Nelze otevrit vstupni soubor.",
                       "Nelze otevrit vystupni soubor.",
                       "Doslo k chybe pri cteni souboru ze zadaneho adresare.",
                       "Doslo k vnitrni chybe programu pri hledani vyskytu retezce.");
  $stderr = fopen('php://stderr', 'w');
  fprintf($stderr, "%s\n", $chyba_vypis[$cislo]);
  fclose($stderr);  
}


/* Funkce pro overeni existence souboru a jeho otevreni. Jako parametr se zde ocekava adresa k souboru. */
function open_file($adresa_soubor)
{
  if(!file_exists($adresa_soubor)){
    return 2;
  }
  $soubor = fopen($adresa_soubor, "r");
  return $soubor;
}


/* Funkce pro analyzu vstupnich souboru. Parametry funkce jsou: ukazatel na otevreny vstupni souboru,  *
 * parametr urcujici typ zpracovani - komentare, klicove slova, identifikatory, operandy. Funkce vraci *
 * promenou, ktera obsahuje pocet vyskyt zvoleneho parametru.                                          */
function analyza($vstupni_soubor, $parametr)
{
  // V poli jsou ulozene retezce obsahujici vsechny klicove slova
  $konstanta = array("auto", "break", "case", "char", "const", "continue", "default", "do", 
                     "double", "else", "enum", "extern", "float", "for", "goto", "if", "int", 
                     "long", "register", "return", "short", "signed", "sizeof", "static", 
                     "struct", "switch", "typedef", "union", "unsigned", "void", "volatile", 
                     "while", "restrict", "inline", "_Bool", "_Complex", "_Imaginary");
  
  // V poli jsou ulozene vsechny povolene kombinace operatoru
  $operator = array(">>=", "<<=", "==", ">>", "<<", "&&", "--", "++", "||", "+=", "-=", "*=",
                     "/=", "%=", "&=", "|=", "^=", "<=", ">=", "->", "!=", "=", "!", "&", "|",
                     "^", "<", ">", "+", "-", "*", "/", "~", ".", "%");
                     
                     
  $pocet_znaku = 0;// Promenna pro pocet komentaru
  $pocet_klic_slovo = 0;// Promenna pro pocet vyskytu klic. slova
  $pocet_identifikatoru = 0;// Promenna pro pocet vyskytu indentifikatoru
  $pocet_operatoru = 0;// Promenna pro pocet vyskytu operatoru
	$stav = "Sstart";// Pocastecni stav
  $zpet_lomitko = FALSE;// Promenna pro zpetne lomitko v def. makra

  // KA pro analyzu souboru
	while (FALSE !== ($nacteny_znak = fgetc($vstupni_soubor))){
  //echo $nacteny_znak." - ".$stav."\n";
    switch ($stav){
      // Uvodni stav
			case "Sstart":
        // Prvni znak poznamky
				if ($nacteny_znak == "/"){
					$stav = "Spoznamka";
        }
        
        // Urcuje zda znak muzet patrit do klicove slova nebo identifikatoru
        elseif((($nacteny_znak >= "a") and ($nacteny_znak <= "z")) or
               (($nacteny_znak >= "A") and ($nacteny_znak <= "Z")) or
                ($nacteny_znak == "_")){
          $stav = "Sje_klic_slovo";
          $retezec[] = $nacteny_znak;
        }
        
        // Definice makra
        elseif($nacteny_znak == "#")
          $stav = "Smakro";
        
        // Prvni znak retezce
        elseif($nacteny_znak == "\"")
          $stav = "Sretezec";
          
        // Prvni znak literalu
        elseif($nacteny_znak == "'")
          $stav = "Sliteral";
          
        // Prvni znak ridiciho znaku
        elseif($nacteny_znak == "\\")
          $stav = "Sridici_znak";
        
        // Urcuje zda znak muze patrit cislu
        elseif(($nacteny_znak >= "0") and ($nacteny_znak <= "9"))
          $stav = "Scislo";
          
        // Urcije zda znak patri operandu
        elseif(preg_match("#[><=&\-\+\|\*/\^!~\.%]#", $nacteny_znak)){
         $pole_operatoru[] = $nacteny_znak;
          $stav = "Soperator";
        }
				else
					$stav = "Sstart";
				break;

        
      // Stav rozhodujici o jaky typ poznamky se jedna, pripadne zda je nacteny znak pouze operator
			case "Spoznamka":
				if ($nacteny_znak == "/"){
					$stav = "Srad_poznamka";
					$pocet_znaku += 2;
				}
				elseif ($nacteny_znak == "*"){
					$stav = "Sblok_poznamka";
					$pocet_znaku += 2;
				}
				else{
          $pocet_operatoru++;// Nejedna se o poznamku ale o operator
					$stav = "Sstart";
				}
				break;

        
      // Stav zpracujici blokovy komentar
			case "Sblok_poznamka":	
				if (($predesly_znak == "*") && ($nacteny_znak == "/")){
					$stav = "Sstart";
				}
        if($nacteny_znak != "\r"){
            $pocet_znaku++;
        }
				else{
					$stav = "Sblok_poznamka";
				}				
        break;

        
      // Stav zpracujici radkovy komentar
			case "Srad_poznamka":
        // Podminka pro zpracovani znaku konce radku pro Windows
				if(($predesly_znak == "\r") and ($nacteny_znak == "\n")){
          $stav = "Sstart";
				}
        // Podminka pro zpracovani znaku konce radku pro Linux
        elseif($nacteny_znak == "\n"){
          $stav = "Sstart";
          $pocet_znaku++;
        }
				else{
          $pocet_znaku++;
          $stav = "Srad_poznamka";
        }
				break;
       
      
      // Stav identifikuje klicove slovo nebo identifikator 
      case "Sje_klic_slovo":
        if((($nacteny_znak < "a") or ($nacteny_znak > "z")) and
           (($nacteny_znak < "A") or ($nacteny_znak > "Z")) and 
           (($nacteny_znak < "0") or ($nacteny_znak > "9")) and
            ($nacteny_znak != "_")){
          //print_r($retezec);
          $retezec[0] = join("", $retezec);
          if(in_array($retezec[0], $konstanta)){
            //fprintf($file_test, "%s - %d\n", $retezec[0], $pocet_klic_slovo); 
            $pocet_klic_slovo++;
          }
          else{
            $pocet_identifikatoru++;
            //$ahoj[] = $retezec[0];
          }  
            
          unset($retezec);
          // Posunuti ukazatele v souboru o jeden znak zpatky
          fseek($vstupni_soubor, -1, SEEK_CUR);
          $stav = "Sstart";
        }
        else{
          $stav = "Sje_klic_slovo";
          $retezec[] = $nacteny_znak;
        }
        break;
      
      
      // Stav zpracuje definici makra
      case "Smakro":
        if($predesly_znak == "\\")
          $zpet_lomitko = TRUE;
        elseif($nacteny_znak == "\n"){
          if($zpet_lomitko != TRUE)
            $stav = "Sstart";
          else{
            $stav = "Smakro";
            $zpet_lomitko = FALSE;
          }
        }
				else
          $stav = "Smakro";
				break;
       
      
      // Stav resici retezce 
      case "Sretezec":
        if($nacteny_znak == "\"")
          $stav = "Sstart";
        else  
          $stav = "Sretezec";
        break;
      
      
      // Stav zpracuje znakovy literal
      case "Sliteral":
        if($nacteny_znak == "'")
          $stav = "Sstart";
        else  
          $stav = "Sliteral";
        break;
      
      
      // Stav resi zda se jedna o cislo
      case "Scislo":
          if(preg_match("/[0-9eEfFlLxXabcdABCDUu\.]/", $nacteny_znak)){
            $stav = "Scislo";
          }
          else{
            $stav = "Sstart";
            fseek($vstupni_soubor, -1, SEEK_CUR);
          }
        break;
      
      
      // Stav vyhodnocuje operatory
      case "Soperator":
        if(preg_match("#[><=&\-\+\|\*/\^!~\.%]#", $nacteny_znak)){
          $pole_operatoru[] = $nacteny_znak;
          if(($predesly_znak == "*") and ($nacteny_znak == "*"))
            $stav = "Snekolika_ukazate";
          else
            $stav = "Soperator";
        }
        elseif(($predesly_znak == ".") and (preg_match("/[0-9]/", $nacteny_znak)))
          $stav = "Scislo";
        else{
          $vysledny_operator = join("", $pole_operatoru); 
          if(in_array($vysledny_operator, $operator)){
            $pocet_operatoru++;
          }
          else{
            if(($pole_operatoru[0] = "=") and (strlen($vysledny_operator) <= 2)){
              $pocet_operatoru += 2;
            }
          }
          $stav = "Sstart";
          unset($pole_operatoru);
          fseek($vstupni_soubor, -1, SEEK_CUR);
        }
        break;
      
  
      // Stav resici nekolika-nasobne ukazatele
      case "Snekolika_ukazate":
        if($nacteny_znak == "*"){
          $pocet_operatoru++;
          $stav = "Snekolika_ukazate";
        }
        else{  
          $pocet_operatoru += 2;
          unset($pole_operatoru);
          fseek($vstupni_soubor, -1, SEEK_CUR);
          $stav = "Sstart";
        }
        break;
      
      
      // Stav zpracuje ridici znak
      case "Sridici_znak":
        $stav = "Sstart";
        break;
        
			default:
        break;
		}
    
		$predesly_znak = $nacteny_znak;
	}
  
  

  switch ($parametr){
    case "-c":
      return $pocet_znaku;
      break;
    case "-k":
      return $pocet_klic_slovo;
      break;
    case "-i":
      return $pocet_identifikatoru;
      break;
    case "-o":
      return $pocet_operatoru;  
      break;
  }
}


/* Funkce pro rekurzivni prochazeni adresaru. Paraemtry funkce jsou: adresa zadaneho    * 
 * adresare a parametr --nosubdir, ktery urci zda se prohleda cela adresarova struktura *
 * nebo se hledani omezi pouze na aktualni adresar.                                     */
function obsah_adresare($start_adresar, $nosubdir)
{
  $vystup = array();
  foreach(scandir($start_adresar) as $soubor) {
     if(($soubor == ".") or ($soubor == "..")) 
      continue;
    $abs_soubor = $start_adresar.DIRECTORY_SEPARATOR.$soubor;
      
    if(is_dir($abs_soubor)){
      if($nosubdir == FALSE)
        $vystup = array_merge($vystup, obsah_adresare($abs_soubor, $nosubdir));
    }
    
    else{
      $info_soubor = pathinfo($abs_soubor);// Ziskani koncovky souboru
      if(count($info_soubor) < 4)
        continue;
      if(($info_soubor["extension"] == "c") or ($info_soubor["extension"] == "h"))
        $vystup[] = $abs_soubor;// Pridani souboru do pole
    }
  }
  
  return $vystup;
}


/* Funkce pro vyhledavani retezce. Funkce ocekava paramtr jmena souboru a hledany retezec. */
function hledani_retezce($file_in, $hledany_retezec)
{
  if(($nacteny_soubor = file_get_contents($file_in)) === false){
    print_err(5);
    exit(11);
  }
  else
    $vyskyt_retezce = substr_count($nacteny_soubor, $hledany_retezec);
  
  return $vyskyt_retezce;
}


/* Funkce pro spravne formatovany vypis. Parametry funkce je vysledne pole vysledku, ktere *
 * obsahuje jmena souboru a pocet vyskytu zvoleneho parametru a ukazatel na otevreny       *
 * vystupni souboru.                                                                       */
function formatovany_vypis($pole_vysledku, $fp)
{
  $delka_pole = count($pole_vysledku);
  ksort($pole_vysledku);// Sezareni pole podle klicu
  $delka_max = 0;
  $celkem = 0;
  $pole_vysledku["CELKEM:"] = 0;
  $max_nazev = 0;
  $hodnota_max = 0;
  
  // Projdu pole a vypocitam nejdelsi delku retezce
  foreach($pole_vysledku as $key => $hodnota){
    $celkem = $celkem + $hodnota;
    if($key == "CELKEM:"){
      $hodnota = $celkem;
      $pole_vysledku[$key] = $hodnota;
    }    
    $delka_key = strlen($key);
    $delka_hodnota = strlen($hodnota);
    if($max_nazev < $delka_key)
      $max_nazev = $delka_key;
    if($hodnota_max < $delka_hodnota)
      $hodnota_max =$delka_hodnota;
    $delka = $max_nazev + $hodnota_max + 1;
    if($delka > $delka_max)
      $delka_max = $delka;
  }

  /* Prochazim pole, vypocitavam potrebny pocet mezer pro spravne *
   *zarovnani, a vypisu na vystup.                                */
  foreach($pole_vysledku as $key => $hodnota){
    /* Osetreni pokud zadany adresar neobsahoval zadne soubory.            *
     * Hodnota $key puvodne nastavena na NULL nude pri vypisu ingnorovana. */
    if(($key == "0") and ($hodnota == 0))
      continue;
    $delka_key = strlen($key);
    $delka_hodnota = strlen($hodnota);
    $pocet_mezer = $delka_max - $delka_hodnota - $delka_key - 1;
    $radek_vypisu[] = $key." ";
    for($i = 0; $i < $pocet_mezer; $i++){
      $radek_vypisu[] = chr(32);
    }
    $radek_vypisu[] = $hodnota;
    $radek_vypisu[0] = join("", $radek_vypisu);
    iconv("ASCII", "ISO-8859-2//TRANSLIT", $radek_vypisu[0]);
    fprintf($fp, "%s\n", $radek_vypisu[0]);
    unset($radek_vypisu);// Zniceni daneho pole po provedeni vypisu
  }
  
  fclose($fp);
  exit(0);
}
?>
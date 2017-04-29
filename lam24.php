<?php
$url = "https://lam24.pl/wyniki.aspx";
$zlecenie = "xxxxxxxx";
$pesel = "xxxxxxxxx";

$f = "lam24.ini";    //String file path
$size = filesize($f);  // File size (how much data to read)
$fH = fopen($f,"r");   // File handle
$data = fread($fH,$size);  // Read data from file handle
fclose($fH);
if(strpos($data,'true') !== false) {
exit();
}

$ckfile = tempnam("/tmp", "CURLCOOKIE");
$useragent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/5.0.342.3 Safari/533.2';

/**
    Get __VIEWSTATE & __EVENTVALIDATION
 */
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

$html = curl_exec($ch);

curl_close($ch);

preg_match('~<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" />~', $html, $viewstate);
preg_match('~<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" />~', $html, $eventValidation);

$viewstate = $viewstate[1];
$eventValidation = $eventValidation[1];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_REFERER, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

$postfields = array();
$postfields['__EVENTTARGET'] = "";
$postfields['__EVENTARGUMENT'] = "";
$postfields['__VIEWSTATE'] = $viewstate;
$postfields['__EVENTVALIDATION'] = $eventValidation;
$postfields['Login$UserName'] = $zlecenie;
$postfields['Login$Password'] = $pesel;
$postfields['Login$loginButton'] = 'PokaÅ¼ wyniki';

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
$ret = curl_exec($ch); // Get result after login page.
if(strpos($ret,'Nie znaleziono wynik') !== true) {
$fp = fopen($f, 'w');
fwrite($fp, 'true');
fclose($fp);

$to      = 'your@email.pl';
$subject = 'Wyniki Lam24';
$message = 'Automatyczny skrypt, ktory sprawdza dostepnosc wynikow co 15 minut informuje za sa one juz dostepne:' . "\r\n" . 'https://lam24.pl/wyniki.aspx' . "\r\n" .
        'Zlecenie: ' . $zlecenie . "\r\n" .
        'Pesel: ' . $pesel;
$headers = 'From: mail@server.pl' . "\r\n" .
    'Reply-To: nmail@server.pl' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
}
curl_close($ch);
?>

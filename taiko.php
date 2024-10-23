<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taiko daily points</title>
    <!-- Dołączenie Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label for="address">Address:</label><br>
        <input type='text' id="address" name="address" size=50 placeholder="Address" value='<?php echo $_POST['address'] ?? ''; ?>'></input><br><br>
        <input type="submit" value="Sprawdź">
    </form>


<?php

$address = $_POST['address'] ?? '';

if($address != ''){
    // URL do API
    $base_url = "https://trailblazer.mainnet.taiko.xyz/s2/user/history?address=$address&page=";

    $today = '';
    $points = array('Transaction'=>0,'TransactionValue'=>0);
    $exit_main_loop = '';
    $j = 0;

    for($i=0;$i<=10;$i++){

        $url = $base_url . $i;

        echo "Read $url<br/>";

        // Ścieżka do pobranego pliku cacert.pem (dodaj tutaj rzeczywistą ścieżkę)
        $cacertPath = __DIR__ . "/cacert.pem";  // Umieść w odpowiedniej lokalizacji

        // Inicjalizacja cURL
        $ch = curl_init();

        // Ustawienia cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Symulowanie przeglądarki przez dodanie nagłówków
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0', // Symuluje przeglądarkę Firefox
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', // Typowe wartości akceptowane
            'Accept-Language: en-US,en;q=0.5', // Język przeglądarki
            'Connection: keep-alive', // Utrzymywanie połączenia
            'Upgrade-Insecure-Requests: 1' // Obsługuje przekierowanie do HTTPS
        ]);

        // Weryfikacja certyfikatu SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Ustawienie ścieżki do certyfikatów CA
        curl_setopt($ch, CURLOPT_CAINFO, $cacertPath);

        // Wykonanie zapytania cURL
        $response = curl_exec($ch);

        // Sprawdzenie, czy nie wystąpił błąd
        if ($response === false) {
            echo 'Błąd cURL: ' . curl_error($ch);
        } else {
            // Dekodowanie JSON do tablicy PHP
            $data = json_decode($response, true);

            // Wyświetlanie danych
            //echo "<pre>";
            //var_dump($data);
            //echo "</pre>";
            //die();

            if(count($data['items']) == 0){
                echo "Brak danych dla tego adresu!";
                die();
            }

            foreach($data['items'] as $n => $n_tab){
                if($today == '') {
                    $today = date("Y-m-d", $n_tab['date']);
                    $last_tran_date = $n_tab['date'];
                }

                if($today != date("Y-m-d", $n_tab['date'])){
                    echo "<font color='red'>Wychodze na stronie $i</font>";
                    $exit_main_loop = 'Y';
                    break;
                }

                $points[$n_tab['event']] += $n_tab['points'];
                if($n_tab['event'] == 'Transaction') {
                    $j++;
                }
            }

            if($exit_main_loop == 'Y') break;

        // Zamknięcie cURL
        curl_close($ch);
        }
    }

    $dateTimeUTC = new DateTime("@$last_tran_date");
    $dateTimeUTC->setTimezone(new DateTimeZone('GMT+2')); 
    $dateText = $dateTimeUTC->format('Y-m-d H:i:s');

    echo "<h2>Transaction: ". $points['Transaction']  ."</h2>";
    echo "<h2>Transaction Value: ". $points['TransactionValue']  ."</h2>";
    echo "<h2>Last tran date: ". $dateText ."</h2>";
    echo "<h2>Tran count: ". $j ."</h2>";
} else {
    echo "Brak adresu!";
}
?>


</body>
</html>
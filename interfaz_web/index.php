<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Karla&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet"> 
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.3.2/dist/chart.min.js"></script>
    <!-- Normalize.css -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- CSS propio -->
    <link rel="stylesheet" href="css/main.min.css">
    <!-- Icon -->
    <link rel="shortcut icon" href="icon/toll-plaza.svg">
    <title>Aforamiento Inteligente Vehículos</title>
    <?php
        error_reporting(0);
        require_once('config/config.php');
        
        date_default_timezone_set('Europe/Madrid');
        $passphrase = "822e130304addcbd3852619ccdbdd1a81152b353ffc4d0e69ed38787e37f22bb";
        $totalHoy = 0;

        // Función para desencriptar los datos
        function decrypt_data($data, $passphrase) {
            $secret_key = hex2bin($passphrase);
            $json = json_decode(base64_decode($data));
            $iv = base64_decode($json->{'iv'});
            $encrypted_64 = $json->{'data'};
            $data_encrypted = base64_decode($encrypted_64);
            $decrypted = openssl_decrypt($data_encrypted, 'aes-256-cbc', $secret_key, OPENSSL_RAW_DATA, $iv);
            return $decrypted;
        }
    ?>
</head>
<body>
    <header>
        <div>
            <img src="icon/toll-plaza.svg" alt="icon-toll-plaza">
            <h1>Aforamiento Inteligente Vehículos</h1>
        </div>
    </header>
    <main>
        <?php
            // Crear la conexión
            $conn = mysqli_connect($servername, $username, $password, $database);
            // Check de la conexión
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "SELECT * FROM matriculas";

            $result = mysqli_query($conn, $sql);
        
            // Comprobar si ocurre algún error en la consulta
            if (!$result) {
                die("Error description: " . mysqli_error($conn));
            }

            $result = mysqli_fetch_all($result);

            foreach ($result as $row) {
                $dt = new DateTime("@$row[1]");  // Convertir marca de tiempo UNIX a PHP DateTime
                $fechas[] = $dt->format('d/m/Y');
                $horas[] = $dt->format('H');
                $matriculas[] = decrypt_data($row[2], $passphrase);
            }

            $totalAfo = count($result); // Total de matriculas

            foreach ($fechas as $fecha) {
                if ($fecha == date('d/m/Y')) {
                    $totalHoy++;
                }
            }

            echo "<div class=\"container\">";
                echo "<div class=\"box\">";
                    echo "<p>Aforamiento total</p>";
                    echo "<span id=\"num\"> $totalAfo </span>";
                echo "</div>";
                echo "<div class=\"box\">";
                    echo "<p>Aforamiento ".date('d/m/Y')."</p>";
                    echo "<span id=\"num\"> $totalHoy </span>";
                echo "</div>";
            echo "</div>";            
            
            function contar($horas, $fechas) { // Cuantos tienen la misma hora y comprobamos la fecha
                $mapa = [];

                foreach ($fechas as $indice => $fecha) {
                    if ($fecha == date('d/m/Y')) {
                        if ($mapa[$horas[$indice]]) {
                            $mapa[$horas[$indice]]++;
                        } else {
                            $mapa[$horas[$indice]] = 1;
                        }
                    }
                }
                
                foreach ($mapa as $indice => $cantidad) {
                    $indices[] = $indice."h";
                    $cantidades[] = $cantidad;
                }

                $array[0] = $indices;
                $array[1] = $cantidades;

                return $array;
            }
            $etiquetas = contar($horas, $fechas)[0];
            $datos = contar($horas, $fechas)[1];
        ?>
        <div id="divCanvas">
            <canvas id="grafica"></canvas>
        </div>
        <script type="text/javascript">
        // Obtener una referencia al elemento canvas del DOM
        const $grafica = document.querySelector("#grafica");
        // Pasamos las etiquetas desde PHP
        const etiquetas = <?php echo json_encode($etiquetas) ?>;
        // Podemos tener varios conjuntos de datos. Comencemos con uno
        const datos = {
            label: "Vehículos/Hora",
            // Pasar los datos igualmente desde PHP
            data: <?php echo json_encode($datos) ?>,
            backgroundColor: 'rgba(106, 38, 205, 0.2)', // Color de fondo
            borderColor: 'rgba(106, 38, 205, 1)', // Color del borde
            borderWidth: 2, // Ancho del borde
        };
        new Chart($grafica, {
            type: 'bar', // Tipo de gráfica
            data: {
                labels: etiquetas,
                datasets: [
                    datos
                ]
            }
        });
    </script>

        <?php
            $sql = "SELECT * FROM matriculas ORDER BY id DESC LIMIT 15";
        
            $result = mysqli_query($conn, $sql);
        
            // Perform a query, check for error
            if (!$result) {
                die("Error description: " . mysqli_error($conn));
            }

            $result = mysqli_fetch_all($result);
        
            echo "<table>";
                echo "<tr>";
                    echo "<th>Fecha</th>";
                    echo "<th>Hora</th>";
                    echo "<th>Matrícula</th>";
                echo "</tr>";
                foreach ($result as $row) {
                    echo "<tr>";
                        $dt = new DateTime("@$row[1]");  // convert UNIX timestamp to PHP DateTime
                        echo "<td>".$dt->format('d/m/Y')."</td>";
                        echo "<td>".$dt->format('H:i:s')."</td>";
                        echo "<td>".decrypt_data($row[2], $passphrase)."</td>";
                    echo "</tr>";
                }
            echo "</table>";
      
            mysqli_close($conn);

        ?>
    </main>
    <footer>
        <a href="https://twitter.com/_anxxt" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://github.com/anxxt" target="_blank"><i class="fab fa-github"></i></a>
        <a href="https://www.linkedin.com/in/antonio-garc%C3%ADa-garc%C3%ADa-861bb7182/" target="_blank"><i class="fab fa-linkedin"></i></a>
    </footer>
</body>
</html>
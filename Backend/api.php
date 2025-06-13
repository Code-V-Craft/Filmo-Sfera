<?php

// Ustawienie nagłówka odpowiedzi na JSON, aby klient wiedział, że otrzymuje dane w formacie JSON
// Dokumentacja do nagłówków HTTP: https://www.php.net/manual/en/function.header.php
header('Content-Type: application/json');


 

session_start(); // Rozpoczęcie sesji, aby móc przechowywać dane sesji
// Dokumentacja funkcji session_start: https://www.php.net/manual/en/function.session-start.php


// Połączenie z bazą danych PHPMyAdmin
$host = 'localhost';
$db   = 'filmosfera';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Ustawienia DSN dla PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Tworzenie połączenia z bazą danych za pomocą PDO
// Dokumentacja 1: https://www.php.net/manual/en/pdo.connections.php
// Dokumentacja 2: https://www.w3schools.com/php/php_mysql_connect.asp
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jeśli wystąpi błąd połączenia, ustaw nagłówek odpowiedzi na 500 Internal Server Error 
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd połączenia z bazą danych',
    ]);
    exit;
}

// Pobierz ścieżkę po api.php
$scriptName = $_SERVER['SCRIPT_NAME']; // np. /backend/api.php
$requestUri = $_SERVER['REQUEST_URI']; // np. /backend/api.php/api/login
// $_SERVER[''] informacje o żądaniach HTTP i serwerze
// Dokumentacja do wszystkich zmiennych $_SERVER: https://www.php.net/manual/en/reserved.variables.server.php


// Wydzielenie z adresu URI ścieżki, która jest po skrypcie api.php
// substr() - zwraca część łańcucha znaków (ciągu znaków, ilo znaków do pominięcia)
// strlen() - zwraca długość łańcucha znaków

$path = substr($requestUri, strlen($scriptName)); // wynik: /api/login
// Dokumentacja substr: https://www.php.net/manual/en/function.substr.php
// Dokumentacja strlen: https://www.php.net/manual/en/function.strlen.php


// Routing switch-case
switch ($path) {
    case '/api/login':
        // Sprawdzenie, czy żądanie jest typu POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Odczytaj dane z żądania i przekonwertuj je z JSON na tablicę asocjacyjną
            $input = json_decode(file_get_contents('php://input'), true);

            // Przypisanie danych z tablicy asocjacyjnej do zmiennych
            $login = $input['login'];
            $password = $input['password'];

            // Pobranie jednego pasującego rekordu z bazy danych 
            $stmt = $pdo->prepare('SELECT * FROM users WHERE login = :login LIMIT 1');
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch();

            // Sprawdzenie, czy $user nie jest nullem i porównanie hasła do zahashowanego hasła w bazie danych 
            // Dokumentacja password_verify: https://www.php.net/manual/en/function.password-verify.php
            if ($user && password_verify($password, $user['password'])) {
                // Przypisanie wyniku działania funkcji generateToken do zmiennej $token
                $token = generateToken($login);
                $_SESSION['token'] = $token; // Zapisanie tokena w sesji
                // Dokumentacja $_SESSION: https://www.php.net/manual/en/reserved.variables.session.php


                // Ustawienie nagłówka odpowiedzi na 200 OK i zwrócenie tokena w formacie JSON
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                ]);
            } else {
                // Ustawienie nagłówka odpowiedzi na 401 Unauthorized i zwrócenie komunikatu o błędzie w formacie JSON
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Nieprawidłowy login lub hasło',
                ]);
            }
        }
        break;
    

    // Dodaj kolejne endpointy tutaj, np.:
    /* <- rozpoczęcie komentarza wieloliniowego
    case '/api/logout': // Obsługa wylogowania 
        
        session_abort(); // Zakończenie sesji
        http_response_code(200);
        break;
    */ // <- zakończenie komentarza wieloliniowego
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint nie istnieje',
        ]);
        break;
}

// Zakończenie wykonania skryptu
exit;


// Prosta funkcja generująca token na podstawie loginu i losowego ciągu znaków
// Dokumentacja funkcji base64_encode: https://www.php.net/manual/en/function.base64-encode.php
// Dokumentacja funkcji bin2hex: https://www.php.net/manual/en/function.bin2hex.php
// Dokumentacja funkcji random_bytes: https://www.php.net/manual/en/function.random-bytes.php
function generateToken($login)
{
    // Generowanie tokena w formacie base64
    return base64_encode($login . '|' . bin2hex(random_bytes(16)));
}

<?php

// ==============================
// KONFIGURACJA BAZY DANYCH
// ==============================

$dbHost = 'localhost';
$dbName = 'pracownicy';
$dbUser = 'opoznienia';
$dbPass = 'W40P6hTiJ0*jzCzH';

// ==============================
// POŁĄCZENIE Z BAZĄ
// ==============================

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych.");
}

// ==============================
// POBRANIE DANYCH
// ==============================

$sql = "
    SELECT
        id,
        imie,
        nazwisko,
        pesel,
        miasto,
        pensja,
        stanowisko,
        data_ur
    FROM pracownicy
    ORDER BY nazwisko, imie
";

$stmt = $pdo->query($sql);
$pracownicy = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lista pracowników</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th {
            background: #343a40;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover td {
            background: #f5f5f5;
        }

        .salary {
            text-align: right;
        }

        .count {
            margin-bottom: 15px;
            color: #666;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Lista pracowników firmy pani Z.</h1>

    <div class="count">
        Liczba pracowników: <strong><?= count($pracownicy) ?></strong>
    </div>

    <?php if (count($pracownicy) > 0): ?>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>PESEL</th>
                        <th>Miasto</th>
                        <th>Pensja</th>
                        <th>Stanowisko</th>
                        <th>Data urodzenia</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($pracownicy as $pracownik): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($pracownik['id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['imie'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['nazwisko'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['pesel'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['miasto'] ?? '') ?>
                        </td>

                        <td class="salary">
                            <?= $pracownik['pensja'] !== null
                                ? number_format((float)$pracownik['pensja'], 2, ',', ' ') . ' zł'
                                : '-'
                            ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['stanowisko'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($pracownik['data_ur'] ?? '') ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty">
            Brak pracowników w bazie danych.
        </div>

    <?php endif; ?>

</div>

</body>
</html>
```

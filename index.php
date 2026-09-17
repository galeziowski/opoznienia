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
// KOMUNIKATY
// ==============================

$successMessage = null;
$errorMessage = null;

$editEmployee = null;


// ==============================
// DODAWANIE / EDYCJA / USUWANIE
// ==============================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';


    // =========================================================
    // DODAWANIE
    // =========================================================

    if ($action === 'add') {

        $imie = trim($_POST['imie'] ?? '');
        $nazwisko = trim($_POST['nazwisko'] ?? '');
        $pesel = trim($_POST['pesel'] ?? '');
        $miasto = trim($_POST['miasto'] ?? '');
        $pensja = trim($_POST['pensja'] ?? '');
        $stanowisko = trim($_POST['stanowisko'] ?? '');
        $dataUr = trim($_POST['data_ur'] ?? '');


        // Walidacja

        if ($imie === '') {

            $errorMessage = 'Imię jest wymagane.';

        } elseif ($nazwisko === '') {

            $errorMessage = 'Nazwisko jest wymagane.';

        } elseif (
            $pesel !== '' &&
            !preg_match('/^[0-9]{11}$/', $pesel)
        ) {

            $errorMessage =
                'PESEL musi składać się z 11 cyfr.';

        } elseif ($pensja !== '') {

            $pensjaNormalized =
                str_replace(',', '.', $pensja);

            if (!is_numeric($pensjaNormalized)) {

                $errorMessage =
                    'Pensja musi być liczbą.';

            } else {

                $pensja = $pensjaNormalized;
            }
        }


        // Walidacja daty

        if (
            $errorMessage === null &&
            $dataUr !== ''
        ) {

            $date = DateTime::createFromFormat(
                'Y-m-d',
                $dataUr
            );

            if (
                !$date ||
                $date->format('Y-m-d') !== $dataUr
            ) {

                $errorMessage =
                    'Nieprawidłowa data urodzenia.';
            }
        }


        // INSERT

        if ($errorMessage === null) {

            try {

                $sql = "
                    INSERT INTO pracownicy
                    (
                        imie,
                        nazwisko,
                        pesel,
                        miasto,
                        pensja,
                        stanowisko,
                        data_ur
                    )
                    VALUES
                    (
                        :imie,
                        :nazwisko,
                        :pesel,
                        :miasto,
                        :pensja,
                        :stanowisko,
                        :data_ur
                    )
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':imie' =>
                        $imie,

                    ':nazwisko' =>
                        $nazwisko,

                    ':pesel' =>
                        $pesel !== ''
                            ? $pesel
                            : null,

                    ':miasto' =>
                        $miasto !== ''
                            ? $miasto
                            : null,

                    ':pensja' =>
                        $pensja !== ''
                            ? $pensja
                            : null,

                    ':stanowisko' =>
                        $stanowisko !== ''
                            ? $stanowisko
                            : null,

                    ':data_ur' =>
                        $dataUr !== ''
                            ? $dataUr
                            : null
                ]);


                $successMessage =
                    'Pracownik został dodany.';


            } catch (PDOException $e) {

                $errorMessage =
                    'Nie udało się dodać pracownika.';
            }
        }
    }


    // =========================================================
    // EDYCJA
    // =========================================================

    elseif ($action === 'edit') {

        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );

        $imie = trim($_POST['imie'] ?? '');
        $nazwisko = trim($_POST['nazwisko'] ?? '');
        $pesel = trim($_POST['pesel'] ?? '');
        $miasto = trim($_POST['miasto'] ?? '');
        $pensja = trim($_POST['pensja'] ?? '');
        $stanowisko = trim($_POST['stanowisko'] ?? '');
        $dataUr = trim($_POST['data_ur'] ?? '');


        // Walidacja ID

        if (!$id) {

            $errorMessage =
                'Nieprawidłowy identyfikator pracownika.';

        } elseif ($imie === '') {

            $errorMessage =
                'Imię jest wymagane.';

        } elseif ($nazwisko === '') {

            $errorMessage =
                'Nazwisko jest wymagane.';

        } elseif (
            $pesel !== '' &&
            !preg_match('/^[0-9]{11}$/', $pesel)
        ) {

            $errorMessage =
                'PESEL musi składać się z 11 cyfr.';

        } elseif ($pensja !== '') {

            $pensjaNormalized =
                str_replace(',', '.', $pensja);

            if (!is_numeric($pensjaNormalized)) {

                $errorMessage =
                    'Pensja musi być liczbą.';

            } else {

                $pensja = $pensjaNormalized;
            }
        }


        // Walidacja daty

        if (
            $errorMessage === null &&
            $dataUr !== ''
        ) {

            $date = DateTime::createFromFormat(
                'Y-m-d',
                $dataUr
            );

            if (
                !$date ||
                $date->format('Y-m-d') !== $dataUr
            ) {

                $errorMessage =
                    'Nieprawidłowa data urodzenia.';
            }
        }


        // UPDATE

        if ($errorMessage === null) {

            try {

                $sql = "
                    UPDATE pracownicy
                    SET
                        imie = :imie,
                        nazwisko = :nazwisko,
                        pesel = :pesel,
                        miasto = :miasto,
                        pensja = :pensja,
                        stanowisko = :stanowisko,
                        data_ur = :data_ur
                    WHERE id = :id
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':id' =>
                        $id,

                    ':imie' =>
                        $imie,

                    ':nazwisko' =>
                        $nazwisko,

                    ':pesel' =>
                        $pesel !== ''
                            ? $pesel
                            : null,

                    ':miasto' =>
                        $miasto !== ''
                            ? $miasto
                            : null,

                    ':pensja' =>
                        $pensja !== ''
                            ? $pensja
                            : null,

                    ':stanowisko' =>
                        $stanowisko !== ''
                            ? $stanowisko
                            : null,

                    ':data_ur' =>
                        $dataUr !== ''
                            ? $dataUr
                            : null
                ]);


                $successMessage =
                    'Dane pracownika zostały zaktualizowane.';


            } catch (PDOException $e) {

                $errorMessage =
                    'Nie udało się zaktualizować pracownika.';
            }
        }
    }


    // =========================================================
    // USUWANIE
    // =========================================================

    elseif ($action === 'delete') {

        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );


        if (!$id) {

            $errorMessage =
                'Nieprawidłowy identyfikator pracownika.';

        } else {

            try {

                $stmt = $pdo->prepare(
                    "DELETE FROM pracownicy WHERE id = :id"
                );

                $stmt->execute([
                    ':id' => $id
                ]);


                if ($stmt->rowCount() > 0) {

                    $successMessage =
                        'Pracownik został usunięty.';

                } else {

                    $errorMessage =
                        'Nie znaleziono pracownika.';
                }


            } catch (PDOException $e) {

                $errorMessage =
                    'Nie udało się usunąć pracownika.';
            }
        }
    }
}


// ==============================
// OTWARCIE FORMULARZA EDYCJI
// ==============================

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['edit'])
) {

    $editId = filter_input(
        INPUT_GET,
        'edit',
        FILTER_VALIDATE_INT
    );


    if ($editId) {

        $stmt = $pdo->prepare(
            "
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
            WHERE id = :id
            "
        );

        $stmt->execute([
            ':id' => $editId
        ]);

        $editEmployee = $stmt->fetch();


        if (!$editEmployee) {

            $errorMessage =
                'Nie znaleziono pracownika.';
        }
    }
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

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

            max-width: 1500px;

            margin: 0 auto;

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.08);
        }


        /* =========================
           HEADER
        ========================= */

        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;
        }


        h1 {

            margin: 0;
        }


        /* =========================
           BUTTONS
        ========================= */

        .btn {

            border: none;

            border-radius: 7px;

            padding: 9px 14px;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            text-decoration: none;

            display: inline-block;
        }


        .btn-add {

            background: #198754;

            color: white;
        }


        .btn-add:hover {

            background: #157347;
        }


        .btn-edit {

            background: #0d6efd;

            color: white;
        }


        .btn-edit:hover {

            background: #0b5ed7;
        }


        .btn-delete {

            background: #dc3545;

            color: white;
        }


        .btn-delete:hover {

            background: #bb2d3b;
        }


        .btn-cancel {

            background: #6c757d;

            color: white;
        }


        .btn-cancel:hover {

            background: #5c636a;
        }


        .btn-save {

            background: #0d6efd;

            color: white;
        }


        .btn-save:hover {

            background: #0b5ed7;
        }


        /* =========================
           FORM
        ========================= */

        .form-container {

            margin-bottom: 25px;

            padding: 22px;

            background: #f8f9fa;

            border: 1px solid #dee2e6;

            border-radius: 10px;
        }


        .form-title {

            margin-top: 0;

            margin-bottom: 20px;

            font-size: 20px;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 16px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        .form-group label {

            margin-bottom: 6px;

            font-size: 14px;

            font-weight: 600;
        }


        .form-group input {

            width: 100%;

            padding: 10px 11px;

            border: 1px solid #ced4da;

            border-radius: 6px;

            font-size: 15px;

            background: white;
        }


        .form-group input:focus {

            outline: none;

            border-color: #86b7fe;

            box-shadow:
                0 0 0 3px rgba(13, 110, 253, .15);
        }


        .required {

            color: #dc3545;
        }


        .form-actions {

            display: flex;

            gap: 10px;

            margin-top: 20px;
        }


        /* =========================
           MESSAGES
        ========================= */

        .message {

            padding: 13px 16px;

            border-radius: 7px;

            margin-bottom: 20px;
        }


        .success {

            background: #d1e7dd;

            color: #0f5132;

            border: 1px solid #badbcc;
        }


        .error {

            background: #f8d7da;

            color: #842029;

            border: 1px solid #f5c2c7;
        }


        /* =========================
           TABLE
        ========================= */

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


        .actions {

            display: flex;

            gap: 6px;
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


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .form-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 650px) {

            body {

                padding: 15px;
            }


            .container {

                padding: 18px;
            }


            .header {

                align-items: flex-start;

                flex-direction: column;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- ==========================
         HEADER
    =========================== -->

    <div class="header">

        <h1>
            Lista pracowników firmy pani Z.
        </h1>


        <button
            type="button"
            class="btn btn-add"
            onclick="showAddForm()">

            + Dodaj pracownika

        </button>

    </div>


    <!-- ==========================
         MESSAGES
    =========================== -->

    <?php if ($successMessage !== null): ?>

        <div class="message success">

            <?= htmlspecialchars($successMessage) ?>

        </div>

    <?php endif; ?>


    <?php if ($errorMessage !== null): ?>

        <div class="message error">

            <?= htmlspecialchars($errorMessage) ?>

        </div>

    <?php endif; ?>


    <!-- ==========================
         ADD FORM
    =========================== -->

    <div
        id="addForm"
        class="form-container"
        style="display:none;">

        <h2 class="form-title">
            Dodaj nowego pracownika
        </h2>


        <form method="post">

            <input
                type="hidden"
                name="action"
                value="add">


            <div class="form-grid">


                <div class="form-group">

                    <label for="add_imie">
                        Imię <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="add_imie"
                        name="imie"
                        maxlength="100"
                        required>

                </div>


                <div class="form-group">

                    <label for="add_nazwisko">
                        Nazwisko <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="add_nazwisko"
                        name="nazwisko"
                        maxlength="100"
                        required>

                </div>


                <div class="form-group">

                    <label for="add_pesel">
                        PESEL
                    </label>

                    <input
                        type="text"
                        id="add_pesel"
                        name="pesel"
                        maxlength="11"
                        inputmode="numeric"
                        pattern="[0-9]{11}">

                </div>


                <div class="form-group">

                    <label for="add_miasto">
                        Miasto
                    </label>

                    <input
                        type="text"
                        id="add_miasto"
                        name="miasto"
                        maxlength="100">

                </div>


                <div class="form-group">

                    <label for="add_pensja">
                        Pensja
                    </label>

                    <input
                        type="text"
                        id="add_pensja"
                        name="pensja"
                        inputmode="decimal"
                        placeholder="np. 5500,00">

                </div>


                <div class="form-group">

                    <label for="add_stanowisko">
                        Stanowisko
                    </label>

                    <input
                        type="text"
                        id="add_stanowisko"
                        name="stanowisko"
                        maxlength="150">

                </div>


                <div class="form-group">

                    <label for="add_data_ur">
                        Data urodzenia
                    </label>

                    <input
                        type="date"
                        id="add_data_ur"
                        name="data_ur">

                </div>


            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-save">

                    ✓ Zapisz

                </button>


                <button
                    type="button"
                    class="btn btn-cancel"
                    onclick="hideAddForm()">

                    Anuluj

                </button>

            </div>

        </form>

    </div>


    <!-- ==========================
         EDIT FORM
    =========================== -->

    <?php if ($editEmployee): ?>

        <div class="form-container">

            <h2 class="form-title">

                Edytuj pracownika:

                <?= htmlspecialchars(
                    $editEmployee['imie']
                ) ?>

                <?= htmlspecialchars(
                    $editEmployee['nazwisko']
                ) ?>

            </h2>


            <form method="post">

                <input
                    type="hidden"
                    name="action"
                    value="edit">


                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars(
                        $editEmployee['id']
                    ) ?>">


                <div class="form-grid">


                    <div class="form-group">

                        <label for="edit_imie">
                            Imię <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="edit_imie"
                            name="imie"
                            maxlength="100"
                            required
                            value="<?= htmlspecialchars(
                                $editEmployee['imie'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_nazwisko">
                            Nazwisko <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="edit_nazwisko"
                            name="nazwisko"
                            maxlength="100"
                            required
                            value="<?= htmlspecialchars(
                                $editEmployee['nazwisko'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_pesel">
                            PESEL
                        </label>

                        <input
                            type="text"
                            id="edit_pesel"
                            name="pesel"
                            maxlength="11"
                            inputmode="numeric"
                            pattern="[0-9]{11}"
                            value="<?= htmlspecialchars(
                                $editEmployee['pesel'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_miasto">
                            Miasto
                        </label>

                        <input
                            type="text"
                            id="edit_miasto"
                            name="miasto"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $editEmployee['miasto'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_pensja">
                            Pensja
                        </label>

                        <input
                            type="text"
                            id="edit_pensja"
                            name="pensja"
                            inputmode="decimal"
                            value="<?= htmlspecialchars(
                                $editEmployee['pensja'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_stanowisko">
                            Stanowisko
                        </label>

                        <input
                            type="text"
                            id="edit_stanowisko"
                            name="stanowisko"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                $editEmployee['stanowisko'] ?? ''
                            ) ?>">

                    </div>


                    <div class="form-group">

                        <label for="edit_data_ur">
                            Data urodzenia
                        </label>

                        <input
                            type="date"
                            id="edit_data_ur"
                            name="data_ur"
                            value="<?= htmlspecialchars(
                                $editEmployee['data_ur'] ?? ''
                            ) ?>">

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-save">

                        ✓ Zapisz zmiany

                    </button>


                    <a
                        href="index.php"
                        class="btn btn-cancel">

                        Anuluj

                    </a>

                </div>

            </form>

        </div>

    <?php endif; ?>


    <!-- ==========================
         COUNT
    =========================== -->

    <div class="count">

        Liczba pracowników:

        <strong>
            <?= count($pracownicy) ?>
        </strong>

    </div>


    <!-- ==========================
         TABLE
    =========================== -->

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

                        <th>Akcje</th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach ($pracownicy as $pracownik): ?>


                    <tr>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['id']
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['imie'] ?? ''
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['nazwisko'] ?? ''
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['pesel'] ?? ''
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['miasto'] ?? ''
                            ) ?>

                        </td>


                        <td class="salary">

                            <?= $pracownik['pensja'] !== null

                                ? number_format(
                                    (float)$pracownik['pensja'],
                                    2,
                                    ',',
                                    ' '
                                ) . ' zł'

                                : '-'
                            ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['stanowisko'] ?? ''
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $pracownik['data_ur'] ?? ''
                            ) ?>

                        </td>


                        <td>

                            <div class="actions">


                                <!-- EDYTUJ -->

                                <a
                                    href="?edit=<?= urlencode(
                                        $pracownik['id']
                                    ) ?>"
                                    class="btn btn-edit">

                                    Edytuj

                                </a>


                                <!-- USUŃ -->

                                <form
                                    method="post"
                                    onsubmit="return confirmDelete(
                                        '<?= htmlspecialchars(
                                            $pracownik['imie'] . ' ' .
                                            $pracownik['nazwisko'],
                                            ENT_QUOTES
                                        ) ?>'
                                    );">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= htmlspecialchars(
                                            $pracownik['id']
                                        ) ?>">

                                    <button
                                        type="submit"
                                        class="btn btn-delete">

                                        Usuń

                                    </button>

                                </form>


                            </div>

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


<script>

function showAddForm()
{
    const form =
        document.getElementById('addForm');

    form.style.display = 'block';

    document
        .getElementById('add_imie')
        .focus();

    form.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}


function hideAddForm()
{
    document
        .getElementById('addForm')
        .style.display = 'none';
}


function confirmDelete(name)
{
    return confirm(
        'Czy na pewno chcesz usunąć pracownika "' +
        name +
        '"?'
    );
}

</script>


</body>

</html>

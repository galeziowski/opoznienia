<?php

// ============================================================
// KONFIGURACJA
// ============================================================

$repoOwner = 'galeziowski';
$repoName  = 'opoznienia';
$branch    = 'main';

$projectDir = '/volume1/web/opoznienia';

$deployScript = '/usr/local/bin/opoznienia-deploy.sh';
$statusScript = '/usr/local/bin/opoznienia-status.sh';

$statusFile = $projectDir . '/.deploy-status.json';


// ============================================================
// FUNKCJE
// ============================================================

function runCommand(string $command): array
{
    $output = [];
    $returnCode = 0;

    exec($command . ' 2>&1', $output, $returnCode);

    return [
        'output' => $output,
        'returnCode' => $returnCode
    ];
}


/**
 * Odczytuje zapisany status.
 *
 * UWAGA:
 * Ta funkcja NIE łączy się z GitHubem.
 */
function readStatus(string $statusFile): array
{
    if (!file_exists($statusFile)) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    $content = file_get_contents($statusFile);

    if ($content === false) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    $data = json_decode($content, true);

    if (!is_array($data)) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    return [
        'local_commit' =>
            $data['local_commit'] ?? null,

        'github_commit' =>
            $data['github_commit'] ?? null,

        'status' =>
            $data['status'] ?? 'unknown',

        'checked_at' =>
            $data['checked_at'] ?? null
    ];
}


/**
 * Sprawdzenie GitHuba.
 */
function refreshStatus(string $statusScript): array
{
    $command =
        '/bin/sudo ' .
        escapeshellarg($statusScript);

    return runCommand($command);
}


/**
 * Deploy.
 */
function deploy(string $deployScript): array
{
    $command =
        '/bin/sudo ' .
        escapeshellarg($deployScript);

    return runCommand($command);
}


// ============================================================
// OBSŁUGA AKCJI
// ============================================================

$actionOutput = null;
$actionSuccess = null;
$actionType = null;


// ------------------------------------------------------------
// ODŚWIEŻ STATUS
// ------------------------------------------------------------

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['refresh_status'])
) {

    $result = refreshStatus($statusScript);

    $actionOutput = implode(
        "\n",
        $result['output']
    );

    $actionSuccess =
        ($result['returnCode'] === 0);

    $actionType = 'refresh';
}


// ------------------------------------------------------------
// DEPLOY
// ------------------------------------------------------------

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['deploy'])
) {

    $result = deploy($deployScript);

    $actionOutput = implode(
        "\n",
        $result['output']
    );

    $actionSuccess =
        ($result['returnCode'] === 0);

    $actionType = 'deploy';


    // --------------------------------------------------------
    // Po udanym deployu od razu aktualizujemy status.
    // --------------------------------------------------------

    if ($actionSuccess) {

        $statusResult =
            refreshStatus($statusScript);

        $actionOutput .=
            "\n\n=== STATUS PO DEPLOY ===\n" .
            implode(
                "\n",
                $statusResult['output']
            );
    }
}


// ============================================================
// ODCZYT ZAPISANEGO STATUSU
// ============================================================

$status = readStatus($statusFile);

$localCommit =
    $status['local_commit'];

$githubCommit =
    $status['github_commit'];

$versionStatus =
    $status['status'];

$checkedAt =
    $status['checked_at'];


// ============================================================
// HTML
// ============================================================

?>
<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Deploy — Opoźnienia</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 40px 20px;

            background: #f5f7fa;

            color: #1f2937;

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
        }


        .container {

            max-width: 900px;

            margin: 0 auto;
        }


        h1 {

            margin: 0 0 8px;

            font-size: 30px;
        }


        h2 {

            margin-top: 0;
        }


        .repo {

            color: #6b7280;

            margin-bottom: 30px;
        }


        .card {

            background: #ffffff;

            border-radius: 12px;

            padding: 24px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.06);
        }


        .status {

            padding: 16px 20px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 18px;

            font-weight: 600;
        }


        .status.current {

            background: #ecfdf5;

            color: #047857;
        }


        .status.outdated {

            background: #fffbeb;

            color: #b45309;
        }


        .status.unknown {

            background: #f3f4f6;

            color: #6b7280;
        }


        .versions {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 16px;

            margin-bottom: 24px;
        }


        .version-box {

            background: #f8fafc;

            border-radius: 10px;

            padding: 18px;
        }


        .version-label {

            display: block;

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 8px;
        }


        .version {

            font-family: monospace;

            font-size: 22px;

            font-weight: 700;
        }


        .checked {

            color: #6b7280;

            font-size: 14px;

            margin-top: 18px;
        }


        .actions {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;
        }


        button {

            border: 0;

            border-radius: 8px;

            padding: 12px 18px;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            background: #2563eb;

            color: white;
        }


        button:hover {

            background: #1d4ed8;
        }


        button.secondary {

            background: #e5e7eb;

            color: #374151;
        }


        button.secondary:hover {

            background: #d1d5db;
        }


        .deploy-success {

            color: #047857;
        }


        .deploy-error {

            color: #b91c1c;
        }


        pre {

            background: #111827;

            color: #e5e7eb;

            padding: 18px;

            border-radius: 8px;

            overflow-x: auto;

            white-space: pre-wrap;

            word-break: break-word;

            line-height: 1.5;

            font-size: 14px;
        }


        @media (max-width: 650px) {

            body {

                padding: 20px 12px;
            }


            .versions {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- ======================================================
         HEADER
    ======================================================= -->

    <h1>
        🚀 Deploy — Opoźnienia
    </h1>


    <div class="repo">

        GitHub:

        <strong>
            <?= htmlspecialchars(
                $repoOwner . '/' . $repoName
            ) ?>
        </strong>

        · branch:

        <strong>
            <?= htmlspecialchars($branch) ?>
        </strong>

    </div>


    <!-- ======================================================
         STATUS
    ======================================================= -->

    <?php if ($versionStatus === 'current'): ?>

        <div class="status current">

            ✓ Serwer jest aktualny

        </div>


    <?php elseif ($versionStatus === 'outdated'): ?>

        <div class="status outdated">

            ⚠ Dostępna jest nowa wersja

        </div>


    <?php else: ?>

        <div class="status unknown">

            ℹ Brak zapisanego statusu.
            Kliknij „Odśwież status”.

        </div>

    <?php endif; ?>


    <!-- ======================================================
         MAIN CARD
    ======================================================= -->

    <div class="card">


        <div class="versions">


            <div class="version-box">

                <span class="version-label">

                    Wersja na serwerze

                </span>


                <span class="version">

                    <?= htmlspecialchars(
                        $localCommit ?? '—'
                    ) ?>

                </span>

            </div>


            <div class="version-box">

                <span class="version-label">

                    Wersja na GitHub

                </span>


                <span class="version">

                    <?= htmlspecialchars(
                        $githubCommit ?? '—'
                    ) ?>

                </span>

            </div>


        </div>


        <?php if ($checkedAt !== null): ?>

            <div class="checked">

                Ostatnie sprawdzenie:
                <strong>
                    <?= htmlspecialchars($checkedAt) ?>
                </strong>

            </div>

        <?php endif; ?>


        <br>


        <!-- ==================================================
             BUTTONS
        =================================================== -->

        <div class="actions">


            <!-- DEPLOY -->

            <form
                method="post"
                onsubmit="return confirmDeploy();">

                <button
                    type="submit"
                    name="deploy"
                    value="1">

                    ⬇ Pobierz najnowszą wersję

                </button>

            </form>


            <!-- REFRESH STATUS -->

            <form method="post">

                <button
                    type="submit"
                    name="refresh_status"
                    value="1"
                    class="secondary">

                    ↻ Odśwież status

                </button>

            </form>


        </div>


    </div>


    <!-- ======================================================
         ACTION RESULT
    ======================================================= -->

    <?php if ($actionOutput !== null): ?>

        <div class="card">


            <?php if ($actionSuccess): ?>

                <h2 class="deploy-success">

                    ✓
                    <?php if ($actionType === 'deploy'): ?>
                        Deploy zakończony pomyślnie
                    <?php else: ?>
                        Status został odświeżony
                    <?php endif; ?>

                </h2>


            <?php else: ?>

                <h2 class="deploy-error">

                    ❌
                    <?php if ($actionType === 'deploy'): ?>
                        Deploy zakończony błędem
                    <?php else: ?>
                        Nie udało się odświeżyć statusu
                    <?php endif; ?>

                </h2>

            <?php endif; ?>


            <pre><?= htmlspecialchars(
                $actionOutput
            ) ?></pre>


        </div>

    <?php endif; ?>


</div>


<script>

function confirmDeploy() {

    return confirm(
        "Czy na pewno chcesz pobrać najnowszą wersję z GitHuba?"
    );

}

</script>


</body>

</html>

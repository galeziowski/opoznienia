<?php

$deployScript = '/usr/local/bin/opoznienia-deploy.sh';
$statusScript = '/usr/local/bin/opoznienia-status.sh';

$statusFile = '/volume1/web/opoznienia/.deploy-status.json';


// ============================================================
// FUNCTIONS
// ============================================================

function runScript(string $script): array
{
    $output = [];
    $returnCode = 0;

    exec(
        '/bin/sudo ' . escapeshellarg($script) . ' 2>&1',
        $output,
        $returnCode
    );

    return [
        'output' => implode("\n", $output),
        'returnCode' => $returnCode
    ];
}


function readStatus(string $file): array
{
    if (!is_readable($file)) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    $json = file_get_contents($file);

    if ($json === false) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [
            'local_commit' => null,
            'github_commit' => null,
            'status' => 'unknown',
            'checked_at' => null
        ];
    }

    return [
        'local_commit' => $data['local_commit'] ?? null,
        'github_commit' => $data['github_commit'] ?? null,
        'status' => $data['status'] ?? 'unknown',
        'checked_at' => $data['checked_at'] ?? null
    ];
}


// ============================================================
// ACTION
// ============================================================

$actionOutput = null;
$actionSuccess = null;
$actionType = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --------------------------------------------------------
    // REFRESH STATUS
    // --------------------------------------------------------

    if (isset($_POST['refresh_status'])) {

        $result = runScript($statusScript);

        $actionOutput = $result['output'];
        $actionSuccess = ($result['returnCode'] === 0);
        $actionType = 'refresh';
    }


    // --------------------------------------------------------
    // DEPLOY
    // --------------------------------------------------------

    elseif (isset($_POST['deploy'])) {

        $result = runScript($deployScript);

        $actionOutput = $result['output'];
        $actionSuccess = ($result['returnCode'] === 0);
        $actionType = 'deploy';
    }
}


// ============================================================
// READ SAVED STATUS
// ============================================================

$status = readStatus($statusFile);

$localCommit = $status['local_commit'];
$githubCommit = $status['github_commit'];
$versionStatus = $status['status'];
$checkedAt = $status['checked_at'];


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

    margin: auto;
}

h1 {

    margin: 0 0 8px;

    font-size: 30px;
}

.subtitle {

    color: #6b7280;

    margin-bottom: 30px;
}

.card {

    background: white;

    border-radius: 12px;

    padding: 24px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.06);
}

.status {

    padding: 16px 20px;

    border-radius: 10px;

    margin-bottom: 24px;

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

    margin-top: 18px;

    color: #6b7280;

    font-size: 14px;
}

.actions {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 24px;
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

.success {

    color: #047857;
}

.error {

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


<h1>
    🚀 Deploy — Opoźnienia
</h1>

<div class="subtitle">
    galeziowski/opoznienia · main
</div>


<!-- ========================================================
     STATUS
========================================================= -->

<?php if ($versionStatus === 'current'): ?>

    <div class="status current">
        ✓ Serwer jest aktualny
    </div>

<?php elseif ($versionStatus === 'outdated'): ?>

    <div class="status outdated">
        ⚠ Dostępna jest nowsza wersja
    </div>

<?php else: ?>

    <div class="status unknown">
        ℹ Brak zapisanego statusu — kliknij „Odśwież status”
    </div>

<?php endif; ?>


<!-- ========================================================
     VERSIONS
========================================================= -->

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


    <?php if ($checkedAt): ?>

        <div class="checked">

            Ostatnie sprawdzenie:
            <strong>
                <?= htmlspecialchars($checkedAt) ?>
            </strong>

        </div>

    <?php endif; ?>


    <!-- ====================================================
         ACTIONS
    ===================================================== -->

    <div class="actions">


        <form
            method="post"
            onsubmit="return confirmDeploy();">

            <button
                type="submit"
                name="deploy"
                value="1">

                ⬇ Deploy

            </button>

        </form>


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


<!-- ========================================================
     ACTION OUTPUT
========================================================= -->

<?php if ($actionOutput !== null): ?>

<div class="card">

    <?php if ($actionSuccess): ?>

        <h2 class="success">

            ✓

            <?php if ($actionType === 'deploy'): ?>

                Deploy zakończony pomyślnie

            <?php else: ?>

                Status został odświeżony

            <?php endif; ?>

        </h2>

    <?php else: ?>

        <h2 class="error">

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

function confirmDeploy()
{
    return confirm(
        'Czy na pewno chcesz pobrać najnowszą wersję z GitHuba?'
    );
}

</script>

</body>

</html>

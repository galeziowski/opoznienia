<?php

// ============================================================
// KONFIGURACJA
// ============================================================

$repoOwner = 'galeziowski';
$repoName = 'opoznienia';
$branch = 'main';

$projectDir = '/volume1/web/opoznienia';
$deployScript = '/usr/local/bin/opoznienia-deploy.sh';

$githubUrl = "https://github.com/{$repoOwner}/{$repoName}.git";


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
 * Pobiera aktualny commit znajdujący się na serwerze.
 */
function getLocalCommit(string $projectDir): ?string
{
    $command =
        '/usr/bin/git -C ' .
        escapeshellarg($projectDir) .
        ' rev-parse --short HEAD';

    $result = runCommand($command);

    if ($result['returnCode'] !== 0 || empty($result['output'])) {
        return null;
    }

    $commit = trim($result['output'][0]);

    if (!preg_match('/^[a-f0-9]{7,40}$/i', $commit)) {
        return null;
    }

    return $commit;
}


/**
 * Pobiera aktualny commit z GitHuba.
 *
 * Korzystamy z tego samego kontenera alpine/git,
 * który jest używany podczas deployu.
 */
function getGithubCommit(string $githubUrl, string $branch): ?string
{
    $command =
        '/usr/local/bin/docker run --rm ' .
        'alpine/git ' .
        'ls-remote ' .
        escapeshellarg($githubUrl) .
        ' ' .
        escapeshellarg('refs/heads/' . $branch);

    $result = runCommand($command);

    if ($result['returnCode'] !== 0 || empty($result['output'])) {
        return null;
    }

    foreach ($result['output'] as $line) {
        $line = trim($line);

        if (preg_match('/^([a-f0-9]{40})\s+refs\/heads\/' . preg_quote($branch, '/') . '$/i', $line, $matches)) {
            return substr($matches[1], 0, 7);
        }
    }

    return null;
}


/**
 * Uruchamia deploy.
 */
function deploy(string $deployScript): array
{
    $command =
        '/bin/sudo ' .
        escapeshellarg($deployScript);

    return runCommand($command);
}


// ============================================================
// OBSŁUGA DEPLOY
// ============================================================

$deployOutput = null;
$deploySuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deploy'])) {

    $result = deploy($deployScript);

    $deployOutput = implode("\n", $result['output']);
    $deploySuccess = ($result['returnCode'] === 0);

}


// ============================================================
// SPRAWDZENIE WERSJI
// ============================================================

$localCommit = getLocalCommit($projectDir);
$githubCommit = getGithubCommit($githubUrl, $branch);

$versionStatus = 'unknown';

if ($localCommit !== null && $githubCommit !== null) {

    if (strcasecmp($localCommit, $githubCommit) === 0) {
        $versionStatus = 'current';
    } else {
        $versionStatus = 'outdated';
    }
}


// ============================================================
// HTML
// ============================================================

?>
<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
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
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 30px;
        }

        .repo {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .card {
            background: white;
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
            background: #fef2f2;
            color: #b91c1c;
        }

        .versions {
            display: grid;
            grid-template-columns: 1fr 1fr;
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

        button,
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            background: #2563eb;
            color: white;
        }

        button:hover,
        .button:hover {
            background: #1d4ed8;
        }

        .refresh {
            margin-left: 10px;
            background: #e5e7eb;
            color: #374151;
        }

        .refresh:hover {
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
        }

        @media (max-width: 650px) {

            body {
                padding: 20px 12px;
            }

            .versions {
                grid-template-columns: 1fr;
            }

            .refresh {
                margin-left: 0;
                margin-top: 10px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>🚀 Deploy — Opoźnienia</h1>

    <div class="repo">
        GitHub:
        <strong><?= htmlspecialchars($repoOwner . '/' . $repoName) ?></strong>
        · branch:
        <strong><?= htmlspecialchars($branch) ?></strong>
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
            ❌ Nie udało się sprawdzić wersji.
        </div>

    <?php endif; ?>


    <!-- ======================================================
         WERSJE
    ======================================================= -->

    <div class="card">

        <div class="versions">

            <div class="version-box">

                <span class="version-label">
                    Wersja na serwerze
                </span>

                <span class="version">
                    <?= htmlspecialchars($localCommit ?? '—') ?>
                </span>

            </div>


            <div class="version-box">

                <span class="version-label">
                    Wersja na GitHub
                </span>

                <span class="version">
                    <?= htmlspecialchars($githubCommit ?? '—') ?>
                </span>

            </div>

        </div>


        <!-- ==================================================
             DEPLOY
        =================================================== -->

        <form method="post"
              onsubmit="return confirmDeploy();">

            <button type="submit"
                    name="deploy"
                    value="1">
                ⬇ Pobierz najnowszą wersję
            </button>

            <a href="admin.php"
               class="button refresh">
                ↻ Odśwież status
            </a>

        </form>

    </div>


    <!-- ======================================================
         WYNIK DEPLOYU
    ======================================================= -->

    <?php if ($deployOutput !== null): ?>

        <div class="card">

            <?php if ($deploySuccess): ?>

                <h2 class="deploy-success">
                    ✓ Deploy zakończony pomyślnie
                </h2>

            <?php else: ?>

                <h2 class="deploy-error">
                    ❌ Deploy zakończony błędem
                </h2>

            <?php endif; ?>


            <pre><?= htmlspecialchars($deployOutput) ?></pre>

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

<?php

// ============================================================
// CONFIGURATION
// ============================================================

$repoOwner = 'galeziowski';
$repoName  = 'opoznienia';
$branch    = 'main';

$projectDir = '/volume1/web/opoznienia';
$deployScript = '/usr/local/bin/opoznienia-deploy.sh';


// ============================================================
// HELPERS
// ============================================================

function runCommand(string $command): array
{
    $output = [];
    $returnCode = 0;

    exec($command . ' 2>&1', $output, $returnCode);

    return [
        'output' => $output,
        'code' => $returnCode
    ];
}

function getLocalCommit(string $projectDir): ?string
{
    $result = runCommand(
        '/usr/local/bin/docker run --rm ' .
        '-v ' . escapeshellarg($projectDir . ':/repo') . ' ' .
        'alpine/git ' .
        '-C /repo rev-parse --short HEAD'
    );

    if ($result['code'] !== 0 || empty($result['output'])) {
        return null;
    }

    return trim($result['output'][0]);
}

function getGithubCommit(string $owner, string $repo, string $branch): ?string
{
    $url = "https://api.github.com/repos/"
         . rawurlencode($owner)
         . "/"
         . rawurlencode($repo)
         . "/commits/"
         . rawurlencode($branch);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Opoznienia-Admin-Panel',
        CURLOPT_HTTPHEADER => [
            'Accept: application/vnd.github+json'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['sha'])) {
        return null;
    }

    return substr($data['sha'], 0, 7);
}


// ============================================================
// DEPLOY
// ============================================================

$deployResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['deploy'])) {

        $deployResult = runCommand(
            '/bin/sudo ' . escapeshellarg($deployScript)
        );
    }
}


// ============================================================
// GET STATUS
// ============================================================

$localCommit = getLocalCommit($projectDir);
$githubCommit = getGithubCommit($repoOwner, $repoName, $branch);

$isUpToDate = (
    $localCommit !== null &&
    $githubCommit !== null &&
    $localCommit === $githubCommit
);


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
        content="width=device-width, initial-scale=1.0"
    >

    <title>Deploy - Opoźnienia</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
            background: #f4f6f8;
            color: #202124;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .repository {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .status {
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 18px;
            font-weight: 600;
        }

        .status-ok {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .status-update {
            background: #fff3cd;
            color: #856404;
        }

        .status-error {
            background: #f8d7da;
            color: #842029;
        }

        .versions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .version {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 18px;
        }

        .version-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .commit {
            font-family: monospace;
            font-size: 20px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button,
        .button {
            border: 0;
            border-radius: 7px;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .deploy {
            background: #1976d2;
            color: white;
        }

        .deploy:hover {
            background: #1565c0;
        }

        .refresh {
            background: #e9ecef;
            color: #343a40;
        }

        .refresh:hover {
            background: #dee2e6;
        }

        .result {
            margin-top: 25px;
        }

        .result h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        pre {
            background: #1e1e1e;
            color: #f1f1f1;
            padding: 18px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 12px;
        }

        @media (max-width: 600px) {

            .card {
                padding: 20px;
            }

            .versions {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            button,
            .button {
                width: 100%;
                text-align: center;
            }
        }

    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1>🚀 Deploy — Opoźnienia</h1>

        <div class="repository">
            GitHub:
            <strong>
                <?= htmlspecialchars($repoOwner . '/' . $repoName) ?>
            </strong>
            · branch:
            <strong>
                <?= htmlspecialchars($branch) ?>
            </strong>
        </div>


        <?php if ($localCommit === null || $githubCommit === null): ?>

            <div class="status status-error">
                ❌ Nie udało się sprawdzić wersji.
            </div>

        <?php elseif ($isUpToDate): ?>

            <div class="status status-ok">
                ✓ Serwer jest aktualny
            </div>

        <?php else: ?>

            <div class="status status-update">
                ⚠ Dostępna jest nowa wersja
            </div>

        <?php endif; ?>


        <div class="versions">

            <div class="version">

                <div class="version-label">
                    Wersja na serwerze
                </div>

                <div class="commit">
                    <?= htmlspecialchars($localCommit ?? '—') ?>
                </div>

            </div>


            <div class="version">

                <div class="version-label">
                    Wersja na GitHub
                </div>

                <div class="commit">
                    <?= htmlspecialchars($githubCommit ?? '—') ?>
                </div>

            </div>

        </div>


        <div class="actions">

            <form method="post">

                <button
                    type="submit"
                    name="deploy"
                    value="1"
                    class="deploy"
                    onclick="
                        return confirm(
                            'Czy na pewno pobrać najnowszą wersję z GitHub?'
                        );
                    "
                >
                    ⬇ Pobierz najnowszą wersję
                </button>

            </form>


            <a
                href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
                class="button refresh"
            >
                ↻ Odśwież status
            </a>

        </div>


        <?php if ($deployResult !== null): ?>

            <div class="result">

                <h2>
                    <?= $deployResult['code'] === 0
                        ? '✓ Wynik deployu'
                        : '❌ Deploy zakończony błędem'
                    ?>
                </h2>

                <pre><?= htmlspecialchars(
                    implode("\n", $deployResult['output'])
                ) ?></pre>

            </div>

        <?php endif; ?>

    </div>


    <div class="footer">
        Opoźnienia · Deployment Panel
    </div>

</div>

</body>
</html>

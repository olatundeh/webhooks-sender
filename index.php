<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$webhookQueueFile = $_ENV['WEBHOOK_QUEUE_FILE'];
$maxFailedAttempts = $_ENV['MAX_FAILED_ATTEMPTS'];
$maxRetryDelay = $_ENV['MAX_RETRY_DELAY'];

function sendWebhook($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}

function exponentialBackoffStrategy($attempt, $maxDelay = 60)
{
    return min(pow(2, $attempt), $maxDelay);
}

function processWebhookQueue($webhookQueueFile, $maxFailedAttempts, $maxRetryDelay)
{
    $failedEndpoints = [];
    
    $webhooklines = file($webhookQueueFile, FILE_IGNORE_NEW_LINES);
    array_shift($webhooklines);
    foreach ($webhooklines as $line) {
        
        $parts = explode(",", $line);
        $url = trim($parts[0]);
        $order_id = trim($parts[1]);
        $name = trim($parts[2]);
        $event = trim($parts[3]);
        
        $attempt = 0;
        $delay = 0;

        while ($attempt < $maxFailedAttempts) {
            if (in_array($url, $failedEndpoints)) {
                break;
            }

            if (sendWebhook($url)) {
                $lines = file($webhookQueueFile, FILE_IGNORE_NEW_LINES);
                $newLines = array_diff($lines, [$line]);
                file_put_contents($webhookQueueFile, implode("\n", $newLines));
                break;
            }

            $delay = exponentialBackoffStrategy($attempt);
            sleep($delay);
            $attempt++;
        }

        if ($attempt == $maxFailedAttempts) {
            $failedEndpoints[] = $url;
        }
    }
}

processWebhookQueue($webhookQueueFile, $maxFailedAttempts, $maxRetryDelay);

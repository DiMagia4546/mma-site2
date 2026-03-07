<?php
if (file_exists(__DIR__ . '/odds_config.php')) {
    require_once __DIR__ . '/odds_config.php';
}

function odds_config_value(string $key, string $default = ''): string
{
    $env = getenv($key);
    if ($env !== false && trim((string) $env) !== '') {
        return trim((string) $env);
    }

    if (defined($key)) {
        $val = constant($key);
        if (is_string($val) && trim($val) !== '') {
            return trim($val);
        }
    }

    return $default;
}

function odds_norm_name(string $name): string
{
    $n = strtolower(trim($name));
    $n = preg_replace('/\b(jr|jr\.)\b/i', 'junior', $n);
    $n = preg_replace('/\b(sr|sr\.)\b/i', 'senior', $n);
    $n = preg_replace('/[^a-z0-9 ]/i', ' ', $n);
    $n = preg_replace('/\s+/', ' ', $n);
    return trim($n);
}

function odds_pair_key(string $a, string $b): string
{
    $na = odds_norm_name($a);
    $nb = odds_norm_name($b);
    $pair = [$na, $nb];
    sort($pair, SORT_STRING);
    return $pair[0] . '|' . $pair[1];
}

function odds_http_get_json(string $url): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code < 200 || $code >= 300) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 8,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function fetch_mma_odds_feed(): ?array
{
    $apiKey = odds_config_value('THE_ODDS_API_KEY');
    if ($apiKey === '') {
        return null;
    }

    $regions = odds_config_value('THE_ODDS_REGIONS', 'eu');
    $markets = odds_config_value('THE_ODDS_MARKETS', 'h2h');
    $oddsFormat = odds_config_value('THE_ODDS_FORMAT', 'american');
    $bookmakers = odds_config_value('THE_ODDS_BOOKMAKERS', '');

    $query = [
        'apiKey' => $apiKey,
        'regions' => $regions,
        'markets' => $markets,
        'oddsFormat' => $oddsFormat,
    ];
    if ($bookmakers !== '') {
        $query['bookmakers'] = $bookmakers;
    }

    $url = 'https://api.the-odds-api.com/v4/sports/mma_mixed_martial_arts/odds/?' . http_build_query($query);

    $cacheFile = __DIR__ . '/uploads/.odds_cache_mma.json';
    $ttl = (int) odds_config_value('THE_ODDS_CACHE_TTL', '180');
    if ($ttl < 30) {
        $ttl = 30;
    }

    if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile) < $ttl)) {
        $cached = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $fresh = odds_http_get_json($url);
    if (is_array($fresh)) {
        @file_put_contents($cacheFile, json_encode($fresh, JSON_UNESCAPED_UNICODE));
        return $fresh;
    }

    if (is_file($cacheFile)) {
        $cached = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    return null;
}

function build_official_odds_map(): array
{
    $feed = fetch_mma_odds_feed();
    if (!$feed || !is_array($feed)) {
        return [];
    }

    $map = [];
    foreach ($feed as $event) {
        if (!is_array($event)) {
            continue;
        }

        $home = (string) ($event['home_team'] ?? '');
        $away = (string) ($event['away_team'] ?? '');
        if ($home === '' || $away === '') {
            $teams = $event['teams'] ?? [];
            if (is_array($teams) && count($teams) >= 2) {
                $home = (string) $teams[0];
                $away = (string) $teams[1];
            }
        }
        if ($home === '' || $away === '') {
            continue;
        }

        $bookmakers = $event['bookmakers'] ?? [];
        if (!is_array($bookmakers) || empty($bookmakers)) {
            continue;
        }

        $pickedBook = $bookmakers[0];
        foreach ($bookmakers as $book) {
            if (!is_array($book)) {
                continue;
            }
            $markets = $book['markets'] ?? [];
            if (!is_array($markets)) {
                continue;
            }
            foreach ($markets as $m) {
                if (($m['key'] ?? '') === 'h2h') {
                    $pickedBook = $book;
                    break 2;
                }
            }
        }

        $marketOutcomes = null;
        foreach (($pickedBook['markets'] ?? []) as $m) {
            if (($m['key'] ?? '') === 'h2h') {
                $marketOutcomes = $m['outcomes'] ?? [];
                break;
            }
        }
        if (!is_array($marketOutcomes) || empty($marketOutcomes)) {
            continue;
        }

        $outcomeMap = [];
        foreach ($marketOutcomes as $out) {
            $name = (string) ($out['name'] ?? '');
            $price = $out['price'] ?? null;
            if ($name === '' || !is_numeric($price)) {
                continue;
            }
            $outcomeMap[odds_norm_name($name)] = (int) $price;
        }

        $nHome = odds_norm_name($home);
        $nAway = odds_norm_name($away);
        if (!isset($outcomeMap[$nHome]) || !isset($outcomeMap[$nAway])) {
            continue;
        }

        $key = odds_pair_key($home, $away);
        $map[$key] = [
            'home_name' => $home,
            'away_name' => $away,
            'home_odds' => $outcomeMap[$nHome],
            'away_odds' => $outcomeMap[$nAway],
            'bookmaker' => (string) ($pickedBook['title'] ?? 'Sportsbook'),
            'last_update' => (string) ($pickedBook['last_update'] ?? ($event['commence_time'] ?? '')),
        ];
    }

    return $map;
}

function official_odds_for_fight(array $oddsMap, string $fighter1, string $fighter2): ?array
{
    $key = odds_pair_key($fighter1, $fighter2);
    if (!isset($oddsMap[$key])) {
        return null;
    }

    $data = $oddsMap[$key];
    $n1 = odds_norm_name($fighter1);
    $homeNorm = odds_norm_name($data['home_name']);
    $awayNorm = odds_norm_name($data['away_name']);

    if ($n1 === $homeNorm) {
        return [
            'odds1' => (int) $data['home_odds'],
            'odds2' => (int) $data['away_odds'],
            'source' => 'official',
            'bookmaker' => $data['bookmaker'],
            'last_update' => $data['last_update'],
        ];
    }

    if ($n1 === $awayNorm) {
        return [
            'odds1' => (int) $data['away_odds'],
            'odds2' => (int) $data['home_odds'],
            'source' => 'official',
            'bookmaker' => $data['bookmaker'],
            'last_update' => $data['last_update'],
        ];
    }

    return null;
}

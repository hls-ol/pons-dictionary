<?php

$available = parseLanguageList('de, en');


// parse list of comma separated language tags and sort it by the quality value
function parseLanguageList($languageList) {
    if (is_null($languageList)) {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return array();
        }
        $languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    $languages = array();
    $languageRanges = explode(',', trim($languageList));
    foreach ($languageRanges as $languageRange) {
        if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match)) {
            if (!isset($match[2])) {
                $match[2] = '1.0';
            } else {
                $match[2] = (string) floatval($match[2]);
            }
            if (!isset($languages[$match[2]])) {
                $languages[$match[2]] = array();
            }
            $languages[$match[2]][] = strtolower($match[1]);
        }
    }
    krsort($languages);
    return $languages;
}

// compare two parsed arrays of language tags and find the matches
function findMatches($accepted, $available) {
    $matches = array();
    $any = false;
    foreach ($accepted as $acceptedQuality => $acceptedValues) {
        $acceptedQuality = floatval($acceptedQuality);
        if ($acceptedQuality === 0.0) continue;
        foreach ($available as $availableQuality => $availableValues) {
            $availableQuality = floatval($availableQuality);
            if ($availableQuality === 0.0) continue;
            foreach ($acceptedValues as $acceptedValue) {
                if ($acceptedValue === '*') {
                    $any = true;
                }
                foreach ($availableValues as $availableValue) {
                    $matchingGrade = matchLanguage($acceptedValue, $availableValue);
                    if ($matchingGrade > 0) {
                        $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);
                        if (!isset($matches[$q])) {
                            $matches[$q] = array();
                        }
                        if (!in_array($availableValue, $matches[$q])) {
                            $matches[$q][] = $availableValue;
                        }
                    }
                }
            }
        }
    }
    if (count($matches) === 0 && $any) {
        $matches = $available;
    }
    krsort($matches);
    return $matches;
}

// compare two language tags and distinguish the degree of matching
function matchLanguage($a, $b) {
    $a = explode('-', $a);
    $b = explode('-', $b);
    for ($i=0, $n=min(count($a), count($b)); $i<$n; $i++) {
        if ($a[$i] !== $b[$i]) break;
    }
    return $i === 0 ? 0 : (float) $i / count($a);
}

$accepted = parseLanguageList($_SERVER['HTTP_ACCEPT_LANGUAGE']);
$matches = findMatches($accepted, $available);


if(empty($matches)) {
    return include_once('en.php');
} else {
    return include_once($matches[array_key_first($matches)][0] . '.php');
}

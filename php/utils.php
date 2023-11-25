<?php
/**
 * Outputs an error message in JSON format and terminates the script execution.
 *
 * @param string $message The error message to be displayed.
 * @param string &$query A reference to the query string used in the request.
 * @return void
 */
function error(string $message, string &$query = '') {
    header('Content-Type: application/json');
    http_response_code(404);
  
    echo '{"type": "error", "message": "' . $message . '", "query": "' . $query . '"}';
  
    die;
}
  
/**
 * Outputs a success message in JSON format and terminates the script execution.
 *
 * @param PonsQueryResult &$text A reference to the response content to be displayed.
 * @param string &$query A reference to the query string used in the request.
 * @return void
 */
function success(PonsQueryResult &$content, string &$query) {
    header('Content-Type: application/json');
    // The browser caches the result for 14 days and can use the cache one day after that
    header('Cache-Control: public, max-age=1209600, immutable, stale-while-revalidate=86400');
  
    echo '{"type": "success", "data": ' . $content->getContent() . ', "query": "' . $query . '", "cached": ' . ($content->isCached() ? 'true' : 'false') . '}';
  
    die;
}

/**
 * Parses the headers string and returns an associative array of headers.
 *
 * @param string $headerString The headers string.
 * @return array An associative array of headers.
 */
function headersToArray(string &$headerString): array {
    $headers = array();
    $headersTmpArray = explode("\r\n", $headerString);
    for ($i = 0; $i < count($headersTmpArray); ++$i) {
        // we dont care about the two \r\n lines at the end of the headers
        if (strlen($headersTmpArray[$i]) > 0) {
            // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
            if (strpos($headersTmpArray[$i], ":")) {
              $headerName = substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ":"));
              $headerValue = substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ":") + 1);
              $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}

class ErrorTypes {
    public static int $NULL = 0;
    public static int $NOT_FOUND = 1;
    public static int $CONTENT_TYPE = 2;
}

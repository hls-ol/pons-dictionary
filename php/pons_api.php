<?php
require_once 'defaults.php';
require_once 'utils.php';

$DB_CONN;
if($DB_CONNECTION_STRING != null && $DB_CONNECTION_STRING != '') {
    try {
        $DB_CONN = new PDO($DB_CONNECTION_STRING, $DB_USERNAME, $DB_PASSWORD, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        ));

        $DB_CONN->exec('SET CHARACTER SET utf8');
    } catch(PDOException $e) {
        error_log("Cannot connect to database.\nException was: $e", 0);

        $DB_CONN = null;
    }
}

/**
 * Represents a query to the PONS API.
 */
class PonsAPIQuery {
    /**
     * @var string The query to send to the API.
     */
    private string $query;

    /**
     * @var string The dictionary to search in.
     */
    private string $dictionary;

    /**
     * Constructs a new instance of the PonsAPIQuery class.
     *
     * @param string $query The query to send to the API.
     * @param string $dictionary The dictionary to search in.
     */
    public function __construct(string &$query, string &$dictionary) {
        $this->query = $query;
        $this->dictionary = $dictionary;
    }

    /**
     * Executes the query against the PONS API and returns a result object.
     *
     * @return PonsQueryResult The result of the API query.
     */
    public function execute(): PonsQueryResult {
        $errors = [];

        $db_hit = $this->executeDatabaseQuery();
        if($db_hit !== false) {
            return new PonsQueryResult($errors, $db_hit, true);
        }

        $api_result = $this->executePonsApi();

        $this->getErrors($api_result, $errors);
        $content = $api_result->getContent();

        if(!$errors) {
            $this->storeInDatabaseCache($content);
        }

        return new PonsQueryResult($errors, $content, false);
    }

    private function getErrors(PonsAPIResult &$result, array &$errors) {
        $body = $result->getContent();

        if($body == null || $body == '') {
            $errors[ErrorTypes::$NULL] = ["Unfortunately there was an error with the word '$this->query'. Maybe try a different word.", 404];

            return;
        }
        
        if($body == 'Not found') {
            $errors[ErrorTypes::$NOT_FOUND] = ['Unfortunately the requested dictionary was not found.', 400];
            return;
        }
        
        /**
         * Checks if the response content-type is JSON. If the Pons API is undergoing maintenance, 
         * it may return an HTML response instead of JSON, causing this check to fail. In such cases,
         * an error message is returned indicating that the API is not available and to try again later.
         */
        if(strpos($result->getHeader('content-type'), 'application/json') == false) {
            $contentType = $result->getHeader('content-type');
            
            $errors[ErrorTypes::$CONTENT_TYPE] = ["There was an error with the API. Please come back later. Content-Type was: $contentType.", 503];
            return;
        }
    }

    private function executeDatabaseQuery() {
        global $DB_CONN;

        if($DB_CONN === null) {
            return false;
        }

        global $DB_TABLE_NAMES;
        if(!in_array($this->dictionary, $DB_TABLE_NAMES)) {
            return false;
        }

        global $DB_CACHE_SECONDS;

        // The query is being checked before which means that
        // this query cannot contain any special things and with
        // that there cannot be any SQL-Injection.
        //
        // But be careful!
        $stmt = $DB_CONN->prepare("SELECT data FROM $this->dictionary WHERE query = ? AND timestamp > (UNIX_TIMESTAMP() - $DB_CACHE_SECONDS)");
        $stmt->bindParam(1, $this->query);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }

        $result = $stmt->fetchAll();
        if(!$result) {
            return false;
        }

        return $result[0][0];
    }

    private function storeInDatabaseCache(string &$content) {
        global $DB_CONN;
        if($DB_CONN === null) {
            return;
        }

        global $DB_TABLE_NAMES;
        
        if(!in_array($this->dictionary, $DB_TABLE_NAMES)) {
            return;
        }

        global $DB_CONN;
        $stmt = $DB_CONN->prepare("REPLACE INTO `$this->dictionary` (`query`, `timestamp`, `data`) VALUES (?, UNIX_TIMESTAMP(), ?);");
        $stmt->bindParam(1, $this->query, PDO::PARAM_STR);
        $stmt->bindParam(2, $content, PDO::PARAM_STR);
        
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return;
        }
    }

    private function executePonsApi(): PonsAPIResult {
        $queryURL = "https://api.pons.com/v1/dictionary?l=$this->dictionary&q=" . urlencode($this->query) . "&fm=1";

        global $PONS_API_KEY;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $queryURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'X-Secret: ' . $PONS_API_KEY
        ]);
        
        // include the response headers in the output
        curl_setopt($curl, CURLOPT_HEADER, true);
        
        $result = curl_exec($curl);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerStr = substr($result, 0 , $headerSize);
        $body = substr($result, $headerSize);
            
        curl_close($curl);
            
        $headers = headersToArray($headerStr);

        return new PonsAPIResult($headers, $body);
    }
}

class PonsQueryResult {
    private array $errors;
    private string $content;
    private bool $cached;

    public function __construct(array &$errors, string &$content, bool $cached) {
        $this->errors = $errors;
        $this->content = $content;
        $this->cached = $cached;
    }

    public function hasErrors(): bool {
        return !!$this->errors;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function isCached(): bool {
        return $this->cached;
    }
}

/**
 * Represents the result of a PONS API query.
 */
class PonsAPIResult {
    /**
     * @var array The headers returned by the API.
     */
    private array $headers;

    /**
     * @var string The content returned by the API.
     */
    private string $content;

    /**
     * Constructs a new instance of the PonsAPIResult class.
     *
     * @param array $headers The headers returned by the API.
     * @param string $content The content returned by the API.
     */
    public function __construct(array &$headers, string &$content) {
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * Returns the value of the specified header.
     *
     * @param string $name The name of the header to get.
     *
     * @return string The value of the header.
     */
    public function getHeader(string $name): string {
        return $this->headers[$name];
    }

    /**
     * Returns the content returned by the API.
     *
     * @return string The content returned by the API.
     */
    public function getContent(): string {
        return $this->content;
    }
}

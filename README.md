# pons-dictionary
A simple PONS dictionary interface to access the [PONS API](https://de.pons.com/p/online-woerterbuch/fuer-entwickler/api)

# Setup
1. [Create an account with PONS](https://login.pons.com/login) and [activate the API](https://en.pons.com/open_dict/public_api). All further information at [PONS' API info site](https://de.pons.com/p/online-woerterbuch/fuer-entwickler/api). Then you will have 1000 requests/month. Should you want to go productive with your project, contact sales at PONS, they will send you a contract which you will need to sign and the official logo, which you will need to add to your site. The official documentation of the [PONS API](https://de.pons.com/p/files/uploads/pons/api/api-documentation.pdf).

2. Copy the code to a webserver of your choice and activate SSL encryption with a certificate.
3. Configure the `defaults.php` in `/php` to your needs, here are some examples:
   1. Without a caching database:
       ```php
       <?php
       // PONS API Key for authentication
       $PONS_API_KEY = '<<api-key>>';
    
       // Typo dictionary for language 'deen' (German)
       $TYPO_DICT = [
           'deen' => [
               'nähmlich' => 'nämlich'
           ]
       ];
    
       // Set the connection string to '' or null
       // This says that no connection should be made
       $DB_CONNECTION_STRING = '';
       ```

   2. With a caching database:
        ```php
        <?php
        // PONS API Key for authentication
        $PONS_API_KEY = '<<api-key>>';
        
        // Typo dictionary for language 'deen' (German)
        $TYPO_DICT = [
            'deen' => [
                'nähmlich' => 'nämlich'
            ]
        ];
        
        // Database configuration
        $DB_USERNAME = 'pons_api_user';
        $DB_PASSWORD = 'password';
        $DB_SERVERNAME = 'localhost';
        $DB_DATABASE_NAME = 'pons_api_cache';
        
        // Database table names for caching
        // These tables represent different dictionaries used for translation
        $DB_TABLE_NAMES = [
            'deen',  // German to English (and vice versa)
            'defr'   // German to French (and vice versa)
        ];
        
        // Database connection string
        // If caching is disabled, set this to an empty string or null
        $DB_CONNECTION_STRING = "mysql:host=$DB_SERVERNAME;dbname=$DB_DATABASE_NAME";
        
        // Cache duration settings
        $DB_CACHE_DAYS = 5;
        $DB_CACHE_SECONDS = $DB_CACHE_DAYS * 60 * 60 * 24;
        ```
4. Add your URL to your Moodle Test activity > description or define the URL in your MDM and you are good to go.

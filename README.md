# pons-dictionary
A simple PONS dictionary interface to access the [PONS API](https://de.pons.com/p/online-woerterbuch/fuer-entwickler/api)

# Setup
1. [Create an account with PONS](https://login.pons.com/login) and [activate the API](https://en.pons.com/open_dict/public_api). All further information at [PONS' API info site](https://de.pons.com/p/online-woerterbuch/fuer-entwickler/api). Then you will have 1000 requests/month. Should you want to go productive with your project, contact sales at PONS, they will send you a contract which you will need to sign and the official logo, which you will need to add to your site.

2. Copy the code to a webserver of your choice and activate SSL encryption with a certificate.
3. Configure the defaults.php in /php to your needs, here are some examples:

```
<?php

$PONS_API_KEY = '<<api-key>>';

$TYPO_DICT = [
    'deen' => [
        'nähmlich' => 'nämlich'
    ]
];

// The output language of the results de -> "maskulinum" || en -> "masculine"
$OUTPUT_LANGUAGE = 'de';
```
4. Add your URL to your Moodle Test activity > description or define the URL in your MDM and you are good to go.

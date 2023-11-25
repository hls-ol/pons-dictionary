<?php
$lang = include_once 'php/lang/language.php';
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title><?= $lang->TITLE ?></title>

    <link rel="stylesheet" href="./css/style.css" />
  </head>
  <body>
    <div class="wrapper">
      <header><span><img src="./img/pons_logo_small_l.png" alt="Pons" loading="lazy" /> <?= $lang->DICTIONARY ?></span></header>
      <div class="search">
        <form action="javascript:formSubmit();" id="submit-form" method="get" novalidate="novalidate">
          <input type="text" name="" id="search" placeholder="<?= $lang->SEARCH ?>" autocomplete="off" required />
          <span id="search-item">
            <input type="submit" src="" alt="" class="magnifying-glass" value="" />
            <img src="./img/magnifying-glass.svg">
          </span>
          <span id="delete-button">
            <img src="./img/xmark.svg" alt="Delete Input" class="delete"/>
          </span>
        </form>
      </div>
      <p class="info-text"><?= $lang->INFO ?></p>
      <ul id="content-list">
        
      </ul>
      <span id="license"><?= $lang->LICENSE ?></span>
    </div>

    <script src="./js/index.js" defer></script>
  </body>
</html>

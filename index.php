<?php
  /**
   * @return PDO|void
   */
  function connect()
  {
    try {
      $connection = new PDO("mysql:host=localhost;dbname=app", "root", "root");
      $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      echo "Connection success";
      return $connection;
    } catch (PDOException $error) {
      echo "Connection failed: " . $error->getMessage();
    }
  }
  
  /**
   * @param int $length
   * @return string
   */
  function makeInsertQuery(int $length = 1500): string
  {
    $insertQuery = 'INSERT `auction` (`title`, `description`, `price`, `country`, `sold`) VALUES ';
    $countries = ['USA', 'Europe', 'ASIA'];
    
    for ($i = 1; $i <= $length; $i++) {
      $country = $countries[array_rand($countries)];
      $price = rand(10, 100);
      $sold = rand(0, 10) > 5 ? 1 : 0;
      $insertQuery .= "('Some lot name $i', 'Some long description $i ...', '$price', '$country', '$sold'),";
    }
    
    return rtrim($insertQuery, ',');
  }
  
  $createTableQuery = "CREATE TABLE `app`.`auction`
                       (`id` INT NOT NULL AUTO_INCREMENT ,
                       `title` VARCHAR(300) NOT NULL ,
                       `description` TEXT NOT NULL ,
                       `price` INT UNSIGNED NOT NULL ,
                       `country` ENUM('USA', 'Europe', 'ASIA') NOT NULL ,
                       `sold` BOOLEAN NOT NULL ,
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
  
  try {
    connect()->exec($createTableQuery);
  } catch (PDOException $err) {
    echo 'MYSQL Error: ' . $err->getMessage();
  }
  
  /*
   * Наполняем данные нашим скриптом
   */
  
  try {
    connect()->exec(makeInsertQuery());
  } catch (PDOException $err) {
    echo 'MYSQL Error: ' . $err->getMessage();
  }
  
  /**
   * Создаем индексы отдельно, ибо по лекции и по доп материалом понял так, что добавлять индексы нужно по мере
   * работы с базой и "лишние" индексы могут навредить
   */
  
  $indexes = [
    'title' => 'CREATE UNIQUE INDEX `index_title` ON `auction`(`title`)',
    'price' => 'CREATE INDEX `index_price` ON `auction`(`price`)',
    'priceAndSold' => 'CREATE INDEX `index_price_sold` on `auction`(`price`, `sold`)',
  ];
  
  foreach ($indexes as $key => $value) {
    // Цикл - это плохо, но решил лишний раз освежить память синтаксисом
    try {
      connect()->exec($value);
    } catch (PDOException $err) {
      echo "MYSQL Error ($key) : " . $err->getMessage();
    }
  }
  
  /*
   * Создаем запрос по ТЗ
   */
  
  $selectQuery = "SELECT * FROM `auction` WHERE `sold` = 0 AND `country` = 'USA' ORDER BY `id`";
  
  try {
    $sth = connect()->prepare($selectQuery);
    $sth->execute();
    $data = $sth->fetch();
    var_dump($data);
  } catch (PDOException $err) {
    echo 'MYSQL Error: ' . $err->getMessage();
  }
  
  /*
   * Создаем Full Text Index
   */
  
  $fullTextIndex = "ALTER TABLE `auction` ADD FULLTEXT(`title`, `description`) ";
  try {
    connect()->exec($fullTextIndex);
  } catch (PDOException $err) {
    echo 'MYSQL Error: ' . $err->getMessage();
  }
      
      

  
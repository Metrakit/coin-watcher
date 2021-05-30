<?php

class CoinLite {

    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO('sqlite:'.dirname(__FILE__).'/database.sqlite');
            $this->pdo ->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(Exception $e) {
            echo "Impossible d'accéder à la base de données SQLite : " . $e->getMessage();
            die();
        }

        $this->initTable();
    }

    private function initTable()
    {
        $this->pdo->query("CREATE TABLE IF NOT EXISTS coins ( 
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            coin_name       VARCHAR(50),
            pair_name       VARCHAR(50),
            coin_address       VARCHAR(50),
            pair_address       VARCHAR(50),
            price         FLOAT(10),
            is_sup         BOOLEAN,
            enabled        BOOLEAN
        );");
    }

    public function insertSomeCoins()
    {
        $query = "INSERT INTO coins (coin_name, pair_name, coin_address, pair_address, price, is_sup, enabled) 
                  VALUES (:coin_name, :pair_name, :coin_address, :pair_address, :price, :is_sup, :enabled)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
                'coin_name' => "MEPAD",
                'pair_name' => "BNB",
                'coin_address' => "0x9d70a3ee3079a6fa2bb16591414678b7ad91f0b5",
                'pair_address' => "0x55d398326f99059ff775485246999027b3197955",
                'price' => 0.50,
                'is_sup' => false,
                'enabled' => true
        ]);
        $stmt->execute([
            'coin_name' => "POO",
            'pair_name' => "BNB",
            'coin_address' => "0xb27adaffb9fea1801459a1a81b17218288c097cc",
            'pair_address' => "0x55d398326f99059ff775485246999027b3197955",
            'price' => 10,
            'is_sup' => true,
            'enabled' => true
        ]);
    }

    public function deleteCoin($coinId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM coins WHERE id = $coinId");
        $stmt->execute();
    }

    public function getCoins()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM coins");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEnabledCoins()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM coins WHERE enabled = true");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addCoin($coin_name, $coin_address, $pair_name, $pair_address, $price, $is_sup)
    {
        $enabled = true;
        $query = "INSERT INTO coins (coin_name, pair_name, coin_address, pair_address, price, is_sup, enabled) 
                  VALUES (:coin_name, :pair_name, :coin_address, :pair_address, :price, :is_sup, :enabled)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(compact('coin_name', 'coin_address', 'pair_name', 'pair_address', 'price', 'is_sup', 'enabled'));
    }

    public function disableCoin($id)
    {
        $query = "UPDATE coins SET enabled = false WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(compact('id'));
    }

    public function enableCoin($id)
    {
        $query = "UPDATE coins SET enabled = true WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(compact('id'));
    }
}
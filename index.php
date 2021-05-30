<?php
    require 'vendor/autoload.php';
    require 'telegram.php';
    require 'bitquery.php';
    require 'sqlite.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $coinlite = new CoinLite();

    if (isset($_GET['delete'])) {
        $coinlite->deleteCoin($_POST['coin_id']);
    }

    $error = false;
    if (isset($_GET['add'])) {
        if (! isset($_POST['coin_name']) || ! isset($_POST['coin_address']) || ! isset($_POST['pair_name']) || ! isset($_POST['pair_address']) || ! isset($_POST['price'])) {
            $error = "Des champs sont manquant.";
        } else {
            try {
                $bitquery = callBitquery($_POST['coin_address'], $_POST['pair_address']);
                if (!is_array($bitquery->data->ethereum->dexTrades) || sizeof($bitquery->data->ethereum->dexTrades) === 0) {
                    $error = "Impossible de récupérer des infos sur le coins.";
                } else {
                    $is_sup = isset($_POST['is_sup']) && $_POST['is_sup'] === 'on';
                    $coinlite->addCoin($_POST['coin_name'], $_POST['coin_address'], $_POST['pair_name'], $_POST['pair_address'], $_POST['price'], $is_sup);
                }
            } catch (Exception $e) {
                $error = "Impossible de récupérer des infos sur le coins.";
            }
        }
    }

    if (isset($_GET['enable']) && isset($_POST['coin_id'])) {
        $coinlite->enableCoin($_POST['coin_id']);
    }

    if (isset($_GET['disable']) && isset($_POST['coin_id'])) {
        $coinlite->disableCoin($_POST['coin_id']);
    }

    $coins = $coinlite->getCoins();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <title>Coin watcher</title>
</head>
<body>

<div class="container">
    <h1 class="text-center mb-3">MoonKhey</h1>

    <div class="text-center mb-5">
        <img src="https://media.giphy.com/media/dchERAZ73GvOE/giphy.gif">
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Coin</th>
                <th>Paire</th>
                <th>Supérieur</th>
                <th>Prix d'alerte</th>
                <th>Supprimer</th>
                <th>Activé</th>
            </tr>
        </thead>
        <?php
        foreach ($coins as $coin) {
            $coin = (object) $coin;
            $is_sup = $coin->is_sup ? 'oui' : 'non';
            echo "<tr>
                <td>
                    <div>$coin->coin_name</div>
                    <small class='text-muted'>$coin->coin_address</small>
                </td>
                <td>
                    <div>$coin->pair_name</div>
                    <small class='text-muted'>$coin->pair_address</small>
                </td>
                <td>$is_sup</td>
                <td>$coin->price</td>
                <td>
                    <form action='?delete' method='POST'>
                        <input type='hidden' name='coin_id' value='$coin->id'>
                        <button type='submit' class='btn btn-sm btn-danger'>X</button>
                    </form>
                </td>
                <td>";

                    if ($coin->enabled) {
                        echo "<form action='?disable' method='POST'>
                                <div class='form-check form-switch'>
                                    <input type='hidden' name='coin_id' value='" . $coin->id . "'>
                                    <input class='form-check-input' type='checkbox' checked  onclick='$(this).closest(\"form\").submit();'>
                                </div>
                            </form>
                        ";
                    } else {
                        echo "<form action='?enable' method='POST'>
                                <div class='form-check form-switch'>
                                    <input type='hidden' name='coin_id' value='" . $coin->id . "'>
                                    <input class='form-check-input' type='checkbox' onclick='$(this).closest(\"form\").submit();'>
                                </div>
                            </form>
                        ";
                    }
                echo "</td>
            </tr>";
        }
        ?>
    </table>

    <div class="card w-50 mt-5">
        <div class="card-body">
            <h2 class="text-center">Ajouter un coin</h2>

            <?php if ($error) {
                echo "<div class='alert alert-danger'>$error</div>";
            } ?>

            <form action="?add" method="POST">
                <div class="mb-3">
                    <label for="coin_name" class="form-label">Nom du coin</label>
                    <input type="text" class="form-control" id="coin_name" name="coin_name" placeholder="POO">
                </div>
                <div class="mb-3">
                    <label for="coin_address" class="form-label">Adresse du coin</label>
                    <input type="text" class="form-control" id="coin_address" name="coin_address" placeholder="0X.....">
                </div>
                <div class="mb-3">
                    <label for="pair_name" class="form-label">Nom de la paire</label>
                    <input type="text" class="form-control" id="pair_name" name="pair_name" placeholder="BNB">
                </div>
                <div class="mb-3">
                    <label for="pair_address" class="form-label">Adresse de la paire</label>
                    <input type="text" class="form-control" id="pair_address" name="pair_address" placeholder="0X.....">
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Limite de prix</label>
                    <input type="text" class="form-control" id="price" name="price" placeholder="5">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_sup" name="is_sup" checked>
                        <label class="form-check-label" for="is_sup">Doit être supérieur au prix</label>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <p class="mt-5 text-end">About: Watch the price of your favorite shitcoins</p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
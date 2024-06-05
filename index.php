<?php
session_start();

function loadXml() {
    if (!file_exists('spiderman.xml')) {
        die("XML datoteka ne postoji u očekivanom direktoriju.");
    }
    
    $xml = simplexml_load_file('spiderman.xml');
    
    if ($xml === false) {
        echo "Ne mogu učitati XML datoteku!";
        foreach(libxml_get_errors() as $error) {
            echo "<br>", $error->message;
        }
        return false;
    }
    return $xml;
}

function saveXml($xml) {
    $xml->asXML('spiderman.xml');
}

$xml = loadXml();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $newStrip = $xml->stripovi->addChild('strip');
        $newStrip->addChild('naslov', $_POST['naslov']);
        $newStrip->addChild('godina', $_POST['godina']);
        $newStrip->addChild('autor', $_POST['autor']);
        $newStrip->addChild('izdavac', $_POST['izdavac']);
        $newStrip->addChild('opis', $_POST['opis']);
        
        saveXml($xml);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['edit'])) {
        $index = $_POST['index'];
        $strip = $xml->stripovi->strip[$index];
        $strip->naslov = $_POST['naslov'];
        $strip->godina = $_POST['godina'];
        $strip->autor = $_POST['autor'];
        $strip->izdavac = $_POST['izdavac'];
        $strip->opis = $_POST['opis'];
        
        saveXml($xml);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['delete'])) {
        $index = $_POST['index'];
        unset($xml->stripovi->strip[$index]);
        
        saveXml($xml);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$search = isset($_GET['search']) ? strtolower($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'naslov';

function sortXml($xml, $sort) {
    $stripovi = [];
    foreach ($xml->stripovi->strip as $strip) {
        $stripovi[] = $strip;
    }

    usort($stripovi, function ($a, $b) use ($sort) {
        return strcmp((string)$a->$sort, (string)$b->$sort);
    });

    return $stripovi;
}

$sortedStripovi = sortXml($xml, $sort);
$perPage = 5;
$totalPages = ceil(count($sortedStripovi) / $perPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$page = min($page, $totalPages);
$start = ($page - 1) * $perPage;
$end = min(($start + $perPage), count($sortedStripovi));

$stripoviZaPrikaz = array_slice($sortedStripovi, $start, $end - $start);
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spider-Man Kolekcija</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .nav-bar {
            background-color: #333;
            overflow: hidden;
        }
        .nav-bar a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .nav-bar a:hover {
            background-color: #ddd;
            color: black;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .item {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        form {
            margin-bottom: 30px;
        }
        form h2 {
            margin-bottom: 15px;
        }
        form label {
            display: block;
            margin-top: 10px;
        }
        form input, form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        form button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .pagination {
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            background-color: #333;
            color: white;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #007BFF;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
       
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="#home">Početna</a>
        <a href="#stripovi">Stripovi</a>
        <a href="#likovi">Likovi</a>
        <a href="#dogadaji">Događaji</a>
        <a href="#dodaj">Dodaj Strip</a>
    </div>

    <h1 id="home">Spider-Man Kolekcija</h1>
    
    <form method="get">
        <label for="sort">Sortiraj po:</label>
        <select name="sort" id="sort">
            <option value="naslov" <?php echo $sort === 'naslov' ? 'selected' : ''; ?>>Nazivu</option>
            <option value="godina" <?php echo $sort === 'godina' ? 'selected' : ''; ?>>Godini izdanja</option>
        </select>
        <button type="submit">Sortiraj</button>
    </form>
    
    <form method="get">
        <input type="text" name="search" placeholder="Pretraži stripove..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Pretraži</button>
    </form>
    
    <form method="post" id="dodaj">
        <h2>Dodaj Novi Strip</h2>
        <input type="hidden" name="add" value="1">
        <label for="naslov">Naslov:</label>
        <input type="text" name="naslov" id="naslov" required>
        
        <label for="godina">Godina:</label>
        <input type="number" name="godina" id="godina" required>
        
        <label for="autor">Autor:</label>
        <input type="text" name="autor" id="autor" required>
        
        <label for="izdavac">Izdavač:</label>
        <input type="text" name="izdavac" id="izdavac" required>
        
        <label for="opis">Opis:</label>
        <textarea name="opis" id="opis" required></textarea>
        
        <button type="submit">Dodaj Strip</button>
    </form>

    <?php if ($xml): ?>
        <div class="section" id="stripovi">
            <h2>Popularni Stripovi</h2>
            <?php foreach ($stripoviZaPrikaz as $index => $strip): ?>
                <?php
                $naslov = strtolower($strip->naslov);
                $autor = strtolower($strip->autor);
                if ($search && strpos($naslov, $search) === false && strpos($autor, $search) === false) {
                    continue;
                }
                ?>
                <div class="item">
                    <h3><?php echo $strip->naslov; ?> (<?php echo $strip->godina; ?>)</h3>
                    <p><strong>Autor:</strong> <?php echo $strip->autor; ?></p>
                    <p><strong>Izdavač:</strong> <?php echo $strip->izdavac; ?></p>
                    <p><?php echo $strip->opis; ?></p>
                    <button onclick="openModal(<?php echo $start + $index; ?>)">Detalji</button>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="index" value="<?php echo $start + $index; ?>">
                        <button type="submit" name="delete">Izbriši</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&search=<?php echo htmlspecialchars($search); ?>#stripovi" <?php if ($i == $page) echo 'style="background-color: #007BFF;"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>

        <div class="section" id="likovi">
            <h2>Popularni Likovi</h2>
            <?php foreach ($xml->likovi->lik as $lik): ?>
                <div class="item">
                    <h3><?php echo $lik->ime; ?> (<?php echo $lik->alias; ?>)</h3>
                    <p><?php echo $lik->opis; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section" id="dogadaji">
            <h2>Ključni Događaji</h2>
            <?php foreach ($xml->dogadaji->dogadaj as $dogadaj): ?>
                <div class="item">
                    <h3><?php echo $dogadaj->naziv; ?> (<?php echo $dogadaj->godina; ?>)</h3>
                    <p><?php echo $dogadaj->opis; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Greška pri učitavanju XML datoteke. Provjerite je li datoteka ispravno postavljena.</p>
    <?php endif; ?>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalNaslov"></h2>
            <p><strong>Autor:</strong> <span id="modalAutor"></span></p>
            <p><strong>Godina:</strong> <span id="modalGodina"></span></p>
            <p><strong>Izdavač:</strong> <span id="modalIzdavac"></span></p>
            <p><span id="modalOpis"></span></p>
            <form method="post" id="editForm">
                <input type="hidden" name="index" id="modalIndex">
                <label for="naslov">Naslov:</label>
                <input type="text" name="naslov" id="modalInputNaslov" required>
                
                <label for="godina">Godina:</label>
                <input type="number" name="godina" id="modalInputGodina" required>
                
                <label for="autor">Autor:</label>
                <input type="text" name="autor" id="modalInputAutor" required>
                
                <label for="izdavac">Izdavač:</label>
                <input type="text" name="izdavac" id="modalInputIzdavac" required>
                
                <label for="opis">Opis:</label>
                <textarea name="opis" id="modalInputOpis" required></textarea>
                
                <button type="submit" name="edit">Ažuriraj Strip</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(index) {
            var strip = <?php echo json_encode($sortedStripovi); ?>[index];
            document.getElementById('modalNaslov').innerText = strip.naslov;
            document.getElementById('modalAutor').innerText = strip.autor;
            document.getElementById('modalGodina').innerText = strip.godina;
            document.getElementById('modalIzdavac').innerText = strip.izdavac;
            document.getElementById('modalOpis').innerText = strip.opis;

            document.getElementById('modalIndex').value = index;
            document.getElementById('modalInputNaslov').value = strip.naslov;
            document.getElementById('modalInputAutor').value = strip.autor;
            document.getElementById('modalInputGodina').value = strip.godina;
            document.getElementById('modalInputIzdavac').value = strip.izdavac;
            document.getElementById('modalInputOpis').value = strip.opis;

            document.getElementById('myModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('myModal').style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('myModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>

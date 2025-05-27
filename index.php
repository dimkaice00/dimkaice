<?php
// ВКЛЮЧАЕМ ВСЁ В ОДИН ФАЙЛ

// Подключение к БД
$pdo = new PDO("mysql:host=localhost;dbname=furnico_db;charset=utf8mb4", "dimkaice", "1234");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Простая маршрутизация
$page = $_GET['page'] ?? 'list';

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Продукция</title>
    <style>
        body { font-family: Candara, sans-serif; background-color: #FFFFFF; color: #000; }
        .container { width: 80%%; margin: 20px auto; background-color: #D2DFFF; padding: 20px; border-radius: 10px; }
        h1, h2 { color: #355CBD; text-align: center; }
        table { width: 100%%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #e0e0ff; }
        input, select, button { padding: 6px; margin: 5px 0; }
        button { background-color: #355CBD; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
<?php
if ($page === 'list') {
    echo "<h1>Список продукции</h1>";
    echo '<a href="?page=add"><button>Добавить продукцию</button></a>';

    $stmt = $pdo->query("SELECT p.id, p.article, p.name, pt.name AS type, m.name AS material, p.min_partner_price
                         FROM products p
                         JOIN product_types pt ON p.product_type_id = pt.id
                         JOIN materials m ON p.material_id = m.id
                         ORDER BY p.id DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        echo "<table><tr><th>Артикул</th><th>Наименование</th><th>Тип</th><th>Материал</th><th>Цена</th><th>Цеха</th></tr>";
        foreach ($rows as $row) {
            echo "<tr>
                <td>" . h($row['article']) . "</td>
                <td>" . h($row['name']) . "</td>
                <td>" . h($row['type']) . "</td>
                <td>" . h($row['material']) . "</td>
                <td>" . h($row['min_partner_price']) . "</td>
                <td><a href='?page=workshops&id={$row['id']}'>Посмотреть</a></td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Продукция не найдена.</p>";
    }

} elseif ($page === 'add') {
    echo "<h1>Добавить продукцию</h1>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $article = $_POST['article'];
        $name = $_POST['name'];
        $type_id = $_POST['type_id'];
        $material_id = $_POST['material_id'];
        $price = $_POST['price'];

        $stmt = $pdo->prepare("INSERT INTO products (article, name, product_type_id, material_id, min_partner_price)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$article, $name, $type_id, $material_id, $price]);

        echo "<p>✅ Добавлено! <a href='?'>Вернуться к списку</a></p>";
    }

    $types = $pdo->query("SELECT id, name FROM product_types")->fetchAll(PDO::FETCH_ASSOC);
    $materials = $pdo->query("SELECT id, name FROM materials")->fetchAll(PDO::FETCH_ASSOC);

    echo '<form method="post">
        <label>Артикул: <input type="text" name="article" required></label><br>
        <label>Название: <input type="text" name="name" required></label><br>
        <label>Тип: 
            <select name="type_id" required>';
                foreach ($types as $type) {
                    echo "<option value='{$type['id']}'>" . h($type['name']) . "</option>";
                }
    echo    '</select></label><br>
        <label>Материал: 
            <select name="material_id" required>';
                foreach ($materials as $m) {
                    echo "<option value='{$m['id']}'>" . h($m['name']) . "</option>";
                }
    echo    '</select></label><br>
        <label>Мин. цена: <input type="number" step="0.01" name="price" required></label><br>
        <button type="submit">Сохранить</button>
        <a href="?"><button type="button">Назад</button></a>
    </form>';

} elseif ($page === 'workshops' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("
        SELECT w.name AS workshop, w.worker_count, pw.time_in_workshop
        FROM product_workshop pw
        JOIN workshops w ON pw.workshop_id = w.id
        WHERE pw.product_id = ?");
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Цеха по продукции ID $id</h1>";
    echo '<a href="?"><button>Назад</button></a>';

    if ($rows) {
        $total = 0;
        echo "<table><tr><th>Цех</th><th>Рабочие</th><th>Время (мин)</th></tr>";
        foreach ($rows as $r) {
            $total += $r['time_in_workshop'];
            echo "<tr>
                    <td>" . h($r['workshop']) . "</td>
                    <td>" . h($r['worker_count']) . "</td>
                    <td>" . h($r['time_in_workshop']) . "</td>
                </tr>";
        }
        echo "<tr><td colspan='2'><strong>Общее время</strong></td><td><strong>$total мин</strong></td></tr>";
        echo "</table>";
    } else {
        echo "<p>Нет данных по цехам для этой продукции.</p>";
    }
}
?>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
<html>
    <!--
</html>
-- Типы продукции
CREATE TABLE product_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    coefficient FLOAT NOT NULL
);

-- Материалы
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    loss_percent FLOAT NOT NULL
);

-- Цеха
CREATE TABLE workshops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    worker_count INT NOT NULL,
    production_time INT NOT NULL DEFAULT 0
);

-- Продукция
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    product_type_id INT NOT NULL,
    material_id INT NOT NULL,
    min_partner_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (product_type_id) REFERENCES product_types(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- Связь "продукт — цех"
CREATE TABLE product_workshop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    workshop_id INT NOT NULL,
    time_in_workshop INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (workshop_id) REFERENCES workshops(id)
);
rmdir /s /q .git
del /f /q .gitignore
del /f /q .gitattributes
del /f /q README.md
del /f /q LICENSE

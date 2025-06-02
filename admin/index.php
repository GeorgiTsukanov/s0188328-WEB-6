<?php
$config = require_once __DIR__ . '/../../../config/s0188328_WEB_6.php';
require_once __DIR__ . '/../WorkWithSQL.php';
require_once __DIR__ . '/../Validate.php';
require_once __DIR__ . "/../session_start.php";

// Базовая аутентификация
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) != md5('123')) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Панель администратора"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// Получаем данные из базы
$applications = getAllApplication(getConnection($config));
$count = is_array($applications) ? count($applications) : 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .btn { padding: 5px 10px; text-decoration: none; }
        .btn-delete { color: #d33; }
        .stat-card { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Админ-панель</h1>
        
        <?php if (isset($message)): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Всего заявок</h3>
                <div class="stat-value"><?= $count ?></div>
            </div>
        </div>
        
        <h2>Заявки пользователей</h2>
        <?php if (!empty($applications)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Отчество</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['ID']) ?></td>
                        <td><?= htmlspecialchars($app['LastName']) ?></td>
                        <td><?= htmlspecialchars($app['FirstName']) ?></td>
                        <td><?= htmlspecialchars($app['Patronymic']) ?></td>
                        <td><?= htmlspecialchars($app['PhoneNumber']) ?></td>
                        <td><?= htmlspecialchars($app['Email']) ?></td>
                        <td><?= htmlspecialchars($app['BirthDay']) ?></td>
                        <td><?= htmlspecialchars($app['Gender'] === 'male' ? 'Мужской' : 'Женский') ?></td>
                        <td><?= htmlspecialchars($app['languages']) ?></td>
                        <td>
                            <a href="update.php?id=<?= $app['ID'] ?>" class="btn">Редактировать</a>
                            <a href="delete.php?id=<?= $app['ID'] ?>" class="btn">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Нет заявок для отображения</p>
        <?php endif; ?>
    </div>

</body>
</html>
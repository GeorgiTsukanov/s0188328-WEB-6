<?php
// Включим вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = require_once __DIR__ . '/../../../config/s0188328_WEB_6.php';
require_once __DIR__ . '/../WorkWithSQL.php';
require_once __DIR__ . '/../Validate.php';
require_once __DIR__ . '/../session_start.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: update.php");
    exit;
}

// Получаем и валидируем данные
$id = (int)($_POST['id'] ?? 0);
$fio = validateInput($_POST['fio'] ?? '');
$phone = "8" . str_replace("-", "", validateInput($_POST['phone'] ?? ''));
$email = validateInput($_POST['email'] ?? '');
$birthday = validateInput($_POST['birthday'] ?? '');
$gender = validateInput($_POST['gender'] ?? '');
$biography = validateInput($_POST['biography'] ?? '');
$languages = $_POST['languages'] ?? [];

// Валидация данных
$error = isValidatePost($fio, $email, $phone, $birthday, $gender, $languages);

if (!empty($error)) {
    // Сохраняем ошибки и введенные данные в сессию
    $_SESSION['form_errors'] = $error;
    $_SESSION['form_data'] = [
        'fio' => $fio,
        'phone' => $_POST['phone'] ?? '',
        'email' => $email,
        'birthday' => $birthday,
        'gender' => $gender,
        'biography' => $biography,
        'languages' => $languages
    ];
    
    header("Location: update.php?id=$id");
    exit;
}

try {
    // Обновляем данные в БД
    $result = updateApplication(
        getConnection($config),
        $id,
        makeAssociativeArray($fio),
        $phone,
        $email,
        $birthday,
        $gender,
        $biography,
        $languages
    );
    
    header("Location: index.php");
    exit;
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Ошибка: ' . $e->getMessage();
    header("Location: update.php?id=$id");
    exit;
}
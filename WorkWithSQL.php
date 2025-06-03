<?php

function getConnection($config){
    try {
        $conn = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['username'],
            $config['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        throw new Exception("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

function findApplication($conn, $loginUser) {
    try {
        $sql = "SELECT ID FROM Application WHERE 
            LoginUser = :loginUser;";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':loginUser', loginUser);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Вернёт ассоциативный массив или false
    } 
    catch (PDOException $e) {
        throw new Exception("Ошибка при поиске id: " . $e->getMessage());
    }
}

function insertApplication($conn, $loginUser, $passwordHash, $fio, $phone, $email, $birthday, $gender, $biography, $languages){
    $conn->beginTransaction();
    try{

        $sql = "INSERT INTO Application (LastName, FirstName, Patronymic, PhoneNumber, Email, BirthDay, Gender, Biography) 
                VALUES (:lastName, :firstName, :patronymic, :phone, :email, :birthDay, :gender, :biography)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':lastName', $fio["LastName"]);
        $stmt->bindParam(':firstName', $fio["FirstName"]);
        $stmt->bindParam(':patronymic', $fio["Patronymic"]);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':birthDay', $birthday);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':biography', $biography);

        $stmt->execute();

        $applicationId = $conn->lastInsertId();

        $sql = "INSERT INTO User (ID, LoginUser, PasswordHash) VALUES (:id, :loginUser, :passwordHash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $applicationId);
        $stmt->bindParam(':loginUser', $loginUser);
        $stmt->bindParam(':passwordHash', $passwordHash);
        $stmt->execute();

        $sql = "INSERT INTO FavoriteProgrammingLanguage (ID, ID_ProgrammingLanguage) VALUES (:id, :pl)";
        $stmt = $conn->prepare($sql);
    
        foreach ($languages as $language) {
            $stmt->bindParam(':id', $applicationId);
            $stmt->bindParam(':pl', $language, PDO::PARAM_INT);
            $stmt->execute();
        }
        $conn->commit();
        echo "Заявка успешно добавлена";
    }
    catch (PDOException $e) {
        $conn->rollBack();
        throw new Exception("Ошибка при добавлении заявки: " . $e->getMessage());
    }
    finally {
        $conn = null;
    }
}

function updateApplication($conn, $id, $fio, $phone, $email, $birthday, $gender, $biography, $languages){
    $conn->beginTransaction();
    try{
        $sql = "UPDATE Application 
                SET 
                LastName = :lastName, 
                FirstName= :firstName, 
                Patronymic= :patronymic, 
                PhoneNumber= :phoneNumber, 
                Email= :email, 
                BirthDay= :birthDay, 
                Gender= :gender, 
                Biography= :biography
                WHERE ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':lastName', $fio["LastName"]);
        $stmt->bindParam(':firstName', $fio["FirstName"]);
        $stmt->bindParam(':patronymic', $fio["Patronymic"]);
        $stmt->bindParam(':phoneNumber', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':birthDay', $birthday);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':biography', $biography);

        $stmt->execute();

        $sql = "DELETE FROM FavoriteProgrammingLanguage WHERE ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $sql = "INSERT INTO FavoriteProgrammingLanguage (ID, ID_ProgrammingLanguage) VALUES (:id, :pl)";
        $stmt = $conn->prepare($sql);
        foreach ($languages as $language) {
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':pl', $language);
            $stmt->execute();
        }
        $conn->commit();
        echo "Заявка успешно обновлена!";
    }
    catch (PDOException $e) {
        $conn->rollBack();
        throw new Exception("Ошибка при обновлении заявки: " . $e->getMessage());
    }
    finally {
        $conn = null;
    }
}

function getAllApplication($conn) {
    try {
        $sql = "SELECT 
                    a.ID,
                    a.LastName,
                    a.FirstName,
                    a.Patronymic,
                    a.PhoneNumber,
                    a.Email,
                    a.BirthDay,
                    a.Gender,
                    GROUP_CONCAT(p.ProgrammingLanguage SEPARATOR ', ') AS languages
                FROM 
                    Application a
                LEFT JOIN 
                    FavoriteProgrammingLanguage f ON a.ID = f.ID
                LEFT JOIN 
                    ProgrammingLanguage p ON f.ID_ProgrammingLanguage = p.ID_ProgrammingLanguage
                GROUP BY 
                    a.ID";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $e) {
        throw new Exception("Ошибка при запросе: " . $e->getMessage());
    }
}


function deleteApp($conn, $ID) {
    try {      
        $conn->beginTransaction();
        $stmt = $conn->prepare("DELETE FROM Application WHERE ID = ?");
        $stmt->execute([$ID]);
        $conn->commit();
        return "Заявка #$ID успешно удалена";
    } 
    catch (PDOException $e) {
        $conn->rollBack();
        throw new Exception("Ошибка при удалении: " . $e->getMessage());
    }
}

function getApp($conn, $ID){
    try {
        $sql = "SELECT * FROM Application WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ID]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $e) {
        throw new Exception("Ошибка при запросе: " . $e->getMessage());
    }
}

function getPL($conn, $ID){
    try {
        $sql = "SELECT ProgrammingLanguage 
        FROM FavoriteProgrammingLanguage F JOIN ProgrammingLanguage P ON F.ID_ProgrammingLanguage = P.ID_ProgrammingLanguage  
        WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ID]);
        
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $result ?: [];
    } 
    catch (PDOException $e) {
        throw new Exception("Ошибка при запросе: " . $e->getMessage());
    }
}

function getLanguageStatistics($connection) {
    $query = "
        SELECT 
            pl.ProgrammingLanguage, 
            COUNT(fpl.ID_ProgrammingLanguage) as count
        FROM 
            ProgrammingLanguage pl
        LEFT JOIN 
            FavoriteProgrammingLanguage fpl ON pl.ID_ProgrammingLanguage = fpl.ID_ProgrammingLanguage
        GROUP BY 
            pl.ProgrammingLanguage
        ORDER BY 
            count DESC
    ";
    
    $stmt = $connection->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

?>

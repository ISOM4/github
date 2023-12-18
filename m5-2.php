<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>DB連携簡易掲示板</title>
</head>
<body>
<?php
    echo "投稿には名前、コメント、任意のパスワードが必要です。削除・編集には投稿時のパスワードが必要です。";
    
    // DB接続設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //テーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS tbboard"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "password TEXT,"
    . "date TEXT"
    .");";
    $stmt = $pdo->query($sql);

    //投稿機能
    if(isset($_POST["name"]) && isset($_POST["text"]) && !empty($_POST["name"]) && !empty($_POST["text"]) && empty($_POST["postnumber"]) && isset($_POST["password"]) && !empty($_POST["password"])){
        //DBにデータを入力
        $name = $_POST["name"];
        $comment = $_POST["text"];
        $pass = $_POST["password"];
        $date = date("Y/m/d H:i:s");
    
        $sql = "INSERT INTO tbboard (name, comment, password, date) VALUES (:name, :comment, :password, :date)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $pass, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        //Web上に投稿内容を表示
        $sql = 'SELECT * FROM tbboard';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        echo "<br>";
        echo "<br>";
        echo "<br>"; 
        echo "<br>";         
        foreach ($results as $row){
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['date'].'<br>';
            echo "<hr>";
        }
    //編集機能２（投稿し直し）
    }elseif(isset($_POST["name"]) && isset($_POST["text"]) && !empty($_POST["name"]) && !empty($_POST["text"]) && isset($_POST["password"]) && !empty($_POST["password"]) && !empty($_POST["postnumber"])){
        //DBにデータを入力
        $id = $_POST["postnumber"];
        $name = $_POST["name"];
        $comment = $_POST["text"];
        $pass = $_POST["password"];
    
        $sql = 'update tbboard set name =:name, comment =:comment, password =:password WHERE id =:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $pass, PDO::PARAM_STR);
        $stmt->execute();
        //Web上に投稿内容を表示
        $sql = 'SELECT * FROM tbboard';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        echo "<br>";
        echo "<br>";
        echo "<br>"; 
        echo "<br>";         
        foreach ($results as $row){
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['date'].'<br>';
            echo "<hr>";
        }
    //削除機能
    }elseif(isset($_POST["number"]) && !empty($_POST["number"]) && isset($_POST["password"]) && !empty($_POST["password"])){
        $sql = 'SELECT * FROM tbboard';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            if($row['id'] == $_POST["number"] && $row['password'] == $_POST["password"]){
                $id = $_POST["number"];
                $postpassword =$_POST["password"];
                $sql = 'delete from tbboard where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                //投稿番号の振り直し
                $sql = 'set @n:=0';
                $stmt = $pdo->prepare($sql);                
                $stmt->execute();
                $sql1 = 'update tbboard set id=@n:=@n+1';
                $stmt = $pdo->prepare($sql1);
                $stmt->execute();
                //次に投稿される番号を修正
                $sql2 = 'ALTER TABLE tbboard AUTO_INCREMENT = 1';
                $stmt = $pdo->prepare($sql2);
                $stmt->execute();

                $sql3 = 'SELECT * FROM tbboard';
                $stmt = $pdo->query($sql3);
                $results2 = $stmt->fetchAll();
                echo "<br>";
                echo "<br>";
                echo "<br>"; 
                echo "<br>";         
                foreach ($results2 as $row2){
                    echo $row2['id'].' ';
                    echo $row2['name'].' ';
                    echo $row2['comment'].' ';
                    echo $row2['date'].'<br>';
                    echo "<hr>";
                }
            }
        }
    //編集機能
    }elseif(isset($_POST["editnumber"]) && !empty($_POST["editnumber"]) && isset($_POST["password"]) && !empty($_POST["password"])){
        $sql = 'SELECT * FROM tbboard';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            if($row['id'] == $_POST["editnumber"] && $row['password'] == $_POST["password"]){
                $re_postnumber = $row['id'];
                $re_name = $row['name'];
                $re_comment = $row['comment'];
            }
        }
        $sql = 'SELECT * FROM tbboard';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        echo "<br>";
        echo "<br>";
        echo "<br>"; 
        echo "<br>";         
        foreach ($results as $row){
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['date'].'<br>';
            echo "<hr>";
        }
    }       
    ?>
    <!-- 入力フォーム　-->
    <form action="" method="post" style="position: absolute; left: 3px; top: 28px">
        <input type="name" name="name" placeholder="名前" value ="<?php if(isset($_POST["editnumber"]) && !empty($_POST["editnumber"]) && isset($_POST["password"]) && !empty($_POST["password"])){echo $re_name;}?>">
        <input type="text" name="text" placeholder="コメント" value="<?php if(isset($_POST["editnumber"]) && !empty($_POST["editnumber"]) && isset($_POST["password"]) && !empty($_POST["password"])){echo $re_comment;}?>">
        <input type="text" name="password" placeholder="パスワード">
        <input type="submit" name="submit" value="投稿"><br>
        <input type="number" name="number" placeholder="削除対象番号">
        <input type="submit" name="submit" value = "削除"><br>
        <input type="number" name="editnumber" placeholder="編集番号">
        <input type="submit" name="submit" value ="編集">
        <input type="hidden" name="postnumber" value="<?php if(isset($_POST["editnumber"]) && !empty($_POST["editnumber"])){echo $re_postnumber;}?>">
    </form>        

</body>
</html>
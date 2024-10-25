<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mission5</title>
</head>

<body>

    <?php
    date_default_timezone_set('Asia/Tokyo');
    // DB接続設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザ名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    // 初期化
    $edit_name = "";
    $edit_comment = "";
    $edit_id = "";
    $edit_pass = "";
    $mode = "新規投稿フォーム"; // 初期状態は新規投稿モード
    

    // 編集番号が送信された場合、その投稿内容を取得してフォームに表示
    if (!empty($_POST["id_edit"]) && !empty($_POST["check_pass"])) {
        $id = $_POST["id_edit"];
        $sql = 'SELECT * FROM board WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            // パスワード確認処理
            if (isset($_POST["check_pass"]) && $_POST["check_pass"] === $result['pass']) {
                $edit_name = $result['name'];
                $edit_comment = $result['comment'];
                $edit_id = $result['id'];
                $edit_pass = $result['pass'];
                $mode = "編集フォーム（投稿番号: " . $edit_id . "）";
            } else {
                echo "<script type='text/javascript'>alert('パスワードが一致しません。');</script>";
            }
        } else {
            echo "<script type='text/javascript'>alert('該当する投稿が見つかりません。');</script>";
        }
    } elseif (!empty($_POST["id_edit"])) {
        echo "<script type='text/javascript'>alert('パスワードを入力してください。');</script>";
    }




    ?>

    <!-- 投稿フォーム（新規投稿と編集を兼ねている） -->
    <h2><?php echo $mode; ?></h2> <!-- 現在のモードを表示 -->

    <form action="" method="post" name="post">
        <input type="text" name="name" placeholder="名前" value="<?php echo $edit_name; ?>">
        <input type="text" name="comment" placeholder="コメント" value="<?php echo $edit_comment; ?>">
        <input type="password" name="pass" placeholder="パスワード" value="<?php echo $edit_pass; ?>">

        <!-- 編集時のみIDを渡す隠しフィールド -->
        <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
        <input type="submit" name="submit" value="送信">
    </form>

    <!-- 編集フォーム -->
    <h2>編集フォーム</h2>
    <form action="" method="post" name="edit">
        <input type="number" name="id_edit" placeholder="投稿番号">
        <input type="password" name="check_pass" placeholder="パスワード">
        <input type="submit" name="submit" value="確認">
    </form>



    <!-- 削除フォーム -->
    <h2>削除フォーム</h2>
    <form action="" method="post" name="delete">
        <input type="number" name="id_del" placeholder="投稿番号">
        <input type="password" name="del_pass" placeholder="パスワード">
        <input type="submit" name="submit" value="削除">
    </form>



    <?php

    // 新規投稿・編集の処理
    if (!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass"])) {
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $date = date("Y/m/d H:i:s");
        $pass = $_POST['pass']; // パスワードを取得
    
        // 編集の場合
        if (!empty($_POST["edit_id"])) {
            $id = $_POST["edit_id"];
            $sql = "UPDATE board SET name=:name, comment=:comment, date=:date, pass=:pass WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo "<script type='text/javascript'>alert('投稿を編集しました。');</script>";
        } else if (empty($_POST["edit_id"])) {
            // 新規投稿の場合
            $sql = "INSERT INTO board (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->execute();
            echo "<script type='text/javascript'>alert('投稿が完了しました。');</script>";
        }
    } elseif (!empty($_POST["name"]) && !empty($_POST["comment"])) {
        echo "<script type='text/javascript'>alert('パスワードを入力してください。');</script>";
    }

    // 削除処理
    if (!empty($_POST["id_del"]) && !empty($_POST["del_pass"])) {
        $id = $_POST["id_del"];
        $sql = 'SELECT * FROM board WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $stored_pass = $result['pass']; // 削除する投稿のパスワードを取得
            if (isset($_POST["del_pass"]) && $_POST["del_pass"] === $stored_pass) {  // パスワード確認処理を追加
                $sql = 'DELETE FROM board WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo "<script type='text/javascript'>alert('投稿が削除されました。');</script>";
            } else {
                echo "<script type='text/javascript'>alert('パスワードが一致しません。');</script>";
            }
        } else {
            $alert = "<script type='text/javascript'>alert('該当する投稿が見つかりません。');</script>";
            echo $alert;
        }

    } elseif (!empty($_POST["id_del"])) {
        echo "<script type='text/javascript'>alert('パスワードを入力してください。');</script>";
    }



    // 投稿の表示
    $sql = 'SELECT * FROM board';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();

    echo '<hr>';
    foreach ($results as $row) {
        echo '<div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">';
        echo '<span style= "font-weight: bold;">' . $row['id'] . ' ' . $row['name'] . '</span>'; // 左側の要素
        echo '<span style="text-align: center;">' . $row['comment'] . '</span>'; // 中央の要素
        echo '<span style="text-align: right; text-align: right; font-size: 10px;">' . $row['date'] . '</span>'; // 右側の要素
        echo '</div>';
        echo '<hr>';
    }


    ?>



</body>

</html>
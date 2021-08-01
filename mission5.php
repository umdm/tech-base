<!DOCTYPE html> 
<html lang="ja"> 
<head> 
    <meta charset="utf-8" /> 
    
</head> 
<body> 


<?php


//DB関連

    //DB接続
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //テーブル制作
   $sql = "CREATE TABLE IF NOT EXISTS tbform"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "date DATETIME,"
    . "form_password TEXT"
    .");";
    $stmt = $pdo->query($sql);
    
//送信削除編集分岐
if(isset($_POST["submit1"])) {
$process = $_POST["submit1"];
    switch ($process){
        case"送信":#送信ボタンが押されたとき
            //新規投稿の場合
            if(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["edit_number"]) && !empty($_POST["submitpass"])){
                
                //新規投稿内容を変数に格納
                $name = $_POST["name"];
                $comment = $_POST["comment"]; 
                $date = date('Y/m/d h:i:s');
                $form_password = $_POST["submitpass"];
                
                //格納した内容を書き込む
                $sql = $pdo -> prepare("INSERT INTO tbform (name, comment, date, form_password) VALUES (:name, :comment, :date, :form_password)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> bindParam(':form_password', $form_password, PDO::PARAM_STR);
                $sql -> execute();
                
                //動作メッセージを格納（下のほうで表示）
                $message = "送信されました。";
                
            //編集送信の場合
            }elseif(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["edit_number"]) && !empty($_POST["submitpass"])){
               
                //変更内容を変数に格納
                $id             = $_POST["edit_number"]; 
                $name           = $_POST["name"];
                $comment        = $_POST["comment"]; 
                $form_password  = $_POST["submitpass"];

                //既存の内容を格納したにものに更新　更新場所はidで指定
                $sql = 'UPDATE tbform SET name=:name,comment=:comment,form_password=:form_password WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':form_password', $form_password, PDO::PARAM_STR);
                $stmt->execute();
                
                //動作メッセージを格納（下のほうで表示）
                $message = "編集が完了しました。";
            }
        break;
        
        case"削除":#削除ボタンが押されたとき
            if(!empty($_POST["delete_num"]) && !empty($_POST["delpass"])){
            
                //idにフォームから削除したい番号を格納
                $id = $_POST["delete_num"];
                
                //idから削除したい内容のパスワードを抽出
                $sql = 'SELECT * FROM tbform WHERE id=:id';
                $stmt = $pdo->prepare($sql);                  
                $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                $stmt->execute();                             
                $results = $stmt->fetchAll(); 
                    foreach ($results as $row){
                        $delete_password = $row['form_password'];
                    }
                    
                //上の処理で$delete_passwordに何も格納されなかった（指定されたidで抽出できなかった時）
                //break;で処理を終了する
                if(empty($delete_password)){
                    $message = "指定した番号はありません。";
                    break;
                }
                
                //$delete_password（格納されたパスワード）と
                //$_POST["delpass"]（入力されたパスワード）が
                //一致する場合は削除、一致しない場合は何もしない 
                if($delete_password == $_POST["delpass"]){
                    
                    //削除する　削除場所はidで指定する。
                    $sql = 'delete from tbform where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute(); 
                    
                    //動作メッセージを格納（下のほうで表示）
                    $message = "削除されました";
                    
                }else{
                    
                    //動作メッセージを格納（下のほうで表示）
                    $message = "パスワードが違います。";
                }
            }
        break;
        
        case"編集":#編集ボタンが押されたときーーーーーー
            if(!empty($_POST["edit_num"]) && !empty($_POST["editpass"])){
                
                //idにフォームから送信フォームに表示したい番号を格納
                $id = $_POST["edit_num"];
                
                //idから削除したい内容のパスワードを抽出
                $sql = 'SELECT * FROM tbform WHERE id=:id ';
                $stmt = $pdo->prepare($sql);                  
                $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                $stmt->execute();                             
                $results = $stmt->fetchAll(); 
                    foreach ($results as $row){
                        $edit_password = $row['form_password'];
                    }
                
                //上の処理で$edit_passwordの何も格納されなかった（指定されたidで抽出できなかった時）
                //break;で処理を終了する
                if(empty($edit_password)){
                    $message = "指定した番号はありません。";
                    break;
                }
                
                //$edit_password（格納されたパスワード）と
                //$_POST["editpass"]（入力されたパスワード）が
                //一致する場合は削除、一致しない場合は何もしない
                if($edit_password == $_POST["editpass"]){
                    
                    //idで指定された内容を変数displayに格納（変数displayを通して送信フォームに表示）
                    $sql = 'SELECT * FROM tbform WHERE id=:id ';
                    $stmt = $pdo->prepare($sql);                  
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                    $stmt->execute();                             
                    $results = $stmt->fetchAll(); 
                        foreach ($results as $row){
                            //指定された内容をdisplayに格納
                            $display_num        = $row['id'];
                            $display_name       = $row['name'];
                            $display_comment    = $row['comment'];
                            $display_password   = $row['form_password'];
                        }
                        
                    //動作メッセージを格納（下のほうで表示）    
                    $message ="編集が押されました。";
                    
                }else{
                    
                    //動作メッセージを格納（下のほうで表示）
                    $message = "パスワードが違います。";
                }
            }
        break;
        default: echo "エラー"; exit;
    }
}

?>


<form action="" method="post">
    <p>
    <strong>【　投稿フォーム　】</strong><br>
    名前　　　：<input type="text"      name="name"         value = "<?php if(empty($display_name)){echo "";}else{echo $display_name;} ?>"        placeholder="名前"><br>
    コメント　：<input type="text"      name="comment"      value = "<?php if(empty($display_comment)){echo "";}else{echo $display_comment;} ?>"  placeholder="コメント"><br>
    パスワード：<input type="password"  name="submitpass"   value = "<?php if(empty($display_password)){echo "";}else{echo $display_password;} ?>"placeholder = "パスワード"><br>
                <input type="number"    name="edit_number"  value = "<?php if(empty($display_num)){echo "";}else{echo $display_num;} ?>" ><br>
                <input type="submit"    name="submit1"      value ="送信"><br>
    <p/>
    
    <p>
    <strong>【　削除フォーム　】</strong><br>
    削除番号　：<input type="number"    name="delete_num"   placeholder="削除番号"><br>
    パスワード：<input type="password"  name="delpass"      placeholder = "パスワード"><br>
                <input type="submit"    name="submit1"      value = "削除"><br>
    </p>
    
    <p>
    <strong>【　編集フォーム　】</strong><br>
    編集番号　：<input type="number"    name="edit_num"     placeholder="編集番号"><br>
    パスワード：<input type="password"  name="editpass"     placeholder = "パスワード"><br>
                <input type="submit"    name="submit1"      value = "編集"><br>
    </p>
    
</form>


<p>
---------------------------------------<br>
    <?php //格納されたメッセージを表示、何もなければ”メッセージはありません”と表示
    if(empty($message)){
        echo "メッセージはありません。"."<br>";
    }else{
        echo $message."<br>";
    }
    ?>
--------------------------------------<br>
    <strong>【　投稿一覧　】</strong>
</p>



<?php
//投稿を出力（表示）

    //DB接続設定
    $dsn = 'mysql:dbname=tb230140db;host=localhost';
    $user = 'tb-230140';
    $password = 'TWnfSpAWxv';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //表示
    $sql = 'SELECT * FROM tbform';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].',';
        echo $row['form_password'].'<br>';
    echo "<hr>";
    }
?>





 
 
</body>
</html>
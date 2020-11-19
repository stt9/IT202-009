<?php require_once(__DIR__. "/partials/nav.php");?>
    <div class="container-fluid">
        <h3>Create Survey</h3>
        <form method= "POST">
            <div class="form-group">
                <label>Title</label>
                <input class="form-control" name="title" placeholder="Title"/>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input class="form-control" name="description" placeholder="Description"/>
            </div>
            <div class="form-group">
                <label>Visibility</label>
                <select class="form-control" name="visibility">
                    <option value="0">Draft</option>
                    <option value="1">Private</option>
                    <option value="2">Public</option>
                </select>
            </div>
            <input class="btn btn-primary" type="submit" name="save" value="Create"/>
        </form>
    </div>
<?php
if(isset($_POST["save"])){
    $title = $_POST["title"];
    $description = $_POST["description"];
    $visibility = $_POST["visibility"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Survey (title, description, visibility, user_id) VALUES(:title, :description, :visibility, :user)");
    $r = $stmt->execute([
        ":title"=>$title,
        ":description"=>$description,
        ":visibility"=>$visibility,
        ":user"=>$user
    ]);

    if($r){
        flash("Created successfully wth id: " . $db->lastInsertID());
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__. "/partials/flash.php");?>
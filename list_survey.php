<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])){
    $query = $_POST["query"];
    $_SESSION["query"] = $query;
}
elseif (isset($_SESSION["query"])){
    $query =  $_SESSION["query"];
}
?>
<?php
$per_page = 10;
if (!empty($query) && has_role("Admin")){
    $db = getDB();
    $q = "SELECT count(*) as total FROM Survey Where (title like :q)";
    $params = [":q" => "%$query%"];
    paginate($q, $params, $per_page);

    $stmt = $db->prepare("SELECT * FROM Survey Where (title like :q) LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":q", "%$query%");
    $r = $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    }
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results");
    }
}
elseif (!empty($query)){
    $db = getDB();
    $q = "SELECT count(*) as total FROM Survey Where visibility = 2 AND (title like :q)";
    $params = [":q" => "%$query%"];
    paginate($q, $params, $per_page);

    $stmt = $db->prepare("SELECT * FROM Survey Where visibility = 2 AND (title like :q) LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":q", "%$query%");
    $r = $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    }
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results");
    }
}
elseif (has_role("Admin")){
    $db = getDB();
    $q = "SELECT count(*) as total FROM Survey";
    $params = [];
    paginate($q, $params, $per_page);

    $stmt = $db->prepare("SELECT * FROM Survey LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $r = $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    }
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results admin");
    }

    $stmt = $db->prepare("Select * from Survey s where s.id not in (SELECT distinct survey_id from Responses where user_id = :user_id) ORDER BY RAND() LIMIT 1");
    $r = $stmt->execute([":user_id" => get_user_id()]);
    $random = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else{
    $db = getDB();
    $q = "SELECT count(*) as total FROM Survey where visibility = 2";
    $params = [];
    paginate($q, $params, $per_page);

    $stmt = $db->prepare("SELECT * FROM Survey Where visibility = 2 LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $r = $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    }
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results");
    }

    $stmt = $db->prepare("Select * from Survey s where s.id not in (SELECT distinct survey_id from Responses where user_id = :user_id) ORDER BY RAND() LIMIT 1");
    $r = $stmt->execute([":user_id" => get_user_id()]);
    $random = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("SELECT Survey.title, Survey.id, count(Responses.survey_id) as total FROM Survey LEFT JOIN (SELECT distinct user_id, survey_id FROM Responses) as Responses on Survey.id = Responses.survey_id GROUP BY title");
$r = $stmt->execute();
if ($r){
    $taken = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else{
    flash("There was a problem fetching the results");
}
?>
    <div class="container-fluid">
        <h3>List Surveys</h3>
        <form method="POST" class="form-inline">
            <input class="form-control" name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
            <input class="btn btn-primary" type="submit" value="Search" name="search"/>
        </form>
        <a class="btn btn-success" type="button" href="take_survey.php?id=<?php safer_echo($random[0]['id']); ?>">Random Survey</a>

        <div class="results">
            <?php if (count($results) > 0): ?>
                <div class="list-group">
                    <?php foreach ($results as $r): ?>
                        <div class="list-group-item">
                            <?php foreach($taken as $ind): ?>
                                <?php if ($ind["title"] == $r["title"]): ?>
                                    <div class="row">
                                        <div class="col">
                                            <div>Title:</div>
                                            <div><?php safer_echo($r["title"]); ?></div>
                                        </div>
                                        <?php if (has_role("Admin")): ?>
                                            <div class="col">
                                                <div>Visibility:</div>
                                                <div><?php safer_echo(getVisibility($r["visibility"])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col">
                                            <a class="btn btn-info" type="button" href="view_profile.php?id=<?php safer_echo($r['user_id']); ?>&query=<?php echo $query ?>">View Creator's Profile</a>
                                        </div>
                                        <div class="col">
                                            <?php if (has_role("Admin")): ?>
                                                <a class="btn btn-warning" type="button" href="edit_survey.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                                            <?php endif; ?>
                                            <?php if ($r["visibility"] == 2): ?>
                                                <a class="btn btn-success" type="button" href="survey_taken.php?id=<?php safer_echo($r['id']); ?>">Take Survey</a>
                                            <?php endif; ?>
                                            <?php if (intval($ind["total"]) > 0): ?>
                                                <a class="btn btn-primary" type="button" href="results.php?id=<?php safer_echo($r['id']); ?>">View Results</a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <div>Times Survey Taken:</div> <div><?php safer_echo($ind["total"]); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No results</p>
            <?php endif; ?>
        </div>
        <?php include(__DIR__."/partials/pagination.php");?>
    </div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
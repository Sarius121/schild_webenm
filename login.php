<?php 
if(!isset($loginHandler)){
    header("Location: index.php");
    die("An Error occurred!");
}
?>

<div id="login-box" class="absolute-container">
    <h2>Anmelden</h2>
    <p>Melden Sie sich mit Ihrem webENM-Account an.</p>
    <?php if(strlen($loginHandler->getError()) > 0){ ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $loginHandler->getError(); ?>
        </div>
    <?php } ?>
    <form method="POST" action="?page=login">
        <input class="form-control" type="text" name="username" placeholder="Benutzername">
        <input class="form-control" type="password" name="password" placeholder="Passwort">
        <input class="btn btn-primary" type="submit" value="Anmelden">
    </form>
</div>
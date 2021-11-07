<?php 
if(!isset($loginHandler)){
    header("Location: index.php");
    die("An Error occurred!");
}
?>

<div id="login-box" class="absolute-container">
    <div class="logo">
        <img src="img/webenm-logo-color.svg">
        <h2>webENM</h2>
    </div>
    <div class="form">
        <h2>Anmelden</h2>
        <p><?php echo getConstant("LOGIN_PROMPT", "Melden Sie sich mit Ihrem webENM-Account an."); ?></p>
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
</div>

<div class="footer">
    <div>
        <a href="doc/webENM Benutzerdokumentation.pdf" target="_blank">Dokumentation</a>
    </div>
    <div>
        webENM
    </div><div>
        &copy; Sarius121
    </div>
</div>
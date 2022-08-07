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
        <h2>Ungesicherte Änderungen wiederherstellen?</h2>
        <p>
            Sie haben sich das letzte Mal nicht ordentlich abgemeldet und deswegen gibt es Änderungen, die möglicherweise nicht gespeichert wurden.
        </p>
        <p><?php echo getConstant("UNSAVED_CHANGES_EXTRA_HINT", "Wenn Sie die Änderungen wiederherstellen, \\
                        könnten außerhalb dieser App durchgeführte Änderungen verloren gehen."); ?></p>
        <p>
            Möchten Sie die Änderungen wiederherstellen?
        </p>
        <?php if(strlen($loginHandler->getError()) > 0){ ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $loginHandler->getError(); ?>
            </div>
        <?php } ?>
        <form method="POST" action="?page=login">
            <div class="input-group">
                <input class="btn btn-primary" type="submit" name="yes" value="Ja">
                <input class="btn btn-secondary" type="submit" name="no" value="Nein">
            </div>
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
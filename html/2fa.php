<?php

use Finobe\Users\TwoFactor;
use Finobe\Web\WebContextManager;

if (TwoFactor::IsSession2FAUnlocked()){
    WebContextManager::Redirect("/");
}

if(isset($_POST['submit_2fa'])) {
    if (TwoFactor::AttemptSession2FAUnlock($_POST['2fa_code'])) {
        WebContextManager::Redirect("/");
    }
}

if(isset($_POST['logout'])) {
    $user->Logout();
    WebContextManager::Redirect("/");
}

$body = <<<EOT
<form action="" method="post">
    <div class="container" align="center" style="display: flex;justify-content: center;align-items: center;text-align: center;min-height: 100vh;">
        <div class="card" style="max-width:38rem;">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm text-center">
                        <h4>2-Step Verification</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm text-center">
                        <h1 style="font-size:14rem;"><i class="fas fa-unlock red-a-nounder"></i></h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm text-center">
                        <div class="input-group">
                            <input class="form-control" name="2fa_code" placeholder="Code" autocomplete="off">
                            <div class="input-group-append">
                                <button name="submit_2fa" class="btn btn-danger">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm text-center mt-3">
                  <button name="logout" class="btn btn-danger">Logout</button>
                </div>
            </div>
        </div>
    </div>
</form>
EOT;

pageHandler();
$ph->pageTitle("2FA");
$ph->footer = "";
$ph->navbar = "";
$ph->body = $body;
$ph->output();

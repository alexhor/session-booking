<?php

namespace App\Authenticators;

use CodeIgniter\Shield\Authentication\Authenticators\Session as ShieldSession;

class Session extends ShieldSession
{
    public function issueRememberMeToken(): void
    {
        if (setting('Auth.sessionConfig')['allowRemembering']) {
            $this->rememberUser(auth()->user());
        } elseif ($this->getRememberMeToken() !== null) {
            $this->removeRememberCookie();

            // @TODO delete the token record.
        }

        // We'll give a 20% chance to need to do a purge since we
        // don't need to purge THAT often, it's just a maintenance issue.
        // to keep the table from getting out of control.
        if (random_int(1, 100) <= 20) {
            $this->rememberModel->purgeOldRememberTokens();
        }
    }
}

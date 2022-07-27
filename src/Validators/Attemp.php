<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;

class Attemp
{
    public static function check(RequestInterface $request)
    {
        $return = true;
        $attemptModel = new \Daycry\RestServer\Models\AttemptModel();
        $attempt = $attemptModel->where('ip_address', $request->getIPAddress())->first();

        if ($attempt && $attempt->attempts >= config('RestServer')->restMaxAttempts) {
            if ($attempt->hour_started <= (time() - config('RestServer')->restTimeBlocked)) {
                $attemptModel->delete($attempt->id, true);
            } else {
                $return = date('Y-m-d H:i:s', $attempt->hour_started + config('RestServer')->restTimeBlocked);
            }
        }

        return $return;
    }
}

<?php

namespace App\Filters;

use App\Libraries\MobileApiJWT;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Mobil API Kimlik Doğrulama Filtresi
 * ------------------------------------------------------------------
 * `/api/mobile/*` uçlarını Bearer token ile korur.
 *
 * Yönetim panelinin `adminauth` filtresi bu iş için uygun değildir:
 * oturum yoksa giriş sayfasına yönlendirir ve mobil istemci HTML alır.
 * Bu filtre bunun yerine JSON gövdeli 401 döner.
 *
 * Token almak için:
 *   POST /api/mobile/authenticate  (username, password)
 */
class MobileApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = NULL)
    {
        $jwt = new MobileApiJWT();
        [$gecerli, $mesaj] = $jwt->verifyRequest();

        if ($gecerli === TRUE) {
            return;
        }

        return service('response')
            ->setStatusCode(401)
            ->setContentType('application/json')
            ->setBody(json_encode([
                'code'   => 401,
                'detail' => $mesaj ?: lang('MobileApi.error.accessDenied'),
            ], JSON_UNESCAPED_UNICODE));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = NULL)
    {
        // Islem gerekmiyor
    }
}

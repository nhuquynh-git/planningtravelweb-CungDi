<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class BaseApiController extends BaseController
{
    protected $helpers = ['cookie'];
    protected function success(
        $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ]);
    }

    protected function error(
        string $message,
        int $statusCode = 400,
        $errors = null
    ): ResponseInterface {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($response);
    }

    protected function validateApiData(
        array $data,
        array $rules,
        array $messages = []
    ): ?ResponseInterface {

        $validation = service('validation');

        $validation->setRules($rules, $messages);

        if (!$validation->run($data)) {
            return $this->error(
                'Dữ liệu không hợp lệ',
                422,
                $validation->getErrors()
            );
        }

        return null;
    }
}
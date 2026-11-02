<?php

use App\Controllers\Api\BaseApiController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

final class BaseApiControllerTestHarness extends BaseApiController
{
    public function successResponse(): ResponseInterface
    {
        return $this->success(['id' => 7], 'Created', 201);
    }

    public function validationErrorResponse(): ?ResponseInterface
    {
        return $this->validateApiData([], [
            'email' => 'required|valid_email',
        ]);
    }

    public function serverErrorResponse(): ResponseInterface
    {
        return $this->error('Request failed', 500);
    }
}

/**
 * @internal
 */
final class ResponseEnvelopeTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testSuccessResponseUsesTheStandardEnvelope(): void
    {
        $result = $this->controller(BaseApiControllerTestHarness::class)
            ->execute('successResponse');

        $result->assertStatus(201);
        $this->assertStringContainsString(
            'application/json',
            $result->response()->getHeaderLine('Content-Type')
        );
        $this->assertSame(
            [
                'status'  => 'success',
                'message' => 'Created',
                'data'    => ['id' => 7],
            ],
            json_decode($result->response()->getBody(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function testValidationErrorsUse422AndTheFieldErrorsEnvelope(): void
    {
        $result = $this->controller(BaseApiControllerTestHarness::class)
            ->execute('validationErrorResponse');

        $result->assertStatus(422);
        $payload = json_decode(
            $result->response()->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame('error', $payload['status']);
        $this->assertSame('Dữ liệu không hợp lệ', $payload['message']);
        $this->assertArrayHasKey('email', $payload['errors']);
        $this->assertCount(3, $payload);
    }

    public function testServerErrorsDoNotIncludeInternalDetails(): void
    {
        $result = $this->controller(BaseApiControllerTestHarness::class)
            ->execute('serverErrorResponse');

        $result->assertStatus(500);
        $payload = json_decode(
            $result->response()->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'Request failed',
            ],
            $payload
        );
        $this->assertStringNotContainsString('trace', strtolower($result->response()->getBody()));
        $this->assertStringNotContainsString('secret', strtolower($result->response()->getBody()));
    }
}

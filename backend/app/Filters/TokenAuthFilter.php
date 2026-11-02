<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\AuthService;
use CodeIgniter\Shield\Entities\User;

/**
 * Token Authentication Filter
 * 
 * Validates access token from:
 * 1. Authorization header (Bearer token)
 * 2. HttpOnly cookie (access_token)
 * 
 * Sets the authenticated user in the auth service.
 */
class TokenAuthFilter implements FilterInterface
{
    /**
     * Routes that don't require authentication
     */
    protected array $publicRoutes = [
        'api/auth/register',
        'api/auth/login',
        'api/health',
    ];

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface
     */
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|null
    {
        // Check if route is public
        $uri = $request->getUri()->getPath();
        
        foreach ($this->publicRoutes as $route) {
            if (str_contains($uri, $route)) {
                return null;
            }
        }

        $token = null;

        // 1. Try to get token from Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        // 2. If no header token, try to get from cookie
        if ($token === null) {
            $token = $request->getCookie('access_token');
        }

        // 3. If still no token, check if it's an options request (CORS preflight)
        if ($token === null && $request->getMethod() === 'options') {
            return null;
        }

        // No token provided
        if ($token === null) {
            return $this->unauthorizedResponse('Vui lòng đăng nhập để tiếp tục');
        }

        // Validate token
        $authService = new AuthService();
        $user = $authService->validateToken($token);

        if ($user === null) {
            return $this->unauthorizedResponse('Token không hợp lệ hoặc đã hết hạn');
        }

        // Set the user in session/auth
        $this->loginUser($user);

        return null;
    }

    /**
     * Allows after filter to set headers, etc.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }

    /**
     * Login user to the authenticator
     */
    protected function loginUser(User $user): void
    {
        // Get the tokens authenticator
        $auth = auth('tokens');
        
        // Set the user directly in the authenticator
        $auth->setUser($user);
    }

    /**
     * Return unauthorized JSON response
     */
    protected function unauthorizedResponse(string $message): ResponseInterface
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
            ]);
    }
}
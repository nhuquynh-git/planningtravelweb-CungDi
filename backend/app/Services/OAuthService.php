<?php

namespace App\Services;

use CodeIgniter\Shield\Entities\User;

/**
 * OAuth Service - Google & Facebook Login
 * 
 * Handles OAuth authentication flow:
 * 1. Generate OAuth authorization URL with CSRF state protection
 * 2. Exchange authorization code for access token with provider
 * 3. Verify user info with provider
 * 4. Create or login user in our system using CodeIgniter Shield
 */
class OAuthService
{
    protected $userProvider;
    protected $identityProvider;
    
    // Google OAuth Config
    protected string $googleClientId;
    protected string $googleClientSecret;
    protected string $googleRedirectUri;
    
    // Facebook OAuth Config  
    protected string $facebookAppId;
    protected string $facebookAppSecret;
    protected string $facebookRedirectUri;
    
    public function __construct()
    {
        $this->userProvider = model(\CodeIgniter\Shield\Models\UserModel::class);
        $this->identityProvider = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
        
        // Load OAuth config from environment with flexible key casing
        $this->googleClientId = $this->getEnvVar(['GOOGLE_CLIENT_ID', 'google_client_id'])
            ?? throw new \RuntimeException('Thiếu biến môi trường GOOGLE_CLIENT_ID trong .env');

        $this->googleClientSecret = $this->getEnvVar(['GOOGLE_CLIENT_SECRET', 'google_client_secret', 'GOOGLE_CLIENT_secret'])
            ?? throw new \RuntimeException('Thiếu biến môi trường GOOGLE_CLIENT_SECRET trong .env');

        $this->googleRedirectUri = $this->getEnvVar(['GOOGLE_REDIRECT_URI', 'google_redirect_uri'])
            ?: base_url('api/auth/oauth/google/callback');
        
        $this->facebookAppId = $this->getEnvVar(['FACEBOOK_APP_ID', 'facebook_app_id', 'FACEBOOK_App_ID'])
            ?? throw new \RuntimeException('Thiếu biến môi trường FACEBOOK_APP_ID trong .env');

        $this->facebookAppSecret = $this->getEnvVar(['FACEBOOK_APP_SECRET', 'facebook_app_secret', 'FACEBOOK_App_secret'])
            ?? throw new \RuntimeException('Thiếu biến môi trường FACEBOOK_APP_SECRET trong .env');

        $this->facebookRedirectUri = $this->getEnvVar(['FACEBOOK_REDIRECT_URI', 'facebook_redirect_uri'])
            ?: base_url('api/auth/oauth/facebook/callback');
    }

    /**
     * Helper to read environment variables with multiple possible casings
     */
    protected function getEnvVar(array $keys): ?string
    {
        foreach ($keys as $key) {
            $val = env($key);
            if ($val !== null && $val !== '' && $val !== false) {
                return (string) $val;
            }
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return (string) $_ENV[$key];
            }
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return (string) $_SERVER[$key];
            }
            $val = getenv($key);
            if ($val !== false && $val !== '') {
                return (string) $val;
            }
        }
        return null;
    }
    
    /**
     * Get Google OAuth URL for frontend to redirect to
     */
    public function getGoogleAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        session()->set('oauth_google_state', $state);
        
        $params = http_build_query([
            'client_id'     => $this->googleClientId,
            'redirect_uri'  => $this->googleRedirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'offline',
            'prompt'        => 'select_account',
            'state'         => $state,
        ]);
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }
    
    /**
     * Get Facebook OAuth URL for frontend to redirect to
     */
    public function getFacebookAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        session()->set('oauth_facebook_state', $state);
        
        $params = http_build_query([
            'client_id'     => $this->facebookAppId,
            'redirect_uri'  => $this->facebookRedirectUri,
            'state'         => $state,
            'scope'         => 'email,public_profile',
            'response_type' => 'code',
        ]);
        
        return 'https://www.facebook.com/v18.0/dialog/oauth?' . $params;
    }

    /**
     * Exchange authorization code with Google and retrieve user info
     */
    public function handleGoogleCallbackCode(string $code): ?array
    {
        try {
            $postData = http_build_query([
                'code'          => $code,
                'client_id'     => $this->googleClientId,
                'client_secret' => $this->googleClientSecret,
                'redirect_uri'  => $this->googleRedirectUri,
                'grant_type'    => 'authorization_code',
            ]);

            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                                 "Content-Length: " . strlen($postData) . "\r\n",
                    'content' => $postData,
                    'timeout' => 15,
                ],
            ]);

            $response = @file_get_contents('https://oauth2.googleapis.com/token', false, $context);
            if (!$response) {
                log_message('error', 'Google token exchange returned empty response');
                return null;
            }

            $tokenData = json_decode($response, true);
            if (!isset($tokenData['access_token'])) {
                log_message('error', 'Google token exchange error: ' . json_encode($tokenData));
                return null;
            }

            return $this->verifyGoogleToken($tokenData['access_token']);
        } catch (\Throwable $e) {
            log_message('error', 'handleGoogleCallbackCode error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Exchange authorization code with Facebook and retrieve user info
     */
    public function handleFacebookCallbackCode(string $code): ?array
    {
        try {
            $params = http_build_query([
                'client_id'     => $this->facebookAppId,
                'client_secret' => $this->facebookAppSecret,
                'redirect_uri'  => $this->facebookRedirectUri,
                'code'          => $code,
            ]);

            $context = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 15,
                ],
            ]);

            $url = 'https://graph.facebook.com/v18.0/oauth/access_token?' . $params;
            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                log_message('error', 'Facebook token exchange returned empty response');
                return null;
            }

            $tokenData = json_decode($response, true);
            if (!isset($tokenData['access_token'])) {
                log_message('error', 'Facebook token exchange error: ' . json_encode($tokenData));
                return null;
            }

            return $this->verifyFacebookToken($tokenData['access_token']);
        } catch (\Throwable $e) {
            log_message('error', 'handleFacebookCallbackCode error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify Google token and get user info
     */
    public function verifyGoogleToken(string $accessToken): ?array
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'header'  => "Authorization: Bearer $accessToken\r\n",
                    'timeout' => 15,
                ],
            ]);

            $response = @file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false, $context);
            if (!$response) {
                log_message('error', 'verifyGoogleToken failed to fetch userinfo');
                return null;
            }
            
            $userInfo = json_decode($response, true);
            if (!isset($userInfo['email']) || empty($userInfo['sub'])) {
                log_message('error', 'Google userinfo missing email or sub: ' . json_encode($userInfo));
                return null;
            }
            
            return [
                'provider'     => 'google',
                'provider_id'  => (string) $userInfo['sub'],
                'email'        => strtolower(trim($userInfo['email'])),
                'name'         => $userInfo['name'] ?? '',
                'avatar'       => $userInfo['picture'] ?? '',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Google OAuth error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify Facebook token and get user info
     */
    public function verifyFacebookToken(string $accessToken): ?array
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15,
                ],
            ]);

            $url = sprintf(
                'https://graph.facebook.com/me?fields=id,email,name,picture.width(200).height(200)&access_token=%s',
                urlencode($accessToken)
            );
            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                log_message('error', 'verifyFacebookToken failed to fetch userinfo');
                return null;
            }
            
            $userInfo = json_decode($response, true);
            if (!isset($userInfo['id'])) {
                log_message('error', 'Facebook userinfo missing id: ' . json_encode($userInfo));
                return null;
            }
            
            $email = isset($userInfo['email']) && !empty($userInfo['email'])
                ? strtolower(trim($userInfo['email']))
                : ($userInfo['id'] . '@facebook.local');

            $avatar = $userInfo['picture']['data']['url'] ?? '';

            return [
                'provider'     => 'facebook',
                'provider_id'  => (string) $userInfo['id'],
                'email'        => $email,
                'name'         => $userInfo['name'] ?? '',
                'avatar'       => $avatar,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Facebook OAuth error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create or update user from OAuth provider
     */
    public function handleOAuthUser(array $providerData): array
    {
        // 1. Check if user already exists via OAuth identity
        $existingIdentity = $this->identityProvider
            ->where('type', 'oauth_' . $providerData['provider'])
            ->where('secret', $providerData['provider_id'])
            ->first();
        
        if ($existingIdentity !== null) {
            $user = $this->userProvider->findById($existingIdentity->user_id);
            if ($user === null) {
                throw new \RuntimeException('Người dùng không tồn tại');
            }
        } else {
            // 2. Check if email already exists in system (email_password identity)
            $existingEmail = $this->identityProvider
                ->where('type', 'email_password')
                ->where('secret', $providerData['email'])
                ->first();
            
            if ($existingEmail !== null) {
                // Link OAuth identity to existing account
                $user = $this->userProvider->findById($existingEmail->user_id);
                if ($user === null) {
                    throw new \RuntimeException('Người dùng không tồn tại');
                }
                
                $this->identityProvider->insert([
                    'user_id' => $user->id,
                    'type'    => 'oauth_' . $providerData['provider'],
                    'secret'  => $providerData['provider_id'],
                    'name'    => ucfirst($providerData['provider']) . ' OAuth',
                ]);
            } else {
                // 3. Create brand-new user with random password
                $cleanName = !empty($providerData['name']) 
                    ? preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(str_replace(' ', '_', $providerData['name'])))
                    : explode('@', $providerData['email'])[0];

                if (empty($cleanName)) {
                    $cleanName = 'user';
                }

                $username = $cleanName . '_' . substr(bin2hex(random_bytes(4)), 0, 6);

                $user = new User([
                    'username' => $username,
                    'email'    => $providerData['email'],
                    'password' => bin2hex(random_bytes(16)),
                    'active'   => 1,
                ]);
                
                $this->userProvider->save($user);
                
                $userId = $this->userProvider->getInsertID();
                $user = $this->userProvider->findById($userId);
                
                if ($user === null) {
                    throw new \RuntimeException('Không thể khởi tạo tài khoản');
                }

                // Add to default group
                $user->addGroup('user');
                
                // Add OAuth identity
                $this->identityProvider->insert([
                    'user_id' => $user->id,
                    'type'    => 'oauth_' . $providerData['provider'],
                    'secret'  => $providerData['provider_id'],
                    'name'    => ucfirst($providerData['provider']) . ' OAuth',
                ]);
            }
        }
        
        // Update avatar if provided
        if (!empty($providerData['avatar']) && isset($user->id)) {
            try {
                $profileModel = new \App\Models\UserProfileModel();
                $existingProfile = $profileModel->find($user->id);
                
                if ($existingProfile === null) {
                    $profileModel->insert([
                        'user_id' => $user->id,
                        'avatar'  => $providerData['avatar'],
                    ]);
                } elseif (empty($existingProfile['avatar'])) {
                    $profileModel->update($user->id, ['avatar' => $providerData['avatar']]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Update avatar error: ' . $e->getMessage());
            }
        }
        
        // Generate Shield access token
        $token = $this->generateToken($user);
        
        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }
    
    /**
     * Generate access token for user
     */
    protected function generateToken(User $user): string
    {
        $accessToken = $user->generateAccessToken('oauth-token');

        $this->identityProvider->update($accessToken->id, [
            'expires' => date('Y-m-d H:i:s', time() + 7 * 86400),
        ]);

        return $accessToken->raw_token;
    }
    
    /**
     * Format user data for response
     */
    protected function formatUser(User $user): array
    {
        $groups = $user->getGroups();
        $permissions = $user->getPermissions();
        
        return [
            'id'          => $user->id,
            'email'       => $user->email,
            'username'    => $user->username ?? '',
            'groups'      => $groups,
            'permissions' => array_keys($permissions),
            'created_at'  => $user->created_at?->format('Y-m-d H:i:s'),
            'is_active'   => $user->isActivated(),
        ];
    }
}

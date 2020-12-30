<?php


namespace SsoBundle\Services\Auth;


use DateInterval;
use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use SsoBundle\Infrastructure\Exceptions\ExternalServiceException;
use SsoBundle\Infrastructure\Exceptions\InternalException;
use SsoBundle\Services\Auth\Dto\SsoUserDTO;
use SsoBundle\Services\Auth\Dto\TokenDTO;
use Throwable;

class SsoAuthService implements SsoAuthServiceInterface
{
    protected $ssoHost;

    protected $clientId;

    protected $secretId;

    /** @var string takes precedence over $redirect */
    protected $redirectUri;

    protected $scope = 'openid email profile permissions offline_access';

    public function __construct($ssoHost, $clientId, $secretId, $redirectUri = null)
    {
        $this->ssoHost = $ssoHost;
        $this->clientId = $clientId;
        $this->secretId = $secretId;
        $this->redirectUri = $redirectUri;
    }

    public function getAuthLink($redirect): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri ?? $redirect,
            'scope' => $this->scope,
            'state' => md5(random_bytes(32))
        ]);
        return "{$this->ssoHost}/oauth2/authorize?{$query}";
    }

    /**
     * @param $code
     * @param $redirect
     * @return TokenDTO|null
     * @throws InternalException
     * @throws ExternalServiceException
     */
    public function auth($code, $redirect): TokenDTO
    {
        try {
            $response = (new Client)->request('POST', "{$this->ssoHost}/oauth2/token", ['form_params' => [
                'code' => $code,
                'client_id' => $this->clientId,
                'grant_type' => "authorization_code",
                'client_secret' => $this->secretId,
                'redirect_uri' => $this->redirectUri ?? $redirect
            ]]);
        } catch (ConnectException $exception) {
            throw new ExternalServiceException('Sso', "Service {$this->ssoHost} unavailable");
        } catch (Throwable $exception) {
            throw new InternalException($exception->getMessage());
        }

        $content = json_decode($response->getBody()->getContents(), true);
        if (!isset($content['token_type']) || !isset($content['access_token'])) {
            throw new InternalException("Service Sso send incorrect token_type or access_token");
        }

        $tokenLive = (int)($content['expires_in'] ?? 3600);
        return new TokenDTO(
            $content['access_token'],
            $content['token_type'],
            (new DateTime())->add(new DateInterval("PT{$tokenLive}S"))
        );
    }

    public function getUserData(TokenDTO $token): SsoUserDTO
    {
        try {
            $response = (new Client)->request('GET', "{$this->ssoHost}/users/me", [
                'headers' => [
                    'Authorization' => "{$token->getType()} {$token->getToken()}"
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if (!($id = ($data['id'] ?? null))) {
                throw new InternalException("Service Sso not send user id");
            }

            $userData = new SsoUserDTO($id);

            $getDateTime = function ($dateString = '') {
                $date = DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $dateString);
                return $date instanceof DateTimeInterface ? $date : null;
            };
            $userData->setUsername($data['username'])
                ->setFirstName($data['firstName'] ?? '')
                ->setLastName($data['lastName'] ?? '')
                ->setEmail($data['email'] ?? '')
                ->setIsSSOAdmin($data['isSSOAdmin'] ?? false)
                ->setFromLDAP($data['fromLDAP'] ?? false)
                ->setCreatedAt($getDateTime($data['createdAt'] ?? null))
                ->setUpdatedAt($getDateTime($data['updatedAt'] ?? null))
                ->setBannedAt($getDateTime($data['bannedAt'] ?? null))
                ->setRoles($data['roles'] ?? []);

            return $userData;
        } catch (ConnectException $exception) {
            throw new ExternalServiceException('Sso', "Service {$this->ssoHost} unavailable");
        } catch (Throwable $exception) {
            throw new InternalException($exception->getMessage());
        }
    }

}
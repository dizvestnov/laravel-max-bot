<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Api;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Dizvestnov\LaravelMaxBot\Exceptions\ApiException;
use Dizvestnov\LaravelMaxBot\Exceptions\RateLimitException;
use Dizvestnov\LaravelMaxBot\Exceptions\UnauthorizedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class MaxBotClient implements MaxBotClientInterface
{
    private Client $http;
    private string $token;
    private array $httpConfig;

    public function __construct(string $token, array $httpConfig = [])
    {
        $this->token      = $token;
        $this->httpConfig = $httpConfig;

        $this->http = new Client([
            'base_uri' => rtrim($httpConfig['base_uri'] ?? 'https://platform-api.max.ru', '/') . '/',
            'timeout'  => $httpConfig['timeout'] ?? 30,
        ]);
    }

    public function getBotInfo(): array
    {
        return $this->request('GET', 'me');
    }

    public function editBotInfo(array $data): array
    {
        return $this->request('PATCH', 'me', ['json' => $data]);
    }

    public function sendMessage(array $params): array
    {
        return $this->request('POST', 'messages', ['json' => $params]);
    }

    public function editMessage(array $params): array
    {
        $messageId = $params['message_id'] ?? '';
        unset($params['message_id']);

        return $this->request('PUT', 'messages', [
            'query' => ['message_id' => $messageId],
            'json'  => $params,
        ]);
    }

    public function deleteMessage(string $messageId): array
    {
        return $this->request('DELETE', 'messages', ['query' => ['message_id' => $messageId]]);
    }

    public function getMessage(string $messageId): array
    {
        return $this->request('GET', 'messages/' . $messageId);
    }

    public function getMessages(array $params = []): array
    {
        return $this->request('GET', 'messages', ['query' => $params]);
    }

    public function answerOnCallback(array $params): array
    {
        return $this->request('POST', 'answers', ['json' => $params]);
    }

    public function getChats(array $params = []): array
    {
        return $this->request('GET', 'chats', ['query' => $params]);
    }

    public function getChat(int $chatId): array
    {
        return $this->request('GET', 'chats/' . $chatId);
    }

    public function editChat(int $chatId, array $data): array
    {
        return $this->request('PATCH', 'chats/' . $chatId, ['json' => $data]);
    }

    public function deleteChat(int $chatId): array
    {
        return $this->request('DELETE', 'chats/' . $chatId);
    }

    public function sendAction(int $chatId, string $action): array
    {
        return $this->request('POST', 'chats/' . $chatId . '/actions', ['json' => ['action' => $action]]);
    }

    public function getPinnedMessage(int $chatId): array
    {
        return $this->request('GET', 'chats/' . $chatId . '/pin');
    }

    public function pinMessage(int $chatId, array $params): array
    {
        return $this->request('PUT', 'chats/' . $chatId . '/pin', ['json' => $params]);
    }

    public function unpinMessage(int $chatId): array
    {
        return $this->request('DELETE', 'chats/' . $chatId . '/pin');
    }

    public function getMembership(int $chatId): array
    {
        return $this->request('GET', 'chats/' . $chatId . '/members/me');
    }

    public function leaveChat(int $chatId): array
    {
        return $this->request('DELETE', 'chats/' . $chatId . '/members/me');
    }

    public function getAdmins(int $chatId): array
    {
        return $this->request('GET', 'chats/' . $chatId . '/members/admins');
    }

    public function addAdmins(int $chatId, array $userIds): array
    {
        return $this->request('POST', 'chats/' . $chatId . '/members/admins', [
            'json' => ['user_ids' => $userIds],
        ]);
    }

    public function deleteAdmin(int $chatId, int $userId): array
    {
        return $this->request('DELETE', 'chats/' . $chatId . '/members/admins/' . $userId);
    }

    public function getMembers(int $chatId, array $params = []): array
    {
        return $this->request('GET', 'chats/' . $chatId . '/members', ['query' => $params]);
    }

    public function addMembers(int $chatId, array $userIds): array
    {
        return $this->request('POST', 'chats/' . $chatId . '/members', [
            'json' => ['user_ids' => $userIds],
        ]);
    }

    public function deleteMember(int $chatId, int $userId): array
    {
        return $this->request('DELETE', 'chats/' . $chatId . '/members', ['query' => ['user_id' => $userId]]);
    }

    public function getSubscriptions(): array
    {
        return $this->request('GET', 'subscriptions');
    }

    public function subscribe(array $params): array
    {
        return $this->request('POST', 'subscriptions', ['json' => $params]);
    }

    public function unsubscribe(): array
    {
        return $this->request('DELETE', 'subscriptions');
    }

    public function getUpdates(array $params = []): array
    {
        return $this->request('GET', 'updates', ['query' => $params]);
    }

    public function getUploadUrl(string $type): array
    {
        return $this->request('POST', 'uploads', ['json' => ['type' => $type]]);
    }

    public function getVideoDetails(string $videoToken): array
    {
        return $this->request('GET', 'videos/' . $videoToken);
    }

    /**
     * @param string $method HTTP method
     * @param string $uri    URI relative to base
     * @param array  $options Guzzle options
     *
     * @return array
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        $retryConfig = $this->httpConfig['retry'] ?? ['times' => 3, 'sleep' => 100];
        $maxAttempts = (int) ($retryConfig['times'] ?? 3);
        $baseSleep   = (int) ($retryConfig['sleep'] ?? 100);

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            ['Authorization' => $this->token]
        );

        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $response = $this->http->request($method, $uri, $options);
                $body     = (string) $response->getBody();

                return (array) json_decode($body, true);
            } catch (ClientException $e) {
                $response     = $e->getResponse();
                $statusCode   = $response->getStatusCode();
                $body         = (string) $response->getBody();
                $responseBody = (array) json_decode($body, true) ?: [];

                if ($statusCode === 401) {
                    throw new UnauthorizedException($responseBody);
                }

                if ($statusCode === 429) {
                    $retryAfter = null;
                    $header     = $response->getHeaderLine('Retry-After');

                    if ($header !== '') {
                        $retryAfter = (int) $header;
                    }

                    if ($attempt < $maxAttempts) {
                        $sleepMs = $baseSleep * (2 ** ($attempt - 1));
                        usleep($sleepMs * 1000);
                        continue;
                    }

                    throw new RateLimitException($responseBody, $retryAfter);
                }

                throw new ApiException($statusCode, $responseBody);
            } catch (ServerException $e) {
                $response   = $e->getResponse();
                $statusCode = $response->getStatusCode();

                if ($statusCode === 503 && $attempt < $maxAttempts) {
                    $sleepMs = $baseSleep * (2 ** ($attempt - 1));
                    usleep($sleepMs * 1000);
                    continue;
                }

                $body         = (string) $response->getBody();
                $responseBody = (array) json_decode($body, true) ?: [];
                throw new ApiException($statusCode, $responseBody);
            }
        }
    }
}

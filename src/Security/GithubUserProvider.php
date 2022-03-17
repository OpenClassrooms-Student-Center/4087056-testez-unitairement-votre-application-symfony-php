<?php

namespace App\Security;

use App\Entity\User;
use GuzzleHttp\Client;
use JetBrains\PhpStorm\Pure;
use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class GithubUserProvider implements UserProviderInterface
{
    private Client $client;
    private SerializerInterface $serializer;

    public function __construct(Client $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @throws GuzzleException
     */
    public function loadUserByUsername(string $username): User
    {
        $response = $this->client->get('https://api.github.com/user?access_token='.$username);
        $result = $response->getBody()->getContents();

        $userData = $this->serializer->deserialize($result, 'array', 'json');

        if (!$userData) {
            throw new LogicException('Did not managed to get your user info from Github.');
        }

        return new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException();
        }
        return $user;
    }

    public function supportsClass($class): bool
    {
        return 'App\Entity\User' === $class;
    }

    #[Pure]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new User('dDupont', 'dupont', 'ddupont@test.fr','avatarUrl','profilHtmlUrl');
    }
}
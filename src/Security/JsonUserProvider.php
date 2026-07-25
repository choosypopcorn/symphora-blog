<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JsonUserProvider implements UserProviderInterface
{
    private string $usersFile;

    public function __construct()
    {
        $this->usersFile = (require __DIR__ . '/../../config/storage.php')['users'];
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $users = $this->loadUsers();

        foreach ($users as $user) {
            if ($user['username'] === $identifier) {
                return new User($user['username'], $user['password']);
            }
        }

        throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    private function loadUsers(): array
    {
        if (!file_exists($this->usersFile)) {
            return [];
        }

        $content = file_get_contents($this->usersFile);
        $data = json_decode($content, true);

        return is_array($data) ? array_values($data) : [];
    }
}

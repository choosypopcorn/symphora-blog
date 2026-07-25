<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    private string $usersFile;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->usersFile = (require __DIR__ . '/../../config/storage.php')['users'];
        $this->filesystem = new Filesystem();
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $username = $request->getPayload()->get('username');
            $password = $request->getPayload()->get('password');
            $passwordConfirm = $request->getPayload()->get('password_confirm');

            // Validate input
            if (!$username || !$password) {
                $error = 'Username and password are required.';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Load existing users
                $users = [];
                if (file_exists($this->usersFile)) {
                    $users = json_decode(file_get_contents($this->usersFile), true) ?: [];
                }

                // Check if user already exists
                foreach ($users as $user) {
                    if ($user['username'] === $username) {
                        $error = 'Username already exists.';
                        break;
                    }
                }

                if (!$error) {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    // Create new user
                    $newUser = [
                        'username' => $username,
                        'password' => $hashedPassword,
                    ];

                    // Add to users array
                    $users[] = $newUser;

                    // Save to file
                    $this->filesystem->dumpFile($this->usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    $success = true;
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

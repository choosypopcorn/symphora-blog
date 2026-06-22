<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authUtils)
    {
        // Login logic using session-based auth
    }

    /**
     * @Route("/register", methods={"POST"}, name="app_register")
     */
    public function register(Request $request)
    {
        if (!$this->getUser()) { // Ensure not already logged in
            $userData = json_decode(file_get_contents(__DIR__ . '/../../../../config/storage.php')['users'], true) ?: [];
            
            // Simple password hashing (consider using Symfony's PasswordHasher for production)
            $hashedPassword = password_hash($request->get('password'), PASSWORD_BCRYPT);
            
            $newUser = [
                'username' => $request->get('username'),
                'password' => $hashedPassword
            ];
            
            array_push($userData, $newUser);
            file_put_contents(__DIR__ . '/../../../../config/storage.php', json_encode($userData));
        }
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        // Logout logic handled by Symfony's Security component
    }
}

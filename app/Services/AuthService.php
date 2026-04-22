<?php

declare(strict_types=1);

final class AuthService
{
    /**
     * @return array<string,mixed>
     */
    public function loginAsRole(string $role): array
    {
        $allowedRoles = ['guest', 'user', 'associate', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException('Rol inválido');
        }

        $user = [
            'id' => null,
            'name' => 'Invitado',
            'role' => $role,
        ];

        if ($role === 'admin') {
            $user['name'] = 'Luz Genovese';
        } elseif ($role === 'associate') {
            $user['name'] = 'Coordinador Red';
        } elseif ($role === 'user') {
            $user['name'] = 'Inscripto Foro';
        }

        return $user;
    }
}

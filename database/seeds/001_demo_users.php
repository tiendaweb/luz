<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;

    $roles = ['admin', 'associate', 'user'];
    $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
    $userStmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, role_id, password_hash, updated_at)
         VALUES (:full_name, :email, :role_id, :password_hash, CURRENT_TIMESTAMP)
         ON CONFLICT(email) DO UPDATE SET
            full_name = excluded.full_name,
            role_id = excluded.role_id,
            password_hash = excluded.password_hash,
            updated_at = CURRENT_TIMESTAMP'
    );

    $demoUsers = [
        [
            'full_name' => 'Administrador Demo',
            'email' => 'admin@psme.local',
            'role' => 'admin',
            'password' => 'Admin123*',
        ],
        [
            'full_name' => 'Asociado Demo',
            'email' => 'asociado@psme.local',
            'role' => 'associate',
            'password' => 'Asociado123*',
        ],
        [
            'full_name' => 'Usuario Demo',
            'email' => 'usuario@psme.local',
            'role' => 'user',
            'password' => 'Usuario123*',
        ],
    ];

    $roleIdBySlug = [];
    foreach ($roles as $roleSlug) {
        $roleStmt->execute(['slug' => $roleSlug]);
        $roleId = $roleStmt->fetchColumn();

        if ($roleId === false) {
            throw new RuntimeException(sprintf('No existe el rol requerido: %s', $roleSlug));
        }

        $roleIdBySlug[$roleSlug] = (int)$roleId;
    }

    foreach ($demoUsers as $user) {
        $userStmt->execute([
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role_id' => $roleIdBySlug[$user['role']],
            'password_hash' => password_hash($user['password'], $hashAlgo),
        ]);
    }
};

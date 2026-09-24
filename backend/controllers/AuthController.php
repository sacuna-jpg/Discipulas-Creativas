<?php
/**
 * AuthController
 * Gestión de registro, autenticación, cierre de sesión y perfil de usuarias.
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Sesion.php';

class AuthController {

    public function register(Request $request) {
        $nombre = trim($request->input('nombre', ''));
        $correo = trim($request->input('correo', ''));
        $password = $request->input('password', '');
        $distritoId = $request->input('distrito_id');
        $iglesiaId = $request->input('iglesia_id');

        // Validaciones
        if (empty($nombre)) {
            Response::unprocessable('El nombre completo es obligatorio.', 'NAME_REQUIRED');
        }

        // Determinar si es correo electrónico o número de celular
        $isEmail = filter_var($correo, FILTER_VALIDATE_EMAIL);
        $cleanPhone = preg_replace('/[^0-9+]/', '', $correo);
        $isPhone = (preg_match('/^\+?[0-9]{7,15}$/', $cleanPhone) === 1);

        if (!$isEmail && !$isPhone) {
            Response::unprocessable('Debes ingresar un correo electrónico o número de celular válido.', 'INVALID_IDENTIFIER');
        }

        // Si es teléfono guardar normalizado, si es correo en minúsculas
        $correo = $isEmail ? strtolower($correo) : $cleanPhone;

        if (strlen($password) < 6) {
            Response::unprocessable('La contraseña debe tener al menos 6 caracteres.', 'PASSWORD_TOO_SHORT');
        }

        // Verificar si el identificador ya está registrado
        $existente = Usuario::findByEmail($correo);
        if ($existente) {
            Response::unprocessable('Este correo o número de celular ya se encuentra registrado. Por favor inicia sesión.', 'IDENTIFIER_ALREADY_EXISTS');
        }

        // Crear usuaria
        $distritoId = ($distritoId !== null && is_numeric($distritoId)) ? (int) $distritoId : null;
        $iglesiaId = ($iglesiaId !== null && is_numeric($iglesiaId)) ? (int) $iglesiaId : null;

        $userId = Usuario::create($nombre, $correo, $password, $distritoId, $iglesiaId);

        // Iniciar sesión automáticamente generando el token
        $token = Sesion::create($userId);
        $usuario = Usuario::findById($userId);

        Response::created([
            'token'   => $token,
            'usuario' => $usuario
        ], '¡Registro exitoso! Bienvenida a Discípulas Creativas.');
    }

    public function login(Request $request) {
        $identificador = trim($request->input('correo', ''));
        $password = $request->input('password', '');

        if (empty($identificador) || empty($password)) {
            Response::badRequest('Datos incorrectos, por favor revise.', 'CREDENTIALS_REQUIRED');
        }

        // Normalizar según si es email o teléfono
        if (strpos($identificador, '@') !== false) {
            $correo = strtolower($identificador);
        } else {
            $correo = preg_replace('/[^0-9+]/', '', $identificador);
        }

        $usuario = Usuario::findByEmail($correo);
        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            Response::unauthorized('Datos incorrectos, por favor revise.', 'INVALID_CREDENTIALS');
        }

        // Crear token en la tabla sesiones
        $token = Sesion::create((int) $usuario['id']);
        $perfil = Usuario::findById((int) $usuario['id']);

        Response::success([
            'token'   => $token,
            'usuario' => $perfil
        ], 'Inicio de sesión exitoso.');
    }

    public function logout(Request $request) {
        $token = $request->getBearerToken();
        if ($token) {
            Sesion::revoke($token);
        }
        Response::success(null, 'Sesión cerrada correctamente.');
    }

    public function me(Request $request) {
        // Obtenido directamente de AuthMiddleware
        Response::success($request->user, 'Perfil de usuaria obtenido correctamente.');
    }

    public function changePassword(Request $request) {
        $currentPassword = $request->input('password_actual', '');
        $newPassword = $request->input('password_nueva', '');

        if (empty($currentPassword) || empty($newPassword)) {
            Response::badRequest('Debes ingresar la contraseña actual y la nueva contraseña.', 'FIELDS_REQUIRED');
        }

        if (strlen($newPassword) < 6) {
            Response::unprocessable('La nueva contraseña debe tener al menos 6 caracteres.', 'PASSWORD_TOO_SHORT');
        }

        $userId = (int) $request->user['id'];
        $dbUser = Usuario::findByEmail($request->user['correo']);

        if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
            Response::unauthorized('La contraseña actual es incorrecta.', 'INVALID_CURRENT_PASSWORD');
        }

        Usuario::updatePassword($userId, $newPassword);
        Response::success(null, 'Tu contraseña ha sido actualizada con éxito.');
    }
}

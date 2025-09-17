<?php
namespace presupuestos\controller\Auth;

require_once __DIR__ . '../../../../config/app.php';

use presupuestos\model\UserModel;
use presupuestos\helpers\ValidationHelper;
use presupuestos\helpers\PasswordHelper;
use presupuestos\exceptions\ValidationException;
use presupuestos\helpers\MailerHelper;
use presupuestos\helpers\TokenHelper;
use presupuestos\model\TokenModel;

class AuthController {

    public function showLogin(){
        require __DIR__ . '/../../view/Auth/login.php';
    }

    public function login(array $credentials){

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {


            $email = trim($credentials['email']);
            $password = trim($credentials['password']);

            if($email === "" || $password === "") {
                echo json_encode([
                    'state' => 0,
                    'message' => "Todos los campos son obligatorios"
                ]);
                return;
            }

            $email = ValidationHelper::normalizeEmail($email);
            $userModel = new UserModel();
            $dataUser = $userModel->findByEmail($email);

            if(!$dataUser){
                echo json_encode([
                    'state' => 0,
                    'message' => "Correo no registrado"
                ]);
                return;
            }

            if($dataUser['es_verificado'] == 0){
                echo json_encode([
                    'state' => 0,
                    'message' => "El correo no se encuentra verificado"
                ]);
                return;
            }

            if(!password_verify($password, $dataUser['password'])){
                echo json_encode([
                    'state' => 0,
                    'message' => "Credenciales incorrectas"
                ]);
                return;
            }


            // Guardar sesión
            $_SESSION[APP_SESSION_NAME] = [
                'id'       => $dataUser['id'],
                'email'    => $dataUser['email'],
                'name'     => $dataUser['nombres'],
                'lastName' => $dataUser['apellidos'],
                'role'     => $dataUser['rol_id']
            ];

            echo json_encode([
                'state' => 1,
                'message' => "Login exitoso",
                'redirect' => APP_URL . "dashboard"
            ]);
            return;

        } catch (ValidationException $e) {
            echo json_encode([
                'state' => 0,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'state' => 0,
                'message' => "Error del sistema. Intenta más tarde."
            ]);
        }
    }

    public function register(array $data){
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {

            $names= trim($data['names'] ?? '');
            $lastNames= trim($data['lastNames'] ?? '');
            $idNumber= trim($data['idNumber'] ?? '');
            $email= trim($data['emailSena'] ?? '');
            $password = trim($data['password'] ?? '');
            $rePassword = trim($data['rePassword'] ?? '');

            if(!$names || !$lastNames || !$email|| !$password || !$rePassword){
                echo json_encode([
                    'state' => 0,
                    'message' => "Todos los campos son obligatorios"
                ]);
                return;
            }

            if($password !== $rePassword){
                echo json_encode([
                    'state' => 0,
                    'message' => "Las contraseñas no coinciden"
                ]);
                return;
            }

            $email = ValidationHelper::normalizeEmail($email);

            $userModel = new UserModel();
            if($userModel->findByEmail($email)){
                echo json_encode([
                    'state' => 0,
                    'message' => "El correo ya está registrado"
                ]);
                return;
            }


            $hashed = PasswordHelper::hashPassword($password);
            $result= $userModel->create([
                'names'=> $names,
                'lastNames'=> $lastNames,
                'idNumber'=> $idNumber,
                'email' => $email,
                'password' => $hashed,
            ]);

            if ($result['success']) {

                $mailer = new MailerHelper();
                $tokenHelper = new TokenHelper();
                $tokenModel = new TokenModel();

                $token = $tokenHelper::generateToken(32);
                $expiresAt = $tokenHelper::expiration(1);

                $user = $userModel->findByEmail($email);

                $tokenModel->create($user['id'], $token, 'verification', $expiresAt);


                $sent = $mailer->sendVerificationEmail([
                    'name' => $user['nombres'] ?? $Names,
                    'lastName' => $user['apellidos'] ?? $lastNames,
                    'email' => $user['email'] ?? $email
                ], $token);

                echo json_encode([
                    'state' => $sent === true ? 1 : 0,
                    'message' => $sent === true
                        ? "Registro exitoso. Verifica tu correo para activar tu cuenta."
                        : "Registro exitoso, pero hubo un error enviando el correo: $sent"
                ]);

            } else {
                echo json_encode([
                    'state' => 0,
                    'message' => "Error: " . $result['error']
                ]);
            }

                            
        } catch (\Exception $e){
            echo json_encode([
                'state' => 0,
                'message' => "Error del sistema. Intenta más tarde.".$e->getMessage()
            ]);
        }
    }

    public function recoveryPassword(array $data){
        $email = trim($data['email']);
        $email = ValidationHelper::normalizeEmail($email);

        $userModel = new UserModel();
        $dataUser = $userModel->findByEmail($email);
        echo json_encode([
            'state' => 1,
            'redirect' => APP_URL."recovery"
        ]);
        return;
    }

    public function showRecoveryPassword(){
        require __DIR__ . '/../../view/Auth/login.php';
    }

    public function verify() {
    $token = $_GET['token'] ?? null;
        if (!$token) {
            require __DIR__ . "/../../view/errors/invalid_token.php";
            exit;
        }

        $tokenModel = new TokenModel();
        $tokenData = $tokenModel->findByToken($token);

        if (!$tokenData) {
            require __DIR__ . "/../../view/errors/invalid_token.php";
            exit;
        }

        // Verificamos que el token no esté expirado
        if (strtotime($tokenData['expires_at']) < time()) {
            $tokenModel->deleteByToken($token);
            require __DIR__ . "/../../view/errors/invalid_token.php";
            exit;
        }

        // Si el token es válido: marcar usuario como verificado
        $userModel = new UserModel();
        $userModel->verifyAccount((int)$tokenData['user_id']);

        // eliminar token para no reutilizarlo
        $tokenModel->deleteByToken($token);

        // Redirigir al login con mensaje de éxito
        header("Location: /login?verified=1");
        exit;
    }

}

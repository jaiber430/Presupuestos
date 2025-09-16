<?php
namespace presupuestos\controller\Auth;

require_once __DIR__ . '../../../../config/app.php';

use presupuestos\model\UserModel;
use presupuestos\helpers\ValidationHelper;
use presupuestos\helpers\PasswordHelper;
use presupuestos\exceptions\ValidationException;

class AuthController {

    public function showLogin(){
        require __DIR__ . '/../../view/Auth/login.php';
    }

    public function login(array $credentials){

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {


            $email = trim($credentials['email'] ?? '');
            $password = trim($credentials['password'] ?? '');

            if($email === "" || $password === "") {
                echo json_encode([
                    'state' => 0,
                    'message' => "Todos los campos son obligatorios"
                ]);
                return;
            }

            $email = ValidationHelper::normalizeEmail($email);
            $userModel = new UserModel($email);
            $dataUser = $userModel->findByEmail();

            if(!$dataUser){
                echo json_encode([
                    'state' => 0,
                    'message' => "Correo no registrado"
                ]);
                return;
            }

            if($dataUser['is_verificate'] == 0){
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
                'name'     => $dataUser['name'],
                'lastName' => $dataUser['last_name'],
                'role'     => $dataUser['role_id']
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

            $name = trim($data['name'] ?? '');
            $lastName = trim($data['lastName'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $rePassword = trim($data['rePassword'] ?? '');

            if(!$name || !$lastName || !$email || !$password || !$rePassword){
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

            $userModel = new UserModel($email);
            if($userModel->findByEmail()){
                echo json_encode([
                    'state' => 0,
                    'message' => "El correo ya está registrado"
                ]);
                return;
            }


            $hashed = PasswordHelper::hashPassword($password);
            $userModel->create([
                'name' => $name,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $hashed,
                'role_id' => 2,
                'is_verificate' => 0
            ]);

            echo json_encode([
                'state' => 1,
                'message' => "Registro exitoso, revisa tu correo para verificar la cuenta"
            ]);

        } catch (\Exception $e){
            echo json_encode([
                'state' => 0,
                'message' => "Error del sistema. Intenta más tarde."
            ]);
        }
    }
}

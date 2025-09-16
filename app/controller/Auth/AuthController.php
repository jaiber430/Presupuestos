<?php
namespace presupuestos\controller\Auth;

require_once __DIR__ . '../../../../config/app.php';

use presupuestos\model\UserModel;
use presupuestos\helpers\ValidationHelper;
use presupuestos\helpers\PasswordHelper;
use presupuestos\exceptions\ValidationException;

class AuthController{
		
    public function showLogin(){
        require __DIR__ . '/../../view/Auth/login.php';
    }

    public function login(array $credentials){
		try {
			session_start();
			
			
			$email = $credentials['email'];
			$password= $credentials["password"];
			
			if($email=== "" || $password=== ""){
				$_SESSION["message"]= "Campo requerido";
				header("Location: ".APP_URL."?email=$email");
				exit;
			}
			
			$email = ValidationHelper::normalizeEmail($email);	
			$userModel= new UserModel($email);		
		
			$dataUser= $userModel->findByEmail();			
		
			if($dataUser){
				if($dataUser['is_verificate']== 0){					
					$_SESSION["message"]= "El correo no se encuentra verificado";
					header("Location: ".APP_URL."?email=$email");
					exit;
				}								
			}else{
				$_SESSION["message"]= "Correo no registrado";
				header("Location: ".APP_URL."?email=$email");
				exit;
			}
			
			if(! password_verify($password, $dataUser['password'])) {				
				$_SESSION["message"]= "Credenciales incorrectos";
				header("Location: ".APP_URL."?email=$email");
				exit;
			}			
			
			$_SESSION[APP_SESSION_NAME] = [
				'id'     => $dataUser['id'],       
				'email'  => $dataUser['email'],
				'name'   => $dataUser['name'],     
				'lastName'   => $dataUser['last_name'],     
				'role'   => $dataUser['role_id'] 
			];
			
			header("Location: ".APP_URL."dashboard");
			exit;
			
		} catch (ValidationException $e) {
			session_start();
			$_SESSION["error"] = $e->getMessage();
			header("Location: ".APP_URL);

		} catch (\Exception $e) { 
			session_start();
			$_SESSION["error"] = "Error del sistema. Intenta más tarde.";	
			header("Location: ".APP_URL);
			exit;
		}
	}
}

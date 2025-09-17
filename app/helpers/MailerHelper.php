<?php
namespace presupuestos\helpers;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MailerHelper {

    private Mailer $mailer;
    private Environment $twig;
    private string $from;

    public function __construct() {
        $transportType = $_ENV['MAILER_TRANSPORT'] ?? 'smtp';
        $user = $_ENV['MAILER_USER'] ?? '';
        $pass = $_ENV['MAILER_PASS'] ?? '';
        $host = $_ENV['MAILER_HOST'] ?? 'localhost';
        $port = $_ENV['MAILER_PORT'] ?? 587; // Cambiado a 587 (puerto común para TLS)
        $this->from = $user;

        // Construir DSN correcto para Symfony Mailer
        $dsn = sprintf(
            '%s://%s:%s@%s:%d',
            $transportType,
            urlencode($user),
            urlencode($pass),
            $host,
            $port
        );

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);

        $emailsPath = __DIR__ . '/../view/emails';
        if (!is_dir($emailsPath)) {
            mkdir($emailsPath, 0755, true);
        }

        $loader = new FilesystemLoader($emailsPath);
        $this->twig = new Environment($loader);
    }

    public function sendRecoveryEmail(array $user, string $token) {
        try {
            $body = $this->twig->render('recovery_email.html.twig', [
                'name' => $user['name'] ?? '',
                'token' => $token,
            ]);

            $email = (new Email())
                ->from($this->from)
                ->to($user['email'] ?? '')
                ->subject('Recuperación de contraseña')
                ->html($body);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo de recuperación: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendVerificationEmail(array $user, string $token) {
        try {
            $body = $this->twig->render('verification_email.html.twig', [
                'name' => $user['name'] ?? '',
                'token' => $token,
            ]);

            $email = (new Email())
                ->from($this->from)
                ->to($user['email'])
                ->subject('Verifica tu cuenta')
                ->html($body);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo de verificación: " . $e->getMessage());
            return $e->getMessage();
        }
    }
    
}
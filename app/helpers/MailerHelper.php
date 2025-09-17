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
        $transportType = getenv('MAILER_TRANSPORT');
        $user = getenv('MAILER_USER');
        $pass = getenv('MAILER_PASS');
        $host = getenv('MAILER_HOST');
        $port = getenv('MAILER_PORT');
        $encryption = getenv('MAILER_ENCRYPTION');
        $this->from = $user;

        // Construir DSN válido
        $dsn = sprintf(
            '%s://%s:%s@%s:%s?encryption=%s',
            $transportType,
            urlencode($user),
            urlencode($pass),
            $host,
            $port,
            $encryption
        );

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);

        // Twig
        $loader = new FilesystemLoader(__DIR__ . '/../view/emails');
        $this->twig = new Environment($loader);
    }

    public function sendRecoveryEmail(array $user, string $token): bool {
        $body = $this->twig->render('recovery_email.html.twig', [
            'name' => $user['name'],
            'token' => $token,
        ]);

        $email = (new Email())
            ->from($this->from)
            ->to($user['email'])
            ->subject('Recuperación de contraseña')
            ->html($body);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo: ".$e->getMessage());
            return false;
        }
    }

    public function sendVerificationEmail(array $user, string $token): bool {
        $body = $this->twig->render('verification_email.html.twig', [
            'name' => $user['name'],
            'token' => $token,
        ]);

        $email = (new Email())
            ->from($this->from)
            ->to($user['email'])
            ->subject('Verifica tu cuenta')
            ->html($body);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo de verificación: ".$e->getMessage());
            return false;
        }
    }
}

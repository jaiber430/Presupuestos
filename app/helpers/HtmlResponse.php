<?php
namespace presupuestos\helpers;

class HtmlResponse{
    
    public static function redirect(string $url, string $message = null, string $type = 'success'): void
    {
        if ($message) {
            $_SESSION['flash_message'] = [
                'type' => $type,
                'message' => $message
            ];
        }
        header("Location: $url");
        exit;
    }

    public static function show(string $message, string $type = 'info'): void
    {
        $class = match($type) {
            'success' => 'alert-success',
            'error'   => 'alert-danger',
            'warning' => 'alert-warning',
            default   => 'alert-info',
        };
        echo "<div class='alert $class'>$message</div>";
    }

    public static function view(string $view, array $data = [], string $message = null, string $type = 'info'): void
    {
        if ($message) {
            $_SESSION['flash_message'] = [
                'type' => $type,
                'message' => $message
            ];
        }

        extract($data);
        require $view;
        exit;
    }
}

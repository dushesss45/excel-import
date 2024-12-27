<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware для реализации базовой аутентификации (Basic Auth).
 *
 * Данный middleware проверяет переданные клиентом логин и пароль,
 * сравнивая их с заданными значениями в `.env` файле. В случае
 * несоответствия возвращается ответ с кодом 401 и заголовком
 * `WWW-Authenticate`.
 */
class BasicAuthMiddleware
{
    /**
     * Обрабатывает входящий запрос.
     *
     * @param Request $request Текущий HTTP-запрос.
     * @param Closure $next Замыкание для передачи управления следующему middleware.
     *
     * @return mixed Ответ HTTP или передача запроса следующему middleware.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Получаем логин/пароль, переданные клиентом через Basic Auth.
        $user = $request->getUser();
        $pass = $request->getPassword();

        // Валидные учетные данные из .env файла.
        $validUser = env('BASIC_AUTH_USER', 'admin'); // Логин по умолчанию: admin
        $validPass = env('BASIC_AUTH_PASS', 'secret'); // Пароль по умолчанию: secret

        // Проверяем соответствие учетных данных.
        if ($user !== $validUser || $pass !== $validPass) {
            // Если логин/пароль неверны, возвращаем ответ 401 Unauthorized.
            return response('Invalid credentials.', 401, [
                'WWW-Authenticate' => 'Basic realm="Laravel"', // Указывает клиенту запросить аутентификацию.
            ]);
        }

        // Передаём запрос следующему middleware.
        return $next($request);
    }
}

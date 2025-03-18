<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 20px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:rgb(0, 0, 0); padding: 20px; color: #DAA520; text-align: center;">
                            <h1>Smart4Finances</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <h2>Olá {{ $user->name }},</h2>
                            <p>Recebemos um pedido para redefinir a sua password.</p>
                            <p>Clique no botão abaixo para criar uma nova password e garantir a segurança da sua conta.</p>
                            <p style="text-align: center; margin: 30px 0;">
                                <a href="{{ $url }}" style="background:rgb(0, 0, 0); color: #DAA520; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                                    Redefinir Password
                                </a>
                            </p>
                            <p>Se não foste tu quem pediu a redefinição de password, por favor ignora este e-mail.</p>
                            <p>Obrigado,<br> Equipa Smart4Finances</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999;">
                            © {{ date('Y') }} Smart4Finances. Todos os direitos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<?php

function EnviarEmailReset($BDconn, $CPFPessoa, $senhaTemporaria)
{
    require("email.php");

    // Busca nome e e-mail prioritário
    $sql = "SELECT 
                p.nomePessoa,
                e.emailPessoa
            FROM pessoa2DS p
            INNER JOIN emailPessoa e
                ON p.CPFpessoa2DS = e.CPFpessoa2DS
            WHERE p.CPFpessoa2DS = ?
              AND e.prioritario = 'S'";

    $stmt = mysqli_prepare($BDconn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $CPFPessoa);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 0) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $dados = mysqli_fetch_assoc($resultado);

    $nomeDestinatario = $dados['nomePessoa'];
    $To = $dados['emailPessoa'];

    mysqli_stmt_close($stmt);


    // Assunto
    $Subject = "Redefinição de senha";


    // Corpo do e-mail
    $Message = '
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="dark">
    </head>
    <body style="margin:0;padding:0;background-color:#080f1d;font-family:Arial,Helvetica,sans-serif;color:#e8eef8;">
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">Sua senha temporária está pronta.</div>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#080f1d;padding:36px 14px;">
            <tr><td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#101a2a;border:1px solid #22344e;border-radius:16px;overflow:hidden;">
                    <tr><td style="height:6px;background-color:#2878ff;font-size:0;line-height:0;">&nbsp;</td></tr>
                    <tr><td style="padding:30px 34px 8px;">
                        <h1 style="margin:0 0 8px;color:#f4f7fc;font-size:26px;line-height:1.25;">Redefinição de senha</h1>
                        <p style="margin:0;color:#aab8cc;font-size:15px;line-height:1.6;">Olá, ' . htmlspecialchars((string)$nomeDestinatario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '.</p>
                    </td></tr>
                    <tr><td style="padding:12px 34px 0;">
                        <p style="margin:0;color:#c0cada;font-size:15px;line-height:1.7;">Uma senha temporária foi criada para sua conta. Use o código abaixo para entrar; depois, o sistema solicitará que você escolha uma nova senha.</p>
                    </td></tr>
                    <tr><td style="padding:24px 34px 8px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0b2343;border:1px solid #2878ff;border-radius:12px;">
                            <tr><td align="center" style="padding:19px 12px 8px;color:#8ebcff;font-size:11px;font-weight:bold;letter-spacing:2px;">SENHA TEMPORÁRIA</td></tr>
                            <tr><td align="center" style="padding:0 12px 20px;color:#f4f8ff;font-family:Consolas,Monaco,monospace;font-size:30px;font-weight:bold;letter-spacing:6px;">' . htmlspecialchars((string)$senhaTemporaria, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td></tr>
                        </table>
                    </td></tr>
                    <tr><td style="padding:16px 34px 28px;">
                        <p style="margin:0;color:#8796aa;font-size:13px;line-height:1.6;">Se você não solicitou esta redefinição, fale com o administrador do sistema.</p>
                    </td></tr>
                    <tr><td style="padding:16px 34px;border-top:1px solid #22344e;color:#74839a;font-size:12px;">Sistema de contas</td></tr>
                </table>
            </td></tr>
        </table>
    </body>
    </html>';


    // Envia o e-mail
    return mandarEmail(
        $nomeDestinatario,
        $To,
        $Subject,
        $Message
    );
}
?>

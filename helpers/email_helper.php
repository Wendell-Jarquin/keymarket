<?php
if (!function_exists('mb_convert_encoding')) {
    function mb_convert_encoding($s, $to, $from) { return $s; }
}
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function crearMailer()
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'mail.wendelljarxd.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sends.keymarket@wendelljarxd.com';
    $mail->Password   = 'P1I}@*_o9*4Jj~OJ';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    $mail->isHTML(true);
    $mail->setFrom('sends.keymarket@wendelljarxd.com', 'Key Market Nicaragua');
    return $mail;
}

function u($s)
{
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($m) {
        return json_decode('"\u' . $m[1] . '"');
    }, $s);
}

function enviarCorreoConfirmacion($destinatario, $nombre, $numeroOrden, $licenciaNombre)
{
    try {
        $mail = crearMailer();
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = u("Confirmaci\u00f3n de orden #$numeroOrden \u2014 Key Market Nicaragua");
        $n = htmlspecialchars($nombre);
        $ln = htmlspecialchars($licenciaNombre);
        $anio = date('Y');
        $mail->Body = <<<HTML
        <div style="font-family:Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;padding:32px">
            <div style="text-align:center;margin-bottom:32px">
                <h1 style="font-size:1.4rem;color:#1a1a1a;margin:0">Key Market <span style="font-weight:300;color:#666">Nicaragua</span></h1>
            </div>
            <div style="background:#f9f9f9;border-radius:12px;padding:32px">
                <h2 style="font-size:1.2rem;color:#1a1a1a;margin:0 0 8px">&iexcl;Hola, {$n}!</h2>
                <p style="color:#555;line-height:1.6;margin:0 0 16px">
                    Hemos recibido su solicitud para la licencia <strong>{$ln}</strong>.
                </p>
                <div style="background:#fff;border-radius:8px;padding:16px;text-align:center;margin-bottom:16px">
                    <p style="font-size:.82rem;color:#999;margin:0 0 4px">N&uacute;mero de orden</p>
                    <p style="font-size:1.3rem;font-weight:700;color:#1a1a1a;margin:0">{$numeroOrden}</p>
                </div>
                <p style="color:#555;line-height:1.6;margin:0 0 8px">
                    Uno de nuestros ejecutivos se pondr&aacute; en contacto con usted para agilizar el pago y la instalaci&oacute;n de su licencia.
                </p>
                <p style="color:#555;line-height:1.6;margin:0">
                    Si tiene alguna consulta, puede responder a este correo o contactarnos por WhatsApp al <strong>+505 8618 1155</strong>.
                </p>
            </div>
            <div style="text-align:center;margin-top:24px;font-size:.8rem;color:#aaa">
                Key Market Nicaragua &copy; {$anio} &mdash; Licencias originales con entrega inmediata.
            </div>
        </div>
HTML;
        $mail->AltBody = u("Hola {$n},\n\nHemos recibido su solicitud para la licencia {$ln}.\n\nSu n\u00famero de orden es: {$numeroOrden}\n\nUno de nuestros ejecutivos se pondr\u00e1 en contacto con usted para agilizar el pago y la instalaci\u00f3n de su licencia.\n\nKey Market Nicaragua");
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function enviarCorreoAtendiendo($destinatario, $nombre, $numeroOrden)
{
    try {
        $mail = crearMailer();
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = u("Su orden #{$numeroOrden} est\u00e1 siendo atendida \u2014 Key Market Nicaragua");
        $n = htmlspecialchars($nombre);
        $anio = date('Y');
        $mail->Body = <<<HTML
        <div style="font-family:Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;padding:32px">
            <div style="text-align:center;margin-bottom:32px">
                <h1 style="font-size:1.4rem;color:#1a1a1a;margin:0">Key Market <span style="font-weight:300;color:#666">Nicaragua</span></h1>
            </div>
            <div style="background:#f9f9f9;border-radius:12px;padding:32px">
                <h2 style="font-size:1.2rem;color:#1a1a1a;margin:0 0 12px">&iexcl;Hola, {$n}!</h2>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    Nos comunicamos para informarle que actualmente su pedido <strong>{$numeroOrden}</strong> est&aacute; siendo atendido y manejado por un ejecutivo.
                </p>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    En breve le llegar&aacute; un mensaje de WhatsApp o correo electr&oacute;nico con m&aacute;s informaci&oacute;n. Por favor, estar atento.
                </p>
                <p style="color:#555;line-height:1.6;margin:0">
                    Saludos cordiales,<br>
                    <strong>Key Market Nicaragua</strong>
                </p>
            </div>
            <div style="text-align:center;margin-top:24px;font-size:.8rem;color:#aaa">
                Key Market Nicaragua &copy; {$anio} &mdash; Licencias originales con entrega inmediata.
            </div>
        </div>
HTML;
        $mail->AltBody = u("Hola {$n},\n\nNos comunicamos para informarle que actualmente su pedido {$numeroOrden} est\u00e1 siendo atendido y manejado por un ejecutivo.\n\nEn breve le llegar\u00e1 un mensaje de WhatsApp o correo electr\u00f3nico. Por favor, estar atento.\n\nSaludos,\nKey Market Nicaragua");
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function enviarCorreoCancelacion($destinatario, $nombre, $numeroOrden, $licenciaNombre, $motivo)
{
    try {
        $mail = crearMailer();
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = u("Su orden #{$numeroOrden} ha sido cancelada \u2014 Key Market Nicaragua");
        $n = htmlspecialchars($nombre);
        $ln = htmlspecialchars($licenciaNombre);
        $m = htmlspecialchars($motivo);
        $anio = date('Y');
        $mail->Body = <<<HTML
        <div style="font-family:Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;padding:32px">
            <div style="text-align:center;margin-bottom:32px">
                <h1 style="font-size:1.4rem;color:#1a1a1a;margin:0">Key Market <span style="font-weight:300;color:#666">Nicaragua</span></h1>
            </div>
            <div style="background:#f9f9f9;border-radius:12px;padding:32px">
                <h2 style="font-size:1.2rem;color:#1a1a1a;margin:0 0 12px">Estimado/a {$n},</h2>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    Nos comunicamos para informarle que su pedido <strong>{$numeroOrden}</strong> de la licencia <strong>{$ln}</strong> ha sido cancelado.
                </p>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    <strong>Motivo:</strong><br>
                    {$m}
                </p>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    Gracias por contar con nosotros, le esperamos.
                </p>
                <p style="color:#555;line-height:1.6;margin:0">
                    Saludos cordiales,<br>
                    <strong>Key Market Nicaragua</strong>
                </p>
            </div>
            <div style="text-align:center;margin-top:24px;font-size:.8rem;color:#aaa">
                Key Market Nicaragua &copy; {$anio} &mdash; Licencias originales con entrega inmediata.
            </div>
        </div>
HTML;
        $mail->AltBody = u("Estimado/a {$n},\n\nNos comunicamos para informarle que su pedido {$numeroOrden} de la licencia {$ln} ha sido cancelado.\n\nMotivo:\n{$m}\n\nGracias por contar con nosotros, le esperamos.\n\nSaludos,\nKey Market Nicaragua");
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function enviarCorreoLicenciaActiva($destinatario, $nombre, $numeroOrden, $licenciaNombre, $duracion, $token)
{
    try {
        $mail = crearMailer();
        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = u("\u00a1Su licencia {$licenciaNombre} ya est\u00e1 lista! \u2014 Key Market Nicaragua");
        $n = htmlspecialchars($nombre);
        $ln = htmlspecialchars($licenciaNombre);
        $d = htmlspecialchars($duracion ?: '—');
        $anio = date('Y');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $urlCanjear = "http://{$host}/canjear/{$token}";
        $mail->Body = <<<HTML
        <div style="font-family:Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;padding:32px">
            <div style="text-align:center;margin-bottom:32px">
                <h1 style="font-size:1.4rem;color:#1a1a1a;margin:0">Key Market <span style="font-weight:300;color:#666">Nicaragua</span></h1>
            </div>
            <div style="background:#f9f9f9;border-radius:12px;padding:32px">
                <h2 style="font-size:1.2rem;color:#1a1a1a;margin:0 0 12px">&iexcl;Hola, {$n}!</h2>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    A continuaci&oacute;n te detallamos los datos de tu licencia <strong>{$ln}</strong>.
                </p>
                <p style="color:#555;line-height:1.6;margin:0 0 12px">
                    Orden: <strong>{$numeroOrden}</strong><br>
                    Licencia: <strong>{$ln}</strong><br>
                    V&aacute;lida por: <strong>{$d}</strong>
                </p>
                <p style="color:#dc2626;line-height:1.6;margin:0 0 16px;font-weight:600">
                    Favor canjear en los pr&oacute;ximos 30 d&iacute;as.
                </p>
                <div style="text-align:center;margin:24px 0">
                    <a href="{$urlCanjear}" style="display:inline-block;padding:14px 36px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:1rem">Canjear licencia</a>
                </div>
                <p style="color:#555;line-height:1.6;margin:0">
                    Si tiene alguna consulta, puede responder a este correo o contactarnos por WhatsApp al <strong>+505 8618 1155</strong>.
                </p>
            </div>
            <div style="text-align:center;margin-top:24px;font-size:.8rem;color:#aaa">
                Key Market Nicaragua &copy; {$anio} &mdash; Licencias originales con entrega inmediata.
            </div>
        </div>
HTML;
        $mail->AltBody = u("Hola {$n},\n\nA continuaci\u00f3n te detallamos los datos de tu licencia {$ln}.\n\nOrden: {$numeroOrden}\nLicencia: {$ln}\nV\u00e1lida por: {$d}\n\nFavor canjear en los pr\u00f3ximos 30 d\u00edas.\n\nCanjear aqu\u00ed: {$urlCanjear}\n\nKey Market Nicaragua");
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

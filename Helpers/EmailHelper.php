<?php

class EmailHelper
{

    protected $email, $assunto, $mensagem;

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function setAssunto($value)
    {
        $this->assunto = $value;
    }

    public function setMensagem($value)
    {
        $this->mensagem = $value;
    }

    public function enviaEmail()
    {

        $mail = new PHPMailer(true);

        $mail->IsSMTP();

        try {

            $mail->Host = SMTPHOST; // SMTP server
            //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
            $mail->SMTPAuth = SMTPAUTH;                  // enable SMTP authentication
            $mail->Host = SMTPHOST;
            $mail->SMTPSecure = SMTPSECURE;
            $mail->Port = PORTA;                    // set the SMTP port for the GMAIL server
            $mail->Username = SMTPUSER; // SMTP account username
            $mail->Password = SMTPPASS;        // SMTP account password
            //$mail->AddReplyTo('name@yourdomain.com', 'First Last');

            if (strstr($this->email, ';')) {

                $em_array = explode(';', $this->email);

                foreach ($em_array as $v) {

                    if (!empty($v)) {
                        $mail->AddAddress($v, '');
                    }
                }

            } else {
                if (!strstr($this->email, ';')) {
                    $mail->AddAddress($this->email, '');
                }
            }

            $mail->SetFrom(FROM, FROMNAME);
            // $mail->AddReplyTo('name@yourdomain.com', 'First Last');
            $mail->Subject = $this->assunto;
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically


            $msg = $this->cabecalho();
            $msg .= $this->mensagem;
            $msg .= $this->rodape();

            $mail->MsgHTML($msg);
            //$mail->AddAttachment('images/phpmailer.gif');      // attachment

            $mail->Send();

            return true;
        } catch (phpmailerException $e) {
            return $e->errorMessage(); //Pretty error messages from PHPMailer
            //echo $e->errorMessage();exit;
        } catch (Exception $e) {
            return $e->getMessage(); //Boring error messages from anything else!
        }

    }

    private function cabecalho()
    {

        $saida = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ';
        $saida .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

        $saida .= '<html xmlns="http://www.w3.org/1999/xhtml">';
        $saida .= '<head>';
        $saida .= '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-7">';

        $saida .= '</head>';

        $saida .= '<body style="margin:0px;">';

        $saida .= '<table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                              <tr>
                                <td bgcolor="#fff">';

        return $saida;
    }

    private function rodape()
    {

        $saida = '';

        $saida .= '</td>
                          </tr>
                        </table>';

        $saida .= '</body></html>';

        return $saida;
    }
}
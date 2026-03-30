<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Parser - Parsea emails desde IMAP
 */
class Inmopress_Email_Parser
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Parsear email completo
     */
    public function parse_email($mailbox, $email_number)
    {
        // Overview
        $overview = imap_fetch_overview($mailbox, $email_number, 0);
        $overview = $overview[0];

        // Headers
        $headers = imap_headerinfo($mailbox, $email_number);

        // Body
        $body = $this->get_email_body($mailbox, $email_number);

        // Parsear From
        $from = $this->parse_address($overview->from);
        $to = $this->parse_address($overview->to);

        return array(
            'from' => $overview->from,
            'from_email' => $from['email'],
            'from_name' => $from['name'],
            'to' => $overview->to,
            'to_email' => $to['email'],
            'to_name' => $to['name'],
            'subject' => $this->decode_mime_string($overview->subject),
            'date' => $overview->date,
            'message_id' => isset($headers->message_id) ? trim($headers->message_id, '<>') : '',
            'in_reply_to' => isset($headers->in_reply_to) ? trim($headers->in_reply_to, '<>') : '',
            'references' => isset($headers->references) ? $headers->references : '',
            'body_html' => $body['html'],
            'body_text' => $body['text'],
            'attachments' => $this->get_attachments($mailbox, $email_number),
        );
    }

    /**
     * Obtener cuerpo del email (HTML y texto)
     */
    private function get_email_body($mailbox, $email_number)
    {
        $structure = imap_fetchstructure($mailbox, $email_number);
        $body_html = '';
        $body_text = '';

        if (isset($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $part_num => $part) {
                $part_number = $part_num + 1;
                $part_body = imap_fetchbody($mailbox, $email_number, $part_number);

                // Decodificar según encoding
                $part_body = $this->decode_body($part_body, $part->encoding);

                // Determinar tipo
                if ($part->subtype === 'HTML') {
                    $body_html = $part_body;
                } elseif ($part->subtype === 'PLAIN') {
                    $body_text = $part_body;
                }
            }
        } else {
            // Email simple sin multipart
            $body = imap_body($mailbox, $email_number);
            $body = $this->decode_body($body, $structure->encoding);
            
            if ($structure->subtype === 'HTML') {
                $body_html = $body;
            } else {
                $body_text = $body;
            }
        }

        // Si no hay HTML, convertir texto a HTML
        if (empty($body_html) && !empty($body_text)) {
            $body_html = wpautop($body_text);
        }

        return array(
            'html' => $body_html,
            'text' => $body_text,
        );
    }

    /**
     * Decodificar cuerpo según encoding
     */
    private function decode_body($body, $encoding)
    {
        switch ($encoding) {
            case 0: // 7BIT
            case 1: // 8BIT
                return $body;
            case 2: // BINARY
                return $body;
            case 3: // BASE64
                return base64_decode($body);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($body);
            case 5: // OTHER
                return $body;
            default:
                return $body;
        }
    }

    /**
     * Parsear dirección de email
     */
    private function parse_address($address_string)
    {
        $email = '';
        $name = '';

        if (preg_match('/^(.+?)\s*<(.+?)>$/', $address_string, $matches)) {
            $name = trim($matches[1], '"\'');
            $email = trim($matches[2]);
        } else {
            $email = trim($address_string);
        }

        return array(
            'email' => $email,
            'name' => $name,
        );
    }

    /**
     * Decodificar string MIME
     */
    private function decode_mime_string($string)
    {
        if (empty($string)) {
            return '';
        }

        // Decodificar según RFC 2047
        $decoded = imap_mime_header_decode($string);
        $result = '';

        foreach ($decoded as $part) {
            $result .= $part->text;
        }

        return $result;
    }

    /**
     * Obtener adjuntos
     */
    private function get_attachments($mailbox, $email_number)
    {
        // Implementación básica - se puede expandir
        return array();
    }
}

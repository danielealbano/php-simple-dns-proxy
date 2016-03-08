<?php namespace SimpleDnsProxy\Rfcs\Rfc1035;

use SimpleDnsProxy\Rfcs\Rfc1035\Header as Rfc1035Header;
use SimpleDnsProxy\Rfcs\Rfc1035\Question as Rfc1035Question;

use Exception;

class Packet {
    protected $header;
    protected $questions;

    const INT32_SIZE = 4;
    const INT16_SIZE = 2;

    public function __construct() {
    }

    public function header($header = null) {
        if (func_num_args() > 0) {
            $this->header = $header;
        }

        return $this->header;
    }

    public function questions($questions = null) {
        if (func_num_args() > 0) {
            $this->questions = $questions;
        }

        return $this->questions;
    }

    static public function parse($data) {
        $packet = new Packet();

        $offset = 0;

        self::parseHeader($packet, $data, $offset);
        self::parseQuestions($packet, $data, $offset);

        return $packet;
    }

    static protected function parseHeader($packet, $data, &$offset) {
        $packet->header(Rfc1035Header::parse($data));
        $offset += 12;
    }

    static protected function parseQuestions($packet, $data, &$offset) {
        $questions = [ ];
        $questionsCount = $packet->header()->qdcount();
        
        for ($questionIndex = 0; $questionIndex < $questionsCount; $questionIndex++) {
            $question = Rfc1035Question::parse($data, $offset);
            $questions[] = $question;
        }

        $packet->questions($questions);

        return $offset;
    }

    static public function extractString($data, &$offset) {
        $datalen = strlen($data);
        $name = '';
        while (1) {
            if ($datalen < ($offset + 1)) {
                return null;
            }

            $a = unpack("@$offset/Cchar", $data);
            $len = (int)$a['char'];

            if ($len == 0) {
                $offset++;
                break;
            } else if (($len & 0xc0) == 0xc0) {
                if ($datalen < ($offset +self::INT16_SIZE)) {
                    return null;
                }
                $ptr = unpack("@$offset/ni", $data);
                $ptr = $ptr['i'];
                $ptr = $ptr & 0x3fff;
                $name2 = self::extractString($data, $ptr);

                if (is_null($name2[0])) {
                    return null;
                }
                $name .= $name2[0];
                $offset +=self::INT16_SIZE;
                break;
            } else {
                $offset++;

                if ($datalen < ($offset + $len)) {
                    return null;
                }

                $elem = substr($data, $offset, $len);
                $name .= $elem . '.';
                $offset += $len;
            }
        }

        $name = preg_replace('/\.$/', '', $name);

        return $name;
    }
}

class PacketException extends Exception {

}


<?php namespace SimpleDnsProxy\Rfcs\Rfc1035;

use SimpleDnsProxy\Rfcs\Rfc1035\Packet;

class Question {
    protected $qname = null;
    protected $qtype = null;
    protected $qclass = null;

    public function qname($qname = null) {
        if (func_num_args() > 0) {
            $this->qname = $qname;
        }

        return $this->qname;
    }

    public function qtype($qtype = null) {
        if (func_num_args() > 0) {
            $this->qtype = $qtype;
        }

        return $this->qtype;
    }

    public function qclass($qclass = null) {
        if (func_num_args() > 0) {
            $this->qclass = $qclass;
        }

        return $this->qclass;
    }

    static public function parse($data, &$offset) {
        $qname = Packet::extractString($data, $offset);

        if (is_null($qname)) {
            return null;
        }

        if (strlen($data) < ($offset + 4)) {
            return null;
        }

        $binaryData = unpack("@$offset/n2int", $data);
        $qtype = $binaryData['int1'];
        $qclass = $binaryData['int2'];

        $qname = preg_replace(array('/^\.+/', '/\.+$/'), '', $qname);

        $question = new Question();
        $question->qname($qname);
        $question->qtype($qtype);
        $question->qclass($qclass);

        return $question;
    }
}
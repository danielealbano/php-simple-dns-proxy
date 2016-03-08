<?php namespace SimpleDnsProxy\Rfcs\Rfc1035;

use Exception;

class Header {
    protected $id;
    protected $qr;
    protected $opcode;
    protected $aa;
    protected $tc;
    protected $rd;
    protected $ra;
    protected $rcode;
    protected $qdcount;
    protected $ancount;
    protected $nscount;
    protected $arcount;

    public function __construct() {
        $this->id = false;
    }

    public function build() {
        if ($this->id === false) {
            $this->id = mt_rand(0, 65535);
        }

        $byte2 = 
            ($this->qr << 7) |
            ($opcode << 3) |
            ($this->aa << 2) |
            ($this->tc << 1) |
            ($this->rd);
        $byte3 =
            ($this->ra << 7) |
            $rcode;

        return pack('nC2n4',
            $this->id,
            $byte2,
            $byte3,
            $this->qdcount,
            $this->ancount,
            $this->nscount,
            $this->arcount);
    }

    public function id($id = null) {
        if (func_num_args() > 0) {
            $this->id = $id;
        }

        return $this->id;
    }

    public function qr($qr = null) {
        if (func_num_args() > 0) {
            $this->qr = $qr;
        }

        return $this->qr;
    }

    public function opcode($opcode = null) {
        if (func_num_args() > 0) {
            $this->opcode = $opcode;
        }

        return $this->opcode;
    }

    public function aa($aa = null) {
        if (func_num_args() > 0) {
            $this->aa = $aa;
        }

        return $this->aa;
    }

    public function tc($tc = null) {
        if (func_num_args() > 0) {
            $this->tc = $tc;
        }

        return $this->tc;
    }

    public function rd($rd = null) {
        if (func_num_args() > 0) {
            $this->rd = $rd;
        }

        return $this->rd;
    }

    public function ra($ra = null) {
        if (func_num_args() > 0) {
            $this->ra = $ra;
        }

        return $this->ra;
    }

    public function rcode($rcode = null) {
        if (func_num_args() > 0) {
            $this->rcode = $rcode;
        }

        return $this->rcode;
    }

    public function qdcount($qdcount = null) {
        if (func_num_args() > 0) {
            $this->qdcount = $qdcount;
        }

        return $this->qdcount;
    }

    public function ancount($ancount = null) {
        if (func_num_args() > 0) {
            $this->ancount = $ancount;
        }

        return $this->ancount;
    }

    public function nscount($nscount = null) {
        if (func_num_args() > 0) {
            $this->nscount = $nscount;
        }

        return $this->nscount;
    }

    public function arcount($arcount = null) {
        if (func_num_args() > 0) {
            $this->arcount = $arcount;
        }

        return $this->arcount;
    }

    static public function parse($data) {
        if (strlen($data) < 12) {
            return false;
        }

        $binaryData = unpack('nid/C2flags/n4counts', $data);

        $header = new Header();
        $header->id($binaryData['id']);
        $header->qr(($binaryData['flags1'] >> 7) & 0x1);
        $header->opcode(($binaryData['flags1'] >> 3) & 0xf);
        $header->aa(($binaryData['flags1'] >> 2) & 0x1);
        $header->tc(($binaryData['flags1'] >> 1) & 0x1);
        $header->rd($binaryData['flags1'] & 0x1);
        $header->ra(($binaryData['flags2'] >> 7) & 0x1);
        $header->rcode($binaryData['flags2'] & 0xf);
        $header->qdcount($binaryData['counts1']);
        $header->ancount($binaryData['counts2']);
        $header->nscount($binaryData['counts3']);
        $header->arcount($binaryData['counts4']);

        return $header;
     }
}

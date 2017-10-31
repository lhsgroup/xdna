<?php
class xdna_Payload
{
    const OPCODE_CONTINUE = 0x0;
    const OPCODE_TEXT = 0x1;
    const OPCODE_BINARY = 0x2;
    const OPCODE_NON_CONTROL_RESERVED_1 = 0x3;
    const OPCODE_NON_CONTROL_RESERVED_2 = 0x4;
    const OPCODE_NON_CONTROL_RESERVED_3 = 0x5;
    const OPCODE_NON_CONTROL_RESERVED_4 = 0x6;
    const OPCODE_NON_CONTROL_RESERVED_5 = 0x7;
    const OPCODE_CLOSE = 0x8;
    const OPCODE_PING = 0x9;
    const OPCODE_PONG = 0xA;
    const OPCODE_CONTROL_RESERVED_1 = 0xB;
    const OPCODE_CONTROL_RESERVED_2 = 0xC;
    const OPCODE_CONTROL_RESERVED_3 = 0xD;
    const OPCODE_CONTROL_RESERVED_4 = 0xE;
    const OPCODE_CONTROL_RESERVED_5 = 0xF;

    private $fin = 0x1;
    private $rsv1 = 0x0;
    private $rsv2 = 0x0;
    private $rsv3 = 0x0;
    private $opcode;
    private $mask = 0x0;
    private $maskKey;
    private $payload;

    public function setFin($fin) {
        $this->fin = $fin;

        return $this;
    }

    public function getFin() {
        return $this->fin;
    }

    public function setRsv1($rsv1) {
        $this->rsv1 = $rsv1;

        return $this;
    }

    public function getRsv1() {
        return $this->rsv1;
    }

    public function setRsv2($rsv2) {
        $this->rsv2 = $rsv2;

        return $this;
    }

    public function getRsv2() {
        return $this->rsv2;
    }

    public function setRsv3($rsv3) {
        $this->rsv3 = $rsv3;

        return $this;
    }

    public function getRsv3() {
        return $this->rsv3;
    }

    public function setOpcode($opcode) {
        $this->opcode = $opcode;

        return $this;
    }

    public function getOpcode() {
        return $this->opcode;
    }

    public function setMask($mask) {
        $this->mask = $mask;

        if ($this->mask == true) {
            $this->generateMaskKey();
        }

        return $this;
    }

    public function getMask() {
        return $this->mask;
    }

    public function getLength() {
        return strlen($this->getPayload());
    }

    public function setMaskKey($maskKey) {
        $this->maskKey = $maskKey;

        return $this;
    }

    public function getMaskKey() {
        return $this->maskKey;
    }

    public function setPayload($payload) {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function generateMaskKey() {
        $this->setMaskKey($key = openssl_random_pseudo_bytes(4));

        return $key;
    }

    public function encodePayload()
    {
        $payload = (($this->getFin()) << 1) | ($this->getRsv1());
        $payload = (($payload) << 1) | ($this->getRsv2());
        $payload = (($payload) << 1) | ($this->getRsv3());
        $payload = (($payload) << 4) | ($this->getOpcode());
        $payload = (($payload) << 1) | ($this->getMask());

        if ($this->getLength() <= 125) {
            $payload = (($payload) << 7) | ($this->getLength());
            $payload = pack('n', $payload);
        } elseif ($this->getLength() <= 0xffff) {
            $payload = (($payload) << 7) | 126;
            $payload = pack('n', $payload).pack('n*', $this->getLength());
        } else {
            $payload = (($payload) << 7) | 127;
            $left = 0xffffffff00000000;
            $right = 0x00000000ffffffff;
            $l = ($this->getLength() & $left) >> 32;
            $r = $this->getLength() & $right;
            $payload = pack('n', $payload).pack('NN', $l, $r);
        }

        if ($this->getMask() == 0x1) {
            $payload .= $this->getMaskKey();
            $data = $this->maskData($this->getPayload(), $this->getMaskKey());
        } else {
            $data = $this->getPayload();
        }

        $payload = $payload.$data;

        return $payload;
    }

    public function maskData($data, $key) {
        $masked = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $masked .= $data[$i] ^ $key[$i % 4];
        }

        return $masked;
    }
}

class xdna_ioClient {
    const TYPE_DISCONNECT   = 0;
    const TYPE_CONNECT      = 1;
    const TYPE_HEARTBEAT    = 2;
    const TYPE_MESSAGE      = 3;
    const TYPE_JSON_MESSAGE = 4;
    const TYPE_EVENT        = 5;
    const TYPE_ACK          = 6;
    const TYPE_ERROR        = 7;
    const TYPE_NOOP         = 8;

    private $socketIOUrl;
    private $serverHost;
    private $serverPort = 80;
    private $session;
    private $fd;
    private $buffer;
    private $lastId = 0;
    private $read;
    private $checkSslPeer = true;
    private $debug;

    public function __construct($socketIOUrl, $socketIOPath = 'socket.io', $protocol = 1, $read = true, $checkSslPeer = true, $debug = false) {
        $this->socketIOUrl = $socketIOUrl.'/'.$socketIOPath.'/'.(string)$protocol;
        $this->read = $read;
        $this->debug = $debug;
        $this->parseUrl();
        $this->checkSslPeer = $checkSslPeer;
    }

    /**
     * Initialize a new connection
     *
     * @param boolean $keepalive
     * @return ElephantIOClient
     */
    public function init($keepalive = false) {
        $this->handshake();
        $this->connect();

        if ($keepalive) {
            $this->keepAlive();
        } else {
            return $this;
        }
    }

    /**
     * Keep the connection alive and dispatch events
     *
     * @access public
     * @todo work on callbacks
     */
    public function keepAlive() {
        while(true) {
            if ($this->session['heartbeat_timeout'] > 0 && $this->session['heartbeat_timeout']+$this->heartbeatStamp-5 < time()) {
                $this->send(self::TYPE_HEARTBEAT);
                $this->heartbeatStamp = time();
            }

            $r = array($this->fd);
            $w = $e = null;

            if (stream_select($r, $w, $e, 5) == 0) continue;

            $this->read();
        }
    }

    /**
     * Read the buffer and return the oldest event in stack
     *
     * @access public
     * @return string
     * // https://tools.ietf.org/html/rfc6455#section-5.2
     */
    public function read() {
        // Ignore first byte, I hope Socket.io does not send fragmented frames, so we don't have to deal with FIN bit.
        // There are also reserved bit's which are 0 in socket.io, and opcode, which is always "text frame" in Socket.io
        fread($this->fd, 1);

        // There is also masking bit, as MSB, but it's 0 in current Socket.io
        $payload_len = ord(fread($this->fd, 1));

        switch ($payload_len) {
            case 126:
                $payload_len = unpack("n", fread($this->fd, 2));
                $payload_len = $payload_len[1];
                break;
            case 127:
                $this->stdout('error', "Next 8 bytes are 64bit uint payload length, not yet implemented, since PHP can't handle 64bit longs!");
                break;
        }

        $payload = fread($this->fd, $payload_len);
        $this->stdout('debug', 'Received ' . $payload);

        return $payload;
    }

    /**
     * Send message to the websocket
     *
     * @access public
     * @param int $type
     * @param int $id
     * @param int $endpoint
     * @param string $message
     * @return ElephantIO\Client
     */
    public function send($type, $id = null, $endpoint = null, $message = null) {
        if (!is_int($type) || $type > 8) {
            throw new \InvalidArgumentException('ElephantIOClient::send() type parameter must be an integer strictly inferior to 9.');
        }

        $raw_message = $type.':'.$id.':'.$endpoint.':'.$message;
        $payload = new xdna_Payload();
        $payload->setOpcode(xdna_Payload::OPCODE_TEXT)
            ->setMask(true)
            ->setPayload($raw_message);
        $encoded = $payload->encodePayload();

        fwrite($this->fd, $encoded);

        // wait 100ms before closing connexion
        usleep(100*1000);

        $this->stdout('debug', 'Sent '.$raw_message);

        return $this;
    }

    /**
     * Emit an event
     *
     * @param string $event
     * @param array $args
     * @param string $endpoint
     * @param function $callback - ignored for the time being
     * @todo work on callbacks
     */
    public function emit($event, $args, $endpoint, $callback = null) {
        $this->send(self::TYPE_EVENT, null, $endpoint, json_encode(array(
            'name' => $event,
            'args' => $args,
            )
        ));
    }

    /**
     * Close the socket
     *
     * @return boolean
     */
    public function close()
    {
        if ($this->fd) {
            $this->send(self::TYPE_DISCONNECT);
            fclose($this->fd);

            return true;
        }

        return false;
    }

    /**
     * Send ANSI formatted message to stdout.
     * First parameter must be either debug, info, error or ok
     *
     * @access private
     * @param string $type
     * @param string $message
     */
    private function stdout($type, $message) {
        if (!defined('STDOUT') || !$this->debug) {
            return false;
        }

        $typeMap = array(
            'debug'   => array(36, '- debug -'),
            'info'    => array(37, '- info  -'),
            'error'   => array(31, '- error -'),
            'ok'      => array(32, '- ok    -'),
        );

        if (!array_key_exists($type, $typeMap)) {
            throw new \InvalidArgumentException('ElephantIOClient::stdout $type parameter must be debug, info, error or success. Got '.$type);
        }

        fwrite(STDOUT, "\033[".$typeMap[$type][0]."m".$typeMap[$type][1]."\033[37m  ".$message."\r\n");
    }

    private function generateKey($length = 16) {
        $c = 0;
        $tmp = '';

        while($c++ * 16 < $length) {
            $tmp .= md5(mt_rand(), true);
        }

        return base64_encode(substr($tmp, 0, $length));
    }

    /**
     * Handshake with socket.io server
     *
     * @access private
     * @return bool
     */
    private function handshake() {
        $ch = curl_init($this->socketIOUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!$this->checkSslPeer)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);

        if ($res === false) {
            throw new \Exception(curl_error($ch));
        }

        $sess = explode(':', $res);
        $this->session['sid'] = $sess[0];
        $this->session['heartbeat_timeout'] = $sess[1];
        $this->session['connection_timeout'] = $sess[2];
        $this->session['supported_transports'] = array_flip(explode(',', $sess[3]));

        if (!isset($this->session['supported_transports']['websocket'])) {
            throw new \Exception('This socket.io server do not support websocket protocol. Terminating connection...');
        }

        return true;
    }

    /**
     * Connects using websocket protocol
     *
     * @access private
     * @return bool
     */
    private function connect() {
        $this->fd = fsockopen($this->serverHost, $this->serverPort, $errno, $errstr);

        if (!$this->fd) {
            throw new \Exception('fsockopen returned: '.$errstr);
        }

        $key = $this->generateKey();

        $out  = "GET ".$this->serverPath."/websocket/".$this->session['sid']." HTTP/1.1\r\n";
        $out .= "Host: ".$this->serverHost."\r\n";
        $out .= "Upgrade: WebSocket\r\n";
        $out .= "Connection: Upgrade\r\n";
        $out .= "Sec-WebSocket-Key: ".$key."\r\n";
        $out .= "Sec-WebSocket-Version: 13\r\n";
        $out .= "Origin: *\r\n\r\n";

        fwrite($this->fd, $out);

        $res = fgets($this->fd);

        if ($res === false) {
            throw new \Exception('Socket.io did not respond properly. Aborting...');
        }

        if ($subres = substr($res, 0, 12) != 'HTTP/1.1 101') {
            throw new \Exception('Unexpected Response. Expected HTTP/1.1 101 got '.$subres.'. Aborting...');
        }

        while(true) {
            $res = trim(fgets($this->fd));
            if ($res === '') break;
        }

        if ($this->read) {
            if ($this->read() != '1::') {
                throw new \Exception('Socket.io did not send connect response. Aborting...');
            } else {
                $this->stdout('info', 'Server report us as connected !');
            }
        }

//        $this->send(self::TYPE_CONNECT);
        $this->heartbeatStamp = time();
    }

    /**
     * Parse the url and set server parameters
     *
     * @access private
     * @return bool
     */
    private function parseUrl() {
        $url = parse_url($this->socketIOUrl);

        $this->serverPath = $url['path'];
        $this->serverHost = $url['host'];
        $this->serverPort = isset($url['port']) ? $url['port'] : null;

        if (array_key_exists('scheme', $url) && $url['scheme'] == 'https') {
            $this->serverHost = 'ssl://'.$this->serverHost;
            if (!$this->serverPort) {
                $this->serverPort = 443;
            }
        }

        return true;
    }

}

class xdna_liveMessage {
	
	private $channel;
	private $msg;
	
	public function __construct($channel,$msg){
		try {
			$this->channel = $channel;
			$this->msg = $msg;
			$io = new xdna_ioClient('localhost:1026', 'socket.io', 1, false, true, true);
			$io->init();
			$io->send(
				xdna_ioClient::TYPE_EVENT,
				null,
				null,
				json_encode(array('name' => 'sendMsg', 'args' => array("channel" => $this->channel,"msg" => $this->msg)))
			);
		} catch (Exception $e) {
			throw new Exception("Socket not start.");
		}
	}
	
	
	
	public function send(){
		
	}

}

?>
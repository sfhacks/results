<?php

/*
  pocketjs v1.1
  [http://anuv.me/pocketjs]
  Copyright: (c) 2016 Anuv Gupta
  File: pocket.php (pocketjs server)
  Source: [https://github.com/anuvgupta/pocketjs]
  License: MIT [https://github.com/anuvgupta/pocketjs/blob/master/LICENSE.md]
*/

if (!isset($cli)) $cli = php_sapi_name() == 'cli'; // if script does not have permission to be run from elsewhere, get cli status
if (!$cli) header('Location: .'); // if script is not run from cli or is not allowed, redirect
if ($cli == true) $cli = 2; // prevent bash escape sequences, just in case
if (!isset($eol)) $eol = PHP_EOL; // if the eol string isn't defaulted, default to platform default

class Pocket {
    //instance fields
    protected $n; //name of server
    protected $s; //master socket
    protected $d; //server domain
    protected $p; //server port
    protected $c; //client array
    protected $mc; //max clients
    protected $ev; //reserved event listeners
    protected $on; //custom event listeners
    protected $v; //verbose run
    protected $bt; //blocking timeout
    //constructor method called when new Pocket constructed
    public function __construct($d, $p, $mc, $bt, $v = null) {
        global $eol, $cli;
        $this->d = isset($d) ? $d : 'localhost';
        $this->p = isset($p) ? $p : 30000;
        $this->c = array();
        $this->mc = isset($mc) ? $mc : 25;
        $this->bt = $bt;
        $this->ev = array(
            'open' => function ($id) { },
            'run' => function () { },
            'close' => function ($id) { }
        );
        $this->on = array();
        $this->v = true;
        $this->n = 'LOG';
        if (is_string($v)) $this->n = strtoupper($v);
        else $this->v = ($v !== null) ? $v : true;
        $bash = $cli == 1 ? '\e[1m' : '';
        if ($this->v && ($this->n != 'LOG')) echo $eol . "$bash{$this->n} POCKET SERVER" . $eol;
        elseif ($this->v) echo $eol . $bash. 'POCKET SERVER' . $eol;
        if (!($this->s = socket_create(AF_INET, SOCK_STREAM, 0))) //create blank websocket
            die ('[ERROR] socket_create(' . AF_INET . ', ' . SOCK_STREAM . ', 0): fail - [' . socket_last_error() . '] ' . socket_strerror(socket_last_error()) . $eol);
        if ($this->v) echo '[SERVER] pocket created' . $eol;
    }
    //destructor method called before destroying Pocket object
    public function __destruct() {
        $this->close(); //close all sockets
    }
    //function open called to start socket server
    public function open() {
        global $eol, $cli;
        if (!(socket_bind($this->s, $this->d, $this->p))) //bind socket to domain and port
            die ("[ERROR] socket_bind(\$this->s, $this->d, $this->p): fail - [" . socket_last_error() . '] ' . socket_strerror(socket_last_error()) . $eol);
        if (!(socket_listen($this->s, $this->mc))) //commence listening for clients
            die ("[ERROR] socket_listen(\$this->s, $this->mc): fail - [" . socket_last_error() . '] ' . socket_strerror(socket_last_error()) . $eol);
        if ($this->v) {
            echo "[SERVER] pocket listening on $this->d:$this->p" . $eol;
            echo '[SERVER] waiting for connections';
            $bash = $cli == 1 ? '..\e[5m.\e[25m' : '...';
            echo "$bash$eol$eol";
        }
        while (true) { //start server
            $read = array(); //array of sockets to be read
            $read[0] = $this->s; //first socket to be read = master socket (to check for new clients)
            //add existing (online) clients to read array (to check for new data)
            for ($i = 0; $i < $this->mc; $i++) {
                if (isset($this->c[$i])) $read[$i + 1] = $this->c[$i];
            }
            //select sockets in read array to be watched for status changes (read array will be modified to contain updated sockets)
            if (@socket_select($read, $write, $except, $this->bt) === false) //pass in read, null write and except, null timeout (to block (pause server to watch for status change) infinitely/indefinitely), or whatever user selected timeout
                echo '[ERROR] socket_select($read , $write , $except , null): fail - [' . socket_last_error() . '] ' . socket_strerror(socket_last_error()) . $eol;

            //check if new client is connecting
            if (in_array($this->s, $read)) { //if master socket is in modified read array, master socket's status has changed, thus a client is connecting
                for ($i = 0; $i < $this->mc; $i++) { //loop through array of clients
                    if (@$this->c[$i] == null) { //if there is space available for a new connection
                        echo $eol;
                        echo 'CLIENT CONNECTING: ' . $eol;
                        $this->c[$i] = socket_accept($this->s);//accept the new socket connection from master socket, construct client and add to client array
                        //display connecting client's details
                        if (socket_getpeername($this->c[$i], $addr, $port)) echo "[SERVER] client[$i] $addr : $port connecting" . $eol;
                        //accept client socket handshake headers
                        $header = @socket_read($this->c[$i], 1024); //read data from new client socket: contains handshake headers
                        echo '[SERVER] headers recieved';
                        $headers = array(); //init headers array
                        $lines = preg_split("/\r\n/", $header); //add each line of header to array
                        foreach ($lines as $line) { //for each header
                            $line = chop($line); //remove whitespaces and escape characters
                            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) $headers[$matches[1]] = $matches[2]; //split header into title and value, load into associative array
                        }
                        echo ' | ';
                        //generate and send back server socket handshake headers to client socket
                        $accept = base64_encode(pack('H*', sha1(@$headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
                        $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                            "Upgrade: websocket\r\n" .
                            "Connection: Upgrade\r\n" .
                            "WebSocket-Origin: $this->d \r\n" .
                            "WebSocket-Location: ws://$this->d:$this->p/server.php\r\n".
                            "Sec-WebSocket-Accept:$accept\r\n\r\n";
                        if (socket_write($this->c[$i], $upgrade, strlen($upgrade)) === false)
                            echo 'socket_write: fail - [' . socket_last_error() . '] ' . socket_strerror(socket_last_error()) . $eol;
                        echo 'headers sent' . $eol;
                        //display connection details if connection successful
                        if (socket_getpeername($this->c[$i], $addr, $port)) {
                            echo '[SERVER] handshake complete - client connected' . $eol . $eol;
                            //send client's data to client
                            $this->data = $this->mask(json_encode(array('id' => $i, 'address' => $addr, 'port' => $port)));
                            socket_write($this->c[$i], $this->data, strlen($this->data));
                            $this->onConn($i);
                        }
                        break;
                    }
                }
            }

            //calculate client data
            $this->onRun();

            //check if client sockets have sent data
            for ($i = 0; $i < $this->mc; $i++) { //loop through client sockets
                if (in_array(@$this->c[$i] , $read)) { //if client socket is defined and is in read array, its status has changed and it is sending data
                    //recieve data from client
                    while (@socket_recv($this->c[$i], $masked_data, 1024, 0) >= 1) { //if client sends new data (0 bytes = disconnected, 1 byte = connected, >1 bytes = new data sent to server)
                        $encodedData = $this->unmask($masked_data);
                        // decode and trim trailing bytes
                        $beginningPos = strpos($encodedData, '~pocketjs~') + 10;
                        $textData = substr($encodedData, $beginningPos, strrpos($encodedData, '~pocketjs~') - $beginningPos);
                        $textData = substr($textData, 0, strrpos($textData, '}') + 1);
                        $data = json_decode($textData, true);
                        // handle data
                        if (isset($data['command'])) {
                            if ($data['command'] == 'close') {
                                $this->close($data['id']);
                                echo $eol . "client[{$data['id']}] {$data['ad']} : {$data['p']} disconnected" . $eol;
                            }
                            //elseif ($data['command'] == 'alive') ;
                        } elseif (!isset($data['call'])) {
                            // echo ($textData);
                            echo "[SERVER] client[$i] kicked for: sending illegal data: no event specified $textData" . $eol;
                            $this->close($i);
                        } elseif (isset($this->on[$data['call']])) {
                            if (isset($data['args'])) {
                                array_push($data['args'], $i);
                                $this->callArr($data['call'], $data['args']);
                            }
                            else $this->call($data['call'], $i);
                        } else {
                            $this->close($i);
                            echo "[SERVER] client[$i] kicked for: sending illegal data: event '{$data['call']}' does not exist" . $eol;
                        }
                        break 2;
                    }
                    //check if client has disconnected
                    $input = @socket_read($this->c[$i], 1024, PHP_NORMAL_READ); //read data from client socket
                    if ($input == null) { //if data is blank, client has disconnected from socket
                        $this->close($i);
                        echo $eol . "client[$i] disconnected" . $eol;
                    }
                }
            }
        }
    }
    //function close called to close all sockets
    public function close($id = null) { //optional parameter id decides which sockets to close
        if (($id !== null) && ($id >= 0)) { //close client socket
            if (isset($this->c[$id])) {
                $this->onClose($id);
                socket_close($this->c[$id]); //close client socket connection
                unset($this->c[$id]); //remove client from list of client sockets
            }
        } elseif (($id !== null) && ($id >= $this->mc)) { //close all clients if id is too large
            for ($i = 0; $i < $this->mc; $i++) { //loop through client array
                if (isset($this->c[$i])) {
                    socket_close($this->c[$i]); //close existing client sockets
                    unset($this->c[$i]);
                }
            }
        }
        if (($id < 0) || ($id === null)) socket_close($this->s); //close master socket if id is negative or null
    }
    //function send called to send messages to all clients
    public function send($call, $id) {
        global $eol, $cli;
        if (!isset($this->c[$id])) {
            echo "[ERROR] client $id does not exist" . $eol;
            debug_print_backtrace();
            return false;
        }
        $data = array('call' => $call);
        if (func_num_args() > 2) $data['args'] = array_slice(func_get_args(), 2);
        $msg = $this->mask(json_encode($data));
        socket_write($this->c[$id], $msg, strlen($msg));
        return true;
    }
    public function sendAll($call) {
        global $eol, $cli;
        $data = array('call' => $call);
        if (func_num_args() > 1) $data['args'] = array_slice(func_get_args(), 1);
        $msg = $this->mask(json_encode($data));
        for ($i = 0; $i < $this->mc; $i++) {
            if ((@$this->c[$i] != null) && isset($this->c[$i])) socket_write($this->c[$i], $msg, strlen($msg));
        }
        return true;
    }
    //function on called to create socket events with callbacks
    public function bind($n, $f) { //event name and callback
        global $eol, $cli;
        $this->on[$n] = $f; //assign event name to callback in assoc array
        return true;
    }
    //function call called to run events created with on()
    public function call($n) { //event name, array args (true = load args from array, false = load args from hidden params)
        global $eol, $cli;
        //error handling
        if (func_num_args() < 1) {
            echo "[ERROR] function 'call()' requires an event name" . $eol;
            debug_print_backtrace();
            return false;
        } elseif (!isset($this->on[$n])) { //if given event is not defined, cannot be run
            echo "[ERROR] event '$n' does not exist" . $eol;
            debug_print_backtrace();
            return false; //error out of function
        }
        //get number of arguments for event callback
        $y = func_num_args() - 1; //get number of params passed in to call()
        $z = new ReflectionFunction($this->on[$n]); //get callback closure as reflection function
        $x = $z->getNumberOfRequiredParameters(); //get number of parameters callback requires
        if (($x != $y) && ($x + 1 != $y)) { //if parameter amounts don't match, event cannot be run
            echo "[ERROR] event '$n' must be given $x arguments, $y given" . $eol;
            debug_print_backtrace();
            return false; //error out of function
        }
        if ($y == 0) $this->on[$n](); //if event has no arguments, run event by simply calling callback
        else call_user_func_array($this->on[$n], array_slice(func_get_args(), 1));
        return true; //if function has not errored out and program has not died, succeed
    }
    public function callArr($n, $a) {
        global $eol, $cli;
        if (is_array($a)) $y = count($a); //if array passed in, arg num is length of array
        else {
            echo "[ERROR] param #2 of callArr() expected to be array -- use call() to pass in individual arguments" . $eol;
            debug_print_backtrace();
            return false;
        }
        if (!isset($this->on[$n])) { //if given event is not defined, cannot be run
            echo "[ERROR] event '$n' does not exist" . $eol;
            debug_print_backtrace();
            return false; //error out of function
        }
        $y = count($a); //get number of params passed in to call()
        $z = new ReflectionFunction($this->on[$n]); //get callback closure as reflection function
        $x = $z->getNumberOfRequiredParameters(); //get number of parameters callback requires
        if ($x != $y) { //if parameter amounts don't match, event cannot be run
            echo "[ERROR] event '$n' must be given $x arguments, $y given" . $eol;
            return false; //error out of function
        }
        call_user_func_array($this->on[$n], $a);
        return true;
    }
    //function onConn called to assign callback to or run event for user connection
    public function onConn($arg = null) {
        global $eol, $cli;
        if (!isset($arg)) {
            echo "[ERROR] event 'onConn' must be given client id or callback function" . $eol;
            debug_print_backtrace();
            return false; //error out of function
        }
        else if (is_callable($arg)) {
            $x = new ReflectionFunction($arg);
            if ($x->getNumberOfRequiredParameters() > 1) {
                echo "[ERROR] callback for event 'onConn' must have only 1 argument: client id" . $eol;
                debug_print_backtrace();
                return false;
            } else $this->ev['open'] = $arg;
        }
        else call_user_func_array($this->ev['open'], func_get_args());
    }
    //function onRun called to assign callback to or run event for server loop
    public function onRun($arg = null) {
        global $eol, $cli;
        if (!isset($arg)) $this->ev['run']();
        else if (is_callable($arg)) $this->ev['run'] = $arg;
        else call_user_func_array($this->ev['run'], func_get_args());
    }
    //function onRun called to assign callback to or run event for server loop
    public function onClose ($arg = null) {
        global $eol, $cli;
        if (!isset($arg)) {
            echo "[ERROR] event 'onClose' must be given client id or callback function" . $eol;
            debug_print_backtrace();
            return false; //error out of function
        }
        else if (is_callable($arg)) {
            $x = new ReflectionFunction($arg);
            if ($x->getNumberOfRequiredParameters() > 1) {
                echo "[ERROR] callback for event 'onClose' must have only 1 argument: client id" . $eol;
                debug_print_backtrace();
                return false;
            } else $this->ev['close'] = $arg;
        }
        else call_user_func_array($this->ev['close'], func_get_args());
    }
    //function log called to sanitize and log data
    public function log($text) {
        global $eol, $cli;
        //sanitize $text here!
        $name = $this->n ?: 'LOG';
        if (is_string($text)) echo "[$name] $text" . $eol;
        else {
            echo "[$name] non-string data: " . $eol;
            print_r($text);
        }
    }
    //function unmask called to unmask masked data received from client
    private function unmask($text) { //parameter text (masked string data) to be unmasked
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) $text .= $data[$i] ^ $masks[$i%4];
        return $text;
    }
    //function mask called to mask unmasked data to send to client
    private function mask($text) { //parameter text (unmasked string data) to be masked
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        if ($length <= 125) $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
        return $header.$text;
    }

    // TESTING ADMIN FEATURES
    //static function start called to execute a pocket server script
    public static function start($path) {
        ignore_user_abort(true);
        // $d = array(
        //     0 => array('pipe', 'r'),
        //     1 => array('pipe', 'w'),
        //     2 => array('pipe', 'w')
        // );
        // $process = proc_open("php $path -- web", $d, $pipes);
        // return array($process, $pipes);
    }
    //static function read called to read output from a pocket server started from start()
    public static function read($process) {
        echo str_pad('<span style = \'display: none;\'>', 4096, ' ');
        echo '</span>';
        @ob_flush();
        flush();
        // fclose($process[1][0]);
        // $pid = proc_get_status($process[0])['pid'];
        // file_put_contents('../server.txt', $pid);
        // while (file_exists('../server.txt') && (intval(file_get_contents('../server.txt')) == $pid)) {
        //     sleep(1);
        //     echo fread($process[1][1], 2096);
        //     @ob_flush();
        //     flush();
        // }
        // proc_close($process[0]);
        for ($i = 0; $i < 10; $i++) {
            echo $i . '<br/>';
            @ob_flush();
            flush();
            sleep(1);
        }
        // @ob_flush();
        // flush();
        ob_end_flush();
    }
    //static function stop called to stop a pocket server started from start()
    public static function stop($pid) {
        global $eol, $cli;
        $pid = intval($pid);
        posix_kill($pid, 15);
        if (!posix_getpgid($pid)) return 'process killed' . $eol;
        else return 'process not killed' . $eol;
    }
}

class PocketClient {
    protected $on;
    protected $socket;
    public function __construct() {
        $this->on = array();
    }
    public function bind($message, $function) {
        $this->on[$message] = $function;
    }
    public function connect($local, $domain, $port, $server) {
        $timeout = 5;
        $context = stream_context_create();
        $this->socket = @stream_socket_client(
            $domain . ':' . $port,
            $errno, $errstr, $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        stream_set_timeout($this->socket, $timeout);
        $key = $this->key();
        $headers = array(
            'host' => $domain . ":" . $port,
            'user-agent' => 'websocket-client-php',
            'connection' => 'Upgrade',
            'upgrade' => 'websocket',
            'sec-websocket-key' => $key,
            'sec-websocket-version' => '13',
        );
        $header = "GET /" . $server . " HTTP/1.1\r\n"
        . implode("\r\n", array_map(function($key, $value) {
                return "$key: $value";
            }, array_keys($headers), $headers)
        ) . "\r\n\r\n";
        fwrite($this->socket, $header);
        $response = stream_get_line($this->socket, 1024, "\r\n\r\n");
        if (!preg_match('#Sec-WebSocket-Accept:\s(.*)$#mUi', $response, $matches)) {
            return false;
        }
        $accept = trim($matches[1]);
        if ($accept !== base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')))) {
            return false;
        }
    }
    public function close() {
        fclose($this->socket);
        $this->socket = null;
    }
    public function send($message) {
        fwrite($this->socket, $this->mask('{"call":"' . $message . '"}'));
    }
    protected function key() {
        $key = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$&/()=[]{}0123456789';
        for ($i = 0; $i < 16; $i++)
            $key .= $chars[mt_rand(0, strlen($chars) - 1)];
        return base64_encode($key);
    }
    private function mask($text) { //parameter text (unmasked string data) to be masked
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        if ($length <= 125) $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
        return $header.$text;
    }
}

?>

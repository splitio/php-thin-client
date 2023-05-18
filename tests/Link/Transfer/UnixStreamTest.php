<?php

namespace SplitIO\Test\Link\Transfer;

use SplitIO\ThinClient\Link\Transfer\UnixStream;
use SplitIO\Test\Utils\SocketServerRemoteControl;

use PHPUnit\Framework\TestCase;

class UnixStreamTest extends TestCase
{
    private $socketServerRC;

    public function setUp(): void
    {
        $this->socketServerRC = new SocketServerRemoteControl();
    }

    public function testHappyExchange(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";

        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'expects' => 'another interaction',
                'returns' => 'another interaction response',
            ],
        ]);

        $this->socketServerRC->await();

        $realSock = new UnixStream($serverAddress);

        $realSock->sendMessage("something");
        $this->socketServerRC->await();
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $realSock->sendMessage("another interaction");
        $this->socketServerRC->await();
        $response = $realSock->readMessage();
        $this->assertEquals($response, "another interaction response");
    }


//    public function testConnectionBreaksBefore2ndInteraction(): void
//    {
//        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";
//
//        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
//            [
//                'expects' => 'something',
//                'returns' => 'something else',
//            ],
//            [
//                'actions' => [
//                    'pre' => 'break',
//                ],
//            ],
//        ]);
//
//        $this->socketServerRC->await();
//
//        $realSock = new UnixStream($serverAddress);
//
//        $realSock->sendMessage("something");
//        $this->socketServerRC->await();
//        $response = $realSock->readMessage();
//        $this->assertEquals($response, "something else");
//
//        $realSock->sendMessage("another interaction");
//        $this->socketServerRC->await();
//        $response = $realSock->readMessage();
//        $this->assertEquals($response, "another interaction response");
//    }

    public function tearDown(): void
    {
        $this->socketServerRC->shutdown();
    }


}
// class UnixStreamTest extends TestCase
// {
// 
//     private $server_script_path = __DIR__ . "/../../utils/stream_server.php";
//     private $server_socket_path;
// 
//     private $procHandle;
//     private $pipes = [];
// 
//     public function setUp(): void
//     {
//         $this->server_socket_path = sys_get_temp_dir() . "/php_thin_client_tests.sock";
//         if (file_exists($this->server_socket_path)) {
//             unlink($this->server_socket_path);
//         }
// 
//         $descs = [
//             0 => ['pipe', 'r'],
//             1 => ['pipe', 'w'],
//         ];
//         $this->procHandle = proc_open('php ' . $this->server_script_path, $descs, $this->pipes);
//         if (!is_resource($this->procHandle)) {
//             throw new \Exception("failed to create process");
//         }
//     }
// 
//     public function testHappyExchange(): void
//     {
//         fwrite($this->pipes[0], json_encode([
//             [
//                 "expect" => "something",
//                 "return" => "something else",
//                 "b64Encoded" => false,
//             ],
//         ]));
//         fclose($this->pipes[0]);
//         $res = fread($this->pipes[1], 2);
//         $this->assertEquals('OK', $res);
// 
//         $realSock = new UnixStream($this->server_socket_path);
//         $realSock->sendMessage("something");
//         $res = fread($this->pipes[1], 2);
//         $this->assertEquals('OK', $res);
//         
//         $response = $realSock->readMessage();
//         $this->assertEquals($response, "something else");
//     }
// 
//     public function tearDown(): void
//     {
//         proc_close($this->procHandle);
//     }
// }

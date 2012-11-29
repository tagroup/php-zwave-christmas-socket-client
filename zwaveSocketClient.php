<?php
/**
 * User: thomasloughlin
 * Date: 11/28/12
 * Time: 8:03 PM
 *
 * A quick, not well planned out, start to a reusable php zwave socket client class.
 *
 * TODO: proper error handling, more functions to show status
 *
 **/
class zwaveSocketClient
{
    private $socket;
    private $zwave_host;
    private $zwave_port;
    private $devices;

    /**
     * constructor that establishes the connection to the zwave server
     * @param string $zwave_host the host running the zwave server
     * @param int $zwave_port the port the host zwave server is accepting connections on
     * @return mixed
     */
    function __construct($zwave_host='localhost',$zwave_port=6004)
    {
        $this->zwave_host=$zwave_host;
        $this->zwave_port=$zwave_port;
        $this->socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            return "ERROR: socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }
        if (($result=socket_connect($this->socket, $this->zwave_host, $this->zwave_port)) === false) {
            return "ERROR: socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n";
        }
        $this->devices=$this->getDevices();
    }

    /**
     * used to send messages over the connected socket
     * @param string $in message sent to the zwave server
     * @return string response from the zwave server
     */
    function sendReceiveMessage($in)
    {

        socket_write($this->socket, $in, strlen($in));
        //TODO: should really change this to keep getting until \n
        $data=socket_read($this->socket, 2048,PHP_NORMAL_READ );
        return $data;
    }

    /**
     * update the device list dynamically
     *
     */
    function updateDevicesList()
    {
        $this->devices=$this->getDevices();
    }

    /**
     * sends the command to zwave server for a listing of all devices
     * @return array of zwave devices with statuses, groups, types, nodes
     */
    function getDevices()
    {
        $list=$this->sendReceiveMessage('ALIST');
        $list = substr($list, 0, strlen($list) - 1);  //doing this instead of trim

        $devicesList = explode("#", $list);
        $devices = array();
        $c=0;
        foreach ($devicesList as $device) {
            $device = explode("~", $device);

            $devices[$c]['name']=$device[1] ;
            $devices[$c]['node']=$device[2] ;
            $devices[$c]['group']=$device[3] ;
            $devices[$c]['type']=$device[4] ;
            if(strpos($device[5],'Basic=255')!==false)
            {
                $devices[$c]['status']='On';
            }
            if(strpos($device[5],'Basic=0')!==false)
            {
                $devices[$c]['status']='Off';
            }
            $c++;
        }

        //var_dump($devices);
        return $devices;
    }

    /**
     * sends a power level to a specific Binary Power Switch group name
     * @param string $group the name previously provided to link all of the nodes together
     * @param int $powerLevel the binary equivalent to on (255) or off (0)
     */
    function toggleBinaryGroup($group, $powerLevel=255)
    {

        for($i=0;$i<count($this->devices);$i++)
        {

            if($this->devices[$i]['group']==$group && $this->devices[$i]['type']=='Binary Power Switch')
            {
                $msg="DEVICE~" . $this->devices[$i]['node'] . "~" . $powerLevel . "~Binary Switch";

                $this->sendReceiveMessage($msg);

                sleep(1);
            }

        }
    }

    /**
     * closes the socket
     */
    function __destruct() {
        socket_close($this->socket);

    }
}


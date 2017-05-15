<?php

require_once('SubnetCalculator.php');

class Calculator {
  
    public function binaryToInt($binary = null) {
        if ($this->isBinaryValid($binary)) {
            $decimal = 0;
            $count = strlen($binary);
            $binary = strrev($binary);


            for ($i = 0; $i < $count; $i++) {
                if ($binary[$i] == "1") {
                    $decimal = $decimal + pow(2,$i);
                };
            };            
            return $decimal;
        } else {
          return false;
        }
    }

    public function intToBinary($decimal) {
        $binary = "";
        if ($this->isDecimalValid($decimal)) {
            $binary = $this->divDecimal($decimal);
            return $binary;
        } else {
            return false;
        }
    }

    public function divDecimal($decimal) {
        $binary = "";
        if ($decimal) {
            if ($decimal > 33554432) {
                return false;
            };
            while($decimal > 1) {
                $bit = $decimal % 2;
                $decimal = floor($decimal / 2);
                $binary = $binary.$bit;
            }
            $binary = $binary.$decimal;
        };
        return strrev($binary);
    }

    public function isBinaryValid($binary = null) {
        if ($binary) {
            if (preg_match("/^[0|1]+$/", $binary)) {
                return true;
            };
        };
        return false;
    }

    public function isDecimalValid($decimal = null) {
        if ($decimal) {
            if (preg_match("/^[0-9]+$/", $decimal)) {
                return true;
            };
        };
        return false;
    }

    public function isValidIpSegment($ip = null) {
        if (preg_match("/^(1[0-9][0-9]|2[0-5]{2}|[1-9][0-9]|[0-9])$/", $ip)) {
            if (strlen($ip) < 4) {
                return true;
            };
        };
        return false;
    }

    public function isValidIp($ip = null) {
        $valid = true;
        $ipArray = explode(".", $ip);
        foreach ($ipArray as $index => $segmentIP) {
            if (!$this->isValidIpSegment($segmentIP)) {
                $valid = false;
            }
        }

        return $valid;
    }

    public function isValidCidr($cidr = null) {
        if (strlen($cidr) <  4) {
            $cidr = str_replace('/', '', $cidr);
            if (preg_match("/^([0-2][0-9]|3[0-2]|[0-9])$/", $cidr)) {
                return true;
            };
        };

        return false;
    }

    public function isValidSubnet($subnet = null, $ip = null) {
        if (strlen($subnet) < 10 ) {
            $classIp = $this->identifyClass($ip);

            switch ($classIp) {
              case "A":
                    $maxSubnets = pow(2,23) -2;
                    if ($subnet > $maxSubnets) {
                        return false;
                    };
                break;
              case "B":
                    $maxSubnets = pow(2,15) - 2;
                    if ($subnet > $maxSubnets) {
                        return false;
                    };
                break;
              case "C":
                    $maxSubnets = pow(2,7) -2;
                    if ($subnet > $maxSubnets) {
                        return false;
                    };
                break;
              default:
                    return false;
                break;
            };

            return true;
        };
        return false;
    }

    public function isHostsValid($hosts = null) {
        if ($hosts) {
            if (preg_match("/^[0-9]+$/", $hosts)) {
                return true;
            };
        };

        return false;
    }

    public function identifyClass($ipArray = null) {
        if ($ipArray) {
            if (isset($ipArray[0])) {
                if (preg_match("/^(1|1[0-9]|1[0-2][0-6])$/", $ipArray[0])) {
                    return 'A';
                };
                if (preg_match("/^(1[2-8][0-9]|19[0-1])$/", $ipArray[0])) {
                    return 'B';
                };
                if (preg_match("/^(19[0-9]|22[0-3]|2[0-1][0-9])$/", $ipArray[0])) {
                    return 'C';
                };
                if (preg_match("/^(22[4-9]|23[0-9])$/", $ipArray[0])) {
                    return 'D';
                };
                if (preg_match("/^(24[0-9]|25[0-5])$/", $ipArray[0])) {
                    return 'E';
                };

                return false;
            };
        }
    }

    //calculate willCard
    public function wildcardMask($mask = null) {
        $a = [0,0,0,0];
        for($i=0; $i < 4; $i++){
            $a[$i] = 255 - $mask[$i];
        }
        return $a;
    }

    /*
    *@param ip string
    *@param cidr integer
    */
    public function calculateSubnet($ip, $cidr) {
        $calculator = new SubnetCalculator($ip, $cidr);
        $report = $calculator->getSubnetArrayReport();
        return $report;
    }
}

$messageInfo = '<div class="alert alert-success"><span><i>';
$messageDanger = '<div class="alert alert-danger"><span><i>';

/**  Main program **/

if (isset($_POST['numeroBinario']) || isset($_POST['numeroDecimal'])) {
    $calcu = new Calculator();
    $numeroBinario = $_POST['numeroBinario'];
    $numeroDecimal = $_POST['numeroDecimal'];

    if (!empty($numeroBinario)) {
        if ($calcu->isBinaryValid($numeroBinario)) {
            $decimal = $calcu->binaryToInt($numeroBinario);
            $messageInfo = $messageInfo."<div>El numero decimal de ".$numeroBinario." es: ".$decimal."</div>";
        } else {
            $messageDanger = $messageDanger."<div>El binario ".$numeroBinario." no es válido</div>";
        }
    }

    if (!empty($numeroDecimal)) {
        if ($calcu->isDecimalValid($numeroDecimal)) {
            $binary = $calcu->intToBinary($numeroDecimal);
            $messageInfo = $messageInfo."<div>El numero binario de ".$numeroDecimal." es: ".$binary."</div>";
        } else {
            $messageDanger = $messageDanger."<div>El decimal ".$numeroDecimal." no es válido </div>";
        }
    }
}

if (isset($_POST['ip']) && isset($_POST['cidr'])) {
    $calcu = new Calculator();
    $ip = $_POST['ip'];
    $cidr = $_POST['cidr'];

    if ($calcu->isValidIp($ip) && $calcu->isValidCidr($cidr)) {
        $cidr = str_replace("/", "", $cidr);
        $subnetReport = $calcu->calculateSubnet($ip, $cidr);
        $messageInfo = $messageInfo."<div>IP y cidr: ".$subnetReport["ip_address_cidr_notation"]."</div>";
        $messageInfo = $messageInfo."<div>IP en binario: ".$subnetReport["ip_address"]["binary"]."</div>";
        $messageInfo = $messageInfo."<div>Mascara de red: ".$subnetReport["subnet_mask"]["quads"]."</div>";
        $messageInfo = $messageInfo."<div>Mascara de red en binario: ".$subnetReport["subnet_mask"]["binary"]."</div>";
        $messageInfo = $messageInfo."<div>Network: ".$subnetReport["network_portion"]["quads"]."</div>";
        $messageInfo = $messageInfo."<div>Direcciones ip: ".$subnetReport["number_of_ip_addresses"]."</div>";
        $messageInfo = $messageInfo."<div>Numero de hosts asignables: ".$subnetReport["number_of_addressable_hosts"]."</div>";
        $messageInfo = $messageInfo."<div>Rango de ips: ";
        foreach ($subnetReport["ip_address_range"] as $index => $ip) {
            $messageInfo = $messageInfo." ".$ip."   ";
        }
        $messageInfo = $messageInfo."</div>";
        $messageInfo = $messageInfo."<div>Dirección broadcast : ".$subnetReport["broadcast_address"]."</div>";
    } else {
        $messageDanger = $messageDanger."<div>ip: ".$ip." o cidr: ".$cidr." no válido </div>";
    }
}

if (isset($_POST['classIp'])) {
    $calcu = new Calculator();
    $ip = $_POST['classIp'];
    if ($calcu->isValidIp($ip)) {
        $ipArray = explode(".", $_POST['classIp']);
        $class = $calcu->identifyClass($ipArray);
        $messageInfo = $messageInfo."<div>La ip: ".$ip." es de clase: ".$class."</div>";
    } else {
        $messageDanger = $messageDanger."<div>La ip: ".$ip." no es válida </div>";
    }
}

$messageInfo = $messageInfo.'</i></span></div>';
$messageDanger = $messageDanger.'</i></span></div>';

if (strlen($messageDanger) > 59 ) {
    echo $messageDanger;
} else {
    echo $messageInfo;
}